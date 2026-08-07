<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Services\ShareKit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "I'm attending Next Step Fair 2026."
 *
 * Two things have to hold. The words have to fit the person — a caption written
 * for a grade 12 student is not one a director general would post — and the
 * badge QR must never appear on anything meant for a public feed.
 */
class ShareKitTest extends TestCase
{
    use RefreshDatabase;

    private function registration(array $attributes = []): Registration
    {
        return Registration::create(array_merge([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'Lava Rebwar',
            'phone' => '7719996201',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'days' => [1, 2, 3],
            'verified_at' => now(),
            'confirmed_at' => now(),
            'badge_generated_at' => now(),
        ], $attributes));
    }

    /* ---------------------------------------------------------- the words -- */

    public function test_each_audience_gets_its_own_caption(): void
    {
        $captions = [];

        foreach ([
            Registration::TYPE_STUDENT,
            Registration::TYPE_PARENT,
            Registration::TYPE_GOVERNMENT,
            Registration::TYPE_PRIVATE,
        ] as $i => $type) {
            $registration = $this->registration([
                'type' => $type,
                'phone' => '77199962'.(10 + $i),
                'track' => in_array($type, [Registration::TYPE_STUDENT, Registration::TYPE_PARENT], true)
                    ? Registration::TRACK_FAIR
                    : Registration::TRACK_CONFERENCE,
            ]);

            $captions[$type] = ShareKit::for($registration)->caption();
        }

        // Four audiences, four different pieces of writing.
        $this->assertCount(4, array_unique($captions));

        foreach ($captions as $type => $caption) {
            $this->assertNotEmpty($caption, $type);

            // Every placeholder resolved. A caption still carrying :venue is
            // worse than no caption at all — somebody will paste it as it is.
            foreach ([':name', ':dates', ':venue', ':city', ':url', ':tags'] as $placeholder) {
                $this->assertStringNotContainsString($placeholder, $caption, "$type / $placeholder");
            }
        }
    }

    public function test_the_caption_carries_the_event_and_a_link(): void
    {
        $caption = ShareKit::for($this->registration())->caption();

        $this->assertStringContainsString('Cultural Factory', $caption);
        $this->assertStringContainsString(route('attending', ['locale' => 'en', 'who' => 'student']), $caption);
        $this->assertStringContainsString('#NextStepFair', $caption);
    }

    /**
     * A hash is a neutral character. Without a mark in front of it, the tag line
     * on a Kurdish or Arabic caption comes out as "…#Kurdistan#".
     */
    public function test_the_tag_line_keeps_its_direction_in_kurdish_and_arabic(): void
    {
        $registration = $this->registration();

        foreach (['ku', 'ar'] as $locale) {
            $this->assertStringContainsString("\u{200E}#NextStepFair", ShareKit::for($registration)->caption($locale));
        }

        // Nothing invisible where nothing is needed.
        $this->assertStringNotContainsString("\u{200E}", ShareKit::for($registration)->caption('en'));
    }

    /** Somebody reading the site in Kurdish is about to write a Kurdish post. */
    public function test_the_caption_follows_the_page_language(): void
    {
        $registration = $this->registration(['locale' => 'en']);

        $english = ShareKit::for($registration)->caption('en');
        $kurdish = ShareKit::for($registration)->caption('ku');

        $this->assertNotSame($english, $kurdish);
        $this->assertStringContainsString('Next Step', $english);
    }

    /* ------------------------------------------------------- the pictures -- */

    public function test_every_card_has_actually_been_rendered(): void
    {
        foreach (['en', 'ku', 'ar'] as $locale) {
            foreach (['student', 'parent', 'delegate'] as $variant) {
                foreach (ShareKit::renderedFormats() as $format) {
                    $file = public_path("assets/share/{$locale}-{$variant}-{$format}.png");

                    $this->assertFileExists($file);
                    // A file that exists but is empty is a broken image on a page
                    // that is asking somebody to post it.
                    $this->assertGreaterThan(10_000, filesize($file), basename($file));
                }
            }
        }
    }

    public function test_the_card_a_person_is_offered_matches_who_they_are(): void
    {
        $student = ShareKit::for($this->registration());
        $parent = ShareKit::for($this->registration(['type' => Registration::TYPE_PARENT, 'phone' => '7719996221']));
        $delegate = ShareKit::for($this->registration([
            'type' => Registration::TYPE_GOVERNMENT,
            'track' => Registration::TRACK_CONFERENCE,
            'phone' => '7719996222',
        ]));

        $this->assertStringContainsString('en-student-feed.png', $student->image('feed'));
        $this->assertStringContainsString('en-parent-feed.png', $parent->image('feed'));
        $this->assertStringContainsString('en-delegate-story.png', $delegate->image('story'));
    }

    public function test_the_artwork_page_renders_for_every_variant(): void
    {
        foreach (['en', 'ku', 'ar'] as $locale) {
            foreach (['student', 'parent', 'delegate'] as $variant) {
                foreach (ShareKit::renderedFormats() as $format) {
                    $this->get("/{$locale}/share/card/{$variant}/{$format}")->assertSuccessful();
                }
            }
        }

        $this->get('/en/share/card/nobody/feed')->assertNotFound();
        $this->get('/en/share/card/student/billboard')->assertNotFound();
    }

