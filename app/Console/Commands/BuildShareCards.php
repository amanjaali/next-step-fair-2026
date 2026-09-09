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

            /*
             * The two ways this fails are both about the browser that takes the
             * screenshots, and both have one command each. Printing them beats
             * printing a stack trace at somebody who is trying to get a logo
             * onto a picture.
             */
            $output = $process->getOutput().$process->getErrorOutput();

            if (str_contains($output, "Cannot find module 'playwright'")) {
                $this->line('The browser that takes the screenshots is not installed yet. Run these two, then try again:');
                $this->newLine();
                $this->line('    npm install --save-dev playwright');
                $this->line('    npx playwright install chromium');
            } elseif (str_contains($output, 'Executable doesn\'t exist') || str_contains($output, 'playwright install')) {
                $this->line('Playwright is installed but its browser is not. Run this, then try again:');
                $this->newLine();
                $this->line('    npx playwright install chromium');
            } else {
                $this->line('Check that the site is running and reachable at '.$url.'.');
            }

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Done. The new cards are in public/assets/share.');

        return self::SUCCESS;
    }
}
