<?php

namespace App\Filament\Resources\FeatureCards\Pages;

use App\Filament\Resources\FeatureCards\FeatureCardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeatureCards extends ListRecords
{
    protected static string $resource = FeatureCardResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
