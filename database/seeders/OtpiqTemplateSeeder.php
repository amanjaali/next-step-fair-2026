<?php

namespace Database\Seeders;

use App\Models\OtpiqTemplate;
use Illuminate\Database\Seeder;

/**
 * OTPIQ WhatsApp template ids and send metadata.
 *
 * Paste new dashboard ids here after approval — one row per logical template
 * key × locale. Names and body slot order must match the OTPIQ submission.
 */
class OtpiqTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // rsvp_confirmed
            ['logical_key' => 'rsvp_confirmed', 'locale' => 'en', 'name' => 'rsvp_confirmed_en_2026', 'provider_id' => '6aa339bd7896ab80e434139d', 'body_variables' => ['name'], 'header_image' => true],
            ['logical_key' => 'rsvp_confirmed', 'locale' => 'ku', 'name' => 'rsvp_confirmed_ku_2026', 'provider_id' => '6aa33af67896ab80e434269c', 'body_variables' => ['name', 'ticket'], 'header_image' => false],
            ['logical_key' => 'rsvp_confirmed', 'locale' => 'ar', 'name' => 'rsvp_confirmed_ar_2026', 'provider_id' => '6aa33a577896ab80e4341bb7', 'body_variables' => ['name'], 'header_image' => false],

            // registration_confirmed_student
            ['logical_key' => 'registration_confirmed_student', 'locale' => 'en', 'name' => 'registration_confirmed_student_en_2026', 'provider_id' => '6aa349a27896ab80e4364967', 'body_variables' => ['name', 'days', 'ticket'], 'header_image' => true],
            ['logical_key' => 'registration_confirmed_student', 'locale' => 'ku', 'name' => 'registration_confirmed_student_ku_2026', 'provider_id' => '6aa34b227896ab80e4365831', 'body_variables' => ['name', 'days', 'ticket'], 'header_image' => true],
            ['logical_key' => 'registration_confirmed_student', 'locale' => 'ar', 'name' => 'registration_confirmed_student_ar_2026', 'provider_id' => '6aa34d937896ab80e4367d54', 'body_variables' => ['name', 'days', 'ticket'], 'header_image' => true],

            // registration_confirmed_parent
            ['logical_key' => 'registration_confirmed_parent', 'locale' => 'en', 'name' => 'registration_confirmed_parent_en_2026', 'provider_id' => '6aa10af706f24b29619aa67a', 'body_variables' => ['name', 'days', 'ticket'], 'header_image' => true],
            ['logical_key' => 'registration_confirmed_parent', 'locale' => 'ku', 'name' => 'registration_confirmed_parent_ku_2026', 'provider_id' => '6aa10c5606f24b29619adc65', 'body_variables' => ['name', 'days', 'ticket'], 'header_image' => true],
            ['logical_key' => 'registration_confirmed_parent', 'locale' => 'ar', 'name' => 'registration_confirmed_parent_ar_2026', 'provider_id' => '6aa10d6206f24b29619b23f8', 'body_variables' => ['name', 'days', 'ticket'], 'header_image' => true],
        ];

        foreach ($templates as $template) {
            OtpiqTemplate::updateOrCreate(
                [
                    'logical_key' => $template['logical_key'],
                    'locale' => $template['locale'],
                ],
                [
                    'name' => $template['name'],
                    'provider_id' => $template['provider_id'],
                    'body_variables' => $template['body_variables'],
                    'header_image' => $template['header_image'],
                    'active' => true,
                ],
            );
        }
    }
}
