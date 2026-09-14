<?php

namespace App\Filament\Resources\HomeTrackPoints\Pages;

use App\Filament\Resources\HomeTrackPoints\HomeTrackPointResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHomeTrackPoints extends ListRecords
{
    protected static string $resource = HomeTrackPointResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
