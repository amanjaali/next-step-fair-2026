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
            'departments' => 'array',
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

    /**
     * Scholarship opportunities that can stand in for a National Scholarship
     * Program university: live, linked to a partner organisation, with at
     * least one department/seats row filled in.
     */
    public function scopeScholarshipUniversities(Builder $query): Builder
    {
        return $query->live()
            ->where('kind', self::KIND_SCHOLARSHIP)
            ->whereNotNull('organization_id')
            ->whereNotNull('departments')
            ->with('organization');
    }

    /* ------------------------------------------------------------ helpers -- */

    public function partner(): string
    {
        return $this->organization?->t('name') ?: (string) $this->partner_name;
    }

    public function logo(): ?string
    {
        if (filled($this->partner_logo_path)) {
            return ns_uploaded($this->partner_logo_path);
        }

        return $this->organization?->logoUrl();
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

    /**
     * The same shape config('scholarship.universities') uses, so the
     * application form's university/department dropdowns can mix
     * dashboard-added scholarships in with the hand-curated founding partners
     * without the view knowing which source a given entry came from.
     *
     * Fields the organisation record doesn't carry (founding year, housing,
     * a written "about" paragraph) fall back to something reasonable rather
     * than a missing-translation key or a blank — this partner just hasn't
     * had that extra profile detail added yet.
     */
    public function toScholarshipUniversityArray(): array
    {
        $org = $this->organization;

        return [
            'slug' => 'opportunity-'.$this->slug,
            'name' => $org?->t('name') ?: $this->partner(),
            // Left null (not '') when the partner has no city on file: the
            // regional filter on the scholarship pages checks for null, and an
            // empty string would pass that check and misreport as "has a city".
            'city' => $org?->city ?: null,
            'language' => is_array($org?->languages)
                ? collect($org->languages)->map(fn ($code) => config("nextstep.locales.$code.label", $code))->implode(' & ')
                : '',
            'tier' => 'donor',
            'founded' => $org?->since_year,
            'housing' => 'none',
            // `summary` is plain text and safe to print escaped, like the rest of
            // this array; `body` is a rich-text editor field, so it is stripped
            // rather than trusted, and only used when there is nothing else.
            'about' => $this->t('summary') ?: strip_tags((string) $this->t('body')),
            'departments' => collect($this->departments ?? [])
                ->map(fn ($d) => [
                    'name' => $d['name'] ?? '',
                    'seats' => (int) ($d['seats'] ?? 0),
                    'requirements' => self::translatedDepartmentText($d['requirements'] ?? null),
                    'requires_form' => (bool) ($d['requires_form'] ?? false),
                ])
                ->values()->all(),
        ];
    }

    /**
     * A department row's requirements, in the current language.
     *
     * Stored per-locale (`['en' => ..., 'ku' => ..., 'ar' => ...]`) since the
     * admin form added translation tabs for it, falling back to English. A
     * bare string is also accepted — rows saved before translation tabs
     * existed there still render instead of silently vanishing.
     */
    private static function translatedDepartmentText(mixed $value): ?string
    {
        if (is_string($value)) {
            return filled($value) ? $value : null;
        }

        if (! is_array($value)) {
            return null;
        }

        $text = $value[app()->getLocale()] ?? $value[config('app.fallback_locale')] ?? null;

        return filled($text) ? $text : null;
    }
}
