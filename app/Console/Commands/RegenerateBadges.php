<?php

namespace App\Console\Commands;

use App\Models\Registration;
use App\Services\BadgeService;
use Illuminate\Console\Command;

class RegenerateBadges extends Command
{
    protected $signature = 'nextstep:regenerate-badges {--track= : fair or conference}';

    protected $description = 'Regenerate badge artwork for every issued ticket';

    public function handle(BadgeService $badges): int
    {
        $query = Registration::query()
            ->whereIn('status', [Registration::STATUS_CONFIRMED, Registration::STATUS_CHECKED_IN])
            ->when($this->option('track'), fn ($q, $track) => $q->where('track', $track));

        $total = $query->count();
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
}
