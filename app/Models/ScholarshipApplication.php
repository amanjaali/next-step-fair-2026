<?php

namespace App\Models;

use App\Observers\ScholarshipApplicationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One student's application for one cycle of the National Scholarship Program.
 *
 * Status moves in one direction only: draft → submitted → screening → shortlisted
 * → interview → decided. Nothing skips a stage, and nothing goes back, so the
 * applicant sees a queue they can trust rather than a state that reshuffles.
 *
 * Every move is told to the student who is waiting on it — see
 * ScholarshipApplicationObserver.
 */
#[ObservedBy(ScholarshipApplicationObserver::class)]
class ScholarshipApplication extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_SCREENING = 'screening';

    public const STATUS_SHORTLISTED = 'shortlisted';

    public const STATUS_INTERVIEW = 'interview';

    public const STATUS_DECIDED = 'decided';

    public const STATUS_WITHDRAWN = 'withdrawn';

    public const DECISION_AWARDED = 'awarded';

    public const DECISION_RESERVE = 'reserve';

    public const DECISION_DECLINED = 'declined';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'eligibility' => 'array',
            'documents' => 'array',
            'exam_average' => 'decimal:2',
            'eligibility_passed_at' => 'datetime',
            'submitted_at' => 'datetime',
            'screened_at' => 'datetime',
            'shortlisted_at' => 'datetime',
            'interviewed_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function scopeForCycle(Builder $query, ?string $cycle = null): Builder
    {
        return $query->where('cycle', $cycle ?? config('scholarship.cycle'));
    }

    /* --------------------------------------------------------- eligibility -- */

    /**
     * Whether the five answers clear the programme's rules.
     *
     * A single fail closes it. Warnings — results still pending, a document not
     * yet in hand — let the student through with something to sort out, because
     * both are normal in September and neither is a reason to stop them writing.
     */
    public function eligibilityVerdict(): string
    {
        $answers = $this->eligibility ?? [];

        $verdict = 'pass';

        foreach (config('scholarship.eligibility') as $question) {
            $answer = $answers[$question['id']] ?? null;

            if ($answer === null) {
                return 'incomplete';
            }
            if (in_array($answer, $question['fail'], true)) {
                return 'fail';
            }
            if (in_array($answer, $question['warn'], true)) {
                $verdict = 'warn';
            }
        }

        return $verdict;
    }

    public function hasPassedEligibility(): bool
    {
        return in_array($this->eligibilityVerdict(), ['pass', 'warn'], true);
    }

    /** The application form only opens once the check is behind them. */
    public function isOpen(): bool
    {
        return $this->hasPassedEligibility() && $this->status === self::STATUS_DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    /* -------------------------------------------------------------- region -- */

    public function regionName(): ?string
    {
        return config("scholarship.regions.{$this->region_code}.name");
    }

    public function seatsInRegion(): int
    {
        return (int) config("scholarship.regions.{$this->region_code}.seats", 0);
    }

    /* ------------------------------------------------------------ progress -- */

    /**
     * How much of the four steps is filled in, for the bar on the form.
     *
     * Counted from what is actually on the record rather than which step they
     * last saw, so closing the browser halfway does not inflate it.
     */
    public function completeness(): int
    {
        $done = collect([
            filled($this->region_code) && filled($this->district),
            filled($this->exam_average) || $this->exam_status === 'pending',
            filled($this->statement) && filled($this->proposal),
            count($this->documents ?? []) >= count(config('scholarship.documents')),
        ])->filter()->count();

        return (int) round($done / 4 * 100);
    }

    /** The stages, in order, with the one they are at marked. */
    public function timeline(): array
    {
        $order = [
            self::STATUS_SUBMITTED,
            self::STATUS_SCREENING,
            self::STATUS_SHORTLISTED,
            self::STATUS_INTERVIEW,
            self::STATUS_DECIDED,
        ];

        $at = array_search($this->status, $order, true);

        return collect($order)->map(fn ($stage, $i) => [
            'key' => $stage,
            'state' => $at === false ? 'todo' : ($i < $at ? 'done' : ($i === $at ? 'now' : 'todo')),
        ])->all();
    }

    /* ------------------------------------------------------- the committee -- */

    /** The stages a committee moves an application through, in order. */
    public const REVIEW_STAGES = [
        self::STATUS_SUBMITTED,
        self::STATUS_SCREENING,
        self::STATUS_SHORTLISTED,
        self::STATUS_INTERVIEW,
        self::STATUS_DECIDED,
    ];

    /**
     * Everything a committee should be looking at.
     *
     * A draft is a student still writing. It is not withheld from the dashboard
     * out of secrecy — it is simply not an application yet, and mixing the two
     * would make the queue lie about how many there are to read.
     */
    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->whereNotNull('submitted_at');
    }

    /**
     * Move an application to a stage, stamping when it happened.
     *
     * The timestamps are what the student's tracker reads, so setting the status
     * without one would show a stage as reached with no date against it. Going
     * backwards is allowed here and nowhere else: a committee that screens
     * something by mistake has to be able to put it back.
     */
    public function advanceTo(string $status, ?string $decision = null): void
    {
        $stamps = [
            self::STATUS_SCREENING => 'screened_at',
            self::STATUS_SHORTLISTED => 'shortlisted_at',
            self::STATUS_INTERVIEW => 'interviewed_at',
            self::STATUS_DECIDED => 'decided_at',
        ];

        $changes = ['status' => $status];

        if (isset($stamps[$status]) && $this->{$stamps[$status]} === null) {
            $changes[$stamps[$status]] = now();
        }

        if ($status === self::STATUS_DECIDED && $decision !== null) {
            $changes['decision'] = $decision;
        }

        $this->forceFill($changes)->save();
    }

    /** The next stage after this one, or null at the end of the queue. */
    public function nextStage(): ?string
    {
        $at = array_search($this->status, self::REVIEW_STAGES, true);

        return $at === false ? null : (self::REVIEW_STAGES[$at + 1] ?? null);
    }

    /**
     * The three scores, added up out of 100.
     *
     * Kept as a method rather than a stored column that can drift: the total is
     * always the sum of what the committee actually entered.
     */
    public function scoreTotal(): ?float
    {
        $scores = array_filter([
            $this->score_academic,
            $this->score_feasibility,
            $this->score_interview,
        ], fn ($s) => $s !== null);

        return $scores === [] ? null : round(array_sum(array_map('floatval', $scores)), 2);
    }

    /** The applicant's name, for a list that is read by people. */
    public function applicantName(): string
    {
        return $this->registration?->full_name ?? '—';
    }
}
