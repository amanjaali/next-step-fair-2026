<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * OTPIQ / Meta WhatsApp template identity — one row per logical key and language.
 *
 * Stores only the dashboard name and provider id. Body variable order and the
 * header-image flag live in config/whatsapp.php.
 */
class OtpiqTemplate extends Model
{
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::saving(function (OtpiqTemplate $template) {
            $template->name = $template->dashboardName();
        });
    }

    public function dashboardName(): string
    {
        return "{$this->logical_key}_{$this->locale}_2026";
    }
}
