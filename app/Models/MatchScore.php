<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A stored match between one student and one institution.
 *
 * Stored rather than computed per request for two reasons. A recruiter's list must
 * not silently reshuffle between page loads, and the number a student was shown in
 * September has to be the number the post-event report explains. `reasons` keeps
 * the components so a match can be justified rather than asserted.
 */
class MatchScore extends Model
{
    protected $table = 'matches';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'reasons' => 'array',
            'computed_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeStrong(Builder $query, int $min = 60): Builder
    {
        return $query->where('score', '>=', $min);
    }

    /** Strong / good / possible — plain words for a number nobody asked to see. */
    public function band(): string
    {
        return match (true) {
            $this->score >= 75 => 'strong',
            $this->score >= 55 => 'good',
            default => 'possible',
        };
    }
}