    /* ------------------------------------------------------------ the page -- */

    public function test_the_share_page_needs_a_registration(): void
    {
        $this->get('/en/me/share')->assertRedirect();

        $this->actingAs($this->registration(), 'attendee')->get('/en/me/share')->assertOk();
    }

    /**
     * The QR is the entry credential. On a public feed it is a free pass for
     * whoever screenshots it first, so nothing here may carry it.
     */
    public function test_nothing_offered_for_sharing_carries_the_badge_qr(): void
    {
        $registration = $this->registration();

        $html = $this->actingAs($registration, 'attendee')->get('/en/me/share')->assertOk()->getContent();

        $this->assertStringNotContainsString($registration->ticket_id, $html);
        $this->assertStringNotContainsString('data:image/png;base64', $html);
        $this->assertStringNotContainsString(route('ticket.png', $registration->ticket_id), $html);

        foreach (ShareKit::formats() as $format) {
            $card = $this->get("/en/share/card/student/{$format}")->assertOk()->getContent();
            $this->assertStringNotContainsString('qr', strtolower(strip_tags($card)));
        }
    }

    /* --------------------------------------------------- the shared link -- */

    /**
     * A link to the home page unfurls as the generic site card and lands
     * somebody who was told "I'm attending" on a page that says nothing about
     * that. This one is the page they were promised.
     */
    public function test_a_shared_link_lands_on_a_page_about_the_thing_shared(): void
    {
        foreach (['student', 'parent', 'delegate'] as $who) {
            $this->get("/en/attending/{$who}")
                ->assertOk()
                // Escaped, as Blade prints it: the title has an apostrophe in it.
                ->assertSee(__('share.landing.title'))
                ->assertSee(__("share.landing.lines.{$who}"));
        }

        $this->get('/en/attending/nobody')->assertNotFound();
    }

    public function test_the_landing_page_opens_in_every_language_and_needs_no_account(): void
    {
        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get("/{$locale}/attending")->assertSuccessful();
        }
    }

    /**
     * What LinkedIn and Facebook read. Without a 1200×630 image and its declared
     * dimensions the first crawl often shows no picture at all — and the first
     * crawl is the one that gets cached.
     */
    public function test_the_link_carries_a_preview_the_platforms_can_use(): void
    {
        $html = $this->get('/en/attending/student')->assertOk()->getContent();

        $this->assertStringContainsString('property="og:image" content="'.asset('assets/share/en-student-og.png'), $html);
        $this->assertStringContainsString('property="og:image:width" content="1200"', $html);
        $this->assertStringContainsString('property="og:image:height" content="630"', $html);
        $this->assertStringContainsString('name="twitter:card" content="summary_large_image"', $html);
        $this->assertStringContainsString('name="twitter:image"', $html);
        $this->assertStringContainsString('property="og:title" content="'.e(__('share.landing.title')), $html);
    }

    public function test_the_share_buttons_point_at_the_landing_page(): void
    {
        $targets = ShareKit::for($this->registration())->targets()->keyBy('key');

        $landing = rawurlencode(route('attending', [
            'who' => 'student',
            'locale' => 'en',
            'utm_source' => 'attendee_share',
            'utm_medium' => 'social',
            'utm_campaign' => 'next_step_'.config('nextstep.event.year'),
        ]));

        $this->assertStringContainsString($landing, $targets['facebook']['url']);
        $this->assertStringContainsString($landing, $targets['linkedin']['url']);
    }

    /** Their name is what makes the card theirs; it is drawn in the browser. */
    public function test_the_card_is_set_up_to_be_signed_with_their_name(): void
    {
        $registration = $this->registration(['full_name' => 'Lava Rebwar']);

        $html = $this->actingAs($registration, 'attendee')->get('/en/me/share')->assertOk()->getContent();

        $this->assertStringContainsString('data-share-figure', $html);
        $this->assertStringContainsString('data-name="Lava Rebwar"', $html);

        // Geometry comes from one place, so the drawing and the artwork behind
        // it cannot drift apart.
        $slot = ShareKit::nameSlot(ShareKit::FORMAT_FEED);
        $this->assertStringContainsString('data-width="'.$slot['width'].'"', $html);
        $this->assertStringContainsString('data-x="'.$slot['x'].'"', $html);
    }

    /** The moment somebody is most likely to post is the moment they are told they are in. */
    public function test_the_confirmation_page_offers_it_straight_away(): void
    {
        $registration = $this->registration();

        $this->get('/en/register/fair/done/'.$registration->ticket_id)
            ->assertOk()
            ->assertSee(__('share.title'))
            ->assertSee('en-student-feed.png', false);
    }

    public function test_a_delegate_is_offered_it_too(): void
    {
        $registration = $this->registration([
            'track' => Registration::TRACK_CONFERENCE,
            'type' => Registration::TYPE_GOVERNMENT,
            'phone' => '7719996231',
            'email' => 'dg@ministry.example',
            'position' => 'Director General',
            'organization' => 'Ministry of Education',
        ]);

        $this->get('/en/register/conference/done/'.$registration->ticket_id)
            ->assertOk()
            ->assertSee(__('share.title'))
            ->assertSee('en-delegate-feed.png', false);
    }
}
