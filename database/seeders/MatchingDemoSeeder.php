<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\InstitutionUser;
use App\Models\Interaction;
use App\Models\Organization;
use App\Models\Registration;
use App\Services\Matching\MatchEngine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Enough of both sides to see the platform work.
 *
 * The demand distribution is deliberately lopsided rather than uniform: medicine
 * and computing pull far more students than agriculture, which is what actually
 * happens and what makes the supply-gap report say something useful. A seeder that
 * spreads interest evenly produces a chart that looks fine and teaches nothing.
 */
class MatchingDemoSeeder extends Seeder
{
    /** Roughly the shape of real demand at a Kurdistan Region fair. */
    private const DEMAND_WEIGHTS = [
        'medicine' => 90, 'computer_science' => 85, 'software_engineering' => 60,
        'pharmacy' => 55, 'dentistry' => 50, 'civil' => 45, 'business_administration' => 45,
        'english_language' => 40, 'nursing' => 38, 'law' => 35, 'architecture' => 32,
        'information_technology' => 30, 'accounting' => 28, 'petroleum' => 26,
        'electrical' => 24, 'mechanical' => 22, 'psychology' => 20, 'marketing' => 18,
        'international_relations' => 16, 'cybersecurity' => 15, 'data_science' => 14,
        'media_communication' => 12, 'graphic_design' => 11, 'physiotherapy' => 10,
        'biology' => 9, 'economics' => 9, 'translation' => 8, 'journalism' => 7,
        'agricultural_engineering' => 4, 'veterinary' => 3, 'horticulture' => 2,
        'food_science' => 3, 'tourism_management' => 5, 'aviation' => 6,
    ];

    /**
     * Under test this seeds a small slice.
     *
     * Every test class calls seed() in setUp, and matching seven hundred students
     * against eighteen institutions each time turns a two-minute suite into a
     * twenty-minute one. Tests that need volume build it themselves.
     */
    private function scale(): int
    {
        return app()->environment('testing') ? 40 : 100000;
    }

    public function run(): void
    {
        $fields = Field::pluck('id', 'slug');
        $this->seedInstitutions($fields);
        $this->seedStudentIntent($fields);
        $this->seedInteractions();

        // Compute the matches the platform is built to produce.
        $engine = app(MatchEngine::class);
        Registration::fair()->active()->whereNotNull('degree_level')->whereHas('fields')
            ->limit($this->scale())
            ->chunkById(100, function ($batch) use ($engine) {
                foreach ($batch as $registration) {
                    $engine->forRegistration($registration);
                }
            });
    }

