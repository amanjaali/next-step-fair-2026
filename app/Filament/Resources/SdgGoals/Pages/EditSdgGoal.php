<?php

namespace App\Filament\Resources\SdgGoals\Pages;

use App\Filament\Resources\SdgGoals\SdgGoalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSdgGoal extends EditRecord
{
    protected static string $resource = SdgGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
