<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One row per registrant per event day, with the staff member who scanned it. */
class CheckIn extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'synced_at' => 'datetime',
            'day' => 'integer',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
