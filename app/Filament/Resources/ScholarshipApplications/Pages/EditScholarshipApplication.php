<?php

namespace App\Filament\Resources\ScholarshipApplications\Pages;

use App\Filament\Resources\ScholarshipApplications\ScholarshipApplicationResource;
use App\Models\ScholarshipApplication;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditScholarshipApplication extends EditRecord
{
    protected static string $resource = ScholarshipApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    /**
     * Saving a stage has to stamp when it happened.
     *
     * The student's tracker prints the date beside each stage it has reached, so
     * a status changed without its timestamp shows as done with nothing against
     * it — which reads, to the person waiting, like a mistake.
     */
    protected function afterSave(): void
    {
        /** @var ScholarshipApplication $record */
        $record = $this->record;

        $record->advanceTo($record->status, $record->decision);
    }
}
