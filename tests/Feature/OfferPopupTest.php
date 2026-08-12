<?php

namespace Tests\Feature;

use App\Models\OfferPopup;
use App\Models\Opportunity;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The popup on the home page.
 *
 * Interrupting somebody is a cost you can only spend once, so the rules that
 * matter are: it appears only when the team switched it on, only to the people
 * it is for, only on the home page, and only once — until it is edited, which is
 * the whole point of a popup that changes daily.
 */
class OfferPopupTest extends TestCase
{
    use RefreshDatabase;

    private function popup(array $attributes = [], int $items = 1): OfferPopup
    {
        $popup = OfferPopup::create(array_merge([
            'title' => ['en' => 'Three things open to you'],
            'audience' => Opportunity::AUDIENCE_STUDENTS,
            'active' => true,
        ], $attributes));

        for ($i = 0; $i < $items; $i++) {
            $popup->items()->create([
                'title' => ['en' => 'Offer number '.($i + 1)],
                'body' => ['en' => 'Something worth knowing.'],
                'action_url' => 'https://example.com/offer',
                'sort' => $i,
            ]);
        }

        return $popup->fresh();
    }

    private function student(array $attributes = []): Registration
    {
        return Registration::create(array_merge([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'Lava Rebwar',
            'phone' => '7719994401',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'email' => 'lava.popup@example.com',
            'password' => 'a-good-password',
            'education_stage' => 'grade12',
            'days' => [1, 2, 3],
            'verified_at' => now(),
            'confirmed_at' => now(),
        ], $attributes));
    }

    /* ------------------------------------------------------ when it shows -- */

    public function test_it_shows_on_the_home_page_when_it_is_switched_on(): void
    {
        $popup = $this->popup([], 3);

        $html = $this->actingAs($this->student(), 'attendee')->get('/en')->assertOk()->getContent();

        $this->assertStringContainsString($popup->t('title'), $html);
        $this->assertStringContainsString('Offer number 1', $html);
        $this->assertStringContainsString('Offer number 3', $html);
    }

    /** Several offers, one interruption. */
    public function test_every_offer_in_it_is_shown(): void
    {
        $this->popup([], 5);

        $html = $this->actingAs($this->student(), 'attendee')->get('/en')->assertOk()->getContent();

        foreach (range(1, 5) as $n) {
            $this->assertStringContainsString("Offer number {$n}", $html);
        }
    }

    public function test_a_popup_that_is_switched_off_does_not_appear(): void
    {
        $popup = $this->popup(['active' => false]);

        $this->actingAs($this->student(), 'attendee')
            ->get('/en')->assertOk()
            ->assertDontSee($popup->t('title'));
    }

    /**
     * An empty box is worse than no box, and it is easy to create one by
     * switching a popup on before the offers have been written.
     */
    public function test_a_popup_with_no_offers_in_it_does_not_appear(): void
    {
        $popup = $this->popup([], 0);

        $this->actingAs($this->student(), 'attendee')
            ->get('/en')->assertOk()
            ->assertDontSee($popup->t('title'));
    }

    public function test_it_respects_its_dates(): void
    {
        $over = $this->popup(['ends_at' => today()->subDay(), 'title' => ['en' => 'Finished Yesterday']]);
        $this->actingAs($this->student(), 'attendee')->get('/en')->assertOk()->assertDontSee('Finished Yesterday');

        $over->delete();

        $this->popup(['starts_at' => today()->addWeek(), 'title' => ['en' => 'Starts Next Week']]);
        $this->actingAs($this->student(), 'attendee')->get('/en')->assertOk()->assertDontSee('Starts Next Week');
    }

    /* -------------------------------------------------------- who sees it -- */

    public function test_a_students_popup_is_not_put_in_front_of_a_parent(): void
    {
        $popup = $this->popup(['audience' => Opportunity::AUDIENCE_STUDENTS]);

        $parent = $this->student([
            'type' => Registration::TYPE_PARENT,
            'phone' => '7719994402',
            'email' => 'parent.popup@example.com',
            'password' => null,
            'education_stage' => null,
        ]);

        $this->actingAs($parent, 'attendee')->get('/en')->assertOk()->assertDontSee($popup->t('title'));
        $this->actingAs($this->student(), 'attendee')->get('/en')->assertOk()->assertSee($popup->t('title'));
    }

    public function test_one_for_everyone_reaches_a_signed_out_visitor(): void
    {
        $popup = $this->popup(['audience' => Opportunity::AUDIENCE_EVERYONE]);

        $this->get('/en')->assertOk()->assertSee($popup->t('title'));
    }

