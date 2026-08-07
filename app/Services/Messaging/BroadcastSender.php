<?php

namespace App\Services\Messaging;

use App\Models\Broadcast;
use App\Models\Registration;

/**
 * Resolves a broadcast's audience filter and queues one message per registrant.
 */
class BroadcastSender
{
    public function __construct(private readonly MessageDispatcher $dispatcher) {}

    public function audience(Broadcast $broadcast)
    {
        $filters = $broadcast->filters ?? [];

        return Registration::query()
            ->active()
            ->when($filters['track'] ?? null, fn ($q, $track) => $q->where('track', $track))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['city'] ?? null, fn ($q, $city) => $q->where('city', $city))
            ->when($filters['locale'] ?? null, fn ($q, $locale) => $q->where('locale', $locale))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['day'] ?? null, fn ($q, $day) => $q->whereJsonContains('days', (int) $day));
    }

    public function send(Broadcast $broadcast): int
    {
        $broadcast->forceFill(['status' => 'sending', 'started_at' => now()])->save();

        $sent = 0;

        $this->audience($broadcast)->chunkById(200, function ($registrations) use ($broadcast, &$sent) {
            foreach ($registrations as $registration) {
                if ($broadcast->channel === 'whatsapp' && ! $registration->phone) {
                    continue;
                }

                $message = $this->dispatcher->whatsapp(
                    $registration,
                    $broadcast->template_key ?: 'event_reminder_3days',
                    ['name' => $registration->firstName(), 'ticket' => $registration->ticket_ref],
                );

                $message->forceFill(['broadcast_id' => $broadcast->id])->save();
                $sent++;
            }
        });

        $broadcast->forceFill([
            'status' => 'sent',
            'audience_count' => $sent,
            'completed_at' => now(),
        ])->save();

        return $sent;
    }
}
