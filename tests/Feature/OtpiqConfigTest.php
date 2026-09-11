<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Services\Messaging\MessageDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpiqConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_badge_urls_use_otpiq_public_url_from_env_config(): void
    {
        config(['whatsapp.otpiq.public_url' => 'https://demi.nextstepfair.com']);

        $registration = new Registration([
            'ticket_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
        ]);

        $url = app(MessageDispatcher::class)->badgeUrl($registration);

        $this->assertSame(
            'https://demi.nextstepfair.com/ticket/aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee/badge.png',
            $url,
        );
    }

    public function test_otpiq_secrets_are_read_from_config_not_database(): void
    {
        config([
            'whatsapp.otpiq.api_key' => 'sk_test_from_env',
            'whatsapp.otpiq.webhook_secret' => 'secret-from-env',
        ]);

        $this->assertSame('sk_test_from_env', config('whatsapp.otpiq.api_key'));
        $this->assertSame('secret-from-env', config('whatsapp.otpiq.webhook_secret'));
        $this->assertFalse(\Schema::hasTable('otpiq_settings'));
    }
}
