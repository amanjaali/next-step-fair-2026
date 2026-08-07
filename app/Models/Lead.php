<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Contact, "exhibit with us" and sponsorship enquiries. */
class Lead extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
