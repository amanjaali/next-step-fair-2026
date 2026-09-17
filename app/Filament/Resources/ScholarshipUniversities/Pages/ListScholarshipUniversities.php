<?php

namespace App\Filament\Resources\ScholarshipUniversities\Pages;

use App\Filament\Resources\ScholarshipUniversities\ScholarshipUniversityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScholarshipUniversities extends ListRecords
{
    protected static string $resource = ScholarshipUniversityResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
