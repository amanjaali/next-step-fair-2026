<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One student's application for one cycle of the National Scholarship Program.
 *
 * Status moves in one direction only: draft → submitted → screening → shortlisted
 * → interview → decided. Nothing skips a stage, and nothing goes back, so the
 * applicant sees a queue they can trust rather than a state that reshuffles.
 */
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
}
