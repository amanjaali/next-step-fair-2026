<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Model;

/**
 * What a student must read and acknowledge before choosing a given university
 * on the National Scholarship application form.
 *
 * Keyed by the university's `slug` rather than a foreign key, because that
 * slug is the one thing every university has regardless of which source it
 * came from — the eight hand-curated founding/donor entries in
 * config('scholarship.universities'), or a partner added as an Opportunity.
 */
class ScholarshipUniversityRequirement extends Model
{
    use HasTranslatableContent;

    public array $translatable = ['requirements'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'requires_external_form' => 'boolean',
        ];
    }
}
