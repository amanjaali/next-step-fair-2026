<?php

namespace App\Filament\Resources\ConferenceRsvps\Pages;

use App\Filament\Resources\ConferenceRsvps\ConferenceRsvpResource;
use App\Filament\Support\RegistrationActions;
use Filament\Resources\Pages\ViewRecord;

class ViewConferenceRsvp extends ViewRecord
{
    protected static string $resource = ConferenceRsvpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RegistrationActions::approve(),
            RegistrationActions::resend(),
            RegistrationActions::regenerateBadge(),
            RegistrationActions::downloadBadge(),
            RegistrationActions::cancel(),
        ];
    }
}
