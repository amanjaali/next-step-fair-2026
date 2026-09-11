<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

/**
 * WhatsApp delivery tracing.
 *
 * Always writes to storage/logs/whatsapp-*.log (level from WHATSAPP_LOG_LEVEL,
 * default debug). Errors and warnings are also mirrored to the default stack
 * so they appear in laravel-*.log when LOG_LEVEL=warning.
 */
final class WhatsAppLog
{
    public static function logger(): LoggerInterface
    {
        return Log::channel('whatsapp');
    }

    /** @param  array<string, mixed>  $context */
    public static function info(string $event, array $context = []): void
    {
        self::write('info', $event, $context);
    }

    /** @param  array<string, mixed>  $context */
    public static function warning(string $event, array $context = []): void
    {
        self::write('warning', $event, $context);
    }

    /** @param  array<string, mixed>  $context */
    public static function error(string $event, array $context = []): void
    {
        self::write('error', $event, $context);
    }

    /** @param  array<string, mixed>  $context */
    private static function write(string $level, string $event, array $context): void
    {
        $payload = array_merge(['event' => $event], $context);

        try {
            self::logger()->{$level}($event, $payload);
        } catch (\Throwable $e) {
            Log::{$level}('[whatsapp] '.$event, $payload + ['log_channel_error' => $e->getMessage()]);
        }

        // INFO lines stay in whatsapp-*.log only; errors/warnings also hit laravel-*.log.
        if (in_array($level, ['error', 'warning'], true)) {
            Log::{$level}('[whatsapp] '.$event, $payload);
        }
    }
}
