<?php

namespace Tests\Feature;

use App\Models\OtpiqSetting;
use App\Models\Registration;
use App\Services\Messaging\MessageDispatcher;
use App\Services\Messaging\OtpiqConfigRegistry;
use Database\Seeders\OtpiqSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpiqSettingsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_a_single_otpiq_settings_row(): void
    {
        $this->seed(OtpiqSettingsSeeder::class);

        $this->assertDatabaseCount('otpiq_settings', 1);

        $row = OtpiqSetting::current();

        $this->assertNotNull($row);
        $this->assertSame('68d4ec30e8ae7f92a41012f8', $row->account_id);
        $this->assertSame('https://demi.nextstepfair.com', $row->public_url);
        $this->assertTrue($row->send_header_image);
        $this->assertTrue($row->send_button_link);
    }

    public function test_the_registry_reads_seeded_settings_before_config_defaults(): void
    {
        $this->seed(OtpiqSettingsSeeder::class);

        OtpiqSetting::current()->update(['public_url' => 'https://staging.example.test']);

        $this->assertSame(
            'https://staging.example.test',
            app(OtpiqConfigRegistry::class)->get('public_url'),
        );
    }

    public function test_badge_urls_use_the_seeded_public_origin(): void
    {
        $this->seed(OtpiqSettingsSeeder::class);

        OtpiqSetting::current()->update(['public_url' => 'https://demi.nextstepfair.com']);

        $registration = new Registration([
            'ticket_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
        ]);

        $url = app(MessageDispatcher::class)->badgeUrl($registration);

        $this->assertSame(
            'https://demi.nextstepfair.com/ticket/aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee/badge.png',
            $url,
        );
    }
}
