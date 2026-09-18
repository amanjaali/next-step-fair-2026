<?php

namespace App\Console\Commands;

use App\Services\BadgeService;
use Illuminate\Console\Command;

class BadgeDoctor extends Command
{
    protected $signature = 'nextstep:badge-doctor';

    protected $description = 'Check Node, Puppeteer, Chrome, GD and fonts for Kurdish badge rendering';

    public function handle(BadgeService $badges): int
    {
        $diag = $badges->renderingDiagnostics();

        $this->line('Badge rendering diagnostics');
        $this->line('──────────────────────────');
        $this->line('Node binary: '.($diag['node'] ?? 'MISSING — sudo apt install nodejs npm'));
        $this->line('Puppeteer package: '.($diag['puppeteer'] ? 'yes (node_modules/puppeteer)' : 'MISSING — run npm install in project root'));
        $this->line('Chrome/Chromium: '.($diag['chrome'] ?? 'MISSING — run npx puppeteer browsers install chrome in project root'));
        $this->line('GD FreeType: '.($diag['freetype'] ? 'yes' : 'MISSING — reinstall php-gd with FreeType'));

        foreach (['UniSirwanPingHeavy.ttf', 'NotoSansArabic-Bold.ttf'] as $font) {
            $path = resource_path('fonts/badge/'.$font);
            $this->line("Font {$font}: ".(is_readable($path) ? 'ok' : 'MISSING'));
        }

        $this->newLine();

        if ($diag['chrome_snap'] ?? false) {
            $this->warn('Chrome is the Ubuntu snap build — it fails under www-data. Use Puppeteer Chrome in .puppeteer-cache instead.');
        }

        if ($diag['chrome_outside_project'] ?? false) {
            $this->warn('Chrome lives under /home/ — www-data cannot use it. Install into .puppeteer-cache in the project instead.');
        }

        if (($diag['chrome_snap'] ?? false) || ($diag['chrome_outside_project'] ?? false)) {
            $this->line('Fix: remove BADGE_CHROME_PATH from .env, then:');
            $this->line('  cd '.base_path());
            $this->line('  PUPPETEER_CACHE_DIR='.base_path('.puppeteer-cache').' npx puppeteer browsers install chrome');
            $this->line('  sudo chown -R www-data:www-data .puppeteer-cache storage');
        }

        $chromeOk = $diag['node'] && $diag['puppeteer'] && $diag['chrome']
            && ! ($diag['chrome_snap'] ?? false)
            && ! ($diag['chrome_outside_project'] ?? false);

        if ($chromeOk) {
            $this->info('Browsershot path: READY (full Blade badge + native Kurdish shaping).');
        } elseif (! ($diag['chrome_snap'] ?? false) && ! ($diag['chrome_outside_project'] ?? false)) {
            $this->warn('Browsershot path: NOT READY — Kurdish PNGs use the GD fallback until Node + Puppeteer work.');
            $this->line('Fix: cd '.base_path().' && npm install');
            $this->line('  PUPPETEER_CACHE_DIR='.base_path('.puppeteer-cache').' npx puppeteer browsers install chrome');
            $this->line('  sudo chown -R www-data:www-data .puppeteer-cache storage');
            $this->line('Then: php artisan config:clear && sudo -u www-data php artisan nextstep:regenerate-badges');
        }

        if (! $diag['freetype']) {
            $this->error('GD fallback cannot draw Kurdish without FreeType.');
        }

        return self::SUCCESS;
    }
}
