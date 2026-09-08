<?php

namespace App\Services\Messaging;

use App\Models\Message;
use App\Services\Messaging\Contracts\WhatsAppGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Meta WhatsApp Business Cloud API.
 *
 * Only pre-approved templates can be sent outside a 24-hour customer service
 * window, which is why every outbound message here is a template send; the
 * template names live in config/whatsapp.php and must match Meta exactly.
 */
class CloudApiWhatsAppGateway implements WhatsAppGateway
{
    public function sendTemplate(
        Message $message,
        string $template,
        string $locale,
        array $variables = [],
        ?string $mediaUrl = null,
        ?string $linkParam = null,
    ): ?string {
        $components = [];

        if ($mediaUrl) {
            $components[] = [
                'type' => 'header',
                'parameters' => [['type' => 'image', 'image' => ['link' => $mediaUrl]]],
            ];
        }

        if ($variables) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map(
                    fn ($value) => ['type' => 'text', 'text' => (string) $value],
                    array_values($variables)
                ),
            ];
        }

        if ($linkParam) {
            // A URL button carries only what is appended to the address approved
            // with the template, so this is the tail of the badge link and never
            // the whole one.
            $components[] = [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => '0',
                'parameters' => [['type' => 'text', 'text' => $linkParam]],
            ];
        }

        return $this->post([
            'messaging_product' => 'whatsapp',
            'to' => $this->normalise($message->recipient),
            'type' => 'template',
            'template' => array_filter([
                'name' => $template,
                'language' => ['code' => config("whatsapp.language_codes.$locale", 'en')],
                'components' => $components ?: null,
            ]),
        ], $message);
    }

    public function sendText(Message $message, string $body): ?string
    {
        return $this->post([
            'messaging_product' => 'whatsapp',
            'to' => $this->normalise($message->recipient),
            'type' => 'text',
            'text' => ['preview_url' => false, 'body' => $body],
        ], $message);
    }

    public function name(): string
    {
        return 'cloud_api';
    }

    private function post(array $payload, Message $message): ?string
    {
        $config = config('whatsapp.cloud_api');

        if (! $config['token'] || ! $config['phone_number_id']) {
            throw new RuntimeException('WhatsApp Cloud API credentials are not configured.');
        }

        $message->forceFill(['payload' => $payload])->save();

        $response = Http::withToken($config['token'])
            ->timeout($config['timeout'])
            ->acceptJson()
            ->post("{$config['base_url']}/{$config['phone_number_id']}/messages", $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                'WhatsApp send failed: '.$response->json('error.message', $response->body())
            );
        }

        return $response->json('messages.0.id');
    }

    /** Meta wants digits only, no plus. */
    private function normalise(string $phone): string
    {
        return ltrim(preg_replace('/\D/', '', $phone), '0');
    }
}
