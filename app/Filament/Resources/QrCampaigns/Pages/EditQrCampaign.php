<?php

namespace App\Filament\Resources\QrCampaigns\Pages;

use App\Filament\Resources\QrCampaigns\QrCampaignResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQrCampaign extends EditRecord
{
    protected static string $resource = QrCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
