<?php

namespace Tests\Feature;

use App\Filament\Resources\ScholarshipApplications\Pages\EditScholarshipApplication;
use App\Filament\Resources\ScholarshipApplications\Pages\ListScholarshipApplications;
use App\Filament\Resources\ScholarshipApplications\ScholarshipApplicationResource;
use App\Models\Registration;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The committee's screen.
 *
 * Applications were being submitted into a table no screen ever read. These
 * tests are written from the two ends that matter: a committee member can see
 * every applicant and move them along, and the student watching their own
 * tracker sees that movement.
 */
class ScholarshipDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function committee(): User
    {
        return User::where('email', 'committee@nextstepfair.com')->firstOrFail();
    }

    private function student(array $attributes = []): Registration
    {
        static $n = 0;
        $n++;

        return Registration::create(array_merge([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => "Applicant {$n}",
            'phone' => '77012345'.str_pad((string) $n, 2, '0', STR_PAD_LEFT),
            'phone_country' => '+964',
            'city' => 'Halabja',
            'email' => "applicant{$n}@example.com",
            'password' => 'a-good-password',
            'education_stage' => 'grade12',
            'days' => [1, 2, 3],
            'verified_at' => now(),
            'confirmed_at' => now(),
        ], $attributes));
    }

    private function application(array $attributes = []): ScholarshipApplication
    {
        return ScholarshipApplication::create(array_merge([
            'registration_id' => $this->student()->id,
            'cycle' => config('scholarship.cycle'),
            'status' => ScholarshipApplication::STATUS_SUBMITTED,
            'step' => 4,
            'eligibility' => ['grade12' => 'y', 'average' => 'y', 'year' => 'y', 'funded' => 'n', 'docs' => 'y'],
            'eligibility_passed_at' => now(),
            'region_code' => 'HLB',
            'district' => 'Halabja Centre',
            'exam_average' => 92.5,
            'exam_status' => 'published',
            'stream' => 'science',
            'school_name' => 'Halabja Preparatory',
            'first_choice_university' => 'University of Sulaimani',
            'first_choice_department' => 'Medicine',
            'statement' => 'A statement about why this matters.',
            'proposal' => 'A proposal about coming back to work.',
            'documents' => ['certificate' => 'a.pdf', 'national_id' => 'b.pdf', 'residence' => 'c.pdf', 'judicial_record' => 'd.pdf'],
            'submitted_at' => now(),
        ], $attributes));
    }

    /* ------------------------------------------------------------- access -- */

    public function test_a_committee_member_can_open_the_screen(): void
    {
        $this->actingAs($this->committee())
            ->get(ScholarshipApplicationResource::getUrl('index'))
            ->assertOk();
    }

    public function test_the_gate_staff_cannot(): void
    {
        $gate = User::where('email', 'gate@nextstepfair.com')->firstOrFail();

        $this->actingAs($gate)
            ->get(ScholarshipApplicationResource::getUrl('index'))
            ->assertForbidden();
    }

    /** A committee member is external; they get applications and nothing else. */
    public function test_a_committee_member_cannot_reach_registrations_or_accounts(): void
    {
        $this->actingAs($this->committee());

        foreach (['/admin/registrations', '/admin/users', '/admin/messages'] as $url) {
            $this->assertContains(
                $this->get($url)->status(), [403, 404], "the committee reached {$url}"
            );
        }
    }

    /* ------------------------------------------------------------ the queue -- */

    public function test_every_submitted_application_is_listed(): void
    {
        $applications = collect(range(1, 3))->map(fn () => $this->application());

        $this->actingAs($this->committee());

        Livewire::test(ListScholarshipApplications::class)
            ->assertCanSeeTableRecords($applications);
    }

    /** A draft is a student still writing, not something to review. */
    public function test_drafts_are_not_in_the_queue_until_asked_for(): void
    {
        $draft = $this->application([
            'status' => ScholarshipApplication::STATUS_DRAFT,
            'submitted_at' => null,
        ]);
        $submitted = $this->application();

        $this->actingAs($this->committee());

        Livewire::test(ListScholarshipApplications::class)
            ->assertCanSeeTableRecords([$submitted])
            ->assertCanNotSeeTableRecords([$draft])
            ->filterTable('hide_drafts', false)
            ->assertCanSeeTableRecords([$submitted, $draft]);
    }

    public function test_the_queue_can_be_narrowed_to_one_region(): void
    {
        $halabja = $this->application(['region_code' => 'HLB']);
        $erbil = $this->application(['region_code' => 'ERB', 'district' => 'Erbil Centre']);

        $this->actingAs($this->committee());

        Livewire::test(ListScholarshipApplications::class)
            ->filterTable('region_code', 'HLB')
            ->assertCanSeeTableRecords([$halabja])
            ->assertCanNotSeeTableRecords([$erbil]);
    }

    /* ----------------------------------------------------------- reviewing -- */

    public function test_the_whole_application_is_readable(): void
    {
        $application = $this->application();

        $this->actingAs($this->committee())
            ->get(ScholarshipApplicationResource::getUrl('view', ['record' => $application]))
            ->assertOk()
            ->assertSee($application->applicantName())
            ->assertSee('A statement about why this matters.')
            ->assertSee('A proposal about coming back to work.')
            ->assertSee('Halabja');
    }

    public function test_scores_and_notes_are_saved(): void
    {
        $application = $this->application();

        $this->actingAs($this->committee());

        Livewire::test(EditScholarshipApplication::class, ['record' => $application->getRouteKey()])
            ->fillForm([
                'status' => ScholarshipApplication::STATUS_SHORTLISTED,
                'score_academic' => 36,
                'score_feasibility' => 24,
                'committee_notes' => 'Strong proposal, weak on costing.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $application->refresh();

        $this->assertSame(ScholarshipApplication::STATUS_SHORTLISTED, $application->status);
        $this->assertSame(60.0, $application->scoreTotal());
        $this->assertStringContainsString('weak on costing', $application->committee_notes);
    }

    /** Moving a stage has to stamp when, or the student's tracker shows a blank. */
    public function test_moving_a_stage_records_when_it_happened(): void
    {
        $application = $this->application();

        $this->assertNull($application->screened_at);

        $application->advanceTo(ScholarshipApplication::STATUS_SCREENING);

        $this->assertNotNull($application->fresh()->screened_at);
    }

    public function test_the_stages_run_in_order(): void
    {
        $application = $this->application();

        $this->assertSame(ScholarshipApplication::STATUS_SCREENING, $application->nextStage());

        $application->advanceTo(ScholarshipApplication::STATUS_SCREENING);
        $this->assertSame(ScholarshipApplication::STATUS_SHORTLISTED, $application->fresh()->nextStage());
    }

    /* ------------------------------------------------- what the student sees -- */

    /**
     * The point of the whole screen: a committee decision is what the applicant
     * reads on their own tracker, without anybody sending them anything.
     */
    public function test_a_stage_set_by_the_committee_shows_on_the_students_tracker(): void
    {
        $application = $this->application();
        $student = $application->registration;

        $this->actingAs($this->committee());

        Livewire::test(EditScholarshipApplication::class, ['record' => $application->getRouteKey()])
            ->fillForm(['status' => ScholarshipApplication::STATUS_INTERVIEW])
            ->call('save');

        auth()->guard('web')->logout();

        $page = $this->actingAs($student, 'attendee')
            ->get('/en/scholarship/my-application')
            ->assertOk();

        $page->assertSee(__('scholarship.status.stages.interview.title'));

        $this->assertSame(
            ScholarshipApplication::STATUS_INTERVIEW,
            $application->fresh()->status
        );
    }

    public function test_an_application_cannot_be_deleted_from_the_dashboard(): void
    {
        $application = $this->application();

        $this->assertFalse(ScholarshipApplicationResource::canDelete($application));
        $this->assertFalse(ScholarshipApplicationResource::canCreate());
    }
}
