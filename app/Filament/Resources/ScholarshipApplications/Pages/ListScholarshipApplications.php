<?php

namespace App\Filament\Resources\ScholarshipApplications\Pages;

use App\Filament\Resources\ScholarshipApplications\ScholarshipApplicationResource;
use Filament\Resources\Pages\ListRecords;

class ListScholarshipApplications extends ListRecords
{
    protected static string $resource = ScholarshipApplicationResource::class;

    public function getSubheading(): ?string
    {
        return __('admin.scholarship.subtitle', ['cycle' => config('scholarship.cycle')]);
    }
}