    /** Give the existing directory entries a real recruitment profile. */
    private function seedInstitutions($fields): void
    {
        $profiles = [
            ['sectors' => ['health'], 'country' => 'IQ', 'levels' => ['bachelor', 'master'], 'langs' => ['en', 'ar'], 'fee' => [0, 1500], 'grade' => '90_plus'],
            ['sectors' => ['engineering', 'computing'], 'country' => 'IQ', 'levels' => ['bachelor'], 'langs' => ['en', 'ku'], 'fee' => [2000, 4500], 'grade' => '80_89'],
            ['sectors' => ['business', 'law_politics'], 'country' => 'IQ', 'levels' => ['bachelor', 'master'], 'langs' => ['en'], 'fee' => [3000, 6000], 'grade' => '70_79'],
            ['sectors' => ['computing', 'engineering', 'business'], 'country' => 'TR', 'levels' => ['bachelor', 'master'], 'langs' => ['en', 'tr'], 'fee' => [4000, 9000], 'grade' => '70_79'],
            ['sectors' => ['health', 'sciences'], 'country' => 'JO', 'levels' => ['bachelor'], 'langs' => ['en', 'ar'], 'fee' => [5000, 11000], 'grade' => '80_89'],
            ['sectors' => ['humanities', 'education', 'media_arts'], 'country' => 'IQ', 'levels' => ['diploma', 'bachelor'], 'langs' => ['ku', 'ar'], 'fee' => [0, 1200], 'grade' => '60_69'],
            ['sectors' => ['vocational', 'tourism'], 'country' => 'IQ', 'levels' => ['diploma'], 'langs' => ['ku'], 'fee' => [500, 1500], 'grade' => 'under_60'],
            ['sectors' => ['engineering', 'sciences'], 'country' => 'DE', 'levels' => ['bachelor', 'master', 'phd'], 'langs' => ['en'], 'fee' => [0, 500], 'grade' => '90_plus'],
            ['sectors' => ['business', 'computing'], 'country' => 'AE', 'levels' => ['bachelor'], 'langs' => ['en'], 'fee' => [12000, 22000], 'grade' => '70_79'],
            ['sectors' => ['agriculture', 'sciences'], 'country' => 'IQ', 'levels' => ['bachelor'], 'langs' => ['ku', 'ar'], 'fee' => [0, 900], 'grade' => '60_69'],
        ];

        $organizations = Organization::whereIn('kind', [Organization::KIND_UNIVERSITY, Organization::KIND_INSTITUTE])
            ->orderBy('id')->get();

        foreach ($organizations as $index => $organization) {
            $profile = $profiles[$index % count($profiles)];

            $organization->forceFill([
                'campus_countries' => [$profile['country']],
                'country' => $profile['country'],
                'degree_levels' => $profile['levels'],
                'languages' => $profile['langs'],
                'tuition_min' => $profile['fee'][0],
                'tuition_max' => $profile['fee'][1],
                'offers_scholarships' => $index % 3 === 0,
                'min_grade_band' => $profile['grade'],
                'intake_capacity' => 200 + $index * 45,
                'recruitment_goals' => ['target_students' => 120 + $index * 20],
                'contact_email' => 'admissions@'.$organization->slug.'.edu',
                'claim_status' => $index < 4 ? 'approved' : 'unclaimed',
                'profile_completed_at' => now(),
            ])->save();

            // Attach every field in the sectors this institution teaches.
            $rows = [];
            foreach ($profile['sectors'] as $sectorSlug) {
                foreach (config("taxonomy.sectors.$sectorSlug") as $fieldSlug) {
                    foreach ($profile['levels'] as $level) {
                        if (! isset($fields[$fieldSlug])) {
                            continue;
                        }
                        $rows[] = [
                            'organization_id' => $organization->id,
                            'field_id' => $fields[$fieldSlug],
                            'level' => $level,
                            'language' => $profile['langs'][0],
                            'tuition_min' => $profile['fee'][0],
                            'tuition_max' => $profile['fee'][1],
                            'scholarship' => $index % 3 === 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            DB::table('field_organization')->where('organization_id', $organization->id)->delete();
            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table('field_organization')->insert($chunk);
            }

            // One staff account per claimed institution, so the portal is testable.
            if ($index < 4) {
                InstitutionUser::firstOrCreate(
                    ['email_hash' => Registration::hashValue('admissions@'.$organization->slug.'.edu')],
                    [
                        'organization_id' => $organization->id,
                        'name' => 'Admissions Office',
                        'email' => 'admissions@'.$organization->slug.'.edu',
                        'job_title' => 'Head of Admissions',
                        'role' => InstitutionUser::ROLE_OWNER,
                        'locale' => 'en',
                    ]
                );
            }
        }
    }

    /** Give existing student registrations intentions, weighted realistically. */
    private function seedStudentIntent($fields): void
    {
        $weighted = [];
        foreach (self::DEMAND_WEIGHTS as $slug => $weight) {
            if (! isset($fields[$slug])) {
                continue;
            }
            $weighted = array_merge($weighted, array_fill(0, $weight, $fields[$slug]));
        }

        $levels = ['bachelor', 'bachelor', 'bachelor', 'diploma', 'master'];
        $countries = [['IQ'], ['IQ'], ['TR'], ['TR', 'DE'], ['undecided'], ['AE', 'JO'], ['GB', 'US']];
        $budgets = ['free', 'under_2k', '2k_5k', '2k_5k', '5k_10k', 'undecided'];
        $grades = ['60_69', '70_79', '70_79', '80_89', '90_plus', 'not_yet'];
        $goals = array_values(config('taxonomy.career_goals'));
        $languages = ['en', 'en', 'ku', 'ar', null];

        Registration::fair()->active()
            ->whereIn('type', [Registration::TYPE_STUDENT, Registration::TYPE_PARENT])
            ->limit($this->scale())
            ->chunkById(200, function ($batch) use ($weighted, $levels, $countries, $budgets, $grades, $goals, $languages) {
                foreach ($batch as $registration) {
                    // Draw from the weighted pool directly. array_flip() would
                    // collapse the repeated ids that carry the weighting and turn
                    // a realistic distribution back into a uniform one.
                    $chosen = collect();
                    $wanted = random_int(1, 3);
                    while ($chosen->count() < $wanted) {
                        $chosen->push($weighted[array_rand($weighted)]);
                        $chosen = $chosen->unique();
                    }
                    $chosen = $chosen->values();

                    $sync = [];
                    foreach ($chosen as $rank => $fieldId) {
                        $sync[$fieldId] = ['rank' => $rank + 1];
                    }
                    $registration->fields()->sync($sync);

                    $registration->forceFill([
                        'degree_level' => $levels[array_rand($levels)],
                        'preferred_countries' => $countries[array_rand($countries)],
                        'language_preference' => $languages[array_rand($languages)],
                        'budget_band' => $budgets[array_rand($budgets)],
                        'grade_band' => $grades[array_rand($grades)],
                        'start_year' => 2026 + random_int(0, 1),
                        'career_goal' => $goals[array_rand($goals)],
                        // Roughly half agree to be contacted, which is about what a
                        // clearly worded, unticked box actually achieves.
                        'share_with_institutions' => random_int(1, 100) <= 48,
                        'profile_completed_at' => now(),
                    ])->save();
                }
            });
    }

    /** Booth scans and shortlists, so engagement reporting has something to show. */
    private function seedInteractions(): void
    {
        $organizations = Organization::matchable()->pluck('id');

        if ($organizations->isEmpty()) {
            return;
        }

        Registration::fair()->active()->whereHas('fields')
            ->inRandomOrder()->limit(min(320, $this->scale()))
            ->chunkById(100, function ($batch) use ($organizations) {
                foreach ($batch as $registration) {
                    foreach ((array) array_rand($organizations->flip()->all(), random_int(1, 3)) as $organizationId) {
                        Interaction::firstOrCreate([
                            'registration_id' => $registration->id,
                            'organization_id' => $organizationId,
                            'type' => random_int(1, 100) <= 65 ? Interaction::TYPE_BOOTH_SCAN : Interaction::TYPE_SHORTLIST,
                            'day' => random_int(1, 3),
                        ], [
                            'rating' => random_int(2, 5),
                            'follow_up' => random_int(1, 100) <= 22,
                            'occurred_at' => now()->subDays(random_int(0, 3))->subHours(random_int(0, 9)),
                        ]);
                    }
                }
            });
    }
}
