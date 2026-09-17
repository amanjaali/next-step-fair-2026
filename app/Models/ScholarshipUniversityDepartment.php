<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A named department that holds seats at a scholarship university.
 *
 * Seats are not pooled: a Pharmacy seat cannot become a Law seat. The name is
 * stored as plain text so the application form can keep recording choices as
 * strings without foreign keys to historical pledges.
 */
class ScholarshipUniversityDepartment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'seats' => 'integer',
            'sort' => 'integer',
        ];
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(ScholarshipUniversity::class, 'scholarship_university_id');
    }
}
