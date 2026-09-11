<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Message;
use App\Models\Registration;
use App\Services\Messaging\Contracts\WhatsAppGateway;
use App\Services\Messaging\LogWhatsAppGateway;
use App\Services\Messaging\MessageDispatcher;
use App\Services\Messaging\OtpiqTemplateRegistry;
use Database\Seeders\OtpiqTemplateSeeder;
use App\Services\Messaging\OtpiqWhatsAppGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
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

        $this->seed(OtpiqTemplateSeeder::class);
    }

    private function registrant(array $attributes = []): Registration
    {
        return Registration::create(array_merge([
            'track' => Registration::TRACK_CONFERENCE,
            'type' => Registration::TYPE_GOVERNMENT,
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
            'template_key' => 'rsvp_confirmed',
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
     * Conference RSVP uses rsvp_confirmed_*. Kurdish keeps name + ticket;
     * English drops ticket from the body (badge travels on the URL button).
     */
    /** Decode the raw JSON body we send with withBody(). */
    private function jsonBody(ClientRequest $request): array
    {
        return json_decode($request->body(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_variables_become_numbered_body_parameters_in_order(): void
    {
        Http::fake([
            '*' => Http::response(['smsId' => 'otpiq-1', 'remainingCredit' => 900], 200),
        ]);

        $registration = $this->registrant();
        $message = $this->message($registration);

        app(OtpiqWhatsAppGateway::class)->sendTemplate(
            $message,
            'rsvp_confirmed',
            'ku',
            ['name' => 'Zardasht', 'ticket' => '90FD-2F0A-905B'],
        );

        Http::assertSent(function (ClientRequest $request) {
            $body = $this->jsonBody($request);

            return $request->url() === 'https://api.otpiq.com/api/sms'
                && $request->hasHeader('Authorization', 'Bearer sk_test_key')
                && $body['smsType'] === 'whatsapp-template'
                && $body['templateName'] === 'rsvp_confirmed_ku_2026'
                && $body['whatsappAccountId'] === 'acc_123'
                && $body['whatsappPhoneId'] === 'phone_456'
                && $body['templateParameters']['body'] === [
                    '1' => 'Zardasht',
                    '2' => '90FD-2F0A-905B',
                ];
        });
    }

    /** Conference RSVP templates were created as rsvp_confirmed_{locale}_2026. */
    public function test_locale_suffixed_otpiq_names_are_used(): void
    {
        Http::fake(['*' => Http::response(['smsId' => 'otpiq-rsvp'], 200)]);

        $registration = $this->registrant(['locale' => 'en']);
        $message = $this->message($registration);
        $message->forceFill(['template_key' => 'rsvp_confirmed', 'locale' => 'en'])->save();

        app(OtpiqWhatsAppGateway::class)->sendTemplate(
            $message,
            'rsvp_confirmed',
            'en',
            ['name' => 'Dr. Rezan', 'ticket' => 'NSF26'],
        );

        Http::assertSent(function (ClientRequest $request) {
            $body = $this->jsonBody($request);

            return $body['templateName'] === 'rsvp_confirmed_en_2026'
                && $body['templateParameters']['body'] === ['1' => 'Dr. Rezan'];
        });
    }

    /** Digits only, country code kept, no leading zero. */
    public function test_the_number_is_sent_the_way_the_provider_wants_it(): void
    {
        Http::fake(['*' => Http::response(['smsId' => 'otpiq-2'], 200)]);

        $registration = $this->registrant();
        app(OtpiqWhatsAppGateway::class)->sendTemplate($this->message($registration), 't', 'en');

        Http::assertSent(fn (ClientRequest $request) => $this->jsonBody($request)['phoneNumber'] === '9647701113322');
    }

    public function test_the_provider_id_is_kept_so_a_delivery_report_can_find_the_message(): void
    {
        Http::fake(['*' => Http::response(['smsId' => 'otpiq-abc'], 200)]);

        $registration = $this->registrant();
        $message = $this->message($registration);

        (new SendWhatsAppMessage($message->id, ['name' => 'Zardasht']))
            ->handle(
                app(WhatsAppGateway::class),
                app(MessageDispatcher::class),
                app(OtpiqTemplateRegistry::class),
            );

        $message->refresh();

        $this->assertSame('otpiq-abc', $message->provider_message_id);
        $this->assertSame(Message::STATUS_SENT, $message->status);
        // Not delivered: that is the provider's word to give, and it comes later.
        $this->assertNull($message->delivered_at);
    }

    /** Without mediaUrl / linkParam, header and buttons are omitted. */
    public function test_the_badge_pieces_are_omitted_when_not_provided(): void
    {
        Http::fake(['*' => Http::response(['smsId' => 'otpiq-3'], 200)]);

        $registration = $this->registrant();

        app(OtpiqWhatsAppGateway::class)->sendTemplate(
            $this->message($registration),
            't',
            'en',
            ['name' => 'Z']
        );

        Http::assertSent(function (ClientRequest $request) {
            $body = $this->jsonBody($request);
            $parameters = $body['templateParameters'];

            return ! isset($parameters['header']) && ! isset($parameters['buttons'])
                && ! array_key_exists('deliveryReport', $body);
        });
    }

    public function test_the_payload_matches_the_otpiq_whatsapp_template_example(): void
    {
        Http::fake(['*' => Http::response(['smsId' => 'otpiq-4'], 200)]);

        $registration = $this->registrant(['locale' => 'en']);
        $message = $this->message($registration);
        $message->forceFill(['template_key' => 'rsvp_confirmed', 'locale' => 'en'])->save();

        app(OtpiqWhatsAppGateway::class)->sendTemplate(
            $message,
            'rsvp_confirmed',
            'en',
            ['name' => 'Z'],
            'https://example.test/badge.png',
            'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee/badge.png'
        );

        Http::assertSent(function (ClientRequest $request) {
            $body = $this->jsonBody($request);
            $parameters = $body['templateParameters'];

            // Buttons must be an object keyed "0", never a JSON array.
            return $body['smsType'] === 'whatsapp-template'
                && $body['provider'] === 'whatsapp'
                && $body['templateName'] === 'rsvp_confirmed_en_2026'
                && ! array_key_exists('deliveryReport', $body)
                && $parameters['header']['imageUrl'] === 'https://example.test/badge.png'
                && $parameters['buttons'] === ['0' => ['1' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee/badge.png']]
                && $parameters['body'] === ['1' => 'Z']
                && str_contains($request->body(), '"buttons":{"0":{"1":');
        });
    }

    /** Local laptop uses OTPIQ_LOCAL_HEADER_IMAGE when the public origin is localhost. */
    public function test_local_env_uses_the_sample_header_when_the_public_origin_is_localhost(): void
    {
        $this->app['env'] = 'local';
        config([
            'whatsapp.otpiq.public_url' => 'http://127.0.0.1:8000',
            'whatsapp.otpiq.local_header_image' => 'https://www.nextstepfair.com/images/logo.png',
            'app.url' => 'http://127.0.0.1:8000',
        ]);

        $registration = $this->registrant();
        $registration->forceFill(['ticket_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee'])->save();

        $url = app(MessageDispatcher::class)->badgeUrl($registration);

        $this->assertSame('https://www.nextstepfair.com/images/logo.png', $url);
        $this->assertStringNotContainsString('127.0.0.1', $url);
    }

    /** Staging keeps the real badge URL even when APP_ENV is local. */
    public function test_staging_public_url_is_used_even_in_local_env(): void
    {
        $this->app['env'] = 'local';
        config([
            'whatsapp.otpiq.public_url' => 'https://demi.nextstepfair.com',
            'whatsapp.otpiq.local_header_image' => 'https://www.nextstepfair.com/images/logo.png',
            'app.url' => 'http://127.0.0.1:8000',
        ]);

        $registration = $this->registrant();
        $registration->forceFill(['ticket_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee'])->save();

        $url = app(MessageDispatcher::class)->badgeUrl($registration);

        $this->assertSame(
            'https://demi.nextstepfair.com/ticket/aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee/badge.png',
            $url,
        );
    }

    /** Production sends the real ticket badge PNG on the public domain. */
    public function test_production_uses_the_generated_badge_url(): void
    {
        $this->app['env'] = 'production';
        config([
            'whatsapp.otpiq.public_url' => 'https://www.nextstepfair.com',
            'whatsapp.otpiq.local_header_image' => 'https://www.nextstepfair.com/images/logo.png',
            'app.url' => 'https://www.nextstepfair.com',
        ]);

        $registration = $this->registrant();
        $registration->forceFill(['ticket_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee'])->save();

        $url = app(MessageDispatcher::class)->badgeUrl($registration);

        $this->assertSame(
            'https://www.nextstepfair.com/ticket/aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee/badge.png',
            $url,
        );
        $this->assertStringNotContainsString('.pdf', $url);
    }

    public function test_whatsapp_is_not_sent_when_the_badge_image_returns_404(): void
    {
        config([
            'whatsapp.otpiq.public_url' => 'https://demi.nextstepfair.com',
            'whatsapp.otpiq.send_header_image' => true,
            'whatsapp.otpiq.send_button_link' => false,
        ]);

        Http::fake([
            'https://demi.nextstepfair.com/*' => Http::response('', 404),
            '*' => Http::response(['smsId' => 'should-not-send'], 200),
        ]);

        $registration = $this->registrant();
        $registration->forceFill([
            'ticket_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'badge_generated_at' => now(),
        ])->save();

        $message = $this->message($registration);

        try {
            (new SendWhatsAppMessage($message->id, ['name' => 'Zardasht'], withBadge: true))->handle(
                app(WhatsAppGateway::class),
                app(MessageDispatcher::class),
                app(OtpiqTemplateRegistry::class),
            );
        } catch (\Throwable) {
            // The job rethrows so the queue retries it.
        }

        $message->refresh();

        $this->assertSame(Message::STATUS_FAILED, $message->status);
        $this->assertStringContainsString('badge image is not reachable', (string) $message->error);
        Http::assertNotSent(fn (ClientRequest $request) => $request->url() === 'https://api.otpiq.com/api/sms');
    }

    public function test_whatsapp_sends_when_the_badge_image_is_reachable(): void
    {
        config([
            'whatsapp.otpiq.public_url' => 'https://demi.nextstepfair.com',
            'whatsapp.otpiq.send_header_image' => true,
            'whatsapp.otpiq.send_button_link' => false,
        ]);

        Http::fake([
            'https://demi.nextstepfair.com/*' => Http::response('', 200),
            'https://api.otpiq.com/api/sms' => Http::response(['smsId' => 'otpiq-badge-ok'], 200),
        ]);

        $registration = $this->registrant();
        $registration->forceFill([
            'ticket_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'badge_generated_at' => now(),
        ])->save();

        $message = $this->message($registration);

        (new SendWhatsAppMessage($message->id, ['name' => 'Zardasht'], withBadge: true))->handle(
            app(WhatsAppGateway::class),
            app(MessageDispatcher::class),
            app(OtpiqTemplateRegistry::class),
        );

        $message->refresh();

        $this->assertSame('otpiq-badge-ok', $message->provider_message_id);
        $this->assertSame(Message::STATUS_SENT, $message->status);
    }

    public function test_a_rejected_send_is_recorded_with_the_provider_reason(): void
    {
        Http::fake(['*' => Http::response(['error' => 'template not found'], 422)]);

        $registration = $this->registrant();
        $message = $this->message($registration);

        try {
            (new SendWhatsAppMessage($message->id))->handle(
                app(WhatsAppGateway::class),
                app(MessageDispatcher::class),
                app(OtpiqTemplateRegistry::class),
            );
        } catch (\Throwable) {
            // The job rethrows so the queue retries it; the record is what matters here.
        }

        $message->refresh();

        $this->assertSame(Message::STATUS_FAILED, $message->status);
        $this->assertStringContainsString('template not found', (string) $message->error);
    }

    /** Sends never include deliveryReport — OTPIQ example has none. */
    public function test_the_send_payload_has_no_delivery_report(): void
    {
        Http::fake(['*' => Http::response(['smsId' => 'otpiq-no-webhook'], 200)]);

        $registration = $this->registrant();
        app(OtpiqWhatsAppGateway::class)->sendTemplate($this->message($registration), 'rsvp_confirmed', 'en', [
            'name' => 'Mohammed',
        ], 'https://storage.database.krd/example.png', 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee/badge.png');

        Http::assertSent(fn (ClientRequest $request) => ! array_key_exists('deliveryReport', $this->jsonBody($request)));
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
