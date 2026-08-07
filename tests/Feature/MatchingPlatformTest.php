<?php

namespace Tests\Feature;

use App\Models\Field;
use App\Models\InstitutionUser;
use App\Models\Interaction;
use App\Models\Organization;
use App\Models\Registration;
use App\Services\Matching\InsightService;
use App\Services\Matching\MatchEngine;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The two-sided data platform: what students want, what institutions offer, and
 * whether the matching between them is defensible.
 *
 * These build their own small fixtures rather than leaning on the demo seeder, so
 * each assertion is about one deliberate arrangement of the data and a failure
 * says which rule broke.
 */
class MatchingPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function student(array $fieldSlugs, array $attributes = []): Registration
    {
        $registration = Registration::create(array_merge([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'Test Student',
            'phone' => '77'.random_int(10000000, 99999999),
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'days' => [1],
            'degree_level' => 'bachelor',
            'confirmed_at' => now(),
        ], $attributes));

        $sync = [];
        foreach (array_values($fieldSlugs) as $rank => $slug) {
            $sync[Field::where('slug', $slug)->firstOrFail()->id] = ['rank' => $rank + 1];
        }
        $registration->fields()->sync($sync);

        return $registration->fresh();
    }

    private function institution(array $fieldSlugs, array $attributes = []): Organization
    {
        $organization = Organization::create(array_merge([
            'slug' => 'test-uni-'.random_int(1000, 999999),
            'kind' => Organization::KIND_UNIVERSITY,
            'name' => ['en' => 'Test University'],
            'country' => 'IQ',
            'campus_countries' => ['IQ'],
            'languages' => ['en'],
            'degree_levels' => ['bachelor'],
            'published' => true,
            'year' => 2026,
        ], $attributes));

        foreach ($fieldSlugs as $slug) {
            DB::table('field_organization')->insert([
                'organization_id' => $organization->id,
                'field_id' => Field::where('slug', $slug)->firstOrFail()->id,
                'level' => 'bachelor',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $organization->fresh();
    }

    /* --------------------------------------------------------- the engine -- */

    public function test_no_shared_field_means_no_match_at_any_price(): void
    {
        $student = $this->student(['medicine']);
        // Everything else about this institution is perfect for them.
        $this->institution(['civil'], ['campus_countries' => ['IQ'], 'languages' => ['en']]);

        app(MatchEngine::class)->forRegistration($student);

        $this->assertSame(0, $student->matches()->count(),
            'A university that teaches nothing the student wants must never be recommended.');
    }

    public function test_a_first_choice_outweighs_a_third(): void
    {
        $student = $this->student(['medicine', 'nursing', 'civil']);

        $first = $this->institution(['medicine']);
        $third = $this->institution(['civil']);

        app(MatchEngine::class)->forRegistration($student);

        $firstScore = $student->matches()->where('organization_id', $first->id)->value('score');
        $thirdScore = $student->matches()->where('organization_id', $third->id)->value('score');

        $this->assertGreaterThan($thirdScore, $firstScore,
            'The university teaching their first choice must rank above one teaching their third.');
    }

    public function test_the_reasons_explain_the_score(): void
    {
        $student = $this->student(['medicine'], [
            'preferred_countries' => ['TR'],
            'language_preference' => 'en',
            'budget_band' => '5k_10k',
            'grade_band' => '90_plus',
        ]);

        $this->institution(['medicine'], [
            'campus_countries' => ['TR'],
            'languages' => ['en'],
            'tuition_min' => 6000,
            'min_grade_band' => '80_89',
        ]);

        app(MatchEngine::class)->forRegistration($student);
        $match = $student->matches()->first();

        // A number nobody can interrogate is a number nobody acts on.
        $this->assertNotEmpty($match->reasons['fields']);
        $this->assertContains('TR', $match->reasons['country']);
        $this->assertSame('en', $match->reasons['language']);
        $this->assertTrue($match->reasons['meets_entry']);
        $this->assertGreaterThanOrEqual(90, $match->score);
    }

    public function test_a_scholarship_rescues_an_unaffordable_university(): void
    {
        $student = $this->student(['medicine'], ['budget_band' => 'under_2k']);

        $expensive = $this->institution(['medicine'], ['tuition_min' => 18000, 'offers_scholarships' => false]);
        $funded = $this->institution(['medicine'], ['tuition_min' => 18000, 'offers_scholarships' => true]);

        app(MatchEngine::class)->forRegistration($student);

        $this->assertGreaterThan(
            $student->matches()->where('organization_id', $expensive->id)->value('score'),
            $student->matches()->where('organization_id', $funded->id)->value('score'),
            'A scholarship is the reason an expensive university is still worth a conversation.'
        );
    }

    public function test_changing_your_mind_drops_the_old_recommendations(): void
    {
        $student = $this->student(['medicine']);
        $this->institution(['medicine']);
        $this->institution(['civil']);

        app(MatchEngine::class)->forRegistration($student);
        $this->assertSame(1, $student->matches()->count());

        // They switch to engineering; the medicine match must not linger.
        $student->fields()->sync([Field::where('slug', 'civil')->first()->id => ['rank' => 1]]);
        app(MatchEngine::class)->forRegistration($student->fresh());

        $this->assertSame(1, $student->matches()->count());
        $this->assertNotEmpty($student->matches()->first()->reasons['fields']);
    }

    public function test_an_institution_with_no_programmes_is_never_recommended(): void
    {
        $student = $this->student(['medicine']);
        $empty = Organization::create([
            'slug' => 'empty-uni', 'kind' => Organization::KIND_UNIVERSITY,
            'name' => ['en' => 'Empty University'], 'country' => 'IQ', 'published' => true, 'year' => 2026,
        ]);

        app(MatchEngine::class)->forRegistration($student);

        $this->assertFalse($student->matches()->where('organization_id', $empty->id)->exists());
    }

    /* -------------------------------------------------------- the insights -- */

    public function test_demand_is_weighted_by_preference_order(): void
    {
        // Three students want nursing as a third choice; one wants medicine first.
        for ($i = 0; $i < 3; $i++) {
            $this->student(['civil', 'law', 'nursing']);
        }
        $this->student(['medicine']);

        $rows = app(InsightService::class)->demandByField(20)->keyBy('slug');

        $this->assertSame(3, $rows['nursing']['students']);
        $this->assertSame(1, $rows['medicine']['students']);
        // Three third-choices weigh 1.0 in total, barely more than one first choice.
        $this->assertLessThan(1.5, $rows['nursing']['weighted']);
        $this->assertSame(1.0, $rows['medicine']['weighted']);
    }

    public function test_the_supply_gap_finds_fields_nobody_teaches(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->student(['veterinary']);
        }
        $this->student(['medicine']);
        $this->institution(['medicine']);

        $gaps = app(InsightService::class)->supplyGaps(10)->keyBy('slug');

        $this->assertSame(0, $gaps['veterinary']['institutions']);
        $this->assertSame(5.0, $gaps['veterinary']['ratio']);
        // A field nobody teaches must sort above one that is merely popular.
        $this->assertGreaterThan($gaps['medicine']['ratio'], $gaps['veterinary']['ratio']);
    }

    public function test_engagement_separates_scans_from_page_views(): void
    {
        $busy = $this->institution(['medicine']);
        $quiet = $this->institution(['nursing']);

        foreach (range(1, 3) as $i) {
            $student = $this->student(['medicine']);
            Interaction::create([
                'registration_id' => $student->id, 'organization_id' => $busy->id,
                'type' => Interaction::TYPE_BOOTH_SCAN, 'occurred_at' => now(),
            ]);
            Interaction::create([
                'registration_id' => $student->id, 'organization_id' => $quiet->id,
                'type' => Interaction::TYPE_PROFILE_VIEW, 'occurred_at' => now(),
            ]);
        }

        $board = app(InsightService::class)->engagementLeaderboard()->keyBy('organization_id');

        $this->assertSame(3, $board[$busy->id]['scans']);
        $this->assertSame(0, $board[$quiet->id]['scans']);
        $this->assertGreaterThan($board[$quiet->id]['score'], $board[$busy->id]['score'],
            'Three conversations at a desk must outweigh three page views.');
    }

    /* ---------------------------------------------------------- the portal -- */

    private function staff(Organization $organization): InstitutionUser
    {
        return InstitutionUser::create([
            'organization_id' => $organization->id,
            'name' => 'Admissions Office',
            'email' => 'admissions@test.edu',
            'role' => InstitutionUser::ROLE_OWNER,
            'locale' => 'en',
        ]);
    }

    public function test_the_portal_needs_an_institution_session(): void
    {
        $this->get('/en/portal')->assertRedirect('/en/portal/signin');
        $this->get('/en/portal/students')->assertRedirect('/en/portal/signin');
    }

    public function test_an_attendee_session_does_not_open_the_portal(): void
    {
        // Three guards, and a student must never reach a recruiter's leads.
        $student = $this->student(['medicine']);

        $this->actingAs($student, 'attendee')
            ->get('/en/portal')
            ->assertRedirect('/en/portal/signin');
    }

    public function test_a_recruiter_sees_only_their_own_institution(): void
    {
        $mine = $this->institution(['medicine']);
        $theirs = $this->institution(['medicine']);

        $student = $this->student(['medicine'], ['share_with_institutions' => true, 'full_name' => 'Visible Student']);
        app(MatchEngine::class)->forRegistration($student);

        Interaction::create([
            'registration_id' => $student->id, 'organization_id' => $theirs->id,
            'type' => Interaction::TYPE_BOOTH_SCAN, 'notes' => 'Their private note', 'occurred_at' => now(),
        ]);

        $this->actingAs($this->staff($mine), 'institution')
            ->get('/en/portal/leads')
            ->assertOk()
            ->assertDontSee('Their private note');
    }

    public function test_a_student_who_declined_is_counted_but_not_named(): void
    {
        $organization = $this->institution(['medicine']);
        $private = $this->student(['medicine'], ['share_with_institutions' => false, 'full_name' => 'Private Person']);
        app(MatchEngine::class)->forRegistration($private);

        $response = $this->actingAs($this->staff($organization), 'institution')
            ->get('/en/portal/students')
            ->assertOk();

        // In the counts, absent from the page.
        $this->assertSame(1, $organization->matches()->count());
        $response->assertDontSee('Private Person');
        $response->assertSee(__('institution.students.anonymous'));
    }

    public function test_saving_programmes_recomputes_matches(): void
    {
        $organization = $this->institution([]);
        $student = $this->student(['medicine']);

        $this->assertSame(0, $organization->matches()->count());

        $this->actingAs($this->staff($organization), 'institution')
            ->post('/en/portal/profile', [
                'fields' => [Field::where('slug', 'medicine')->first()->id => ['bachelor']],
                'campus_countries' => ['IQ'],
                'degree_levels' => ['bachelor'],
                'languages' => ['en'],
            ])
            ->assertRedirect('/en/portal');

        $this->assertSame(1, $organization->fresh()->matches()->count(),
            'Listing a programme must immediately make the institution matchable.');
    }

    public function test_a_booth_scan_is_recorded_once_per_day(): void
    {
        $organization = $this->institution(['medicine']);
        $student = $this->student(['medicine']);
        $staff = $this->staff($organization);

        $ticket = app(TicketService::class);
        $payload = ['ticket' => $student->ticket_id, 'sig' => $ticket->signature($student->ticket_id)];

        $this->actingAs($staff, 'institution')->postJson('/en/portal/scanner', $payload)
            ->assertOk()->assertJsonPath('state', 'ok');

        // Scanning the same badge again while talking is not a second person.
        $this->actingAs($staff, 'institution')->postJson('/en/portal/scanner', $payload)
            ->assertOk()->assertJsonPath('state', 'already');

        $this->assertSame(1, Interaction::where('type', Interaction::TYPE_BOOTH_SCAN)->count());
    }

    public function test_an_invalid_badge_is_rejected(): void
    {
        $organization = $this->institution(['medicine']);

        $this->actingAs($this->staff($organization), 'institution')
            ->postJson('/en/portal/scanner', ['ticket' => 'not-a-real-ticket', 'sig' => 'nope'])
            ->assertStatus(422)
            ->assertJsonPath('state', 'invalid');
    }

    /* -------------------------------------------------------- the student -- */

    public function test_a_student_declares_intent_and_gets_matches(): void
    {
        $this->institution(['medicine'], ['campus_countries' => ['TR'], 'languages' => ['en']]);
        $student = $this->student([], ['degree_level' => null]);

        $this->actingAs($student, 'attendee')
            ->post('/en/me/interests', [
                'fields' => [Field::where('slug', 'medicine')->first()->id],
                'degree_level' => 'bachelor',
                'preferred_countries' => ['TR'],
                'language_preference' => 'en',
                'budget_band' => '5k_10k',
                'grade_band' => '90_plus',
                'career_goal' => 'employment',
            ])
            ->assertRedirect('/en/me/matches');

        $this->assertGreaterThan(0, $student->fresh()->matches()->count());
        $this->actingAs($student, 'attendee')->get('/en/me/matches')->assertOk()->assertSee('Test University');
    }

    public function test_consent_is_off_unless_asked_for(): void
    {
        $student = $this->student([], ['degree_level' => null]);

        $this->actingAs($student, 'attendee')->post('/en/me/interests', [
            'fields' => [Field::where('slug', 'medicine')->first()->id],
            'degree_level' => 'bachelor',
        ])->assertRedirect();

        $this->assertFalse($student->fresh()->share_with_institutions);
    }

    public function test_the_interests_form_requires_a_field_and_a_level(): void
    {
        $student = $this->student([], ['degree_level' => null]);

        $this->actingAs($student, 'attendee')
            ->post('/en/me/interests', [])
            ->assertSessionHasErrors(['fields', 'degree_level']);
    }

    public function test_shortlisting_records_an_interaction(): void
    {
        $organization = $this->institution(['medicine']);
        $student = $this->student(['medicine']);

        $this->actingAs($student, 'attendee')->post("/en/me/matches/{$organization->id}")->assertRedirect();
        $this->assertSame(1, Interaction::ofType(Interaction::TYPE_SHORTLIST)->count());

        // Pressing it again takes it off the list.
        $this->actingAs($student, 'attendee')->post("/en/me/matches/{$organization->id}")->assertRedirect();
        $this->assertSame(0, Interaction::ofType(Interaction::TYPE_SHORTLIST)->count());
    }

    public function test_the_platform_renders_in_every_language(): void
    {
        $organization = $this->institution(['medicine']);
        $student = $this->student(['medicine']);
        $staff = $this->staff($organization);

        // Guest pages first: a signed-in recruiter is sent past /portal/signin,
        // so the three sets cannot share one session.
        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get("/{$locale}/portal/signin")->assertOk();
            $this->get("/{$locale}/portal/register")->assertOk();
        }

        $this->actingAs($student, 'attendee');
        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get("/{$locale}/me/interests")->assertOk();
            $this->get("/{$locale}/me/matches")->assertOk();
        }

        $this->actingAs($staff, 'institution');
        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get("/{$locale}/portal")->assertOk();
            $this->get("/{$locale}/portal/profile")->assertOk();
            $this->get("/{$locale}/portal/students")->assertOk();
            $this->get("/{$locale}/portal/leads")->assertOk();
            $this->get("/{$locale}/portal/scanner")->assertOk();
        }
    }
}
