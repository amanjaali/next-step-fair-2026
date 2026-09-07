<?php

namespace Tests\Feature;

use App\Filament\Resources\ScholarshipApplications\Pages\EditScholarshipApplication;
use App\Models\AttendeeNotification;
use App\Models\Registration;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Telling the student.
 *
 * The committee's screen recorded decisions that never reached the person they
 * were about: the tracker moved a marker to "Decision" and stopped, so an
 * applicant could see that an answer existed and not what it was. These tests
 * are written from the student's side of that — what lands in their account,
 * what their tracker says, and what their profile carries afterwards.
 */
class ScholarshipDecisionTest extends TestCase
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

    private function student(): Registration
    {
        static $n = 0;
        $n++;

        return Registration::create([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => "Applicant {$n}",
            'phone' => '77098765'.str_pad((string) $n, 2, '0', STR_PAD_LEFT),
            'phone_country' => '+964',
            'city' => 'Halabja',
            'email' => "decided{$n}@example.com",
            'password' => 'a-good-password',
            'education_stage' => 'grade12',
            'days' => [1, 2, 3],
            'verified_at' => now(),
            'confirmed_at' => now(),
        ]);
    }

    private function application(?Registration $student = null, array $attributes = []): ScholarshipApplication
    {
        return ScholarshipApplication::create(array_merge([
            'registration_id' => ($student ?? $this->student())->id,
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

    /* -------------------------------------------------- what gets recorded -- */

    public function test_a_decision_reaches_the_student_as_the_decision_itself(): void
    {
        $application = $this->application();

        $application->advanceTo(
            ScholarshipApplication::STATUS_DECIDED,
            ScholarshipApplication::DECISION_AWARDED,
        );

        $update = AttendeeNotification::where('registration_id', $application->registration_id)->sole();

        $this->assertSame('scholarship.decision', $update->type);
        $this->assertSame('awarded', $update->data['decision']);
        $this->assertSame(__('updates.types.scholarship.decision.awarded.title'), $update->title());
        $this->assertTrue($update->isUnread());
    }

    /**
     * Stage and outcome are saved together in the ordinary case. They are one
     * event, and two notices would read as two results.
     */
    public function test_a_stage_and_a_decision_saved_together_are_one_update(): void
    {
        $application = $this->application();

        $application->advanceTo(
            ScholarshipApplication::STATUS_DECIDED,
            ScholarshipApplication::DECISION_DECLINED,
        );

        $updates = AttendeeNotification::where('registration_id', $application->registration_id)->get();

        $this->assertCount(1, $updates);
        $this->assertSame('scholarship.decision', $updates->first()->type);
    }

    public function test_moving_a_stage_tells_the_student_which_stage(): void
    {
        $application = $this->application();

        $application->advanceTo(ScholarshipApplication::STATUS_SCREENING);

        $update = AttendeeNotification::where('registration_id', $application->registration_id)->sole();

        $this->assertSame('scholarship.stage', $update->type);
        $this->assertStringContainsString(
            __('scholarship.status.stages.screening.title'),
            $update->title(),
        );
    }

    /** Submitting is the student's own act, done on the page that says so. */
    public function test_the_students_own_submission_is_not_announced_back_to_them(): void
    {
        $student = $this->student();
        $application = $this->application($student, [
            'status' => ScholarshipApplication::STATUS_DRAFT,
            'submitted_at' => null,
        ]);

        $application->update([
            'status' => ScholarshipApplication::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $this->assertSame(0, AttendeeNotification::where('registration_id', $student->id)->count());
    }

    public function test_saving_without_changing_anything_says_nothing(): void
    {
        $application = $this->application();
        $application->advanceTo(ScholarshipApplication::STATUS_SCREENING);

        // The same stage again — a committee member re-saving the form.
        $application->advanceTo(ScholarshipApplication::STATUS_SCREENING);

        $this->assertSame(1, AttendeeNotification::where('registration_id', $application->registration_id)->count());
    }

    /** A committee that corrects itself has to be able to say so. */
    public function test_a_changed_decision_is_told_again(): void
    {
        $application = $this->application();

        $application->advanceTo(ScholarshipApplication::STATUS_DECIDED, ScholarshipApplication::DECISION_RESERVE);
        $application->advanceTo(ScholarshipApplication::STATUS_DECIDED, ScholarshipApplication::DECISION_AWARDED);

        $updates = AttendeeNotification::where('registration_id', $application->registration_id)
            ->orderBy('id')->get();

        $this->assertCount(2, $updates);
        $this->assertSame('reserve', $updates[0]->data['decision']);
        $this->assertSame('awarded', $updates[1]->data['decision']);
    }

    /* ------------------------------------------------- through the dashboard */

    public function test_the_committees_own_screen_sends_exactly_one_update(): void
    {
        $application = $this->application();

        $this->actingAs($this->committee());

        Livewire::test(EditScholarshipApplication::class, ['record' => $application->getRouteKey()])
            ->fillForm([
                'status' => ScholarshipApplication::STATUS_DECIDED,
                'decision' => ScholarshipApplication::DECISION_AWARDED,
                'score_academic' => 36,
                'score_feasibility' => 27,
                'score_interview' => 25,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $update = AttendeeNotification::where('registration_id', $application->registration_id)->sole();

        $this->assertSame('awarded', $update->data['decision']);
        $this->assertNotNull($application->fresh()->decided_at);
    }

    /* ------------------------------------------------------- what they see -- */

    public function test_the_tracker_prints_the_result_in_words(): void
    {
        $student = $this->student();
        $application = $this->application($student);
        $application->advanceTo(ScholarshipApplication::STATUS_DECIDED, ScholarshipApplication::DECISION_AWARDED);

        $this->actingAs($student, 'attendee')
            ->get('/en/scholarship/my-application')
            ->assertOk()
            ->assertSee(__('scholarship.status.outcome.awarded.title'))
            ->assertSee(__('scholarship.status.outcome.awarded.next'));
    }

    /** Turned down is still a result, and is said rather than left implied. */
    public function test_a_student_who_was_not_selected_is_told_so(): void
    {
        $student = $this->student();
        $application = $this->application($student);
        $application->advanceTo(ScholarshipApplication::STATUS_DECIDED, ScholarshipApplication::DECISION_DECLINED);

        $this->actingAs($student, 'attendee')
            ->get('/en/scholarship/my-application')
            ->assertOk()
            ->assertSee(__('scholarship.status.outcome.declined.title'))
            ->assertDontSee(__('scholarship.status.outcome.awarded.title'));
    }

    public function test_nothing_is_claimed_before_a_decision_is_recorded(): void
    {
        $student = $this->student();
        $this->application($student)->advanceTo(ScholarshipApplication::STATUS_INTERVIEW);

        $this->actingAs($student, 'attendee')
            ->get('/en/scholarship/my-application')
            ->assertOk()
            ->assertDontSee(__('scholarship.status.outcome.awarded.title'))
            ->assertDontSee(__('scholarship.status.outcome.declined.title'));
    }

    /* -------------------------------------------------------- the badge ----- */

    public function test_an_awarded_student_carries_the_badge_on_their_profile(): void
    {
        $student = $this->student();
        $this->application($student)->advanceTo(
            ScholarshipApplication::STATUS_DECIDED,
            ScholarshipApplication::DECISION_AWARDED,
        );

        $this->actingAs($student, 'attendee')
            ->get('/en/me')
            ->assertOk()
            ->assertSee(__('scholarship.status.award.title'));

        $this->assertTrue($student->fresh()->isScholar());
    }

    public function test_the_badge_belongs_to_the_award_and_not_to_applying(): void
    {
        $student = $this->student();
        $this->application($student)->advanceTo(
            ScholarshipApplication::STATUS_DECIDED,
            ScholarshipApplication::DECISION_RESERVE,
        );

        $this->actingAs($student, 'attendee')
            ->get('/en/me')
            ->assertOk()
            ->assertDontSee(__('scholarship.status.award.title'));

        $this->assertFalse($student->fresh()->isScholar());
    }

    /* ------------------------------------------------------- the updates ---- */

    public function test_the_update_waits_on_their_profile_and_in_the_header(): void
    {
        $student = $this->student();
        $this->application($student)->advanceTo(ScholarshipApplication::STATUS_SHORTLISTED);

        $page = $this->actingAs($student, 'attendee')->get('/en/me')->assertOk();

        $page->assertSee(__('updates.title'));
        $page->assertSee(__('scholarship.status.stages.shortlisted.title'), false);

        // The count in the header, on every page.
        $this->actingAs($student, 'attendee')
            ->get('/en')
            ->assertOk()
            ->assertSee(trans_choice('updates.unread', 1, ['count' => 1]));
    }

    public function test_opening_an_update_marks_it_read_and_goes_to_the_application(): void
    {
        $student = $this->student();
        $this->application($student)->advanceTo(
            ScholarshipApplication::STATUS_DECIDED,
            ScholarshipApplication::DECISION_AWARDED,
        );

        $update = AttendeeNotification::where('registration_id', $student->id)->sole();

        $this->actingAs($student, 'attendee')
            ->get('/en/me/updates/'.$update->id)
            ->assertRedirect(route('scholarship.status', ['locale' => 'en']));

        $this->assertNotNull($update->fresh()->read_at);
        $this->assertSame(0, $student->fresh()->unreadUpdates());
    }

    public function test_one_student_cannot_open_another_students_update(): void
    {
        $mine = $this->student();
        $theirs = $this->student();

        $this->application($theirs)->advanceTo(ScholarshipApplication::STATUS_SCREENING);
        $update = AttendeeNotification::where('registration_id', $theirs->id)->sole();

        $this->actingAs($mine, 'attendee')
            ->get('/en/me/updates/'.$update->id)
            ->assertNotFound();

        $this->assertNull($update->fresh()->read_at);
    }

    public function test_signing_in_is_required_to_read_an_update(): void
    {
        $student = $this->student();
        $this->application($student)->advanceTo(ScholarshipApplication::STATUS_SCREENING);
        $update = AttendeeNotification::where('registration_id', $student->id)->sole();

        $this->get('/en/me/updates/'.$update->id)->assertRedirect();
    }

    /**
     * The row holds facts, not a sentence, so it reads in whatever language the
     * student opens their account in — including one they switched to after the
     * decision was recorded.
     */
    public function test_the_wording_follows_the_reader_not_the_committee(): void
    {
        $student = $this->student();
        $this->application($student)->advanceTo(
            ScholarshipApplication::STATUS_DECIDED,
            ScholarshipApplication::DECISION_AWARDED,
        );

        $this->actingAs($student, 'attendee')
            ->get('/ku/me')
            ->assertOk()
            ->assertSee(__('updates.types.scholarship.decision.awarded.title', [], 'ku'), false);
    }
}
