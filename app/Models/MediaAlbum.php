<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaAlbum extends Model
{
    use HasTranslatableContent;

    public array $translatable = ['title', 'description'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['published' => 'boolean'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(MediaItem::class)->orderBy('sort');
    }

    public function photos(): HasMany
    {
        return $this->items()->where('type', 'photo');
    }
}
