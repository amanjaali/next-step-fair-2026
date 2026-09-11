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
    public function __construct(private readonly OtpiqConfigRegistry $otpiq) {}

    /** Queues a WhatsApp template message for a registration with a phone number. */
    public function whatsapp(
        Registration $registration,
        string $templateKey,
        array $variables = [],
        bool $withBadge = false,
    ): ?Message {
        if (! $registration->msisdn()) {
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
     * otpiq_settings.public_url (default https://www.nextstepfair.com).
     *
     * When the public origin is localhost, tickets only live in the local DB — a
     * production badge URL 404s and Meta drops the message. Use the sample PNG so
     * delivery still works from a laptop. Staging hosts (e.g. demi.nextstepfair.com)
     * always use the real badge URL from otpiq_settings.public_url.
     */
    public function badgeUrl(Registration $registration): string
    {
        if ($this->shouldUseLocalHeaderSample()) {
            $sample = $this->otpiq->get('local_header_image');

            if (is_string($sample) && $sample !== '') {
                return $sample;
            }
        }

        return $this->publicOrigin().'/ticket/'.$registration->ticket_id.'/badge.png';
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

    /** HTTPS origin Meta/OTPIQ use for badge.png — from otpiq_settings. */
    public function publicOrigin(): string
    {
        $public = $this->otpiq->get('public_url');

        if (is_string($public) && $public !== '') {
            return rtrim($public, '/');
        }

        $appUrl = config('app.url');

        if (is_string($appUrl) && str_starts_with($appUrl, 'https://') && ! $this->isLocalhostUrl($appUrl)) {
            return rtrim($appUrl, '/');
        }

        return 'https://www.nextstepfair.com';
    }

    private function shouldUseLocalHeaderSample(): bool
    {
        return $this->isLocalhostUrl($this->publicOrigin());
    }

    private function isLocalhostUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return in_array($host, ['localhost', '127.0.0.1', '[::1]'], true);
    }
}
