<?php

namespace App\Services\Messaging;

use App\Models\Message;
use App\Services\Messaging\Contracts\WhatsAppGateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * The driver that runs until Meta approves the templates.
 *
 * It writes the fully rendered message to the log and to the delivery log, so
 * the team can read exactly what a registrant would have received, and the admin
 * dashboard behaves as it will in production.
 */
class LogWhatsAppGateway implements WhatsAppGateway
{
    public function sendTemplate(Message $message, string $template, string $locale, array $variables = [], ?string $mediaUrl = null): ?string
    {
        Log::channel(config('logging.default'))->info('[whatsapp:log] template', [
            'template' => $template,
            'locale' => $locale,
            'to' => $message->recipient,
            'variables' => $variables,
            'media' => $mediaUrl,
            'preview' => $message->preview,
        ]);

        return 'log-'.Str::uuid();
    }

    public function sendText(Message $message, string $body): ?string
    {
        Log::channel(config('logging.default'))->info('[whatsapp:log] text', [
            'to' => $message->recipient,
            'body' => $body,
        ]);

        return 'log-'.Str::uuid();
    }

    public function name(): string
    {
        return 'log';
    }
}
