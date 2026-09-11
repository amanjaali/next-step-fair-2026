<?php

namespace Database\Seeders;

use App\Models\OtpiqSetting;
use Illuminate\Database\Seeder;

/**
 * OTPIQ account credentials and badge send flags.
 *
 * Non-sensitive values are hardcoded here. Secrets (api_key, webhook_secret)
 * come from .env on the server — never commit those. After deploy:
 * php artisan db:seed --class=OtpiqSettingsSeeder
 */
class OtpiqSettingsSeeder extends Seeder
{
    public function run(): void
    {
        OtpiqSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'base_url' => 'https://api.otpiq.com/api',
                'api_key' => env('OTPIQ_API_KEY'),
                'account_id' => '68d4ec30e8ae7f92a41012f8',
                'phone_id' => '68d4ec3ae8ae7f92a410133d',
                'webhook_secret' => env('OTPIQ_WEBHOOK_SECRET'),
                'send_header_image' => true,
                'send_button_link' => true,
                'public_url' => 'https://demi.nextstepfair.com',
                'local_header_image' => 'https://www.nextstepfair.com/images/logo.png',
                'verify_ssl' => true,
            ],
        );
    }
}
