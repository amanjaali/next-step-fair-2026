<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Registration;
use App\Models\Setting;
use App\Services\BadgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The Kurdistan Students Association, in the lockup and everywhere else.
 *
 * The association joined after the design was drawn, so no logo file ships with
 * the site: the mark is uploaded on the Brand images screen and nothing is drawn
 * for it before that. Both halves of that matter — a partner whose logo silently
 * fails to appear is one problem, a broken image in the header of every page in
 * the meantime is a worse one.
 */
class PartnerMarksTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function upload(string $path = 'brand/ksa.png'): void
    {
        Setting::put('brand_images', ['ksa' => $path], 'images');
    }

    /* ------------------------------------------------------- before upload -- */

    public function test_nothing_is_drawn_for_the_association_until_a_logo_is_uploaded(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertStringNotContainsString('/storage/brand/ksa.png', $html);
        $this->assertStringNotContainsString('src=""', $html);
    }

    public function test_the_shipped_marks_still_show_with_nothing_uploaded(): void
    {
        $this->get('/en')->assertOk()->assertSee('/assets/brand/mohe.png', false);
    }

    /* -------------------------------------------------------- after upload -- */

    public function test_the_mark_appears_in_the_header_and_the_footer_once_uploaded(): void
    {
        $this->upload();

        // A page with no partner wall of its own, so what is counted is the
        // chrome alone: the header lockup and the footer partnership band.
        $chrome = $this->get('/en/agenda')->assertOk()->getContent();

        $this->assertSame(2, substr_count($chrome, '/storage/brand/ksa.png'));

        // The home page adds the logo wall, where it appears once more.
        $home = $this->get('/en')->assertOk()->getContent();

        $this->assertSame(3, substr_count($home, '/storage/brand/ksa.png'));
    }

    /**
     * The agreed sequence is Next Step, then the Ministry, then the association.
     * Order in the markup is order on the screen here, so it is what is checked.
     */
    public function test_it_comes_after_the_ministry_mark_in_the_lockup(): void
    {
        $this->upload();

        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($html, '/storage/brand/ksa.png'),
            strpos($html, '/assets/brand/mohe.png'),
        );
    }

    /**
     * Whatever shape of file is uploaded, the header keeps its one line: the
     * mark is capped in width, not only in height. Measured in Chromium, English
     * at 1280 wraps somewhere between 68px and 76px of extra lockup.
     */
    public function test_the_header_mark_is_capped_so_a_wide_logo_cannot_break_the_menu(): void
    {
        $this->upload();

        $html = $this->get('/en')->assertOk()->getContent();

        $mark = strpos($html, '/storage/brand/ksa.png');
        $tag = substr($html, strrpos(substr($html, 0, $mark), '<img'), 400);

        $this->assertStringContainsString('max-width:64px', $tag);
    }

    public function test_the_mark_is_on_every_page_in_every_language(): void
    {
        $this->upload();

        foreach (['en', 'ku', 'ar'] as $locale) {
            foreach ([$locale, $locale.'/sponsors', $locale.'/about', $locale.'/agenda'] as $path) {
                $this->get('/'.$path)
                    ->assertOk()
                    ->assertSee('/storage/brand/ksa.png', false);
            }
        }
    }

    /* ------------------------------------------------- the partners page --- */

    public function test_the_association_is_listed_among_the_strategic_partners(): void
    {
        $names = [
            'en' => 'Kurdistan Students Association',
            'ku' => 'کۆمەڵەی خوێندکارانی کوردستان',
            'ar' => 'جمعية طلبة كوردستان',
        ];

        foreach ($names as $locale => $name) {
            $this->get('/'.$locale.'/sponsors')->assertOk()->assertSee($name, false);
        }
    }

    public function test_it_sits_third_among_the_strategic_partners(): void
    {
        $slugs = Organization::where('kind', Organization::KIND_STRATEGIC)
            ->orderBy('sort')->pluck('slug')->all();

        $this->assertSame(['mohe', 'krg', 'ksa'], $slugs);
    }

    /**
     * One upload, three places. The partners page reads the same slot as the
     * header, so an editor never has to find and load the file twice.
     */
    public function test_the_partners_page_uses_the_logo_uploaded_for_the_header(): void
    {
        $ksa = Organization::where('slug', 'ksa')->firstOrFail();

        $this->assertNull($ksa->logoUrl());

        $this->upload();

        $this->assertSame('/storage/brand/ksa.png', $ksa->fresh()->logoUrl());
    }

    /* ------------------------------------------- the card and the badge --- */

    /**
     * The card people post carries the marks at the top, not the bottom.
     *
     * A feed thumbnail crops the bottom corner, and that is where these used to
     * sit — so the partnership was on the artwork and invisible in the place the
     * artwork is actually seen.
     */
    public function test_the_share_card_carries_the_marks_above_the_headline(): void
    {
        $this->upload();

        $html = $this->get('/en/share/card/student/feed')->assertOk()->getContent();

        $this->assertStringContainsString('/storage/brand/ksa.png', $html);
        $this->assertStringContainsString('/assets/brand/mohe.png', $html);

        // Above the headline, which is the whole point of the move.
        $this->assertLessThan(
            strpos($html, '<h1>'),
            strpos($html, '/storage/brand/ksa.png'),
            'The partnership marks are below the headline — they were meant to move to the top.'
        );
    }

    public function test_the_marks_are_no_longer_repeated_in_the_card_footer(): void
    {
        $this->upload();

        $html = $this->get('/en/share/card/student/feed')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '/storage/brand/ksa.png'));
        $this->assertSame(1, substr_count($html, '/assets/brand/mohe.png'));
    }

    /**
     * The badge had no partnership marks at all. It is the one artefact three
     * thousand people hold in their hand for three days.
     */
    public function test_the_badge_carries_the_marks_above_the_name(): void
    {
        $this->upload('brand/ksa.png');

        // The uploaded file has to exist for the badge: its renderers read the
        // bytes rather than a URL, so a missing file is silently left out.
        Storage::disk('public')->put('brand/ksa.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        $registration = Registration::create([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'Zardasht Aziz',
            'phone' => '7701119911',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'days' => [1, 2, 3],
            'confirmed_at' => now(),
        ]);

        $payload = app(BadgeService::class)->payload($registration);

        $this->assertNotEmpty($payload['marks'], 'The badge was handed no partnership marks.');

        $html = view('badges.badge', $payload)->render();

        // Data URIs, not paths: DomPDF, the browser and GD all read this view.
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertLessThan(
            strpos($html, 'class="name"'),
            strpos($html, 'class="partners"'),
            'The marks are below the name; they belong at the top of the badge.'
        );
    }

    /** An upload replaces a shipped mark rather than sitting beside it. */
    public function test_an_upload_replaces_the_shipped_ministry_mark(): void
    {
        Setting::put('brand_images', ['mohe' => 'brand/mohe-2027.png'], 'images');

        $mohe = Organization::where('slug', 'mohe')->firstOrFail();

        $this->assertSame('/storage/brand/mohe-2027.png', $mohe->logoUrl());
        $this->get('/en')->assertOk()->assertSee('/storage/brand/mohe-2027.png', false);
    }
}
