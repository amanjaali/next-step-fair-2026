<?php

namespace App\Services\Messaging;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\Registration;
use Illuminate\Support\Facades\URL;

/**
 * Queues outbound messages and records them in the delivery log.
 *
 * Nothing sends inline: a registrant should never wait on Meta's API to see
 * their confirmation page, and a failed send has to be retryable from the admin.
 */
class MessageDispatcher
{
    /** Queues a WhatsApp template message for a conference RSVP registration. */
    public function whatsapp(
        Registration $registration,
        string $templateKey,
        array $variables = [],
        bool $withBadge = false,
    ): ?Message {
        if (! $registration->isConference()) {
            return null;
        }

        $locale = $registration->locale;
        $template = MessageTemplate::where('key', $templateKey)
            ->where('channel', 'whatsapp')
            ->where('locale', $locale)
            ->first();

        $preview = $template
            ? $template->render($variables)
            : __("notifications.whatsapp.$templateKey", $variables, $locale);

        $message = Message::create([
            'registration_id' => $registration->id,
            'channel' => 'whatsapp',
            'template_key' => $templateKey,
            'locale' => $locale,
            'recipient' => $registration->msisdn(),
            'preview' => $preview,
            'status' => Message::STATUS_QUEUED,
            'queued_at' => now(),
        ]);

        SendWhatsAppMessage::dispatch($message->id, $variables, $withBadge);

        return $message;
    }

    /** Records an e-mail in the delivery log (no outbound mail is sent). */
    public function logEmail(Registration $registration, string $templateKey, string $subject, string $preview): Message
    {
        return Message::create([
            'registration_id' => $registration->id,
            'channel' => 'email',
            'template_key' => $templateKey,
            'locale' => $registration->locale,
            'recipient' => $registration->email,
            'subject' => $subject,
            'preview' => $preview,
            'status' => Message::STATUS_QUEUED,
            'queued_at' => now(),
        ]);
    }

    /**
     * Header image URL for WhatsApp templateParameters.header.imageUrl.
     *
     * Always the generated badge PNG for this registration. OTPIQ/Meta must be
     * able to fetch it over HTTPS — set OTPIQ_PUBLIC_URL to your public origin
     * (production domain or an ngrok tunnel when developing locally).
     */
    public function badgeUrl(Registration $registration): string
    {
        $public = config('whatsapp.otpiq.public_url') ?: config('app.url');
        $previous = config('app.url');

        URL::forceRootUrl(rtrim((string) $public, '/'));
        URL::forceScheme(str_starts_with((string) $public, 'https') ? 'https' : 'http');

        try {
            return URL::temporarySignedRoute(
                'ticket.png',
                now()->addMinutes((int) config('nextstep.badge.download_link_ttl')),
                ['ticket' => $registration->ticket_id]
            );
        } finally {
            URL::forceRootUrl(rtrim((string) $previous, '/'));
            URL::forceScheme(str_starts_with((string) $previous, 'https') ? 'https' : 'http');
        }
    }

    /**
     * What a URL button appends to the address approved with the template.
     *
     * The template is approved as https://…/{{1}}, so this is "b/<ticket>" and
     * never a whole address. It does not expire, unlike the picture link above:
     * this is the one somebody opens in October to find the badge they were sent
     * in September, and a signature that has run out would be worse than useless
     * to them.
     */
    public function badgeLinkParam(Registration $registration): string
    {
        return ltrim(route('badge.link', $registration->ticket_id, absolute: false), '/');
    }
}
