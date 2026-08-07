<?php

namespace App\Services\Messaging\Contracts;

use App\Models\Message;

/**
 * The WhatsApp transport.
 *
 * Two implementations: `cloud_api` talks to Meta, `log` records the message and
 * marks it sent. Everything above this interface — the queue, the retry policy,
 * the delivery log, the admin resend — is identical for both.
 */
interface WhatsAppGateway
{
    /** Sends a pre-approved template message. Returns the provider message id. */
    public function sendTemplate(Message $message, string $template, string $locale, array $variables = [], ?string $mediaUrl = null): ?string;

    /** Sends a free-form text message (only valid inside a 24-hour session window). */
    public function sendText(Message $message, string $body): ?string;

    public function name(): string;
}
