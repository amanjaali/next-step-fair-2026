<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * One bullet under the Expo or Conference card on the home page.
 */
class HomeTrackPoint extends Model
{
    use HasTranslatableContent;

    public const TRACK_FAIR = 'fair';

    public const TRACK_CONFERENCE = 'conference';

    public array $translatable = ['label'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeForTrack(Builder $query, string $track): Builder
    {
        return $query->where('track', $track);
    }
}
