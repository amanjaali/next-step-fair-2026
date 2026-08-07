<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hall extends Model
{
    use HasTranslatableContent;

    public array $translatable = ['name', 'description', 'meta'];

    protected $guarded = ['id'];

    public function booths(): HasMany
    {
        return $this->hasMany(Booth::class)->orderBy('sort');
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(EventSession::class);
    }
}
