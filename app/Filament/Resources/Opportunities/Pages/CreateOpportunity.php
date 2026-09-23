<?php

namespace App\Filament\Resources\Opportunities\Pages;

use App\Filament\Resources\Opportunities\OpportunityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOpportunity extends CreateRecord
{
    protected static string $resource = OpportunityResource::class;

    protected ?array $universityRequirements = null;

    protected bool $requiresExternalForm = false;

    protected ?string $externalFormUrl = null;

    /**
     * The opportunity has no slug yet at this point — that is only assigned
     * on save — so the "whole university" text cannot be written to its
     * ScholarshipUniversityRequirement row until afterCreate(). Stashed here
     * in between.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->universityRequirements = $data['university_requirements'] ?? null;
        $this->requiresExternalForm = (bool) ($data['requires_external_form'] ?? false);
        $this->externalFormUrl = $data['external_form_url'] ?? null;
        unset($data['university_requirements'], $data['requires_external_form'], $data['external_form_url']);

        return $data;
    }

    protected function afterCreate(): void
    {
        OpportunityResource::syncUniversityRequirements(
            $this->record,
            $this->universityRequirements,
            $this->requiresExternalForm,
            $this->externalFormUrl,
        );
    }
}
