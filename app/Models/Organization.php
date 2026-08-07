<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Universities, institutes, exhibitors, partners and sponsors.
 *
 * `kind` separates the directory from the sponsor wall; `tier` orders the wall.
 */
class Organization extends Model
{
    use HasTranslatableContent;

    public const KIND_UNIVERSITY = 'university';

    public const KIND_INSTITUTE = 'institute';

    public const KIND_EXHIBITOR = 'exhibitor';

    public const KIND_STRATEGIC = 'strategic';

    public const KIND_SUPPORTER = 'supporter';

    public const KIND_SPONSOR = 'sponsor';

    public const KIND_MEDIA = 'media';

    public array $translatable = ['name', 'description', 'badge'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'degree_levels' => 'array',
            'languages' => 'array',
            'campus_countries' => 'array',
            'scholarship_notes' => 'array',
            'entry_requirements' => 'array',
            'recruitment_goals' => 'array',
            'offers_scholarships' => 'boolean',
            'application_deadline' => 'date',
            'claimed_at' => 'datetime',
            'profile_completed_at' => 'datetime',
        ];
    }

    /* ------------------------------------------------- recruitment profile -- */

    /** Fields taught, with level, language, fee band and capacity per row. */
    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(Field::class, 'field_organization')
            ->withPivot(['level', 'language', 'tuition_min', 'tuition_max', 'scholarship', 'capacity'])
            ->withTimestamps();
    }

    public function staff(): HasMany
    {
        return $this->hasMany(InstitutionUser::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchScore::class)->orderByDesc('score');
    }

    /**
     * True once there is enough here to match a student against.
     *
     * An institution with no programmes listed cannot be recommended to anyone, so
     * it is left out of matching rather than recommended on name alone.
     */
    public function canBeMatched(): bool
    {
        return $this->published && $this->fields()->exists();
    }

    /** Percentage of the recruitment profile filled in, for the portal nudge. */
    public function profileCompleteness(): int
    {
        $answered = collect([
            $this->fields()->exists(),
            filled($this->degree_levels),
            filled($this->languages),
            filled($this->campus_countries),
            filled($this->tuition_min) || filled($this->tuition_max),
            filled($this->min_grade_band),
            filled($this->contact_email),
            filled($this->description),
        ])->filter()->count();

        return (int) round($answered / 8 * 100);
    }

    /** Institutions that have listed programmes and are visible to students. */
    public function scopeMatchable(Builder $query): Builder
    {
        return $query->where('published', true)
            ->whereIn('kind', [self::KIND_UNIVERSITY, self::KIND_INSTITUTE])
            ->whereHas('fields');
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeOfKind(Builder $query, string|array $kind): Builder
    {
        return $query->whereIn('kind', (array) $kind);
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function isAcademic(): bool
    {
        return in_array($this->kind, [self::KIND_UNIVERSITY, self::KIND_INSTITUTE], true);
    }
}
