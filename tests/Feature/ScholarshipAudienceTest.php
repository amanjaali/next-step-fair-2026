<?php

namespace Tests\Feature;

use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Who is offered the scholarship, and who is told it is not theirs.
 *
 * Only a student can apply. A parent signed in to their own badge was still
 * shown "Start your application", and pressing it took them to a gate that
 * turned them away. Saying who it is for comes first, and the parent gets the
 * one thing they can usefully do instead.
 */
class ScholarshipAudienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function registration(array $attributes = []): Registration
    {
        return Registration::create(array_merge([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'Lava Rebwar',
            'phone' => '7719997301',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'email' => 'lava.audience@example.com',
            'password' => 'a-good-password',
            'education_stage' => 'grade12',
            'days' => [1, 2, 3],
            'verified_at' => now(),
            'confirmed_at' => now(),
        ], $attributes));
    }

    private function parent(): Registration
    {
        return $this->registration([
            'type' => Registration::TYPE_PARENT,
            'phone' => '7719997302',
            'email' => 'parent.audience@example.com',
            'password' => null,
            'education_stage' => null,
            'full_name' => 'Aram Jalal',
        ]);
    }

    /* -------------------------------------------------------- scholarship -- */

    public function test_a_signed_out_visitor_is_still_offered_the_application(): void
    {
        // They might be the student. The gate explains the requirements.
        $this->get('/en/scholarship')
            ->assertOk()
            ->assertSee(__('scholarship.home.cta'))
            ->assertDontSee(__('scholarship.not_for_you.title'));
    }

    public function test_a_student_is_offered_the_application(): void
    {
        $this->actingAs($this->registration(), 'attendee')
            ->get('/en/scholarship')
            ->assertOk()
            ->assertSee(__('scholarship.home.cta'))
            ->assertDontSee(__('scholarship.not_for_you.title'));
    }

    public function test_a_parent_is_told_it_is_for_students_instead(): void
    {
        $response = $this->actingAs($this->parent(), 'attendee')->get('/en/scholarship')->assertOk();

        $response->assertSee(__('scholarship.not_for_you.title'));
        $response->assertSee(__('scholarship.not_for_you.send'));
        $response->assertDontSee(__('scholarship.home.cta'));
    }

    public function test_a_conference_delegate_gets_the_same_treatment(): void
    {
        $delegate = $this->registration([
            'track' => Registration::TRACK_CONFERENCE,
            'type' => Registration::TYPE_GOVERNMENT,
            'phone' => '7719997303',
            'email' => 'dg.audience@example.com',
            'password' => null,
            'education_stage' => null,
        ]);

        $this->actingAs($delegate, 'attendee')
            ->get('/en/scholarship')
            ->assertOk()
            ->assertDontSee(__('scholarship.home.cta'));
    }

    /** Every page that carries the button, not just the front one. */
    public function test_no_scholarship_page_offers_a_parent_the_application(): void
    {
        $parent = $this->parent();
        $region = array_key_first(config('scholarship.regions'));
        $university = config('scholarship.universities')[0]['slug'];

        foreach ([
            '/en/scholarship',
            '/en/scholarship/about',
            "/en/scholarship/regions/{$region}",
            "/en/scholarship/universities/{$university}",
        ] as $url) {
            $this->actingAs($parent, 'attendee')
                ->get($url)
                ->assertOk()
                ->assertDontSee(__('scholarship.home.cta'), false);
        }
    }

    public function test_it_opens_in_every_language_for_a_parent(): void
    {
        $parent = $this->parent();

        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->actingAs($parent, 'attendee')
                ->get("/{$locale}/scholarship")
                ->assertSuccessful()
                ->assertSee(__('scholarship.not_for_you.title', [], $locale));
        }
    }

    /* ------------------------------------------------------ opportunities -- */

    public function test_the_board_tells_a_parent_who_the_offers_are_aimed_at(): void
    {
        $this->actingAs($this->parent(), 'attendee')
            ->get('/en/opportunities')
            ->assertOk()
            ->assertSee(__('opportunities.for_students.title'))
            ->assertSee(__('opportunities.for_students.send'));
    }

    public function test_a_student_is_not_told_that(): void
    {
        $this->actingAs($this->registration(), 'attendee')
            ->get('/en/opportunities')
            ->assertOk()
            ->assertDontSee(__('opportunities.for_students.title'));
    }

    /** A signed-out parent must not be told to register as a student. */
    public function test_the_locked_board_does_not_send_everybody_down_the_student_route(): void
    {
        $html = $this->get('/en/opportunities')->assertOk()->getContent();

        $this->assertStringContainsString(__('opportunities.locked_cta'), $html);
        $this->assertStringNotContainsString('Register as a student', $html);
    }
}
