<?php

namespace App\Services\Analytics;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Throwable;

/**
 * Meta Pixel and Conversions API settings, editable from the admin so nobody
 * needs server access. Values saved in the admin win over .env; the token is
 * stored encrypted.
 */
class MetaSettings
{
    public static function pixelId(): ?string
    {
        return self::clean(Setting::get('meta_pixel_id')) ?? self::clean(config('nextstep.analytics.meta_pixel'));
    }

    public static function token(): ?string
    {
        $stored = Setting::get('meta_capi_token');

        if (is_array($stored) && filled($stored['encrypted'] ?? null)) {
            try {
                return Crypt::decryptString($stored['encrypted']);
            } catch (Throwable) {
                return null;
            }
        }

        return self::clean(config('nextstep.analytics.meta_capi_token'));
    }

    public static function testCode(): ?string
    {
        return self::clean(Setting::get('meta_capi_test_code')) ?? self::clean(config('nextstep.analytics.meta_capi_test_code'));
    }

    public static function graphVersion(): string
    {
        return (string) config('nextstep.analytics.meta_graph_version', 'v21.0');
    }

    public static function serverEnabled(): bool
    {
        return self::pixelId() !== null && self::token() !== null;
    }

    public static function saveToken(?string $token): void
    {
        Setting::put('meta_capi_token', filled($token) ? ['encrypted' => Crypt::encryptString(trim($token))] : null, 'meta');
    }

    public static function recordSuccess(): void
    {
        Setting::put('meta_capi_status', ['ok_at' => now()->toIso8601String(), 'error' => null, 'error_at' => null] + self::status(), 'meta');
    }

    public static function recordError(string $error): void
    {
        Setting::put('meta_capi_status', ['error' => mb_substr($error, 0, 500), 'error_at' => now()->toIso8601String()] + self::status(), 'meta');
    }

    /** @return array{ok_at?: ?string, error?: ?string, error_at?: ?string} */
    public static function status(): array
    {
        return (array) Setting::get('meta_capi_status', []);
    }

    private static function clean(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        return filled($value) ? $value : null;
    }
}
