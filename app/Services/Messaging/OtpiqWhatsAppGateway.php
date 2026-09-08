<?php

namespace App\Services\Messaging;

use App\Models\Message;
use App\Services\Messaging\Contracts\WhatsAppGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * OTPIQ — the WhatsApp provider used in Iraq and Kurdistan.
 *
 * They hold the WhatsApp Business account and talk to Meta on our behalf, which
 * is why the templates are built and approved in their dashboard rather than in
 * Meta's. What crosses this class is the same as for Meta: a template name, a
 * language, and the values that fill its numbered slots.
 *
 * The one thing to keep in mind while reading this: OTPIQ take template
 * parameters as a map keyed "1", "2", "3", not as a list. The order of the array
 * handed down from the dispatcher is therefore the order of the placeholders in
 * the approved template, and renaming a key in a caller changes nothing while
 * reordering two of them silently swaps a name for a ticket number. The mapping
 * of every template to its slots is written down in docs/whatsapp-otpiq.md.
 */
class OtpiqWhatsAppGateway implements WhatsAppGateway
{
    public function sendTemplate(
        Message $message,
        string $template,
        string $locale,
        array $variables = [],
        ?string $mediaUrl = null,
        ?string $linkParam = null,
    ): ?string {
        $config = config('whatsapp.otpiq');

        $parameters = ['body' => $this->numbered($variables)];

        /*
         * The badge picture and the button link are sent only when the account
         * is configured for them. OTPIQ document the body slots and nothing
         * else, so these two shapes are what their support confirms for the
         * account — until then the badge travels as the link in the button, and
         * the link as the address in the message text. A guess sent to a live
         * template is a rejected send, not a missing picture.
         */
        if ($mediaUrl && $config['send_header_image']) {
            $parameters['header'] = ['image' => ['link' => $mediaUrl]];
        }

        if ($linkParam && $config['send_button_link']) {
            $parameters['buttons'] = [
                ['index' => 0, 'type' => 'url', 'parameter' => $linkParam],
            ];
        }

        return $this->post([
            'phoneNumber' => $this->normalise($message->recipient),
            'smsType' => 'whatsapp-template',
            'provider' => 'whatsapp',
            'templateName' => $template,
            'whatsappAccountId' => $config['account_id'],
            'whatsappPhoneId' => $config['phone_id'],
            'templateParameters' => $parameters,
        ], $message);
    }

    /**
     * Free text, which WhatsApp allows only inside the 24 hours after somebody
     * has written to us. Nothing in the registration flow uses it — every
     * message we start is a template — but the admin's reply box does.
     */
    public function sendText(Message $message, string $body): ?string
    {
        return $this->post([
            'phoneNumber' => $this->normalise($message->recipient),
            'smsType' => 'custom',
            'provider' => 'whatsapp',
            'customMessage' => $body,
            'whatsappAccountId' => config('whatsapp.otpiq.account_id'),
            'whatsappPhoneId' => config('whatsapp.otpiq.phone_id'),
        ], $message);
    }

    public function name(): string
    {
        return 'otpiq';
    }

    /** ['name' => 'Zardasht', 'ticket' => '90FD'] becomes ['1' => …, '2' => …]. */
    private function numbered(array $variables): array
    {
        $numbered = [];

        foreach (array_values($variables) as $index => $value) {
            $numbered[(string) ($index + 1)] = (string) $value;
        }

        return $numbered;
    }

    private function post(array $payload, Message $message): ?string
    {
        $config = config('whatsapp.otpiq');

        if (! $config['api_key']) {
            throw new RuntimeException('OTPIQ is not configured: OTPIQ_API_KEY is empty.');
        }

        if (! $config['account_id'] || ! $config['phone_id']) {
            throw new RuntimeException(
                'OTPIQ is not configured: OTPIQ_WHATSAPP_ACCOUNT_ID and OTPIQ_WHATSAPP_PHONE_ID '
                .'come from the WhatsApp account in the OTPIQ dashboard.'
            );
        }

        // Delivery reports, so the admin's log shows what actually arrived rather
        // than only what we handed over.
        if ($config['webhook_secret']) {
            $payload['deliveryReport'] = [
                'webhookUrl' => route('webhooks.otpiq'),
                'deliveryReportType' => 'all',
                'webhookSecret' => $config['webhook_secret'],
            ];
        }

        // Kept for the admin: the exact body sent, which is the first thing worth
        // seeing when a provider rejects something.
        $message->forceFill(['payload' => $payload])->save();

        $response = Http::withToken($config['api_key'])
            ->timeout($config['timeout'])
            ->acceptJson()
            ->post(rtrim($config['base_url'], '/').'/sms', $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                'OTPIQ send failed: '.$response->json('message', $response->body())
            );
        }

        return $response->json('smsId');
    }

    /** OTPIQ want country code and number, digits only: 964770xxxxxxx. */
    private function normalise(string $phone): string
    {
        return ltrim(preg_replace('/\D/', '', $phone), '0');
    }
}
