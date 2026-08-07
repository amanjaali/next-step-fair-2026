<?php

namespace App\Console\Commands;

use App\Models\Registration;
use App\Services\Messaging\MessageDispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * The scheduled WhatsApp reminders: three days out, the day before, and the
 * morning-of directions. Run from the scheduler; safe to run twice.
 */
class SendEventReminders extends Command
{
    protected $signature = 'nextstep:reminders {which : three-days|one-day|day-of} {--dry-run}';

    protected $description = 'Queue the scheduled event reminders to confirmed registrants';

    public function handle(MessageDispatcher $dispatcher): int
    {
        $template = match ($this->argument('which')) {
            'three-days' => 'event_reminder_3days',
            'one-day' => 'event_reminder_1day',
            'day-of' => 'day_of_directions',
            default => null,
        };

        if (! $template) {
            $this->error('Unknown reminder. Use three-days, one-day or day-of.');

            return self::FAILURE;
        }

        $query = Registration::query()
            ->fair()
            ->whereIn('status', [Registration::STATUS_CONFIRMED, Registration::STATUS_CHECKED_IN])
            // Do not re-send a reminder someone already has.
            ->whereDoesntHave('messages', fn ($q) => $q->where('template_key', $template));

        // The day-of message only goes to people attending today.
        if ($template === 'day_of_directions') {
            $today = collect(config('nextstep.event.days'))
                ->search(fn ($meta) => Carbon::parse($meta['date'])->isToday());

            if (! $today) {
                $this->warn('Today is not an event day. Nothing sent.');

                return self::SUCCESS;
            }

            $query->whereJsonContains('days', (int) $today);
        }

        $count = $query->count();

        if ($this->option('dry-run')) {
            $this->info("Dry run: {$count} registrant(s) would receive {$template}.");

            return self::SUCCESS;
        }

        $query->chunkById(200, function ($registrations) use ($dispatcher, $template) {
            foreach ($registrations as $registration) {
                $dispatcher->whatsapp($registration, $template, [
                    'name' => $registration->firstName(),
                    'days' => $registration->daysLabel(),
                    'ticket' => $registration->ticket_ref,
                ]);
            }
        });

        $this->info("Queued {$template} for {$count} registrant(s).");

        return self::SUCCESS;
    }
}
