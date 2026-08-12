<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\ScholarshipApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The National Scholarship Program.
 *
 * The rules it enforces are the ones a student would otherwise find out about in
 * November, at screening, after spending an evening writing: the eligibility
 * gate, the 85% baseline, the regional quota, and the fact that the same Next
 * Step ID carries them from the expo into the application.
 */
class ScholarshipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function student(array $attributes = []): Registration
    {
        return Registration::create(array_merge([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'Lava Rebwar Jalal',
            'phone' => '7719995001',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'email' => 'lava@example.com',
            'password' => 'a-good-password',
            'education_stage' => 'grade12',
            'days' => [1, 2, 3],
            'verified_at' => now(),
            'confirmed_at' => now(),
            'badge_generated_at' => now(),
        ], $attributes));
    }

    /** @return list<array{string}> */
    public static function publicPages(): array
    {
        return array_map(fn ($p) => [$p], [
            'scholarship',
            'scholarship/about',
            'scholarship/guidelines',
            'scholarship/universities',
            'scholarship/universities/auis',
            'scholarship/regions/slm',
            'scholarship/committee',
            'scholarship/recipients',
            'scholarship/apply',
        ]);
    }

    #[DataProvider('publicPages')]
    public function test_the_programme_reads_without_an_account_in_every_language(string $path): void
    {
        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get("/{$locale}/{$path}")->assertSuccessful();
        }
    }

    public function test_it_is_in_the_main_menu(): void
    {
        $this->get('/en')->assertOk()->assertSee(route('scholarship.home', ['locale' => 'en']));
    }

    public function test_an_unknown_university_or_region_is_not_invented(): void
    {
        $this->get('/en/scholarship/universities/not-a-university')->assertNotFound();
        $this->get('/en/scholarship/regions/zzz')->assertNotFound();
    }

    /* ------------------------------------------------------------- gates -- */

    /**
     * Closed, but never with a bare 403.
     *
     * A blank "403 Forbidden" was what a signed-out visitor got for opening the
     * eligibility link — no explanation and nowhere to go, on a URL people send
     * each other. They land on the gate instead, which lists the three things
     * that have to be true and how far along they are on each.
     */
    public function test_a_stranger_is_sent_to_the_gate_not_a_forbidden_page(): void
    {
        foreach (['eligibility', 'form'] as $step) {
            $this->get("/en/scholarship/apply/{$step}")
                ->assertRedirect(route('scholarship.apply', ['locale' => 'en']))
                ->assertSessionHas('gate_blocked');
        }

        $this->followingRedirects()
            ->get('/en/scholarship/apply/eligibility')
            ->assertOk()
            ->assertSee(__('scholarship.eligibility.gate_blocked'));
    }

    /** A parent has an expo badge, not a student account. */
    public function test_a_parent_cannot_apply(): void
    {
        $parent = $this->student([
            'type' => Registration::TYPE_PARENT,
            'phone' => '7719995002',
            'email' => 'parent@example.com',
            'password' => null,
        ]);

        $this->actingAs($parent, 'attendee')
            ->get('/en/scholarship/apply/eligibility')
            ->assertRedirect(route('scholarship.apply', ['locale' => 'en']));
    }

    /**
     * The award funds a first degree from year one, so somebody already at
     * university is told why rather than shown a locked card.
     */
    public function test_a_student_already_at_university_is_told_why_not(): void
    {
        $student = $this->student(['education_stage' => 'university']);

        $this->actingAs($student, 'attendee')
            ->get('/en/scholarship/apply')
            ->assertOk()
            ->assertSee(__('scholarship.apply.not_eligible_title'));

        $this->actingAs($student, 'attendee')
            ->get('/en/scholarship/apply/eligibility')
            ->assertRedirect(route('scholarship.apply', ['locale' => 'en']));
    }

    /** Registering for the expo is the only sign-up there is. */
    public function test_the_expo_account_is_the_application_account(): void
    {
        $student = $this->student();

        $this->actingAs($student, 'attendee')
            ->get('/en/scholarship/apply')
            ->assertOk()
            ->assertSee(__('scholarship.apply.state_done'))
            ->assertDontSee(__('scholarship.apply.gate1_cta'));
    }

    /* ------------------------------------------------------- eligibility -- */

    public function test_a_failing_answer_closes_the_application(): void
    {
        $student = $this->student();

        $this->actingAs($student, 'attendee')->post('/en/scholarship/apply/eligibility', [
            'answers' => ['grade12' => 'n', 'average' => 'y', 'year' => 'y', 'funded' => 'n', 'docs' => 'y'],
        ])->assertRedirect();

        $application = $student->scholarshipApplication();

        $this->assertSame('fail', $application->eligibilityVerdict());
        $this->assertFalse($application->hasPassedEligibility());

        // And the form stays shut.
        $this->actingAs($student, 'attendee')
            ->get('/en/scholarship/apply/form')
            ->assertRedirect(route('scholarship.apply', ['locale' => 'en']));
    }

    /**
     * Pending results are normal in September and are not a reason to stop
     * somebody writing, so they warn rather than fail.
     */
    public function test_pending_results_warn_but_do_not_close_it(): void
    {
        $student = $this->student();

        $this->actingAs($student, 'attendee')->post('/en/scholarship/apply/eligibility', [
            'answers' => ['grade12' => 'y', 'average' => 'p', 'year' => 'y', 'funded' => 'n', 'docs' => 'p'],
        ]);

        $application = $student->scholarshipApplication();

        $this->assertSame('warn', $application->eligibilityVerdict());
        $this->assertTrue($application->hasPassedEligibility());

        $this->actingAs($student, 'attendee')->get('/en/scholarship/apply/form')->assertOk();
    }

    public function test_every_question_must_be_answered(): void
    {
        $student = $this->student();

        $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/eligibility', ['answers' => ['grade12' => 'y']])
            ->assertSessionHasErrors();
    }

    /* ------------------------------------------------------------- apply -- */

    private function pass(Registration $student): ScholarshipApplication
    {
        $this->actingAs($student, 'attendee')->post('/en/scholarship/apply/eligibility', [
            'answers' => ['grade12' => 'y', 'average' => 'y', 'year' => 'y', 'funded' => 'n', 'docs' => 'y'],
        ]);

        return $student->scholarshipApplication();
    }

    public function test_the_application_saves_a_step_at_a_time(): void
    {
        $student = $this->student();
        $this->pass($student);

        $this->actingAs($student, 'attendee')->post('/en/scholarship/apply/form', [
            'step' => 1, 'region_code' => 'HLB', 'district' => 'Khurmal',
        ])->assertRedirect();

        $application = $student->scholarshipApplication();

        $this->assertSame('HLB', $application->region_code);
        $this->assertSame('Halabja', $application->regionName());
        $this->assertSame(8, $application->seatsInRegion());
        $this->assertSame(2, $application->step);
    }

    public function test_a_half_finished_application_cannot_be_submitted(): void
    {
        $student = $this->student();
        $this->pass($student);

        $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/submit', ['confirm' => '1'])
            ->assertSessionHasErrors('submit');

        $this->assertFalse($student->scholarshipApplication()->isSubmitted());
    }

    public function test_a_complete_application_submits_and_locks(): void
    {
        $student = $this->student();
        $this->pass($student);

        $this->actingAs($student, 'attendee')->post('/en/scholarship/apply/form', [
            'step' => 1, 'region_code' => 'SLM', 'district' => 'Chamchamal',
        ]);
        $this->actingAs($student, 'attendee')->post('/en/scholarship/apply/form', [
            'step' => 2, 'exam_status' => 'published', 'exam_average' => '92.5',
            'stream' => 'scientific', 'school_name' => 'Chamchamal Preparatory',
            'first_choice_university' => 'American University of Iraq, Sulaimani',
            'first_choice_department' => 'Computer Science',
        ]);
        $this->actingAs($student, 'attendee')->post('/en/scholarship/apply/form', [
            'step' => 3,
            'statement' => str_repeat('word ', 420),
            'proposal' => str_repeat('idea ', 520),
        ]);

        $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/submit', ['confirm' => '1'])
            ->assertRedirect(route('scholarship.status', ['locale' => 'en']));

        $application = $student->scholarshipApplication();

        $this->assertTrue($application->isSubmitted());
        $this->assertSame(ScholarshipApplication::STATUS_SUBMITTED, $application->status);

        // Nothing can be edited afterwards: the quota is fixed at this moment.
        $this->actingAs($student, 'attendee')->post('/en/scholarship/apply/form', [
            'step' => 1, 'region_code' => 'ERB', 'district' => 'Koya',
        ])->assertForbidden();

        $this->assertSame('SLM', $application->fresh()->region_code);
    }

    public function test_a_statement_below_the_word_count_is_rejected(): void
    {
        $student = $this->student();
        $this->pass($student);

        $this->actingAs($student, 'attendee')->post('/en/scholarship/apply/form', [
            'step' => 3, 'statement' => 'Too short.', 'proposal' => str_repeat('idea ', 520),
        ])->assertSessionHasErrors('statement');
    }

    public function test_the_status_page_shows_where_it_stands(): void
    {
        $student = $this->student();
        $this->pass($student);

        $this->actingAs($student, 'attendee')
            ->get('/en/scholarship/my-application')
            ->assertOk()
            ->assertSee(__('scholarship.status.not_submitted'));
    }

    /* -------------------------------------------------------------- data -- */

    /** Forty seats, and the regional quotas have to add up to exactly that. */
    public function test_the_quotas_add_up_to_the_published_number(): void
    {
        $this->assertSame(
            config('scholarship.seats'),
            collect(config('scholarship.regions'))->sum('seats'),
        );
    }

    public function test_every_pledged_department_belongs_to_a_university(): void
    {
        foreach (config('scholarship.universities') as $university) {
            $this->assertNotEmpty($university['departments'], "{$university['name']} pledges no seats.");
            $this->assertNotEmpty(__("scholarship.university_about.{$university['slug']}"));
            $this->assertStringNotContainsString(
                'scholarship.university_about',
                __("scholarship.university_about.{$university['slug']}"),
                "{$university['slug']} has no description, so its page would print the lookup key."
            );
        }
    }
}
