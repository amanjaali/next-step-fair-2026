<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * OTPIQ / Meta WhatsApp template identity — one row per logical key and language.
 *
 * Stores the dashboard template name and provider id (both editable in Filament).
 * Body variable order and the header-image flag live in config/whatsapp.php.
 */
class OtpiqTemplate extends Model
{
    protected $guarded = ['id'];

    public static function defaultName(string $logicalKey, string $locale): string
    {
        return "{$logicalKey}_{$locale}_2026";
    }
}
