<?php

namespace App\Services\Messaging\Contracts;

use App\Models\Message;

/**
 * The WhatsApp transport.
 *
 * Three implementations: `cloud_api` talks to Meta directly, `otpiq` talks to
 * OTPIQ, who talk to Meta on our behalf, and `log` records the message and marks
 * it sent. Everything above this interface — the queue, the retry policy, the
 * delivery log, the admin resend — is identical for all three.
 */
interface WhatsAppGateway
{
    /**
     * Sends a pre-approved template message. Returns the provider message id.
     *
     * `$mediaUrl` is the badge picture for the template's header, and `$linkParam`
     * the piece a URL button appends to its own fixed prefix — the tail of the
     * badge address, not the whole address. Both are ignored by a template that
     * was approved without a header or without a button.
     */
    public function sendTemplate(
        Message $message,
        string $template,
        string $locale,
        array $variables = [],
        ?string $mediaUrl = null,
        ?string $linkParam = null,
    ): ?string;

    /** Sends a free-form text message (only valid inside a 24-hour session window). */
    public function sendText(Message $message, string $body): ?string;

    public function name(): string;
}
