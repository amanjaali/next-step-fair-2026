<?php

namespace App\Services\Messaging;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\Registration;
use App\Services\Messaging\Contracts\WhatsAppGateway;
use App\Support\WhatsAppLog;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Queues outbound messages and records them in the delivery log.
 *
 * Nothing sends inline: a registrant should never wait on Meta's API to see
 * their confirmation page, and a failed send has to be retryable from the admin.
 */
class MessageDispatcher
{
    /** Queues a WhatsApp template message for a registration with a phone number. */
    public function whatsapp(
        Registration $registration,
        string $templateKey,
        array $variables = [],
        bool $withBadge = false,
    ): ?Message {
        if (! $registration->msisdn()) {
            WhatsAppLog::warning('whatsapp.skipped_no_phone', [
                'registration_id' => $registration->id,
                'ticket_id' => $registration->ticket_id,
                'template_key' => $templateKey,
            ]);

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

        $this->dispatchWhatsApp($message->id, $variables, $withBadge);

        WhatsAppLog::info('whatsapp.queued', [
            'message_id' => $message->id,
            'registration_id' => $registration->id,
            'ticket_id' => $registration->ticket_id,
            'template_key' => $templateKey,
            'locale' => $locale,
            'recipient' => $message->recipient,
            'with_badge' => $withBadge,
            'driver' => config('whatsapp.driver'),
            'queue_connection' => config('queue.default'),
            'variables' => $variables,
        ]);

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
     * OTPIQ_PUBLIC_URL in .env (default https://www.nextstepfair.com).
     *
     * When the public origin is localhost, tickets only live in the local DB — a
     * production badge URL 404s and Meta drops the message. Use the sample PNG so
     * delivery still works from a laptop. Staging hosts (e.g. demi.nextstepfair.com)
     * always use the real badge URL from OTPIQ_PUBLIC_URL.
     */
    public function badgeUrl(Registration $registration): string
    {
        if ($this->shouldUseLocalHeaderSample()) {
            $sample = config('whatsapp.otpiq.local_header_image');

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

    /**
     * HTTPS origin Meta/OTPIQ use for badge.png — from OTPIQ_PUBLIC_URL only.
     *
     * This must match the host that actually serves /ticket/{id}/badge.png. A
     * mismatch (e.g. www while the app runs on demi) makes Meta fetch a 404 and
     * silently drop the whole template.
     */
    public function publicOrigin(): string
    {
        $public = config('whatsapp.otpiq.public_url');

        if (! is_string($public) || $public === '') {
            throw new RuntimeException(
                'OTPIQ_PUBLIC_URL is not set. Set it to the HTTPS origin that serves badge images '
                .'(e.g. https://demi.nextstepfair.com), then run: php artisan config:clear && php artisan queue:restart'
            );
        }

        if (! $this->isLocalhostUrl($public) && ! str_starts_with($public, 'https://')) {
            throw new RuntimeException('OTPIQ_PUBLIC_URL must be an https:// origin.');
        }

        return rtrim($public, '/');
    }

    /**
     * Meta/OTPIQ fetch the header image before delivery. A 404 means the message
     * is accepted by OTPIQ but never arrives on the phone — so we refuse to send.
     *
     * Badge PNGs are rendered on demand and can take several seconds on first
     * request, so we use GET (not HEAD) with a generous timeout.
     */
    public function assertBadgeImageReachable(string $url, ?Registration $registration = null): void
    {
        if ($registration && ! $registration->badgeIssued()) {
            throw new RuntimeException(
                'WhatsApp not sent: badge has not been generated for ticket '.$registration->ticket_id
            );
        }

        $response = Http::timeout(45)
            ->withOptions(['allow_redirects' => true])
            ->get($url);

        $contentType = strtolower((string) $response->header('Content-Type'));

        if ($response->ok() && (str_starts_with($contentType, 'image/') || $contentType === '')) {
            return;
        }

        WhatsAppLog::error('whatsapp.badge_image_unreachable', [
            'url' => $url,
            'http_status' => $response->status(),
            'content_type' => $contentType !== '' ? $contentType : null,
            'registration_id' => $registration?->id,
            'ticket_id' => $registration?->ticket_id,
        ]);

        throw new RuntimeException(
            'WhatsApp not sent: badge image is not reachable at '.$url.' (HTTP '.$response->status().'). '
            .'Set OTPIQ_PUBLIC_URL to the host that serves this ticket, ensure the badge is generated, '
            .'then run: php artisan config:clear && php artisan queue:restart'
        );
    }

    /**
     * Fair/RSVP confirmations are triggered from the web. On demi we use
     * QUEUE_CONNECTION=database but often no long-running worker — so for HTTP
     * requests we send after the response returns instead of leaving jobs stuck
     * in the jobs table. Console commands and sync/tests keep normal dispatch.
     */
    private function dispatchWhatsApp(int $messageId, array $variables, bool $withBadge): void
    {
        if (app()->runningUnitTests() || app()->runningInConsole() || config('queue.default') === 'sync') {
            SendWhatsAppMessage::dispatch($messageId, $variables, $withBadge);

            return;
        }

        $job = new SendWhatsAppMessage($messageId, $variables, $withBadge);

        app()->terminating(function () use ($job) {
            try {
                $job->handle(
                    app(WhatsAppGateway::class),
                    app(self::class),
                    app(OtpiqTemplateRegistry::class),
                );
            } catch (Throwable $e) {
                report($e);
            }
        });
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
