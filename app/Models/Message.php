<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One row per outbound message, from queued through to delivered or failed. */
class Message extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENT = 'sent';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_READ = 'read';

    public const STATUS_FAILED = 'failed';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'recipient' => 'encrypted',
            'payload' => 'array',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $message) {
            if ($message->isDirty('recipient') && $message->recipient) {
                $message->recipient_hash = Registration::hashValue(strtolower($message->recipient));
            }
        });
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(Broadcast::class);
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}
