<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Filament\Support\RegistrationActions;
use App\Services\BadgeService;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRegistration extends EditRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            RegistrationActions::resend(),
            RegistrationActions::regenerateBadge(),
            RegistrationActions::downloadBadge(),
        ];
    }

    /** A corrected name has to reach the badge, not just the database. */
    protected function afterSave(): void
    {
        if ($this->record->badgeIssued() && $this->record->wasChanged(['full_name', 'organization', 'position', 'type', 'days'])) {
            app(BadgeService::class)->generate($this->record);
        }
    }
}
