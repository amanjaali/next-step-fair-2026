<?php

namespace Tests\Feature;

use App\Models\OtpiqTemplate;
use App\Services\Messaging\OtpiqTemplateRegistry;
use Database\Seeders\OtpiqTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpiqTemplateSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_all_nine_otpiq_templates(): void
    {
        $this->seed(OtpiqTemplateSeeder::class);

        $this->assertDatabaseCount('otpiq_templates', 9);

        $this->assertDatabaseHas('otpiq_templates', [
            'logical_key' => 'rsvp_confirmed',
            'locale' => 'en',
            'name' => 'rsvp_confirmed_en_2026',
            'provider_id' => '6aa339bd7896ab80e434139d',
        ]);

        $this->assertDatabaseHas('otpiq_templates', [
            'logical_key' => 'registration_confirmed_parent',
            'locale' => 'ar',
            'name' => 'registration_confirmed_parent_ar_2026',
            'provider_id' => '6aa10d6206f24b29619b23f8',
        ]);
    }

    public function test_the_registry_uses_db_id_and_derived_template_name(): void
    {
        $this->seed(OtpiqTemplateSeeder::class);

        OtpiqTemplate::where('logical_key', 'rsvp_confirmed')
            ->where('locale', 'en')
            ->update(['provider_id' => 'new-id-123']);

        $row = app(OtpiqTemplateRegistry::class)->get('rsvp_confirmed', 'en');

        $this->assertSame('rsvp_confirmed_en_2026', $row['name']);
        $this->assertSame('new-id-123', $row['id']);
        $this->assertSame(['name'], $row['body']);
    }
}
