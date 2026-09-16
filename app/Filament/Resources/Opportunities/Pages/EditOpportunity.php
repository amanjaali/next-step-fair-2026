<?php

namespace App\Filament\Resources\Opportunities\Pages;

use App\Filament\Resources\Opportunities\OpportunityResource;
use App\Models\ScholarshipUniversityRequirement;
use Filament\Resources\Pages\EditRecord;

class EditOpportunity extends EditRecord
{
    protected static string $resource = OpportunityResource::class;

    protected ?array $universityRequirements = null;

    /** Pulls the "whole university" text in from its own table to pre-fill the field. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['university_requirements'] = ScholarshipUniversityRequirement::query()
            ->where('university_slug', 'opportunity-'.$this->record->slug)
            ->first()?->getTranslations('requirements') ?? [];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->universityRequirements = $data['university_requirements'] ?? null;
        unset($data['university_requirements']);

        return $data;
    }

    protected function afterSave(): void
    {
        OpportunityResource::syncUniversityRequirements($this->record, $this->universityRequirements);
    }
}
