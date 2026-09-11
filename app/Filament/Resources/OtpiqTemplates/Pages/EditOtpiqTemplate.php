<?php

namespace App\Filament\Resources\OtpiqTemplates\Pages;

use App\Filament\Resources\OtpiqTemplates\OtpiqTemplateResource;
use Filament\Resources\Pages\EditRecord;

class EditOtpiqTemplate extends EditRecord
{
    protected static string $resource = OtpiqTemplateResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return [
            'name' => $data['name'],
            'provider_id' => $data['provider_id'],
        ];
    }
}
