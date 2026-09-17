<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A university that has pledged seats to the National Scholarship Program.
 *
 * Separate from the fair directory: seats are pledged per department for this
 * cycle, and a university can hold them without exhibiting. The catalogue is
 * edited from the dashboard so seat counts can change without a deploy.
 */
class ScholarshipUniversity extends Model
{
    use HasTranslatableContent;

    public const TIER_FOUNDING = 'founding';

    public const TIER_DONOR = 'donor';

    public const HOUSING_INCLUDED = 'included';

    public const HOUSING_CONTRIBUTION = 'contribution';

    public const HOUSING_NONE = 'none';

    public array $translatable = ['about'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'founded' => 'integer',
            'students' => 'integer',
            'sort' => 'integer',
            'published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $university) {
            $university->slug = $university->slug
                ?: Str::slug($university->name ?: Str::random(8));
        });
    }

    /** @return list<string> */
    public static function tiers(): array
    {
        return [self::TIER_FOUNDING, self::TIER_DONOR];
    }

    /** @return list<string> */
    public static function housingOptions(): array
    {
        return [self::HOUSING_INCLUDED, self::HOUSING_CONTRIBUTION, self::HOUSING_NONE];
    }

    public function departments(): HasMany
    {
        return $this->hasMany(ScholarshipUniversityDepartment::class)->orderBy('sort');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort')->orderBy('name');
    }

    public function seatsTotal(): int
    {
        return (int) $this->departments->sum('seats');
    }

    /**
     * The shape the public pages and the application form already expect.
     *
     * Keeping the array contract means the blades stay simple and applications
     * continue to store university/department names as plain strings.
     *
     * @return array{
     *     slug: string,
     *     name: string,
     *     city: string,
     *     language: string,
     *     tier: string,
     *     founded: int|null,
     *     students: int|null,
     *     housing: string,
     *     about: string,
     *     departments: list<array{name: string, seats: int}>
     * }
     */
    public function toCatalogArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'city' => $this->city,
            'language' => $this->language,
            'tier' => $this->tier,
            'founded' => $this->founded,
            'students' => $this->students,
            'housing' => $this->housing,
            'about' => $this->t('about'),
            'departments' => $this->departments
                ->map(fn (ScholarshipUniversityDepartment $d) => [
                    'name' => $d->name,
                    'seats' => (int) $d->seats,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * Published universities, ready for the public site and the apply form.
     *
     * @return list<array<string, mixed>>
     */
    public static function catalog(): array
    {
        return static::query()
            ->published()
            ->ordered()
            ->with('departments')
            ->get()
            ->map->toCatalogArray()
            ->all();
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::query()
            ->published()
            ->where('slug', $slug)
            ->with('departments')
            ->first();
    }
}
