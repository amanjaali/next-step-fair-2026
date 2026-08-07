<?php

namespace App\Filament\Resources\SdgGoals\Pages;

use App\Filament\Resources\SdgGoals\SdgGoalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSdgGoals extends ListRecords
{
    protected static string $resource = SdgGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
