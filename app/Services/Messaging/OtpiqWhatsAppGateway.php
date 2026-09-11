<?php

namespace App\Services\Messaging;

use App\Models\Message;
use App\Services\Messaging\Contracts\WhatsAppGateway;
use App\Support\WhatsAppLog;
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
 * OTPIQ holds one template per language (`rsvp_confirmed_en_2026`, …), not one
 * name with three language codes. Everything for a send — name, id, body slots,
 * header image — lives in `otpiq_templates` (seeded), with config as fallback.
 *
 * Body parameters are a map keyed "1", "2", "3". The order of the filtered
 * array is therefore the order of the placeholders in the approved template.
 */
class OtpiqWhatsAppGateway implements WhatsAppGateway
{
    public function __construct(
        private readonly OtpiqTemplateRegistry $templates,
    ) {}

    public function sendTemplate(
        Message $message,
        string $template,
        string $locale,
        array $variables = [],
        ?string $mediaUrl = null,
        ?string $linkParam = null,
    ): ?string {
        $config = config('whatsapp.otpiq', []);
        $logicalKey = $message->template_key ?: $template;
        $templateName = $this->resolveName($logicalKey, $locale, $template);
        $bodyVariables = $this->bodyVariables($logicalKey, $locale, $variables);

        /*
         * POST /api/sms — same shape as OTPIQ's WhatsApp-template example:
         * phoneNumber, smsType, provider, templateName, whatsappAccountId,
         * whatsappPhoneId, templateParameters { body, buttons, header }.
         * No deliveryReport webhook.
         */
        $parameters = ['body' => $this->numbered($bodyVariables)];

        if ($mediaUrl) {
            $parameters['header'] = ['imageUrl' => $mediaUrl];
        }

        if ($linkParam) {
            // Must encode as {"0":{"1":"…"}} — a PHP list [0 => …] becomes [{…}]
            // and Meta/OTPIQ ignore the button (see broken stdClass log shape).
            $parameters['buttons'] = (object) [
                '0' => (object) ['1' => $linkParam],
            ];
        }

        return $this->post([
            'phoneNumber' => $this->normalise($message->recipient),
            'smsType' => 'whatsapp-template',
            'provider' => 'whatsapp',
            'templateName' => $templateName,
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

    /** Logical key + locale → the exact name approved in the OTPIQ dashboard. */
    private function resolveName(string $logicalKey, string $locale, string $fallback): string
    {
        return $this->templates->get($logicalKey, $locale)['name'] ?? $fallback;
    }

    /**
     * Keep only the named slots this locale's template expects, in that order.
     * When there is no override, every variable is sent (OTP, reminders, etc.).
     *
     * @param  array<string, mixed>  $variables
     * @return array<string, mixed>
     */
    private function bodyVariables(string $logicalKey, string $locale, array $variables): array
    {
        $keys = $this->templates->get($logicalKey, $locale)['body'] ?? null;

        if (! is_array($keys)) {
            return $variables;
        }

        $filtered = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $variables)) {
                $filtered[$key] = $variables[$key];
            }
        }

        return $filtered;
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
        $config = config('whatsapp.otpiq', []);

        // Exact JSON body — Laravel's array encoder can turn button key "0" into
        // a list and Meta then accepts the SMS but never delivers the WhatsApp.
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        // Stored for the admin as a plain array (objects → arrays for JSON column).
        $message->forceFill(['payload' => json_decode($json, true)])->save();

        $url = rtrim((string) ($config['base_url'] ?? 'https://api.otpiq.com/api'), '/').'/sms';

        $this->logOutboundRequest($message, $url, $payload, $json);

        if (! $config['api_key']) {
            $cached = is_file(base_path('bootstrap/cache/config.php'));

            WhatsAppLog::error('otpiq.not_configured', [
                'message_id' => $message->id,
                'config_cached' => $cached,
                'driver' => config('whatsapp.driver'),
                'url' => $url,
                'template' => $payload['templateName'] ?? null,
                'phone' => $payload['phoneNumber'] ?? null,
                'json' => $json,
            ]);

            throw new RuntimeException(
                'OTPIQ is not configured: OTPIQ_API_KEY is empty in config. '
                .'Set OTPIQ_API_KEY in .env, then run: php artisan config:clear && php artisan queue:restart'
                .($cached ? ' (config cache was built before the key was added)' : '')
            );
        }

        if (! $config['account_id'] || ! $config['phone_id']) {
            WhatsAppLog::error('otpiq.not_configured', [
                'message_id' => $message->id,
                'missing' => array_values(array_filter([
                    empty($config['account_id']) ? 'account_id' : null,
                    empty($config['phone_id']) ? 'phone_id' : null,
                ])),
                'url' => $url,
                'template' => $payload['templateName'] ?? null,
                'json' => $json,
            ]);

            throw new RuntimeException(
                'OTPIQ is not configured: OTPIQ_WHATSAPP_ACCOUNT_ID and OTPIQ_WHATSAPP_PHONE_ID '
                .'come from the WhatsApp account in the OTPIQ dashboard.'
            );
        }

        $request = Http::withToken($config['api_key'])
            ->timeout($config['timeout'])
            ->acceptJson()
            ->withBody($json, 'application/json');

        // Local Windows without a CA bundle hits cURL error 60; production stays verified.
        if (! ($config['verify_ssl'] ?? true)) {
            $request = $request->withoutVerifying();
        }

        $started = microtime(true);
        $response = $request->post($url);
        $elapsedMs = (int) round((microtime(true) - $started) * 1000);

        if ($response->failed()) {
            $reason = $response->json('error')
                ?? $response->json('message')
                ?? $response->body();

            WhatsAppLog::error('otpiq.rejected', [
                'message_id' => $message->id,
                'http_status' => $response->status(),
                'elapsed_ms' => $elapsedMs,
                'url' => $url,
                'template' => $payload['templateName'] ?? null,
                'phone' => $payload['phoneNumber'] ?? null,
                'request_json' => $json,
                'response' => $response->json() ?? $response->body(),
            ]);

            throw new RuntimeException('OTPIQ send failed: '.(is_string($reason) ? $reason : json_encode($reason)));
        }

        $smsId = $response->json('smsId');

        WhatsAppLog::info('otpiq.accepted', [
            'message_id' => $message->id,
            'sms_id' => $smsId,
            'elapsed_ms' => $elapsedMs,
            'http_status' => $response->status(),
            'remaining_credit' => $response->json('remainingCredit'),
            'cost' => $response->json('cost'),
            'response' => $response->json(),
        ]);

        return $smsId;
    }

