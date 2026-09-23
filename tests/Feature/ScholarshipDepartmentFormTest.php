<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\Registration;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipUniversityRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * A partner whose seats live on an Opportunity, and the paper form one of its
 * departments hands out.
 *
 * Both halves of the same failure: a university students could not pick, and a
 * requirement the form had no way to collect.
 */
class ScholarshipDepartmentFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_a_scholarship_opportunity_reaches_the_application_form(): void
    {
        $this->partnerWithSeats();

        $student = $this->student();
        $this->pass($student);

        $this->actingAs($student, 'attendee')
            ->get('/en/scholarship/apply/form?step=2')
            ->assertOk()
            ->assertSee('Lutka Institute')
            ->assertSee('Graphic Design');
    }

    public function test_it_is_listed_on_the_public_universities_page_too(): void
    {
        $this->partnerWithSeats();

        $this->get('/en/scholarship/universities')
            ->assertOk()
            ->assertSee('Lutka Institute');
    }

    public function test_a_department_that_hands_out_a_form_will_not_save_without_it(): void
    {
        $this->partnerWithSeats();

        $student = $this->student();
        $this->pass($student);

        $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/form', $this->step2() + ['first_choice_ack' => '1'])
            ->assertSessionHasErrors('first_choice_form');

        $this->assertNull($student->scholarshipApplication()->first_choice_university);
    }

    public function test_the_form_is_kept_off_the_public_disk_and_noted_on_the_application(): void
    {
        Storage::fake('local');

        $this->partnerWithSeats();

        $student = $this->student();
        $this->pass($student);

        $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/form', $this->step2() + [
                'first_choice_ack' => '1',
                'first_choice_form' => UploadedFile::fake()->image('lutka.jpg'),
            ])
            ->assertSessionHasNoErrors();

        $application = $student->scholarshipApplication();
        $path = $application->documents['first_choice_form'] ?? null;

        $this->assertNotNull($path, 'the form was not recorded on the application');
        Storage::disk('local')->assertExists($path);
        $this->assertSame('Lutka Institute', $application->first_choice_university);
    }

    /**
     * A form belongs to the department that issued it.
     *
     * Switching to another university that also wants one must ask again,
     * rather than quietly passing the first department's paperwork off as the
     * second's.
     */
    public function test_a_form_does_not_follow_a_student_to_another_department(): void
    {
        Storage::fake('local');

        $this->partnerWithSeats();
        $this->secondPartnerWithSeats();

        $student = $this->student();
        $this->pass($student);

        $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/form', $this->step2() + [
                'first_choice_ack' => '1',
                'first_choice_form' => UploadedFile::fake()->image('lutka.jpg'),
            ])
            ->assertSessionHasNoErrors();

        $held = $student->scholarshipApplication()->documents['first_choice_form'];

        $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/form', [
                'step' => 2,
                'exam_status' => 'published',
                'exam_average' => '91.5',
                'stream' => 'scientific',
                'school_name' => 'Sulaimani Preparatory',
                'first_choice_university' => 'Bardi Institute',
                'first_choice_department' => 'Nursing',
                'first_choice_ack' => '1',
            ])
            ->assertSessionHasErrors('first_choice_form');

        Storage::disk('local')->assertExists($held);
    }

    /** A department nobody asked a form of still saves on its own. */
    public function test_an_ordinary_department_asks_for_nothing_extra(): void
    {
        $this->partnerWithSeats();

        $student = $this->student();
        $this->pass($student);

        $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/form', $this->step2(department: 'Civil Engineering') + ['first_choice_ack' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Civil Engineering', $student->scholarshipApplication()->first_choice_department);
    }

    /**
     * Two partners can share a name — most plausibly the same organisation
     * entered as two separate scholarship announcements. The form must not
     * let that make a choice ambiguous: each stays individually selectable
     * and a submission is validated against its own department data, not
     * whichever namesake happens to come first in the merged list.
     */
    public function test_two_partners_with_the_same_name_stay_individually_selectable(): void
    {
        Storage::fake('local');

        $this->partnerWithSeats();
        $this->namesakePartnerWithSeats();

        $universities = collect(ns_scholarship_universities())
            ->filter(fn (array $u) => str_starts_with($u['name'], 'Lutka Institute'))
            ->values();

        $this->assertCount(2, $universities);
        $this->assertNotSame($universities[0]['name'], $universities[1]['name']);

        $namesake = $universities->firstWhere('name', '!=', 'Lutka Institute');
        $this->assertNotNull($namesake, 'the second Lutka Institute was not disambiguated');

        $student = $this->student();
        $this->pass($student);

        // The namesake's own department needs a form. Submitting without one
        // must fail for it, the way it already does for the first partner's —
        // proving this choice resolved to its own data, not the first
        // "Lutka Institute" match with no "Fine Arts" department at all.
        $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/form', [
                'step' => 2,
                'exam_status' => 'published',
                'exam_average' => '91.5',
                'stream' => 'scientific',
                'school_name' => 'Sulaimani Preparatory',
                'first_choice_university' => $namesake['name'],
                'first_choice_department' => 'Fine Arts',
                'first_choice_ack' => '1',
            ])
            ->assertSessionHasErrors('first_choice_form');

        $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/form', [
                'step' => 2,
                'exam_status' => 'published',
                'exam_average' => '91.5',
                'stream' => 'scientific',
                'school_name' => 'Sulaimani Preparatory',
                'first_choice_university' => $namesake['name'],
                'first_choice_department' => 'Fine Arts',
                'first_choice_ack' => '1',
                'first_choice_form' => UploadedFile::fake()->image('namesake.jpg'),
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($namesake['name'], $student->scholarshipApplication()->fresh()->first_choice_university);
    }

    /**
     * A partner's own application form is whole-university: it must block
     * saving no matter which department was picked, not just the one that
     * also happens to hand out a paper form.
     */
    public function test_a_university_with_its_own_form_blocks_saving_until_confirmed(): void
    {
        $this->partnerWithSeats();
        $this->flagExternalForm('opportunity-lutka-grade-based-scholarship', 'https://lutka.example/apply');

        $student = $this->student();
        $this->pass($student);

        // Civil Engineering asks nothing of its own — proving the block comes
        // from the university-wide flag, not a department requirement.
        $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/form', $this->step2(department: 'Civil Engineering') + [
                'first_choice_ack' => '1',
            ])
            ->assertSessionHasErrors('first_choice_external_form_ack');

        $this->assertNull($student->scholarshipApplication()->fresh()->first_choice_university);

        $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/form', $this->step2(department: 'Civil Engineering') + [
                'first_choice_ack' => '1',
                'first_choice_external_form_ack' => '1',
            ])
            ->assertSessionHasNoErrors();

        $application = $student->scholarshipApplication()->fresh();
        $this->assertSame('Lutka Institute', $application->first_choice_university);
        $this->assertNotNull($application->first_choice_external_form_ack_at);
        $this->assertSame('https://lutka.example/apply', $application->first_choice_external_form_url_ack);
    }

    /** A university with nothing flagged asks for nothing extra — this stays additive. */
    public function test_a_university_without_its_own_form_is_unaffected(): void
    {
        $this->partnerWithSeats();

        $student = $this->student();
        $this->pass($student);

        $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/form', $this->step2(department: 'Civil Engineering') + ['first_choice_ack' => '1'])
            ->assertSessionHasNoErrors();

        $application = $student->scholarshipApplication()->fresh();
        $this->assertNull($application->first_choice_external_form_ack_at);
        $this->assertNull($application->first_choice_external_form_url_ack);
    }

    /** The same mechanism works for a hand-curated catalogue university, not just an Opportunity partner. */
    public function test_a_catalogue_university_can_also_require_its_own_form(): void
    {
        $this->flagExternalForm('auis', 'https://auis.example/apply');

        $student = $this->student();
        $this->pass($student);

        $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/form', [
                'step' => 2,
                'exam_status' => 'published',
                'exam_average' => '91.5',
                'stream' => 'scientific',
                'school_name' => 'Sulaimani Preparatory',
                'first_choice_university' => 'American University of Iraq, Sulaimani',
                'first_choice_department' => 'Computer Science',
            ])
            ->assertSessionHasErrors('first_choice_external_form_ack');
    }

    /* ------------------------------------------------------- five choices -- */

    public function test_a_student_can_list_a_third_choice(): void
    {
        $this->partnerWithSeats();
        $this->secondPartnerWithSeats();

        $student = $this->student();
        $this->pass($student);

        $response = $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/form', $this->step2(department: 'Civil Engineering') + [
                'second_choice_university' => 'Bardi Institute',
                'second_choice_department' => 'Nursing',
                'second_choice_ack' => '1',
                'second_choice_form' => UploadedFile::fake()->image('bardi.jpg'),
                'third_choice_university' => 'Lutka Institute',
                'third_choice_department' => 'Graphic Design',
                'third_choice_ack' => '1',
                'third_choice_form' => UploadedFile::fake()->image('lutka.jpg'),
            ]);

        $response->assertSessionDoesntHaveErrors();

        $application = $student->scholarshipApplication()->fresh();
        $this->assertSame('Lutka Institute', $application->third_choice_university);
        $this->assertSame('Graphic Design', $application->third_choice_department);
        $this->assertNotNull($application->documents['third_choice_form'] ?? null);
    }

    public function test_the_same_seat_cannot_be_listed_twice(): void
    {
        $this->partnerWithSeats();

        $student = $this->student();
        $this->pass($student);

        $response = $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/form', $this->step2(department: 'Civil Engineering') + [
                'second_choice_university' => 'Lutka Institute',
                'second_choice_department' => 'Civil Engineering',
            ]);

        $response->assertSessionHasErrors('second_choice_department');

        $this->assertNull($student->scholarshipApplication()->fresh()->second_choice_university);
    }

    public function test_the_same_university_with_a_different_department_is_not_a_duplicate(): void
    {
        $this->partnerWithSeats();

        $student = $this->student();
        $this->pass($student);

        $response = $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/form', $this->step2(department: 'Civil Engineering') + [
                'second_choice_university' => 'Lutka Institute',
                'second_choice_department' => 'Graphic Design',
                'second_choice_ack' => '1',
                'second_choice_form' => UploadedFile::fake()->image('lutka-2nd.jpg'),
            ]);

        $response->assertSessionDoesntHaveErrors();

        $application = $student->scholarshipApplication()->fresh();
        $this->assertSame('Civil Engineering', $application->first_choice_department);
        $this->assertSame('Graphic Design', $application->second_choice_department);
    }

    /* ----------------------------------------------------------- helpers -- */

    private function flagExternalForm(string $universitySlug, string $url): ScholarshipUniversityRequirement
    {
        return ScholarshipUniversityRequirement::create([
            'university_slug' => $universitySlug,
            'requires_external_form' => true,
            'external_form_url' => $url,
        ]);
    }

    private function namesakePartnerWithSeats(): Opportunity
    {
        $organization = Organization::create([
            'slug' => 'lutka-institute-fine-arts',
            'kind' => 'university',
            'name' => ['en' => 'Lutka Institute'],
            'city' => 'Duhok',
            'year' => 2026,
        ]);

        return Opportunity::create([
            'slug' => 'lutka-fine-arts-scholarship',
            'kind' => Opportunity::KIND_SCHOLARSHIP,
            'audience' => Opportunity::AUDIENCE_GRADE12,
            'organization_id' => $organization->id,
            'published' => true,
            'title' => ['en' => 'Fine Arts Scholarship'],
            'summary' => ['en' => 'Seats in fine arts.'],
            'departments' => [
                ['name' => 'Fine Arts', 'seats' => 8, 'requires_form' => true],
            ],
        ]);
    }

    private function partnerWithSeats(): Opportunity
    {
        $organization = Organization::create([
            'slug' => 'lutka-institute',
            'kind' => 'university',
            'name' => ['en' => 'Lutka Institute', 'ku' => 'پەیمانگای لوتکە', 'ar' => 'معهد لوتكا'],
            'city' => 'Sulaimani',
            'year' => 2026,
        ]);

        return Opportunity::create([
            'slug' => 'lutka-grade-based-scholarship',
            'kind' => Opportunity::KIND_SCHOLARSHIP,
            'audience' => Opportunity::AUDIENCE_GRADE12,
            'organization_id' => $organization->id,
            'published' => true,
            'title' => ['en' => 'Grade-Based Scholarship'],
            'summary' => ['en' => 'Seats by grade 12 average.'],
            'departments' => [
                ['name' => 'Graphic Design', 'seats' => 45, 'requires_form' => true],
                ['name' => 'Civil Engineering', 'seats' => 10],
            ],
        ]);
    }

    private function secondPartnerWithSeats(): Opportunity
    {
        $organization = Organization::create([
            'slug' => 'bardi-institute',
            'kind' => 'university',
            'name' => ['en' => 'Bardi Institute'],
            'city' => 'Erbil',
            'year' => 2026,
        ]);

        return Opportunity::create([
            'slug' => 'bardi-nursing-scholarship',
            'kind' => Opportunity::KIND_SCHOLARSHIP,
            'audience' => Opportunity::AUDIENCE_GRADE12,
            'organization_id' => $organization->id,
            'published' => true,
            'title' => ['en' => 'Nursing Scholarship'],
            'summary' => ['en' => 'Seats in nursing.'],
            'departments' => [
                ['name' => 'Nursing', 'seats' => 12, 'requires_form' => true],
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function step2(string $department = 'Graphic Design'): array
    {
        return [
            'step' => 2,
            'exam_status' => 'published',
            'exam_average' => '91.5',
            'stream' => 'scientific',
            'school_name' => 'Sulaimani Preparatory',
            'first_choice_university' => 'Lutka Institute',
            'first_choice_department' => $department,
        ];
    }

    private function student(): Registration
    {
        return Registration::create([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'Lava Rebwar Jalal',
            'phone' => '7719995044',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'email' => 'lava.forms@example.com',
            'password' => 'a-good-password',
            'education_stage' => 'grade12',
            'days' => [1, 2, 3],
            'verified_at' => now(),
            'confirmed_at' => now(),
            'badge_generated_at' => now(),
        ]);
    }

    private function pass(Registration $student): ScholarshipApplication
    {
        $this->actingAs($student, 'attendee')->post('/en/scholarship/apply/eligibility', [
            'answers' => ['year' => 'y', 'funded' => 'n', 'docs' => 'y'],
        ]);

        return $student->scholarshipApplication();
    }
}
