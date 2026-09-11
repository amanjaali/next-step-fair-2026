<?php

namespace App\Services\Messaging;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\Registration;

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
     * Always a public HTTPS PNG — Meta fetches this; localhost / 127.0.0.1 never
     * works, and OTPIQ does not accept PDF headers. Domain comes from
     * OTPIQ_PUBLIC_URL (default https://www.nextstepfair.com).
     *
     * On local, tickets only live in the local DB — a production badge URL 404s,
     * Meta drops the message after OTPIQ already said "accepted". Use the sample
     * PNG so delivery still works while testing from a laptop.
     */
    public function badgeUrl(Registration $registration): string
    {
        if (app()->isLocal()) {
            $sample = config('whatsapp.otpiq.local_header_image');

            if (is_string($sample) && $sample !== '') {
                return $sample;
            }
        }

        $public = rtrim((string) (config('whatsapp.otpiq.public_url') ?: 'https://www.nextstepfair.com'), '/');

        return $public.'/ticket/'.$registration->ticket_id.'/badge.png';
    }

    /**
     * URL-button tail for the template approved as …/ticket/{{1}}.
     *
     * OTPIQ's example uses "{ticket}/badge.png" (PNG only — not PDF, not /b/).
     */
    public function badgeLinkParam(Registration $registration): string
    {
        return $registration->ticket_id.'/badge.png';
    }
}
