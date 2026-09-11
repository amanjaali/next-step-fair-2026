<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Support\WhatsAppLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Quick server-side check: driver, OTPIQ .env config, queue, recent messages, log write.
 */
class DiagnoseWhatsApp extends Command
{
    protected $signature = 'nextstep:whatsapp-diagnose';

    protected $description = 'Print WhatsApp / OTPIQ configuration and write a test line to the log';

    public function handle(): int
    {
        $otpiq = config('whatsapp.otpiq', []);

        $this->line('WhatsApp diagnose');
        $this->line(str_repeat('-', 40));

        $this->line('WHATSAPP_DRIVER: '.config('whatsapp.driver'));
        $this->line('QUEUE_CONNECTION: '.config('queue.default'));
        $this->line('LOG_CHANNEL: '.config('logging.default'));
        $this->line('LOG_STACK: '.implode(',', config('logging.channels.stack.channels', [])));

        $this->line('');
        $this->line('OTPIQ (.env via config/whatsapp.php):');
        $this->line('  public_url: '.($otpiq['public_url'] ?? 'EMPTY'));
        $this->line('  api_key: '.(! empty($otpiq['api_key']) ? 'set' : 'EMPTY — set OTPIQ_API_KEY'));
        $this->line('  webhook_secret: '.(! empty($otpiq['webhook_secret']) ? 'set' : 'EMPTY'));
        $this->line('  account_id: '.($otpiq['account_id'] ?: 'EMPTY'));
        $this->line('  phone_id: '.($otpiq['phone_id'] ?: 'EMPTY'));
        $this->line('  send_header_image: '.(! empty($otpiq['send_header_image']) ? 'yes' : 'no'));
        $this->line('  send_button_link: '.(! empty($otpiq['send_button_link']) ? 'yes' : 'no'));

        if (Schema::hasTable('otpiq_templates')) {
            $count = (int) \DB::table('otpiq_templates')->count();
            $this->line('');
            $this->line("otpiq_templates rows: {$count}".($count === 0 ? ' — run db:seed --class=OtpiqTemplateSeeder' : ''));
        }

        if (Schema::hasTable('messages')) {
            $recent = Message::query()
                ->where('channel', 'whatsapp')
                ->latest('id')
                ->limit(5)
                ->get(['id', 'template_key', 'status', 'recipient', 'error', 'created_at']);

            $this->line('');
            $this->line('Last 5 WhatsApp messages in DB:');
            if ($recent->isEmpty()) {
                $this->warn('  (none — registration may not have reached the queue step)');
            } else {
                foreach ($recent as $message) {
                    $this->line(sprintf(
                        '  #%d %s %s %s %s',
                        $message->id,
                        $message->status,
                        $message->template_key,
                        $message->recipient,
                        $message->error ? 'ERR: '.$message->error : '',
                    ));
                }
            }
        }

        if (Schema::hasTable('jobs')) {
            $pending = (int) \DB::table('jobs')->count();
            $this->line('');
            $this->line('Pending queue jobs: '.$pending);
            if ($pending > 0) {
                $this->warn('Queue worker may not be running — try: php artisan queue:work --once');
            }
        }

        WhatsAppLog::info('diagnose.test_line', [
            'server' => gethostname(),
            'at' => now()->toIso8601String(),
        ]);

        $this->line('');
        $this->info('Wrote diagnose.test_line to storage/logs/whatsapp-*.log and laravel-*.log');
        $this->line('After registering, run: tail -f storage/logs/whatsapp-*.log storage/logs/laravel-*.log');

        return self::SUCCESS;
    }
}
