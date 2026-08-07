<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Model;

/** One of the five goals Next Step reports against, in official UN colours. */
class SdgGoal extends Model
{
    use HasTranslatableContent;

    public array $translatable = ['title', 'what', 'detail', 'metric'];

    protected $guarded = ['id'];
}
