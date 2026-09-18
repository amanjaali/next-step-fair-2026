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
        $this->line('Chrome/Chromium: '.($diag['chrome'] ?? 'MISSING — npm install downloads it, or set BADGE_CHROME_PATH'));
        $this->line('GD FreeType: '.($diag['freetype'] ? 'yes' : 'MISSING — reinstall php-gd with FreeType'));

        foreach (['UniSirwanPingHeavy.ttf', 'NotoSansArabic-Bold.ttf'] as $font) {
            $path = resource_path('fonts/badge/'.$font);
            $this->line("Font {$font}: ".(is_readable($path) ? 'ok' : 'MISSING'));
        }

        $this->newLine();

        if ($diag['node'] && $diag['puppeteer'] && $diag['chrome']) {
            $this->info('Browsershot path: READY (full Blade badge + native Kurdish shaping).');
        } else {
            $this->warn('Browsershot path: NOT READY — Kurdish PNGs use the GD fallback until Node + Puppeteer work.');
            $this->line('Fix: sudo apt install -y nodejs npm && cd '.base_path().' && npm install');
            $this->line('Then: php artisan config:clear && php artisan nextstep:regenerate-badges');
        }

        if (! $diag['freetype']) {
            $this->error('GD fallback cannot draw Kurdish without FreeType.');
        }

        return self::SUCCESS;
    }
}
