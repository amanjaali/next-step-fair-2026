<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A broad area of study — health, engineering, business.
 *
 * Sectors exist because the two sides of the fair think at different resolutions.
 * A student says "something in health"; a university lists "BSc Nursing". Reporting
 * needs to roll one up to the other.
 */
class Sector extends Model
{
    use HasTranslatableContent;

    public array $translatable = ['name'];

    protected $guarded = ['id'];

    public function fields(): HasMany
    {
        return $this->hasMany(Field::class)->orderBy('sort');
    }
}
