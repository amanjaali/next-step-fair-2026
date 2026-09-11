<?php

namespace App\Filament\Resources\OtpiqTemplates\Pages;

use App\Filament\Resources\OtpiqTemplates\OtpiqTemplateResource;
use Filament\Resources\Pages\EditRecord;

class EditOtpiqTemplate extends EditRecord
{
    protected static string $resource = OtpiqTemplateResource::class;

    /** Only the OTPIQ dashboard id is editable — name and shape are fixed in code. */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return [
            'provider_id' => $data['provider_id'],
        ];
    }
}
