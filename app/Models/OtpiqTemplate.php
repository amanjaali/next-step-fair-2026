<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * OTPIQ / Meta WhatsApp template identity — one row per logical key and language.
 *
 * Stores the dashboard name, provider id, and the send shape (body slot names,
 * header image, URL button) so each row matches what OTPIQ approved.
 */
class OtpiqTemplate extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'body_variables' => 'array',
            'send_header' => 'boolean',
            'send_button' => 'boolean',
        ];
    }

    public static function defaultName(string $logicalKey, string $locale): string
    {
        return "{$logicalKey}_{$locale}_2026";
    }

    /** @return array<int, string> */
    public function bodySlotNames(): array
    {
        if (is_array($this->body_variables)) {
            return $this->body_variables;
        }

        $fromConfig = config("whatsapp.otpiq_templates.{$this->logical_key}.{$this->locale}.body");

        return is_array($fromConfig) ? $fromConfig : [];
    }

    public function wantsHeaderImage(): bool
    {
        if ($this->send_header !== null) {
            return $this->send_header;
        }

        return (bool) config("whatsapp.otpiq_templates.{$this->logical_key}.{$this->locale}.header", true);
    }

    public function wantsButtonLink(): bool
    {
        if ($this->send_button !== null) {
            return $this->send_button;
        }

        return (bool) config("whatsapp.otpiq_templates.{$this->logical_key}.{$this->locale}.button", false);
    }
}
