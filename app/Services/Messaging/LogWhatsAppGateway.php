<?php

namespace App\Services\Messaging;

use App\Models\Message;
use App\Services\Messaging\Contracts\WhatsAppGateway;
use App\Support\WhatsAppLog;
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
    public function sendTemplate(
        Message $message,
        string $template,
        string $locale,
        array $variables = [],
        ?string $mediaUrl = null,
        ?string $linkParam = null,
    ): ?string {
        WhatsAppLog::info('log_driver.template', [
            'template' => $template,
            'locale' => $locale,
            'to' => $message->recipient,
            'variables' => $variables,
            'media' => $mediaUrl,
            'link' => $linkParam,
            'preview' => $message->preview,
        ]);

        return 'log-'.Str::uuid();
    }

    public function sendText(Message $message, string $body): ?string
    {
        WhatsAppLog::info('log_driver.text', [
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
