<?php

namespace App\Filament\Resources\Opportunities\Pages;

use App\Filament\Resources\Opportunities\OpportunityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOpportunity extends CreateRecord
{
    protected static string $resource = OpportunityResource::class;

    protected ?array $universityRequirements = null;

    /**
     * The opportunity has no slug yet at this point — that is only assigned
     * on save — so the "whole university" text cannot be written to its
     * ScholarshipUniversityRequirement row until afterCreate(). Stashed here
     * in between.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->universityRequirements = $data['university_requirements'] ?? null;
        unset($data['university_requirements']);

        return $data;
    }

    protected function afterCreate(): void
    {
        OpportunityResource::syncUniversityRequirements($this->record, $this->universityRequirements);
    }
}
