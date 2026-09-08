<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Re-bakes the cards people post.
 *
 * The eighteen cards under public/assets/share are screenshots of a real page,
 * taken once and committed — which means a logo uploaded on the Brand images
 * screen appears everywhere on the site immediately and on those cards not at
 * all, until somebody re-renders them. That gap is invisible and it is exactly
 * the sort of thing that gets reported as "the logo is missing" three days
 * before the fair.
 *
 * So the re-render is a command rather than a paragraph in a README about node
 * and headless browsers.
 */
class BuildShareCards extends Command
{
    protected $signature = 'share:cards {--url= : Where the site is running, e.g. http://127.0.0.1:8000}';

    protected $description = 'Re-render the share cards, picking up any newly uploaded logos';

    public function handle(): int
    {
        $url = rtrim($this->option('url') ?: (string) config('app.url'), '/');
        $script = base_path('tools/build-share-cards.cjs');

        if (! is_readable($script)) {
            $this->error('tools/build-share-cards.cjs is missing.');

            return self::FAILURE;
        }

        $this->line("Rendering the cards from {$url} …");
        $this->line('The site has to be running and reachable at that address.');
        $this->newLine();

        $process = new Process(['node', $script, $url], base_path(), null, null, 600);
        $process->run(fn ($type, $buffer) => $this->output->write($buffer));

        if (! $process->isSuccessful()) {
            $this->newLine();
            $this->error('The cards were not rendered.');
            $this->line('Node and Playwright are needed for this: npm install playwright');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Done. The new cards are in public/assets/share.');

        return self::SUCCESS;
    }
}
