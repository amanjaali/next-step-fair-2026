<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppMessage;
use App\Mail\RsvpConfirmation;
use App\Models\Message;
use App\Models\Registration;
use App\Services\BadgeService;
use Database\Seeders\MessageTemplateSeeder;
use Database\Seeders\ProgrammeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ConferenceRsvpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProgrammeSeeder::class);
        $this->seed(MessageTemplateSeeder::class);
        Mail::fake();
    }

    /** Seven fields and a tick box — the whole form. */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'government',
            'full_name' => 'Dr. Rezan Ahmed Kareem',
            'position' => 'Director General, Scholarships',
            'organization' => 'Ministry of Higher Education and Scientific Research',
            'email' => 'r.kareem@mhe.krd',
            'phone_country' => '+964',
            'phone' => '7513004412',
            'city' => 'Erbil',
            'locale' => 'en',
            'consent_terms' => '1',
        ], $overrides);
    }

    public function test_an_institutional_address_is_confirmed_immediately_with_a_badge(): void
    {
        $this->post('/en/register/conference', $this->payload())->assertRedirect();

        $registration = Registration::conference()->firstOrFail();

        $this->assertSame(Registration::STATUS_CONFIRMED, $registration->status);
        $this->assertNotNull($registration->approved_at);
        $this->assertNotNull($registration->badge_generated_at);
        $this->assertSame([1], $registration->dayList());

        Mail::assertQueued(RsvpConfirmation::class, fn ($mail) => $mail->pending === false);
    }

    /**
     * A delegate gets the QR on WhatsApp as well as the e-mail.
     *
     * They read one or the other and rarely both: the e-mail is for the diary,
     * the WhatsApp message is what is open in their hand at the door.
     */
    public function test_a_confirmed_delegate_is_sent_the_badge_on_whatsapp(): void
    {
        Queue::fake();

        $this->post('/en/register/conference', $this->payload());

        $registration = Registration::conference()->firstOrFail();

        $this->assertTrue(
            Message::where('registration_id', $registration->id)
                ->where('channel', 'whatsapp')
                ->where('template_key', 'rsvp_confirmed')
                ->exists()
        );

        Queue::assertPushed(SendWhatsAppMessage::class, fn (SendWhatsAppMessage $job) => $job->withBadge === true);
    }

    /** Nothing is sent to a phone before the protocol team has approved it. */
    public function test_a_pending_delegate_is_not_sent_a_qr_they_cannot_use(): void
    {
        $this->post('/en/register/conference', $this->payload(['email' => 'delegate@gmail.com']));

        $registration = Registration::conference()->firstOrFail();

        $this->assertFalse(
            Message::where('registration_id', $registration->id)->where('channel', 'whatsapp')->exists()
        );
    }

    public function test_a_free_mail_address_goes_to_the_protocol_team_without_a_badge(): void
    {
        $this->post('/en/register/conference', $this->payload([
            'email' => 'delegate@gmail.com',
        ]))->assertRedirect();

        $registration = Registration::conference()->firstOrFail();

        $this->assertSame(Registration::STATUS_PENDING, $registration->status);
        $this->assertNull($registration->badge_generated_at);

        Mail::assertQueued(RsvpConfirmation::class, fn ($mail) => $mail->pending === true);
    }

    public function test_the_badge_carries_the_institution(): void
    {
        $this->post('/en/register/conference', $this->payload());
        $registration = Registration::conference()->firstOrFail();

        $payload = app(BadgeService::class)->payload($registration);

        $this->assertTrue($payload['isConference']);
        $this->assertSame('GOVERNMENT', $payload['typeChip']);
        $this->assertSame('#2C4BE0', $payload['accent']);

        $html = view('badges.badge', $payload)->render();
        $this->assertStringContainsString('Ministry of Higher Education and Scientific Research', $html);
        $this->assertStringContainsString($registration->ticket_ref, $html);
    }

    public function test_a_duplicate_email_is_blocked(): void
    {
        $this->post('/en/register/conference', $this->payload());
        $this->post('/en/register/conference', $this->payload(['full_name' => 'Someone Else']))
            ->assertSessionHas('duplicate');

        $this->assertSame(1, Registration::conference()->count());
    }

    /**
     * The conference is for more than ministries now: a company and a person
     * attending on their own account both have a way in.
     */
    public function test_the_private_sector_and_individuals_can_rsvp(): void
    {
        $this->post('/en/register/conference', $this->payload([
            'type' => 'private',
            'email' => 'ceo@company.com',
            'organization' => 'Kurdistan Tech Company',
        ]))->assertRedirect();

        $this->assertSame('private', Registration::conference()->latest('id')->first()->type);
    }

    /** An individual represents nobody, so no organisation is asked for. */
    public function test_an_individual_needs_no_organisation(): void
    {
        $this->post('/en/register/conference', $this->payload([
            'type' => 'individual',
            'email' => 'someone@company.com',
            'organization' => null,
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('individual', Registration::conference()->latest('id')->first()->type);
    }

    public function test_an_organisation_is_required_for_everyone_else(): void
    {
        $this->post('/en/register/conference', $this->payload(['organization' => null]))
            ->assertSessionHasErrors('organization');
    }

    public function test_the_confirmation_page_shows_the_badge_and_ticket(): void
    {
        $this->post('/en/register/conference', $this->payload());
        $registration = Registration::conference()->firstOrFail();

        $this->get("/en/register/conference/done/{$registration->ticket_id}")
            ->assertOk()
            ->assertSee($registration->ticket_ref)
            ->assertSee('Ministry of Higher Education and Scientific Research');
    }

    public function test_an_rsvp_can_be_cancelled_from_the_signed_link(): void
    {
        $this->post('/en/register/conference', $this->payload());
        $registration = Registration::conference()->firstOrFail();

        $manageUrl = URL::signedRoute('rsvp.manage', ['ticket' => $registration->ticket_id]);
        $this->get($manageUrl)->assertOk();

        $cancelUrl = URL::signedRoute('rsvp.cancel', ['ticket' => $registration->ticket_id]);
        $this->post($cancelUrl)->assertRedirect();

        $this->assertTrue($registration->refresh()->isCancelled());
    }
}
