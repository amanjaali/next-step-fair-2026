<?php

namespace App\Filament\Resources\OfferPopups\Pages;

use App\Filament\Resources\OfferPopups\OfferPopupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOfferPopup extends EditRecord
{
    protected static string $resource = OfferPopupResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
