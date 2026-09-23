<?php

namespace App\Filament\Resources\Opportunities\Pages;

use App\Filament\Resources\Opportunities\OpportunityResource;
use App\Models\ScholarshipUniversityRequirement;
use Filament\Resources\Pages\EditRecord;

class EditOpportunity extends EditRecord
{
    protected static string $resource = OpportunityResource::class;

    protected ?array $universityRequirements = null;

    protected bool $requiresExternalForm = false;

    protected ?string $externalFormUrl = null;

    /** Pulls the "whole university" text (and its own form, if any) in to pre-fill the fields. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $requirement = ScholarshipUniversityRequirement::query()
            ->where('university_slug', 'opportunity-'.$this->record->slug)
            ->first();

        $data['university_requirements'] = $requirement?->getTranslations('requirements') ?? [];
        $data['requires_external_form'] = $requirement?->requires_external_form ?? false;
        $data['external_form_url'] = $requirement?->external_form_url;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->universityRequirements = $data['university_requirements'] ?? null;
        $this->requiresExternalForm = (bool) ($data['requires_external_form'] ?? false);
        $this->externalFormUrl = $data['external_form_url'] ?? null;
        unset($data['university_requirements'], $data['requires_external_form'], $data['external_form_url']);

        return $data;
    }

    protected function afterSave(): void
    {
        OpportunityResource::syncUniversityRequirements(
            $this->record,
            $this->universityRequirements,
            $this->requiresExternalForm,
            $this->externalFormUrl,
        );
    }
}
