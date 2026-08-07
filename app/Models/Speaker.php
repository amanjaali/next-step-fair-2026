<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Speaker extends Model
{
    use HasTranslatableContent;

    public array $translatable = ['name', 'role', 'organization', 'bio'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'topics' => 'array',
            'links' => 'array',
            'featured' => 'boolean',
            'published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $speaker) {
            $speaker->slug = $speaker->slug ?: Str::slug($speaker->getTranslation('name', 'en', false) ?: Str::random(8));
        });
    }

    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(EventSession::class, 'event_session_speaker', 'speaker_id', 'session_id')
            ->withPivot('role', 'sort')
            ->withTimestamps()
            ->orderBy('day')
            ->orderBy('starts_at');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function accent(): string
    {
        return ns_track_accent($this->track);
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? asset('storage/'.$this->photo_path) : null;
    }

    public function countryName(): string
    {
        return match ($this->country) {
            'IQ' => 'Iraq',
            'TR' => 'Türkiye',
            'UK', 'GB' => 'United Kingdom',
            'US' => 'United States',
            'DE' => 'Germany',
            'FR' => 'France',
            default => $this->country,
        };
    }

    /**
     * A generated stub so a speaker with no written biography still gets a page
     * rather than an empty column.
     */
    public function bioParagraphs(): array
    {
        $bio = trim(strip_tags($this->t('bio')));

        if ($bio !== '') {
            return preg_split('/\n\s*\n|<\/p>/', $this->t('bio')) ?: [$this->t('bio')];
        }

        return [
            __(':name is :role at :org, and joins the :year programme as a :type.', [
                'name' => $this->t('name'),
                'role' => Str::lower($this->t('role')),
                'org' => $this->t('organization'),
                'year' => $this->year,
                'type' => Str::lower($this->speaker_type),
            ]),
        ];
    }
}
