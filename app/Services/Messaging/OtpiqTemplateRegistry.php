<?php

namespace App\Services\Messaging;

use App\Models\OtpiqTemplate;
use Illuminate\Support\Facades\Schema;

/**
 * Resolves OTPIQ template metadata for a logical key and locale.
 *
 * Name, id, body slots, and header/button flags come from `otpiq_templates`
 * (editable in Filament), with config/whatsapp.php as fallback.
 */
class OtpiqTemplateRegistry
{
    /**
     * @return array{
     *     name: string,
     *     id: string|null,
     *     body: array<int, string>,
     *     header: bool,
     *     button: bool
     * }|null
     */
    public function get(string $logicalKey, string $locale): ?array
    {
        $configShape = config("whatsapp.otpiq_templates.$logicalKey.$locale");

        if (! is_array($configShape) && ! $this->row($logicalKey, $locale)) {
            return null;
        }

        $identity = $this->identity($logicalKey, $locale);

        if ($identity === null) {
            return null;
        }

        $row = $this->row($logicalKey, $locale);

        return [
            'name' => $identity['name'],
            'id' => $identity['id'],
            'body' => $row?->bodySlotNames() ?? (is_array($configShape['body'] ?? null) ? $configShape['body'] : []),
            'header' => $row?->wantsHeaderImage() ?? (bool) ($configShape['header'] ?? true),
            'button' => $row?->wantsButtonLink() ?? (bool) ($configShape['button'] ?? false),
        ];
    }

    /**
     * @return array{name: string, id: string|null}|null
     */
    private function identity(string $logicalKey, string $locale): ?array
    {
        $name = "{$logicalKey}_{$locale}_2026";

        if ($this->tableExists()) {
            $row = $this->row($logicalKey, $locale);

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

    private function row(string $logicalKey, string $locale): ?OtpiqTemplate
    {
        if (! $this->tableExists()) {
            return null;
        }

        return OtpiqTemplate::query()
            ->where('logical_key', $logicalKey)
            ->where('locale', $locale)
            ->first();
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
