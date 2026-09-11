<?php

namespace Database\Seeders;

use App\Models\OtpiqTemplate;
use Illuminate\Database\Seeder;

/**
 * OTPIQ template names and dashboard ids.
 *
 * When OTPIQ re-issues a template, update the row here or in Filament — body
 * slots and header flags stay in config/whatsapp.php.
 */
class OtpiqTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['logical_key' => 'rsvp_confirmed', 'locale' => 'en', 'name' => 'rsvp_confirmed_en_2026', 'provider_id' => '6aa339bd7896ab80e434139d'],
            ['logical_key' => 'rsvp_confirmed', 'locale' => 'ku', 'name' => 'rsvp_confirmed_ku_2026', 'provider_id' => '6aa33af67896ab80e434269c'],
            ['logical_key' => 'rsvp_confirmed', 'locale' => 'ar', 'name' => 'rsvp_confirmed_ar_2026', 'provider_id' => '6aa33a577896ab80e4341bb7'],

            ['logical_key' => 'registration_confirmed_student', 'locale' => 'en', 'name' => 'registration_confirmed_student_en_2026', 'provider_id' => '6aa349a27896ab80e4364967'],
            ['logical_key' => 'registration_confirmed_student', 'locale' => 'ku', 'name' => 'registration_confirmed_student_ku_2026', 'provider_id' => '6aa34b227896ab80e4365831'],
            ['logical_key' => 'registration_confirmed_student', 'locale' => 'ar', 'name' => 'registration_confirmed_student_ar_2026', 'provider_id' => '6aa34d937896ab80e4367d54'],

            ['logical_key' => 'registration_confirmed_parent', 'locale' => 'en', 'name' => 'registration_confirmed_parent_en_2026', 'provider_id' => '6aa10af706f24b29619aa67a'],
            ['logical_key' => 'registration_confirmed_parent', 'locale' => 'ku', 'name' => 'registration_confirmed_parent_ku_2026', 'provider_id' => '6aa10c5606f24b29619adc65'],
            ['logical_key' => 'registration_confirmed_parent', 'locale' => 'ar', 'name' => 'registration_confirmed_parent_ar_2026', 'provider_id' => '6aa10d6206f24b29619b23f8'],
        ];

        foreach ($templates as $template) {
            $row = OtpiqTemplate::firstOrNew([
                'logical_key' => $template['logical_key'],
                'locale' => $template['locale'],
            ]);

            if (! $row->exists) {
                $row->name = $template['name'];
            }

            $row->provider_id = $template['provider_id'];
            $row->save();
        }
    }
}
