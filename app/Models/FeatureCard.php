<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Model;

/**
 * "Why attend" cards. The icons are solid bar compositions taken from the logo's
 * Step element — right angles only, one magenta bar each.
 */
class FeatureCard extends Model
{
    use HasTranslatableContent;

    public array $translatable = ['title', 'body'];

    protected $guarded = ['id'];
}
