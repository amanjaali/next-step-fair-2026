<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * OTPIQ WhatsApp account settings — credentials, webhook secret and badge flags.
 *
 * The app expects a single row. OtpiqSettingsSeeder creates it; edit the row
 * (or re-run the seeder) when keys or the public badge URL change.
 */
class OtpiqSetting extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'send_header_image' => 'boolean',
            'send_button_link' => 'boolean',
            'verify_ssl' => 'boolean',
        ];
    }

    public static function current(): ?self
    {
        return static::query()->first();
    }
}
