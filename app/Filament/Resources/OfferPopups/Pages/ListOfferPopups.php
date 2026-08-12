<?php

namespace App\Filament\Resources\OfferPopups\Pages;

use App\Filament\Resources\OfferPopups\OfferPopupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOfferPopups extends ListRecords
{
    protected static string $resource = OfferPopupResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
