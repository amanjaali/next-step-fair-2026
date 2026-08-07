<?php

namespace App\Filament\Resources\QrCampaigns\Pages;

use App\Filament\Resources\QrCampaigns\QrCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQrCampaigns extends ListRecords
{
    protected static string $resource = QrCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
