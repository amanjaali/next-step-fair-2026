<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Services\Messaging\MessageDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /b/{ticket} — the address that goes out on WhatsApp.
 *
 * It has to be short, because a URL button is approved with a fixed prefix and
 * given only a tail at send time, and it has to open the right page in the
 * right language without the sender having to choose which.
 */
class BadgeLinkTest extends TestCase
{
    use RefreshDatabase;

    private function registrant(array $attributes = []): Registration
    {
        return Registration::create(array_merge([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_VISITOR,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'ku',
            'full_name' => 'Zardasht Aziz',
            'phone' => '7701114455',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'days' => [1, 2, 3],
            'confirmed_at' => now(),
        ], $attributes));
    }

    public function test_it_opens_the_badge_page_in_the_language_the_person_registered_in(): void
    {
        $registration = $this->registrant();

        $this->get("/b/{$registration->ticket_id}")
            ->assertRedirect("/ku/register/fair/done/{$registration->ticket_id}");
    }

    public function test_a_delegate_lands_on_the_conference_page_not_the_fair_one(): void
    {
        $registration = $this->registrant([
            'track' => Registration::TRACK_CONFERENCE,
            'type' => Registration::TYPE_GOVERNMENT,
            'locale' => 'en',
            'phone' => '7701114466',
        ]);

        $this->get("/b/{$registration->ticket_id}")
            ->assertRedirect("/en/register/conference/done/{$registration->ticket_id}");
    }

    /** The ticket is a UUID, so the address cannot be walked by trying numbers. */
    public function test_an_unknown_ticket_is_not_found(): void
    {
        $this->get('/b/00000000-0000-0000-0000-000000000000')->assertNotFound();
    }

    /**
     * What the button carries is "{ticket}/badge.png" — the template is approved
     * as https://www.nextstepfair.com/ticket/{{1}}. PNG only (OTPIQ rejects PDF).
     */
    public function test_the_button_parameter_is_a_tail_and_not_a_whole_address(): void
    {
        $registration = $this->registrant(['phone' => '7701114477']);

        $param = app(MessageDispatcher::class)->badgeLinkParam($registration);

        $this->assertSame("{$registration->ticket_id}/badge.png", $param);
        $this->assertStringNotContainsString('http', $param);
        $this->assertStringNotContainsString('.pdf', $param);
    }

    public function test_the_header_image_url_uses_the_public_domain_and_png(): void
    {
        $this->app['env'] = 'production';
        config(['whatsapp.otpiq.public_url' => 'https://www.nextstepfair.com']);

        $registration = $this->registrant(['phone' => '7701114488']);
        $url = app(MessageDispatcher::class)->badgeUrl($registration);

        $this->assertSame(
            "https://www.nextstepfair.com/ticket/{$registration->ticket_id}/badge.png",
            $url,
        );
        $this->assertStringNotContainsString('127.0.0.1', $url);
        $this->assertStringNotContainsString('localhost', $url);
        $this->assertStringNotContainsString('.pdf', $url);
    }

    public function test_the_header_image_url_uses_otpiq_public_url_on_staging(): void
    {
        config(['whatsapp.otpiq.public_url' => 'https://demi.nextstepfair.com']);

        $registration = $this->registrant(['phone' => '7701114499']);
        $url = app(MessageDispatcher::class)->badgeUrl($registration);

        $this->assertSame(
            "https://demi.nextstepfair.com/ticket/{$registration->ticket_id}/badge.png",
            $url,
        );
    }

    /**
     * A broadcast puts many different people's links in front of them at once,
     * and a lot of real phones share one IP on carrier-grade NAT — an IP-only
     * throttle would lock all of them out together once the broadcast's
     * recipients cross it, even though each person only opened their own link
     * once. The per-IP ceiling has to have enough room for a whole broadcast
     * landing in the same minute.
     */
    public function test_many_different_tickets_opened_from_one_ip_are_not_throttled(): void
    {
        for ($i = 0; $i < 65; $i++) {
            $registration = $this->registrant([
                'phone' => '7701200'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
            ]);

            $this->get("/b/{$registration->ticket_id}")->assertRedirect();
        }
    }

    /**
     * The per-IP ceiling being loose does not mean one link can be hammered
     * without limit — a second, narrower ceiling keyed by the ticket itself
     * still catches that.
     */
    public function test_one_ticket_opened_far_more_than_normal_is_throttled(): void
    {
        $registration = $this->registrant(['phone' => '7701199001']);

        for ($i = 0; $i < 10; $i++) {
            $this->get("/b/{$registration->ticket_id}")->assertRedirect();
        }

        $this->get("/b/{$registration->ticket_id}")->assertStatus(429);
    }
}
