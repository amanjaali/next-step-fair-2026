<?php

namespace App\Services\Messaging;

use App\Models\OtpiqTemplate;
use Illuminate\Support\Facades\Schema;

/**
 * Resolves OTPIQ template metadata for a logical key and locale.
 *
 * Dashboard name and id come from `otpiq_templates` (editable in Filament).
 * Body slots live in config.
 */
class OtpiqTemplateRegistry
{
    /**
     * @return array{name: string, id: string|null, body: array<int, string>}|null
     */
    public function get(string $logicalKey, string $locale): ?array
    {
        $shape = config("whatsapp.otpiq_templates.$logicalKey.$locale");

        if (! is_array($shape)) {
            return null;
        }

        $identity = $this->identity($logicalKey, $locale);

        if ($identity === null) {
            return null;
        }

        return [
            'name' => $identity['name'],
            'id' => $identity['id'],
            'body' => $shape['body'] ?? [],
        ];
    }

    /**
     * @return array{name: string, id: string|null}|null
     */
    private function identity(string $logicalKey, string $locale): ?array
    {
        $name = "{$logicalKey}_{$locale}_2026";

        if ($this->tableExists()) {
            $row = OtpiqTemplate::query()
                ->where('logical_key', $logicalKey)
                ->where('locale', $locale)
                ->first();

            if ($row) {
                return [
                    'name' => $row->name,
                    'id' => $row->provider_id,
                ];
            }
        }

        return [
            'name' => $name,
            'id' => null,
        ];
    }

    private function tableExists(): bool
    {
        try {
            return Schema::hasTable('otpiq_templates');
        } catch (\Throwable) {
            return false;
        }
    }
}
