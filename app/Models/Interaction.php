<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One touch between a student and an institution.
 *
 * A badge scanned at a desk is the strongest signal the fair produces: it is the
 * only one that cost both people time in the same place. Shortlists and profile
 * views are cheaper and are weighted accordingly, but they are kept separately
 * rather than summed, because "forty scans" and "forty page views" describe two
 * completely different exhibitors.
 */
class Interaction extends Model
{
    public const TYPE_BOOTH_SCAN = 'booth_scan';

    public const TYPE_SHORTLIST = 'shortlist';

    public const TYPE_ENQUIRY = 'enquiry';

    public const TYPE_PROFILE_VIEW = 'profile_view';

    public const TYPE_BROCHURE = 'brochure';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'day' => 'integer',
            'rating' => 'integer',
            'follow_up' => 'boolean',
            'occurred_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(InstitutionUser::class, 'institution_user_id');
    }

    /** What this type of touch is worth when scoring a lead. */
    public function weight(): int
    {
        return (int) config("taxonomy.interaction_weights.{$this->type}", 5);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
