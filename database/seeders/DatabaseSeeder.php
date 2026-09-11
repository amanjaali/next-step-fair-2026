<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            TaxonomySeeder::class,
            SiteContentSeeder::class,
            PageSeeder::class,
            ProgrammeSeeder::class,
            NewsSeeder::class,
            EditionSeeder::class,
            MediaSeeder::class,
            MessageTemplateSeeder::class,
            OtpiqSettingsSeeder::class,
            OtpiqTemplateSeeder::class,
            OpportunitySeeder::class,
            OfferPopupSeeder::class,
            DemoDataSeeder::class,
        ]);

        /*
         * The matching demo is heavy — it scores every student against every
         * institution — and every test class seeds in setUp. Tests that need
         * matches call this seeder themselves; the demo database always gets it.
         */
        if (! app()->environment('testing')) {
            $this->call(MatchingDemoSeeder::class);
        }
    }
}
