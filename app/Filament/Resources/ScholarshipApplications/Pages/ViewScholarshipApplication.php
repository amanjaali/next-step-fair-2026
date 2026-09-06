<?php

namespace App\Filament\Resources\ScholarshipApplications\Pages;

use App\Filament\Resources\ScholarshipApplications\ScholarshipApplicationResource;
use App\Models\ScholarshipApplication;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewScholarshipApplication extends ViewRecord
{
    protected static string $resource = ScholarshipApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label(__('admin.scholarship.review')),

            // The same one-click advance as the list, for a reviewer who has just
            // finished reading and does not want to go back to the queue first.
            Action::make('advance')
                ->label(fn () => __('admin.scholarship.move_to', [
                    'stage' => ScholarshipApplicationResource::statusOptions()[$this->record->nextStage()] ?? '',
                ]))
                ->visible(fn () => $this->record->nextStage() !== null
                    && $this->record->nextStage() !== ScholarshipApplication::STATUS_DECIDED)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->advanceTo($this->record->nextStage());

                    Notification::make()->title(__('admin.notify.saved'))->success()->send();
                }),
        ];
    }
}
