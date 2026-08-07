<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\MediaAlbum;
use App\Models\Page;
use App\Models\Post;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public static function paths(): array
    {
        return array_map(fn ($path) => [$path], [
            '', 'about', 'fair', 'conference', 'agenda', 'seminars', 'speakers',
            'universities', 'exhibitors', 'floor-plan', 'partners', 'sponsors',
            'news', 'blog', 'media', 'media/photos', 'media/videos', 'archive',
            'sdg', 'reports', 'scholarships', 'privacy', 'terms', 'press-kit',
            'contact', 'exhibit', 'register/fair', 'register/conference',
        ]);
    }

    #[DataProvider('paths')]
    public function test_pages_render_in_every_language(string $path): void
    {
        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get(trim("/{$locale}/{$path}", '/'))->assertOk();
        }
    }

    public function test_root_redirects_to_a_language(): void
    {
        $this->get('/')->assertRedirect('/en');
    }

    /**
     * People type /privacy and paste links with the language prefix stripped.
     * Those have to land on the page, not on a 404.
     */
    #[DataProvider('paths')]
    public function test_urls_without_a_language_prefix_redirect(string $path): void
    {
        $this->get('/'.$path)->assertRedirect($path === '' ? '/en' : '/en/'.$path);
    }

    public function test_a_genuine_typo_still_returns_404(): void
    {
        $this->get('/not-a-real-page')->assertNotFound();
        $this->get('/en/not-a-real-page')->assertNotFound();
    }

    /**
     * The skip link used to sit at left:-9999px, which LTR ignores but RTL counts
     * as page width — every Kurdish and Arabic page scrolled 10,000px sideways.
     */
    public function test_no_page_parks_content_off_canvas(): void
    {
        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get("/{$locale}")->assertOk()->assertDontSee('left: -9999px', false);
        }
    }

    /** Carbon's ku locale is Kurmanji in Latin script; the site is Sorani. */
    public function test_dates_use_local_month_names(): void
    {
        $this->get('/ku/fair')->assertOk()->assertSee('ئەیلول', false)->assertDontSee('rezber', false);
        $this->get('/ar/fair')->assertOk()->assertSee('أيلول', false)->assertDontSee('سبتمبر', false);
        $this->get('/en/fair')->assertOk()->assertSee('September', false);
    }

    /** The two track pages carry the bulk of the editorial content. */
    public function test_track_pages_carry_their_full_content(): void
    {
        foreach (['fair', 'conference'] as $key) {
            $page = Page::where('key', $key)->firstOrFail();
            $this->assertGreaterThanOrEqual(5, count($page->localisedSections()));
        }

        $this->get('/en/fair')->assertOk()->assertSee('Who exhibits')->assertSee('The three halls');
        $this->get('/en/conference')->assertOk()->assertSee('Who attends')->assertSee('Invitations and badges');
    }

    public function test_rtl_languages_mirror_the_layout(): void
    {
        $this->get('/ku')->assertOk()->assertSee('dir="rtl"', false);
        $this->get('/ar')->assertOk()->assertSee('dir="rtl"', false);
        $this->get('/en')->assertOk()->assertSee('dir="ltr"', false);
    }

    public function test_every_page_carries_hreflang_for_all_three_languages(): void
    {
        $response = $this->get('/en/agenda');

        $response->assertSee('hreflang="en"', false)
            ->assertSee('hreflang="ckb"', false)
            ->assertSee('hreflang="ar"', false)
            ->assertSee('hreflang="x-default"', false);
    }

    public function test_detail_pages_render(): void
    {
        $this->get('/en/news/'.Post::published()->news()->first()->slug)->assertOk();
        $this->get('/en/blog/'.Post::published()->blog()->first()->slug)->assertOk();
        $this->get('/en/speakers/'.Speaker::first()->slug)->assertOk();
        $this->get('/en/archive/'.Edition::first()->year)->assertOk();
        $this->get('/en/media/photos/'.MediaAlbum::first()->slug)->assertOk();
    }

    public function test_the_footer_is_on_every_page(): void
    {
        foreach (['/en', '/en/agenda', '/en/register/fair', '/ku/sponsors'] as $path) {
            $this->get($path)->assertSee('assets/brand/krg.png', false);
        }
    }

    public function test_language_switch_keeps_the_current_page(): void
    {
        $this->get('/lang/ku?to=en/agenda&query=day%3D2')
            ->assertRedirect('/ku/agenda?day=2');
    }

    public function test_home_page_publishes_event_schema(): void
    {
        $this->get('/en')
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type":"Event"', false);
    }

    public function test_agenda_filters_by_day_and_type(): void
    {
        $this->get('/en/agenda?day=2')->assertOk();
        $this->get('/en/agenda?day=1&track=conference')->assertOk();
        $this->get('/en/agenda?day=1&type=Workshop')->assertOk();
    }

    public function test_agenda_exports(): void
    {
        $this->get('/en/agenda/export.ics')
            ->assertOk()
            ->assertHeader('content-type', 'text/calendar; charset=utf-8');

        $this->get('/en/agenda/export.pdf')->assertOk();
    }

    public function test_newsletter_signup_records_a_subscriber(): void
    {
        $this->post('/en/newsletter', ['email' => 'reader@example.com'])
            ->assertRedirect();

        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'reader@example.com']);
    }

    public function test_the_newsletter_honeypot_silently_rejects_bots(): void
    {
        $this->post('/en/newsletter', ['email' => 'bot@example.com', 'ns_hp' => 'spam ltd']);

        $this->assertDatabaseMissing('newsletter_subscribers', ['email' => 'bot@example.com']);
    }

    public function test_contact_form_creates_a_lead(): void
    {
        $this->post('/en/contact', [
            'name' => 'Rojin Ahmed',
            'email' => 'rojin@example.com',
            'message' => 'Can schools book a group visit?',
        ])->assertRedirect();

        $this->assertDatabaseHas('leads', ['email' => 'rojin@example.com', 'type' => 'contact']);
    }
}
