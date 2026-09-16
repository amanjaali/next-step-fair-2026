<?php

namespace App\Filament\Resources\ScholarshipUniversityRequirements\Pages;

use App\Filament\Resources\ScholarshipUniversityRequirements\ScholarshipUniversityRequirementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScholarshipUniversityRequirements extends ListRecords
{
    protected static string $resource = ScholarshipUniversityRequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
