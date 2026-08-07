<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Models\Registration;
use App\Services\BadgeService;
use Filament\Resources\Pages\CreateRecord;

class CreateRegistration extends CreateRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['track'] = Registration::TRACK_FAIR;
        $data['is_walk_in'] = true;
        $data['created_by'] = auth()->id();
        $data['consented_at'] = now();
        $data['confirmed_at'] ??= now();

        return $data;
    }

    /** A walk-in still needs a badge before they reach the gate. */
    protected function afterCreate(): void
    {
        if ($this->record->badgeIssued()) {
            app(BadgeService::class)->generate($this->record);
        }
    }
}
