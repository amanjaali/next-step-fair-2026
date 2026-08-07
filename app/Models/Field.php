<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * One field of study, in the vocabulary both sides use.
 *
 * A student picks fields as intentions; an institution attaches them to the levels
 * it teaches. Because it is the same row on both sides, demand and supply can be
 * counted against each other — which is the whole point.
 */
class Field extends Model
{
    use HasTranslatableContent;

    public array $translatable = ['name'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    /** Students who named this field, with the rank they gave it. */
    public function registrations(): BelongsToMany
    {
        return $this->belongsToMany(Registration::class, 'field_registration')
            ->withPivot('rank')
            ->withTimestamps();
    }

    /** Institutions that teach it, with level, language, fee and capacity. */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'field_organization')
            ->withPivot(['level', 'language', 'tuition_min', 'tuition_max', 'scholarship', 'capacity'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