    /** @param  array<string, mixed>  $payload */
    private function logOutboundRequest(Message $message, string $url, array $payload, string $json): void
    {
        $templateKey = $message->template_key;
        $locale = $message->locale;

        WhatsAppLog::info('otpiq.request', [
            'message_id' => $message->id,
            'registration_id' => $message->registration_id,
            'url' => $url,
            'template_key' => $templateKey,
            'template' => $payload['templateName'] ?? null,
            'template_id' => $templateKey ? ($this->templates->get($templateKey, $locale)['id'] ?? null) : null,
            'locale' => $locale,
            'phone' => $payload['phoneNumber'] ?? null,
            'account_id' => $payload['whatsappAccountId'] ?? null,
            'phone_id' => $payload['whatsappPhoneId'] ?? null,
            'has_header' => isset($payload['templateParameters']['header']),
            'has_buttons' => isset($payload['templateParameters']['buttons']),
            'header' => $payload['templateParameters']['header'] ?? null,
            'buttons' => isset($payload['templateParameters']['buttons'])
                ? json_decode(json_encode($payload['templateParameters']['buttons']), true)
                : null,
            'body' => $payload['templateParameters']['body'] ?? null,
            'json' => $json,
        ]);
    }

    /** OTPIQ want country code and number, digits only: 964770xxxxxxx. */
    private function normalise(string $phone): string
    {
        return ltrim(preg_replace('/\D/', '', $phone), '0');
    }
}
