<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\Registration;
use App\Models\ScholarshipApplication;
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

    /* ----------------------------------------------------------- helpers -- */

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
            'answers' => ['grade12' => 'y', 'average' => 'y', 'year' => 'y', 'funded' => 'n', 'docs' => 'y'],
        ]);

        return $student->scholarshipApplication();
    }
}
