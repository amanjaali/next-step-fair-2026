<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasTranslatableContent;

    public array $translatable = ['name'];

    protected $guarded = ['id'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
