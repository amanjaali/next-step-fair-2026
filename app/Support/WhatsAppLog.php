<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

/**
 * WhatsApp delivery tracing.
 *
 * Writes to storage/logs/whatsapp-*.log and mirrors the same line to the default
 * log stack (laravel-*.log) so a missing dedicated file on the server still
 * leaves a trace after deploy.
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

        Log::{$level}('[whatsapp] '.$event, $payload);
    }
}
