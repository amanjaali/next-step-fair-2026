<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One tap of an exhibitor's desk QR, by whoever's phone it was.
 *
 * Unlike {@see Interaction}, `registration_id` is nullable and every scan is
 * kept — a visitor who never signs in still counts, they just can't be named.
 */
class BoothScan extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'day' => 'integer',
            'scanned_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
