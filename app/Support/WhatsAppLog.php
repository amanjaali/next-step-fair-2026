<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

/** Writes to storage/logs/whatsapp.log — one place to debug delivery. */
final class WhatsAppLog
{
    public static function logger(): LoggerInterface
    {
        return Log::channel('whatsapp');
    }

    /** @param  array<string, mixed>  $context */
    public static function info(string $event, array $context = []): void
    {
        self::logger()->info($event, $context);
    }

    /** @param  array<string, mixed>  $context */
    public static function warning(string $event, array $context = []): void
    {
        self::logger()->warning($event, $context);
    }

    /** @param  array<string, mixed>  $context */
    public static function error(string $event, array $context = []): void
    {
        self::logger()->error($event, $context);
    }
}
