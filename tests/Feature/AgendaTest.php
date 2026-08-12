<?php

namespace Tests\Feature;

use App\Models\EventSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The agenda, and the two programmes it has to keep apart.
 *
 * Day 1 runs a policy conference for invited delegates at the same time as an
 * expo anyone can walk into. Run together in one list they are indistinguishable
 * — a student scrolls the day and finds a closed roundtable for ministry
 * delegations between two of their own workshops, with a button offering to add
 * it to their agenda.
 */
class AgendaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /* ------------------------------------------------- keeping them apart -- */

    public function test_day_one_is_split_into_the_conference_and_the_expo(): void
    {
        $this->get('/en/agenda?day=1')
            ->assertOk()
            ->assertSee(__('site.pages.agenda.tracks.conference_who'))
            ->assertSee(__('site.pages.agenda.tracks.expo_who'))
            ->assertSee(__('site.pages.agenda.tracks.conference_note'))
            ->assertSee(__('site.pages.agenda.tracks.expo_note'));
    }

    /**
     * Days 2 and 3 are expo only, so a heading saying so would be a label on the
     * only thing there — noise, not orientation.
     */
    public function test_the_other_days_carry_no_track_headings(): void
    {
        foreach ([2, 3] as $day) {
            $this->get("/en/agenda?day={$day}")
                ->assertOk()
                ->assertDontSee(__('site.pages.agenda.tracks.conference_who'))
                ->assertDontSee(__('site.pages.agenda.tracks.expo_who'));
        }
    }

    /** Conference before expo: on Day 1 it is what opens the event. */
    public function test_the_conference_is_listed_before_the_expo(): void
    {
        $html = $this->get('/en/agenda?day=1')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($html, __('site.pages.agenda.tracks.expo_who')),
            strpos($html, __('site.pages.agenda.tracks.conference_who')),
        );
    }

    /* ------------------------------------------------------ one filter row -- */

    /**
     * There were two unlabelled chip rows, each opening with an identical "All",
     * and "Conference" and "Expo" appeared in both — as a programme in one and a
     * kind of session in the other. That collision is what made the page
     * unreadable, so no session type may carry a track's name again.
     */
    public function test_no_session_type_is_named_after_a_track(): void
    {
        $types = EventSession::query()->distinct()->pluck('type');

        $this->assertNotContains('Conference', $types);
        $this->assertNotContains('Expo', $types);
    }

    public function test_the_filter_row_says_what_it_filters(): void
    {
        $this->get('/en/agenda')->assertOk()->assertSee(__('site.pages.agenda.filter_by'));
    }

    /** The track is no longer offered as a filter, but old links still work. */
    public function test_a_track_link_still_narrows_the_page(): void
    {
        $html = $this->get('/en/agenda?day=1&track=conference')->assertOk()->getContent();

        // One track left, so the headings drop away.
        $this->assertStringNotContainsString(__('site.pages.agenda.tracks.expo_who'), $html);
    }

    public function test_filtering_by_type_still_works(): void
    {
        $this->get('/en/agenda?day=1&type=Workshop')->assertOk();
    }

    /* ------------------------------------------------------- closed doors -- */

    /** A delegations-only roundtable must not offer a student a save button. */
    public function test_a_closed_conference_session_says_so_instead_of_offering_a_button(): void
    {
        $closed = EventSession::query()
            ->where('track', 'conference')->where('type', 'Roundtable')->firstOrFail();

        $this->assertFalse((bool) $closed->bookable);

        $this->get('/en/agenda?day=1')
            ->assertOk()
            ->assertSee(__('site.pages.agenda.by_invitation'));
    }

    /* ------------------------------------------------------------ reading -- */

    /** The chips are labels, not raw column values, so they translate. */
    public function test_session_types_are_shown_in_the_readers_language(): void
    {
        $this->assertSame('Workshop', EventSession::labelForType('Workshop'));

        $this->app->setLocale('ar');
        $this->assertSame(__('site.pages.agenda.types.workshop'), EventSession::labelForType('Workshop'));

        // Anything the team invents in the dashboard falls through untranslated
        // rather than rendering a missing key.
        $this->assertSame('Fireside chat', EventSession::labelForType('Fireside chat'));
    }

    public function test_it_opens_in_every_language(): void
    {
        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get("/{$locale}/agenda")->assertSuccessful();
        }
    }
}
