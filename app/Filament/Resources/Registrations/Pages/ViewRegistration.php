<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Filament\Support\RegistrationActions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRegistration extends ViewRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->visible(fn () => auth()->user()?->can('edit-registrations') ?? false),
            RegistrationActions::approve(),
            RegistrationActions::resend(),
            RegistrationActions::regenerateBadge(),
            RegistrationActions::downloadBadge(),
            RegistrationActions::cancel(),
            RegistrationActions::delete(),
        ];
    }
}
