<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The opportunities board, and the home page that changes once you have one.
 *
 * Two rules carry most of the value here: an opportunity is only shown to the
 * audience it is for, and an expired one disappears. A board that shows a parent
 * a grade 12 scholarship, or tells a student they were too late for something
 * nobody took down, is worse than no board.
 */
class OpportunityTest extends TestCase
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
            'full_name' => 'Lava Rebwar',
            'phone' => '7719997001',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'email' => 'lava.opps@example.com',
            'password' => 'a-good-password',
            'education_stage' => 'grade12',
            'days' => [1, 2, 3],
            'verified_at' => now(),
            'confirmed_at' => now(),
            'badge_generated_at' => now(),
        ], $attributes));
    }

    private function opportunity(array $attributes = []): Opportunity
    {
        return Opportunity::create(array_merge([
            'slug' => 'test-'.uniqid(),
            'kind' => Opportunity::KIND_OFFER,
            'partner_name' => 'A University',
            'audience' => Opportunity::AUDIENCE_STUDENTS,
            'published' => true,
            'title' => ['en' => 'A real offer'],
            'summary' => ['en' => 'Something worth having.'],
            'action_url' => 'https://example.com/apply',
        ], $attributes));
    }

    /* -------------------------------------------------------------- board -- */

    public function test_the_board_opens_in_every_language(): void
    {
        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get("/{$locale}/opportunities")->assertSuccessful();
        }
    }

    /** A stranger is told what is behind it, not shown a list they cannot use. */
    public function test_a_visitor_sees_the_reason_to_register_and_no_listings(): void
    {
        $this->opportunity(['title' => ['en' => 'Secret Offer XYZ']]);

        $this->get('/en/opportunities')
            ->assertOk()
            ->assertSee(__('opportunities.locked_title'))
            ->assertDontSee('Secret Offer XYZ');
    }

    public function test_a_registered_student_sees_the_listings(): void
    {
        $student = $this->student();
        $this->opportunity(['title' => ['en' => 'Visible Offer ABC']]);

        $this->actingAs($student, 'attendee')
            ->get('/en/opportunities')
            ->assertOk()
            ->assertSee('Visible Offer ABC')
            ->assertDontSee(__('opportunities.locked_title'));
    }

    /* ----------------------------------------------------------- audience -- */

    public function test_a_parent_is_not_shown_a_grade_twelve_opportunity(): void
    {
        $parent = $this->student([
            'type' => Registration::TYPE_PARENT,
            'phone' => '7719997002',
            'email' => 'parent.opps@example.com',
            'password' => null,
            'education_stage' => null,
        ]);

        $this->opportunity(['audience' => Opportunity::AUDIENCE_GRADE12, 'title' => ['en' => 'For Leavers Only']]);
        $this->opportunity(['audience' => Opportunity::AUDIENCE_PARENTS, 'title' => ['en' => 'A Parents Evening']]);

        $this->actingAs($parent, 'attendee')
            ->get('/en/opportunities')
            ->assertOk()
            ->assertSee('A Parents Evening')
            ->assertDontSee('For Leavers Only');
    }

    public function test_a_student_already_at_university_misses_the_school_leaver_ones(): void
    {
        $student = $this->student(['education_stage' => 'university']);

        $this->opportunity(['audience' => Opportunity::AUDIENCE_GRADE12, 'title' => ['en' => 'Leavers Scholarship']]);
        $this->opportunity(['audience' => Opportunity::AUDIENCE_STUDENTS, 'title' => ['en' => 'Open To All Students']]);

        $this->actingAs($student, 'attendee')
            ->get('/en/opportunities')
            ->assertOk()
            ->assertSee('Open To All Students')
            ->assertDontSee('Leavers Scholarship');
    }

    /* ------------------------------------------------------------- expiry -- */

    public function test_a_closed_opportunity_disappears(): void
    {
        $student = $this->student();

        $closed = $this->opportunity([
            'closes_at' => today()->subDay(),
            'title' => ['en' => 'Closed Yesterday'],
        ]);

        $this->actingAs($student, 'attendee')
            ->get('/en/opportunities')
            ->assertOk()
            ->assertDontSee('Closed Yesterday');

        // And its own page is gone too, rather than sitting there taking applications.
        $this->actingAs($student, 'attendee')
            ->get('/en/opportunities/'.$closed->slug)
            ->assertNotFound();
    }

    public function test_one_not_yet_open_is_not_shown_early(): void
    {
        $student = $this->student();
        $this->opportunity(['opens_at' => today()->addWeek(), 'title' => ['en' => 'Opens Next Week']]);

        $this->actingAs($student, 'attendee')
            ->get('/en/opportunities')
            ->assertOk()
            ->assertDontSee('Opens Next Week');
    }

    public function test_closing_soon_is_only_said_when_it_is_nearly_true(): void
    {
        $soon = $this->opportunity(['closes_at' => today()->addDays(5)]);
        $later = $this->opportunity(['closes_at' => today()->addMonths(4)]);
        $never = $this->opportunity(['closes_at' => null]);

        $this->assertTrue($soon->isClosingSoon());
        $this->assertFalse($later->isClosingSoon());
        $this->assertFalse($never->isClosingSoon());
        $this->assertNull($never->daysLeft());
    }

    /** Something with a deadline outranks something without one. */
    public function test_the_ones_with_a_clock_on_them_come_first(): void
    {
        Opportunity::query()->delete();

        $this->opportunity(['closes_at' => null, 'title' => ['en' => 'No Deadline']]);
        $this->opportunity(['closes_at' => today()->addMonths(3), 'title' => ['en' => 'Closes In Autumn']]);
        $this->opportunity(['closes_at' => today()->addDays(3), 'title' => ['en' => 'Closes This Week']]);

        $order = Opportunity::live()->ranked()->get()->map(fn ($o) => $o->t('title'))->all();

        $this->assertSame(['Closes This Week', 'Closes In Autumn', 'No Deadline'], $order);
    }

    /* ------------------------------------------------------- going onward -- */

    public function test_following_one_out_is_counted_for_the_partner(): void
    {
        $student = $this->student();
        $opportunity = $this->opportunity();

        $this->actingAs($student, 'attendee')
            ->get('/en/opportunities/'.$opportunity->slug.'/go')
            ->assertRedirect('https://example.com/apply');

        $opportunity = $opportunity->fresh();

        // Reading it and going through are separate numbers. One column for both
        // would tell a partner every placement worked.
        $this->assertSame(1, $opportunity->follow_count);
        $this->assertSame(0, $opportunity->view_count);
    }

    public function test_opening_the_page_counts_as_a_read_not_a_follow(): void
    {
        $student = $this->student();
        $opportunity = $this->opportunity();

        $this->actingAs($student, 'attendee')
            ->get('/en/opportunities/'.$opportunity->slug)
            ->assertOk();

        $opportunity = $opportunity->fresh();

        $this->assertSame(1, $opportunity->view_count);
        $this->assertSame(0, $opportunity->follow_count);
    }

    /* --------------------------------------------------------- home page -- */

    /** A registered visitor is not sold registration a second time. */
    public function test_the_home_page_changes_once_you_have_registered(): void
    {
        $guest = $this->get('/en')->assertOk();
        $guest->assertSee(__('site.cta.register_fair'));
        $guest->assertDontSee(__('site.home.signed_in.my_badge'));

        $student = $this->student();
        $this->opportunity(['title' => ['en' => 'On The Home Page']]);

        $signedIn = $this->actingAs($student, 'attendee')->get('/en')->assertOk();

        $signedIn->assertSee(__('site.home.signed_in.welcome', ['name' => 'Lava']));
        $signedIn->assertSee(__('site.home.signed_in.my_badge'));
        $signedIn->assertSee('On The Home Page');
        $signedIn->assertDontSee(__('site.cta.register_fair'));
    }

    /** A parent has no scholarship to apply for, so no card pretends otherwise. */
    public function test_the_next_step_cards_only_offer_what_this_person_can_use(): void
    {
        $parent = $this->student([
            'type' => Registration::TYPE_PARENT,
            'phone' => '7719997003',
            'email' => 'parent.home@example.com',
            'password' => null,
            'education_stage' => null,
        ]);

        $this->actingAs($parent, 'attendee')
            ->get('/en')
            ->assertOk()
            ->assertSee(__('site.home.signed_in.cards.agenda'))
            ->assertDontSee(__('site.home.signed_in.cards.scholarship'));

        $this->actingAs($this->student(), 'attendee')
            ->get('/en')
            ->assertOk()
            ->assertSee(__('site.home.signed_in.cards.scholarship'));
    }

    /** The menu item is worthless to somebody who cannot open what it leads to. */
    public function test_the_menu_gains_opportunities_only_once_signed_in(): void
    {
        $this->get('/en')->assertOk()->assertDontSee(route('opportunities', ['locale' => 'en']));

        $this->actingAs($this->student(), 'attendee')
            ->get('/en')
            ->assertOk()
            ->assertSee(route('opportunities', ['locale' => 'en']));
    }
}
