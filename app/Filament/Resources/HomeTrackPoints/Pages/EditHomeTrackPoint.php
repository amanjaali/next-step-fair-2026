<?php

namespace App\Filament\Resources\HomeTrackPoints\Pages;

use App\Filament\Resources\HomeTrackPoints\HomeTrackPointResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHomeTrackPoint extends EditRecord
{
    protected static string $resource = HomeTrackPointResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
