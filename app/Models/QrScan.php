<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrScan extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
            'is_unique' => 'boolean',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(QrCampaign::class, 'qr_campaign_id');
    }
}
