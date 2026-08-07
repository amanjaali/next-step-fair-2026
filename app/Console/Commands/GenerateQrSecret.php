<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Generates the HMAC secret that signs ticket QR codes.
 *
 * Rotating it invalidates every badge already issued, so the command says so and
 * asks before writing.
 */
class GenerateQrSecret extends Command
{
    protected $signature = 'nextstep:qr-secret {--show : Print the secret instead of writing it to .env}';

    protected $description = 'Generate the ticket QR signing secret';

    public function handle(): int
    {
        $secret = 'base64:'.base64_encode(random_bytes(32));

        if ($this->option('show')) {
            $this->line($secret);

            return self::SUCCESS;
        }

        $path = base_path('.env');

        if (! is_writable($path)) {
            $this->error('.env is not writable. Run with --show and set TICKET_QR_SECRET by hand.');

            return self::FAILURE;
        }

        $env = file_get_contents($path);
        $current = Str::match('/^TICKET_QR_SECRET=(.*)$/m', $env);

        if (filled($current) && ! $this->confirm('A secret is already set. Rotating it invalidates every issued badge. Continue?')) {
            return self::SUCCESS;
        }

        $env = preg_match('/^TICKET_QR_SECRET=/m', $env)
            ? preg_replace('/^TICKET_QR_SECRET=.*$/m', 'TICKET_QR_SECRET='.$secret, $env)
            : $env."\nTICKET_QR_SECRET=".$secret."\n";

        file_put_contents($path, $env);

        $this->info('Ticket QR secret written to .env.');
        $this->comment('Regenerate badges for existing registrations: php artisan nextstep:regenerate-badges');

        return self::SUCCESS;
    }
}
