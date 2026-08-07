<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booth extends Model
{
    use HasTranslatableContent;

    public array $translatable = ['name', 'kind'];

    protected $guarded = ['id'];

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
