<?php

namespace App\Console\Commands;

use App\Models\Registration;
use App\Services\BadgeService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RegenerateBadges extends Command
{
    protected $signature = 'nextstep:regenerate-badges
                            {--track= : fair or conference}
                            {--registration= : Database id, ticket UUID, or ticket ref (e.g. 5DD7-E32A-65AD)}';

    protected $description = 'Regenerate badge artwork for every issued ticket, or one registration';

    public function handle(BadgeService $badges): int
    {
        $query = Registration::query()
            ->whereIn('status', [Registration::STATUS_CONFIRMED, Registration::STATUS_CHECKED_IN])
            ->when($this->option('track'), fn ($q, $track) => $q->where('track', $track));

        if ($registrationKey = $this->option('registration')) {
            $registration = $this->resolveRegistration((string) $registrationKey);

            if (! $registration) {
                $this->error("Registration not found: {$registrationKey}");

                return self::FAILURE;
            }

            if (! $registration->badgeIssued()) {
                $this->error(
                    "Registration {$registrationKey} is {$registration->status}; "
                    .'only confirmed or checked-in tickets have badges.'
                );

                return self::FAILURE;
            }

            $query->whereKey($registration->id);
        }

        $total = $query->count();

        if ($total === 0) {
            $this->warn('No badges to regenerate.');

            return self::SUCCESS;
        }

        $this->info("Regenerating {$total} badge(s)...");

        $bar = $this->output->createProgressBar($total);

        $query->chunkById(100, function ($registrations) use ($badges, $bar) {
            foreach ($registrations as $registration) {
                $badges->generate($registration);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info('Done.');

        return self::SUCCESS;
    }

    /**
     * Accept the database id, public ticket UUID, or the short ticket ref
     * printed on the badge — whichever is easiest to copy from the admin desk.
     */
    private function resolveRegistration(string $key): ?Registration
    {
        if (ctype_digit($key)) {
            return Registration::query()->find((int) $key);
        }

        if (Str::isUuid($key)) {
            return Registration::query()->where('ticket_id', $key)->first();
        }

        $normalizedRef = strtoupper(str_replace('-', '', $key));

        return Registration::query()
            ->where('ticket_id', $key)
            ->orWhere('ticket_ref', $key)
            ->orWhereRaw(
                "REPLACE(UPPER(ticket_ref), '-', '') = ?",
                [$normalizedRef]
            )
            ->first();
    }
}
