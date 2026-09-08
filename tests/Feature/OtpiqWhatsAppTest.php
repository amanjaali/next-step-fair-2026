<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Message;
use App\Models\Registration;
use App\Services\Messaging\Contracts\WhatsAppGateway;
use App\Services\Messaging\LogWhatsAppGateway;
use App\Services\Messaging\MessageDispatcher;
use App\Services\Messaging\OtpiqWhatsAppGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Sending through OTPIQ, who hold the WhatsApp account for Iraq and Kurdistan.
 *
 * What these hold in place is the shape of one HTTP call and the meaning of one
 * webhook. Both are invisible until the day of the fair, when three thousand
 * badges either arrive or do not — and by then the difference between "sent"
 * and "delivered" in the log is what the desk is working from.
 */
class OtpiqWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'whatsapp.driver' => 'otpiq',
            'whatsapp.otpiq.api_key' => 'sk_test_key',
            'whatsapp.otpiq.account_id' => 'acc_123',
            'whatsapp.otpiq.phone_id' => 'phone_456',
            'whatsapp.otpiq.webhook_secret' => 'a-shared-secret',
        ]);
    }

    private function registrant(array $attributes = []): Registration
    {
        return Registration::create(array_merge([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'ku',
            'full_name' => 'Zardasht Aziz',
            'phone' => '7701113322',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'days' => [1, 2, 3],
            'confirmed_at' => now(),
        ], $attributes));
    }

    private function message(Registration $registration): Message
    {
        return Message::create([
            'registration_id' => $registration->id,
            'channel' => 'whatsapp',
            'template_key' => 'registration_confirmed_student',
            'locale' => $registration->locale,
            'recipient' => $registration->msisdn(),
            'preview' => 'preview',
            'status' => Message::STATUS_QUEUED,
            'queued_at' => now(),
        ]);
    }

    public function test_the_driver_is_chosen_by_configuration_alone(): void
    {
        $this->assertInstanceOf(OtpiqWhatsAppGateway::class, app(WhatsAppGateway::class));

        config(['whatsapp.driver' => 'log']);
        $this->assertInstanceOf(LogWhatsAppGateway::class, app(WhatsAppGateway::class));

        // Anything unrecognised falls back to the log driver rather than to a
        // provider: a typo in .env must not start sending real messages.
        config(['whatsapp.driver' => 'otpiqq']);
        $this->assertInstanceOf(LogWhatsAppGateway::class, app(WhatsAppGateway::class));
    }

    /**
     * The variables are handed down as a named array and arrive at OTPIQ as
     * "1", "2", "3" — so the order of that array is the order of the
     * placeholders in the approved template.
     */
    public function test_variables_become_numbered_body_parameters_in_order(): void
    {
        Http::fake([
            '*' => Http::response(['smsId' => 'otpiq-1', 'remainingCredit' => 900], 200),
        ]);

        $registration = $this->registrant();
        $message = $this->message($registration);

        app(OtpiqWhatsAppGateway::class)->sendTemplate(
            $message,
            'registration_confirmed_student',
            'ku',
            ['name' => 'Zardasht', 'days' => 'Day 1, Day 2, Day 3', 'ticket' => '90FD-2F0A-905B'],
        );

        Http::assertSent(function (ClientRequest $request) {
            $body = $request->data();

            return $request->url() === 'https://api.otpiq.com/api/sms'
                && $request->hasHeader('Authorization', 'Bearer sk_test_key')
                && $body['smsType'] === 'whatsapp-template'
                && $body['templateName'] === 'registration_confirmed_student'
                && $body['whatsappAccountId'] === 'acc_123'
                && $body['whatsappPhoneId'] === 'phone_456'
                && $body['templateParameters']['body'] === [
                    '1' => 'Zardasht',
                    '2' => 'Day 1, Day 2, Day 3',
                    '3' => '90FD-2F0A-905B',
                ];
        });
    }

    /** Digits only, country code kept, no leading zero. */
    public function test_the_number_is_sent_the_way_the_provider_wants_it(): void
    {
        Http::fake(['*' => Http::response(['smsId' => 'otpiq-2'], 200)]);

        $registration = $this->registrant();
        app(OtpiqWhatsAppGateway::class)->sendTemplate($this->message($registration), 't', 'en');

        Http::assertSent(fn (ClientRequest $request) => $request->data()['phoneNumber'] === '9647701113322');
    }

    public function test_the_provider_id_is_kept_so_a_delivery_report_can_find_the_message(): void
    {
        Http::fake(['*' => Http::response(['smsId' => 'otpiq-abc'], 200)]);

        $registration = $this->registrant();
        $message = $this->message($registration);

        (new SendWhatsAppMessage($message->id, ['name' => 'Zardasht']))
            ->handle(app(WhatsAppGateway::class), app(MessageDispatcher::class));

        $message->refresh();

        $this->assertSame('otpiq-abc', $message->provider_message_id);
        $this->assertSame(Message::STATUS_SENT, $message->status);
        // Not delivered: that is the provider's word to give, and it comes later.
        $this->assertNull($message->delivered_at);
    }

    /**
     * The badge picture and the button link are offered only when the account is
     * set up for them — a shape the provider does not expect is a rejected send,
     * which loses the whole message rather than one part of it.
     */
    public function test_the_badge_pieces_are_left_out_until_they_are_switched_on(): void
    {
        Http::fake(['*' => Http::response(['smsId' => 'otpiq-3'], 200)]);

        $registration = $this->registrant();

        app(OtpiqWhatsAppGateway::class)->sendTemplate(
            $this->message($registration), 't', 'en', ['name' => 'Z'], 'https://example.test/badge.png', 'b/xyz'
        );

        Http::assertSent(function (ClientRequest $request) {
            $parameters = $request->data()['templateParameters'];

            return ! isset($parameters['header']) && ! isset($parameters['buttons']);
        });
    }

    public function test_the_badge_pieces_are_sent_once_they_are_switched_on(): void
    {
        config([
            'whatsapp.otpiq.send_header_image' => true,
            'whatsapp.otpiq.send_button_link' => true,
        ]);

        Http::fake(['*' => Http::response(['smsId' => 'otpiq-4'], 200)]);

        $registration = $this->registrant();

        app(OtpiqWhatsAppGateway::class)->sendTemplate(
            $this->message($registration), 't', 'en', ['name' => 'Z'], 'https://example.test/badge.png', 'b/xyz'
        );

        Http::assertSent(function (ClientRequest $request) {
            $parameters = $request->data()['templateParameters'];

            return $parameters['header']['image']['link'] === 'https://example.test/badge.png'
                && $parameters['buttons'][0]['parameter'] === 'b/xyz';
        });
    }

    public function test_a_rejected_send_is_recorded_with_the_provider_reason(): void
    {
        Http::fake(['*' => Http::response(['message' => 'template not found'], 422)]);

        $registration = $this->registrant();
        $message = $this->message($registration);

        try {
            (new SendWhatsAppMessage($message->id))->handle(app(WhatsAppGateway::class), app(MessageDispatcher::class));
        } catch (\Throwable) {
            // The job rethrows so the queue retries it; the record is what matters here.
        }

        $message->refresh();

        $this->assertSame(Message::STATUS_FAILED, $message->status);
        $this->assertStringContainsString('template not found', (string) $message->error);
    }

    /* ------------------------------------------------------------- webhook -- */

    public function test_a_delivery_report_settles_the_message(): void
    {
        $message = $this->message($this->registrant());
        $message->forceFill(['provider_message_id' => 'otpiq-9', 'status' => Message::STATUS_SENT])->save();

        $this->withHeaders(['X-Webhook-Secret' => 'a-shared-secret'])
            ->postJson('/webhooks/otpiq', ['smsId' => 'otpiq-9', 'status' => 'delivered'])
            ->assertOk();

        $message->refresh();

        $this->assertSame(Message::STATUS_DELIVERED, $message->status);
        $this->assertNotNull($message->delivered_at);
    }

    public function test_a_failure_report_says_so_rather_than_staying_sent(): void
    {
        $message = $this->message($this->registrant());
        $message->forceFill(['provider_message_id' => 'otpiq-10', 'status' => Message::STATUS_SENT])->save();

        $this->withHeaders(['X-Webhook-Secret' => 'a-shared-secret'])
            ->postJson('/webhooks/otpiq', ['smsId' => 'otpiq-10', 'status' => 'failed'])
            ->assertOk();

        $this->assertSame(Message::STATUS_FAILED, $message->refresh()->status);
    }

    /**
     * The address is public. Without the secret this would let anybody mark
     * every badge delivered, which at the desk reads as "they have it, stop
     * helping them".
     */
    public function test_a_report_without_the_secret_changes_nothing(): void
    {
        $message = $this->message($this->registrant());
        $message->forceFill(['provider_message_id' => 'otpiq-11', 'status' => Message::STATUS_SENT])->save();

        $this->postJson('/webhooks/otpiq', ['smsId' => 'otpiq-11', 'status' => 'delivered'])
            ->assertForbidden();

        $this->withHeaders(['X-Webhook-Secret' => 'not-the-secret'])
            ->postJson('/webhooks/otpiq', ['smsId' => 'otpiq-11', 'status' => 'delivered'])
            ->assertForbidden();

        $this->assertSame(Message::STATUS_SENT, $message->refresh()->status);
    }
}
