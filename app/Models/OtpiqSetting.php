<?php

namespace App\Models;

use App\Services\Messaging\OtpiqConfigRegistry;
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

    protected static function booted(): void
    {
        static::saved(fn () => OtpiqConfigRegistry::flush());
        static::deleted(fn () => OtpiqConfigRegistry::flush());
    }

    public static function current(): ?self
    {
        return static::query()->first();
    }

    public static function singleton(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'base_url' => 'https://api.otpiq.com/api',
            'public_url' => 'https://www.nextstepfair.com',
            'verify_ssl' => true,
        ]);
    }
}
