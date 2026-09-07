<?php

namespace App\Filament\Resources\Partners\Pages;

use App\Filament\Resources\Partners\PartnerResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePartner extends CreateRecord
{
    protected static string $resource = PartnerResource::class;

    /**
     * A partner added here is a partner, whatever else is on the form.
     *
     * The slug is a URL and is filled from the name when it is left alone, so
     * nobody has to know what a slug is to add the students' association.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = trim((string) ($data['slug'] ?? '')) !== ''
            ? $data['slug']
            : PartnerResource::slugify($data['name']['en'] ?? null);

        return $data;
    }
}
