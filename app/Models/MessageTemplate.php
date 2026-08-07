<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A message body per key, channel and language.
 *
 * `meta_template_name` and `approval_status` mirror the state of the matching
 * template inside the Meta Business Manager, because nothing can be sent on the
 * WhatsApp track until Meta has approved that exact name in that exact language.
 */
class MessageTemplate extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'approved_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** Substitutes :name style placeholders with the given values. */
    public function render(array $values = []): string
    {
        $body = $this->body;

        foreach ($values as $key => $value) {
            $body = str_replace(':'.$key, (string) $value, $body);
        }

        return $body;
    }
}