    /** It is the home page popup, not a popup on every page. */
    public function test_it_does_not_follow_them_around_the_site(): void
    {
        $popup = $this->popup(['audience' => Opportunity::AUDIENCE_EVERYONE]);

        foreach (['/en/agenda', '/en/universities', '/en/speakers'] as $url) {
            $this->get($url)->assertOk()->assertDontSee($popup->t('title'));
        }
    }

    /* ------------------------------------------------------ no flickering -- */

    /**
     * It must not arrive on screen open.
     *
     * The page decides in the browser whether to show it, and that decision
     * cannot happen until Alpine has booted — a few hundred milliseconds after
     * the HTML lands. Anything that renders visible in that gap appears, then
     * disappears when Alpine catches up, then appears again when the timer
     * fires. Three states where there should be one.
     */
    public function test_the_popup_does_not_arrive_on_screen_open(): void
    {
        $this->popup(['audience' => Opportunity::AUDIENCE_EVERYONE]);

        $html = $this->get('/en')->assertOk()->getContent();

        // Anchored on the popup's own label — the mobile menu in the header also
        // opens on `x-show="open"` and comes first in the page.
        $dialog = Str::afterLast(Str::before($html, 'aria-labelledby="ns-popup-title"'), '<div ');

        $this->assertStringContainsString('x-show="open"', $dialog);
        $this->assertStringContainsString('x-cloak', $dialog);
        $this->assertStringContainsString('display: none', $dialog);
    }

    /**
     * x-cloak is an attribute and nothing more — it hides nothing on its own.
     *
     * This rule went missing once and every x-cloak on the site silently stopped
     * working, the popup included. Nine components depend on it, so it is worth
     * a test of its own rather than trusting that nobody tidies it away.
     */
    public function test_the_stylesheet_actually_hides_x_cloak(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression('/\[x-cloak\]\s*\{[^}]*display:\s*none/', $css);
    }

    /* --------------------------------------------------------- once each -- */

    /**
     * The browser remembers a key carrying the popup's version. Editing it in the
     * dashboard has to change that key, or a popup rewritten daily would stay
     * closed for everybody who dismissed it once.
     */
    public function test_editing_it_brings_it_back_for_people_who_closed_it(): void
    {
        $popup = $this->popup();
        $before = $popup->dismissKey();

        $this->travel(2)->minutes();
        $popup->update(['title' => ['en' => 'Now it says something else']]);

        $this->assertNotSame($before, $popup->fresh()->dismissKey());
    }

    public function test_leaving_it_alone_keeps_it_closed(): void
    {
        $popup = $this->popup();

        $this->assertSame($popup->dismissKey(), $popup->fresh()->dismissKey());
    }

    /** The key must reach the page, or the browser has nothing to remember. */
    public function test_the_page_carries_the_version_key(): void
    {
        $popup = $this->popup(['audience' => Opportunity::AUDIENCE_EVERYONE]);

        $this->get('/en')->assertOk()->assertSee($popup->dismissKey(), false);
    }

    /* ------------------------------------------------------- going onward -- */

    public function test_following_an_offer_out_is_counted(): void
    {
        $popup = $this->popup();
        $item = $popup->items->first();

        $this->get('/en/popup/go/'.$item->id)->assertRedirect('https://example.com/offer');

        $this->assertSame(1, $item->fresh()->click_count);
        $this->assertSame(1, $popup->fresh()->view_count);
    }

    public function test_an_offer_with_no_link_is_not_a_dead_end(): void
    {
        $popup = $this->popup();
        $item = $popup->items->first();
        $item->update(['action_url' => null]);

        // Nothing renders a link for it, and the route refuses rather than
        // redirecting somebody to nowhere.
        $this->get('/en/popup/go/'.$item->id)->assertNotFound();
    }

    public function test_it_opens_in_every_language(): void
    {
        $this->popup(['audience' => Opportunity::AUDIENCE_EVERYONE]);

        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get("/{$locale}")->assertSuccessful();
        }
    }

    /** Two switched on: the most recently edited one wins, not both. */
    public function test_only_one_popup_is_ever_shown(): void
    {
        $this->popup(['title' => ['en' => 'The Older One'], 'audience' => Opportunity::AUDIENCE_EVERYONE]);
        $this->travel(1)->minute();
        $this->popup(['title' => ['en' => 'The Newer One'], 'audience' => Opportunity::AUDIENCE_EVERYONE]);

        $this->get('/en')->assertOk()
            ->assertSee('The Newer One')
            ->assertDontSee('The Older One');
    }
}
