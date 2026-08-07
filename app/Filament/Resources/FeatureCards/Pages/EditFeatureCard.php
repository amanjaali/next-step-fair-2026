<?php

namespace App\Filament\Resources\FeatureCards\Pages;

use App\Filament\Resources\FeatureCards\FeatureCardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeatureCard extends EditRecord
{
    protected static string $resource = FeatureCardResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
