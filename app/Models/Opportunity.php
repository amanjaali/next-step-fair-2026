<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Something a registered student can go after: a scholarship, an offer, a place.
 *
 * These come from partners — the ministry, a university, an employer — and they
 * are the answer to "what do I actually get for registering?". Which is why they
 * are shown to somebody who has registered and not to somebody who has not: an
 * offer dangled in front of a stranger is an advert, and the same offer shown to
 * a student who has an account is a reason the account was worth making.
 */
class Opportunity extends Model
{
    use HasTranslatableContent;

    public const KIND_SCHOLARSHIP = 'scholarship';

    public const KIND_OFFER = 'offer';

    public const KIND_PROGRAMME = 'programme';

    public const KIND_INTERNSHIP = 'internship';

    public const KIND_WORKSHOP = 'workshop';

    public const AUDIENCE_STUDENTS = 'students';

    public const AUDIENCE_GRADE12 = 'grade12';

    public const AUDIENCE_PARENTS = 'parents';

    public const AUDIENCE_EVERYONE = 'everyone';

    public array $translatable = ['title', 'summary', 'body', 'eligibility', 'action_label'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'opens_at' => 'date',
            'closes_at' => 'date',
            'featured' => 'boolean',
            'published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $opportunity) {
            $opportunity->slug = $opportunity->slug
                ?: Str::slug($opportunity->getTranslation('title', 'en', false) ?: Str::random(8));
        });
    }

    /** @return list<string> */
    public static function kinds(): array
    {
        return [
            self::KIND_SCHOLARSHIP, self::KIND_OFFER, self::KIND_PROGRAMME,
            self::KIND_INTERNSHIP, self::KIND_WORKSHOP,
        ];
    }

    /** @return list<string> */
    public static function audiences(): array
    {
        return [
            self::AUDIENCE_STUDENTS, self::AUDIENCE_GRADE12,
            self::AUDIENCE_PARENTS, self::AUDIENCE_EVERYONE,
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /* ------------------------------------------------------------- scopes -- */

    /**
     * Live: published, open, and not already closed.
     *
     * An expired opportunity is worse than none — it tells a student they were
     * too late for something nobody has taken down.
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('published', true)
            ->where(fn ($q) => $q->whereNull('opens_at')->orWhere('opens_at', '<=', today()))
            ->where(fn ($q) => $q->whereNull('closes_at')->orWhere('closes_at', '>=', today()));
    }

    /** What this particular person should be shown. */
    public function scopeFor(Builder $query, ?Registration $registration): Builder
    {
        $audiences = [self::AUDIENCE_EVERYONE];

        if ($registration?->type === Registration::TYPE_STUDENT) {
            $audiences[] = self::AUDIENCE_STUDENTS;

            if (in_array($registration->education_stage, ['grade12', 'graduate'], true)) {
                $audiences[] = self::AUDIENCE_GRADE12;
            }
        }

        if ($registration?->type === Registration::TYPE_PARENT) {
            $audiences[] = self::AUDIENCE_PARENTS;
        }

        return $query->whereIn('audience', $audiences);
    }

    /**
     * Featured first, then the hand-set order, then by deadline.
     *
     * Plain `orderBy('closes_at')` sorts nulls to the front on both MySQL and
     * SQLite, which would put an offer with no deadline above one closing on
     * Friday. The ones with a clock on them go first.
     */
    public function scopeRanked(Builder $query): Builder
    {
        return $query->orderByDesc('featured')
            ->orderBy('sort')
            ->orderByRaw('closes_at is null, closes_at');
    }

    /* ------------------------------------------------------------ helpers -- */

    public function partner(): string
    {
        return $this->organization?->t('name') ?: (string) $this->partner_name;
    }

    public function logo(): ?string
    {
        $path = $this->partner_logo_path ?: $this->organization?->logo_path;

        return $path ? asset(str_starts_with($path, 'assets/') ? $path : 'storage/'.$path) : null;
    }

    /** Days left, or null when it never closes. */
    public function daysLeft(): ?int
    {
        return $this->closes_at ? (int) today()->diffInDays($this->closes_at, false) : null;
    }

    /**
     * Closing within a fortnight, which is when saying so starts to be useful
     * rather than nagging.
     */
    public function isClosingSoon(): bool
    {
        $left = $this->daysLeft();

        return $left !== null && $left >= 0 && $left <= 14;
    }

    public function actionLabel(): string
    {
        return $this->t('action_label') ?: __('opportunities.default_action');
    }
}
