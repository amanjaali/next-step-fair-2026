<?php

namespace App\Console\Commands;

use App\Models\EventSession;
use App\Services\Messaging\MessageDispatcher;
use Illuminate\Console\Command;

/**
 * The 15-minute reminder for sessions a registrant saved to their own agenda.
 * Runs every five minutes during the event.
 */
class SendSessionReminders extends Command
{
    protected $signature = 'nextstep:session-reminders';

    protected $description = 'Remind registrants 15 minutes before a session they saved';

    public function handle(MessageDispatcher $dispatcher): int
    {
        $sent = 0;

        EventSession::published()->forYear((int) config('nextstep.event.year'))
            ->where('bookable', true)
            ->get()
            ->filter(function (EventSession $session) {
                $minutes = now()->diffInMinutes($session->startsAtDateTime(), false);

                return $minutes > 0 && $minutes <= 15;
            })
            ->each(function (EventSession $session) use ($dispatcher, &$sent) {
                $session->registrations()
                    ->wherePivotNull('reminder_sent_at')
                    ->chunkById(200, function ($registrations) use ($session, $dispatcher, &$sent) {
                        foreach ($registrations as $registration) {
                            $dispatcher->whatsapp($registration, 'session_reminder', [
                                'title' => $session->t('title'),
                                'hall' => $session->hallLabel(),
                            ]);

                            $session->registrations()->updateExistingPivot($registration->id, [
                                'reminder_sent_at' => now(),
                            ]);

                            $sent++;
                        }
                    });
            });

        $this->info("Queued {$sent} session reminder(s).");

        return self::SUCCESS;
    }
}
