<?php

namespace App\Services\Messaging;

use App\Models\OtpiqSetting;
use Illuminate\Support\Facades\Schema;

/**
 * OTPIQ credentials and badge flags from `otpiq_settings`.
 *
 * Falls back to `config/whatsapp.otpiq` when the table is missing or empty so
 * tests and fresh installs still boot.
 */
class OtpiqConfigRegistry
{
    /** @var array<string, mixed>|null */
    private static ?array $cache = null;

    public static function flush(): void
    {
        self::$cache = null;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $defaults = config('whatsapp.otpiq', []);

        if (! $this->tableExists()) {
            return self::$cache = $defaults;
        }

        $row = OtpiqSetting::current();

        if (! $row) {
            return self::$cache = $defaults;
        }

        return self::$cache = array_merge($defaults, [
            'base_url' => $row->base_url ?: ($defaults['base_url'] ?? null),
            'api_key' => $row->api_key ?: ($defaults['api_key'] ?? null),
            'account_id' => $row->account_id ?: ($defaults['account_id'] ?? null),
            'phone_id' => $row->phone_id ?: ($defaults['phone_id'] ?? null),
            'webhook_secret' => $row->webhook_secret ?: ($defaults['webhook_secret'] ?? null),
            'send_header_image' => $row->send_header_image,
            'send_button_link' => $row->send_button_link,
            'public_url' => $row->public_url ?: ($defaults['public_url'] ?? null),
            'local_header_image' => $row->local_header_image ?: ($defaults['local_header_image'] ?? null),
            'verify_ssl' => $row->verify_ssl,
        ]);
    }

    private function tableExists(): bool
    {
        try {
            return Schema::hasTable('otpiq_settings');
        } catch (\Throwable) {
            return false;
        }
    }
}
