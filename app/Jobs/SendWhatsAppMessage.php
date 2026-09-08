<?php

namespace App\Jobs;

use App\Models\Message;
use App\Services\Messaging\Contracts\WhatsAppGateway;
use App\Services\Messaging\MessageDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * One outbound WhatsApp message, with the retry schedule from config.
 *
 * A failure is recorded on the message row with the provider's reason, so the
 * registration manager can see why a badge did not arrive and retry it.
 */
class SendWhatsAppMessage implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $messageId,
        public array $variables = [],
        public bool $withBadge = false,
    ) {}

    public function tries(): int
    {
        return (int) config('whatsapp.max_attempts', 4);
    }

    /** @return array<int, int> */
    public function backoff(): array
    {
        return config('whatsapp.retry_backoff', [60, 300, 1800]);
    }

    public function handle(WhatsAppGateway $gateway, MessageDispatcher $dispatcher): void
    {
        $message = Message::with('registration')->find($this->messageId);

        if (! $message || $message->status === Message::STATUS_DELIVERED) {
            return;
        }

        $message->increment('attempts');

        try {
            $mediaUrl = null;
            $linkParam = null;

            // The badge travels two ways in the same message: the picture in the
            // template's header, and the link on its button. A template approved
            // without one of them simply ignores what it was not given, so both
            // are offered whenever there is a badge to offer.
            if ($this->withBadge && $message->registration) {
                $mediaUrl = $dispatcher->badgeUrl($message->registration);
                $linkParam = $dispatcher->badgeLinkParam($message->registration);
            }

            $providerId = $gateway->sendTemplate(
                $message,
                config('whatsapp.templates.'.$message->template_key, $message->template_key),
                $message->locale,
                $this->variables,
                $mediaUrl,
                $linkParam,
            );

            $message->forceFill([
                'status' => Message::STATUS_SENT,
                'provider_message_id' => $providerId,
                'sent_at' => now(),
                'error' => null,
            ])->save();

            // The log driver has no delivery webhook, so it settles immediately.
            if ($gateway->name() === 'log') {
                $message->forceFill([
                    'status' => Message::STATUS_DELIVERED,
                    'delivered_at' => now(),
                ])->save();
            }
        } catch (Throwable $e) {
            $message->forceFill([
                'status' => Message::STATUS_FAILED,
                'error' => $e->getMessage(),
                'failed_at' => now(),
            ])->save();

            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        Message::where('id', $this->messageId)->update([
            'status' => Message::STATUS_FAILED,
            'error' => $e->getMessage(),
            'failed_at' => now(),
        ]);
    }
}
