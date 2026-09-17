<?php

namespace App\Filament\Resources\ScholarshipUniversities\Pages;

use App\Filament\Resources\ScholarshipUniversities\ScholarshipUniversityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScholarshipUniversity extends EditRecord
{
    protected static string $resource = ScholarshipUniversityResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
