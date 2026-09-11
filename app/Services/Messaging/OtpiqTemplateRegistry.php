<?php

namespace App\Services\Messaging;

use App\Models\OtpiqTemplate;
use Illuminate\Support\Facades\Schema;

/**
 * Resolves OTPIQ template metadata for a logical key and locale.
 *
 * Rows from `otpiq_templates` win; `config/whatsapp.otpiq_templates` is the
 * fallback when the table is missing (migrations not run) or a row is absent.
 */
class OtpiqTemplateRegistry
{
    /**
     * @return array{name?: string, id?: string|null, body?: array<int, string>, header_image?: bool}|null
     */
    public function get(string $logicalKey, string $locale): ?array
    {
        if ($this->tableExists()) {
            $row = OtpiqTemplate::query()
                ->where('logical_key', $logicalKey)
                ->where('locale', $locale)
                ->where('active', true)
                ->first();

            if ($row) {
                return [
                    'name' => $row->name,
                    'id' => $row->provider_id,
                    'body' => $row->body_variables ?? [],
                    'header_image' => $row->header_image,
                ];
            }
        }

        $fallback = config("whatsapp.otpiq_templates.$logicalKey.$locale");

        return is_array($fallback) ? $fallback : null;
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
