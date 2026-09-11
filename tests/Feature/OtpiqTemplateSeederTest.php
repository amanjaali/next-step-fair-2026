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
            'logical_key' => 'registration_confirmed_student',
            'locale' => 'en',
            'name' => 'registration_confirmed_new_student_en_2026',
            'provider_id' => '6aa4034e7896ab80e4538234',
        ]);

        $this->assertDatabaseHas('otpiq_templates', [
            'logical_key' => 'registration_confirmed_parent',
            'locale' => 'ar',
            'name' => 'registration_confirmed_new_parent_ar_2026',
            'provider_id' => '6aa409377896ab80e4544188',
        ]);
    }

    public function test_the_registry_uses_db_name_and_id(): void
    {
        $this->seed(OtpiqTemplateSeeder::class);

        OtpiqTemplate::where('logical_key', 'rsvp_confirmed')
            ->where('locale', 'en')
            ->update([
                'name' => 'custom_rsvp_en',
                'provider_id' => 'new-id-123',
            ]);

        $row = app(OtpiqTemplateRegistry::class)->get('rsvp_confirmed', 'en');

        $this->assertSame('custom_rsvp_en', $row['name']);
        $this->assertSame('new-id-123', $row['id']);
        $this->assertSame(['name'], $row['body']);
    }

    public function test_the_registry_uses_db_body_slots_and_send_flags(): void
    {
        $this->seed(OtpiqTemplateSeeder::class);

        OtpiqTemplate::where('logical_key', 'registration_confirmed_student')
            ->where('locale', 'en')
            ->update([
                'body_variables' => ['name'],
                'send_header' => true,
                'send_button' => false,
            ]);

        $row = app(OtpiqTemplateRegistry::class)->get('registration_confirmed_student', 'en');

        $this->assertSame(['name'], $row['body']);
        $this->assertTrue($row['header']);
        $this->assertFalse($row['button']);
    }

    public function test_reseeding_refreshes_otpiq_names_and_ids(): void
    {
        $this->seed(OtpiqTemplateSeeder::class);

        OtpiqTemplate::where('logical_key', 'rsvp_confirmed')
            ->where('locale', 'en')
            ->update(['name' => 'old_name', 'provider_id' => 'old-id']);

        $this->seed(OtpiqTemplateSeeder::class);

        $this->assertDatabaseHas('otpiq_templates', [
            'logical_key' => 'rsvp_confirmed',
            'locale' => 'en',
            'name' => 'rsvp_confirmed_en_2026',
            'provider_id' => '6aa339bd7896ab80e434139d',
        ]);
    }

    public function test_reseeding_refreshes_body_slots_from_config(): void
    {
        $this->seed(OtpiqTemplateSeeder::class);

        OtpiqTemplate::where('logical_key', 'registration_confirmed_student')
            ->where('locale', 'en')
            ->update(['body_variables' => ['name', 'days', 'ticket']]);

        $this->seed(OtpiqTemplateSeeder::class);

        $this->assertDatabaseHas('otpiq_templates', [
            'logical_key' => 'registration_confirmed_student',
            'locale' => 'en',
            'body_variables' => json_encode(['name']),
        ]);
    }
}
