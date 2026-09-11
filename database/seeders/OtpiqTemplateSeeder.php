<?php

namespace Database\Seeders;

use App\Models\OtpiqTemplate;
use Illuminate\Database\Seeder;

/**
 * OTPIQ template names, ids, and default send shape.
 *
 * Name and provider id are always refreshed from here on reseed. Body slots and
 * header/button flags are only set on first insert — customise them in Filament.
 */
class OtpiqTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['logical_key' => 'rsvp_confirmed', 'locale' => 'en', 'name' => 'rsvp_confirmed_en_2026', 'provider_id' => '6aa339bd7896ab80e434139d'],
            ['logical_key' => 'rsvp_confirmed', 'locale' => 'ku', 'name' => 'rsvp_confirmed_ku_2026', 'provider_id' => '6aa33af67896ab80e434269c'],
            ['logical_key' => 'rsvp_confirmed', 'locale' => 'ar', 'name' => 'rsvp_confirmed_ar_2026', 'provider_id' => '6aa33a577896ab80e4341bb7'],

            ['logical_key' => 'registration_confirmed_student', 'locale' => 'en', 'name' => 'registration_confirmed_new_student_en_2026', 'provider_id' => '6aa4034e7896ab80e4538234'],
            ['logical_key' => 'registration_confirmed_student', 'locale' => 'ku', 'name' => 'registration_confirmed_new_student_ku_2026', 'provider_id' => '6aa403bf7896ab80e4538c21'],
            ['logical_key' => 'registration_confirmed_student', 'locale' => 'ar', 'name' => 'registration_confirmed_new_student_ar_2026', 'provider_id' => '6aa402d37896ab80e4537366'],

            ['logical_key' => 'registration_confirmed_parent', 'locale' => 'en', 'name' => 'registration_confirmed_new_parent_en_2026', 'provider_id' => '6aa40adb7896ab80e45570df'],
            ['logical_key' => 'registration_confirmed_parent', 'locale' => 'ku', 'name' => 'registration_confirmed_new_parent_ku_2026', 'provider_id' => '6aa409d17896ab80e4554fe8'],
            ['logical_key' => 'registration_confirmed_parent', 'locale' => 'ar', 'name' => 'registration_confirmed_new_parent_ar_2026', 'provider_id' => '6aa409377896ab80e4544188'],
        ];

        foreach ($templates as $template) {
            $shape = config("whatsapp.otpiq_templates.{$template['logical_key']}.{$template['locale']}") ?? [];

            $row = OtpiqTemplate::firstOrNew([
                'logical_key' => $template['logical_key'],
                'locale' => $template['locale'],
            ]);

            if (! $row->exists) {
                $row->body_variables = is_array($shape['body'] ?? null) ? $shape['body'] : null;
                $row->send_header = array_key_exists('header', $shape) ? (bool) $shape['header'] : null;
                $row->send_button = array_key_exists('button', $shape) ? (bool) $shape['button'] : null;
            }

            $row->name = $template['name'];
            $row->provider_id = $template['provider_id'];
            $row->save();
        }
    }
}
