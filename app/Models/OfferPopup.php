<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * What a student is shown the first time they open the home page.
 *
 * One popup, several offers inside it. Interrupting somebody is a cost you can
 * only spend once, so everything worth telling them goes in the same box rather
 * than one box per offer.
 */
class OfferPopup extends Model
{
    use HasTranslatableContent;

    public array $translatable = ['title', 'intro', 'dismiss_label'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OfferPopupItem::class)->orderBy('sort');
    }

    /**
     * On today, and switched on.
     *
     * A popup with nothing in it is not shown at all — an empty box is worse
     * than no box, and it is easy to create one by turning a popup on before
     * the offers have been written.
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('active', true)
            ->whereHas('items')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', today()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', today()));
    }

    /** Whose home page it appears on. */
    public function scopeFor(Builder $query, ?Registration $registration): Builder
    {
        $audiences = [Opportunity::AUDIENCE_EVERYONE];

        if ($registration?->type === Registration::TYPE_STUDENT) {
            $audiences[] = Opportunity::AUDIENCE_STUDENTS;

            if (in_array($registration->education_stage, ['grade12', 'graduate'], true)) {
                $audiences[] = Opportunity::AUDIENCE_GRADE12;
            }
        }

        if ($registration?->type === Registration::TYPE_PARENT) {
            $audiences[] = Opportunity::AUDIENCE_PARENTS;
        }

        return $query->whereIn('audience', $audiences);
    }

    /**
     * What the browser remembers having dismissed.
     *
     * The timestamp is the point: edit the popup and the key changes, so it
     * comes back for everybody — which is the whole reason for editing it. Leave
     * it alone and it stays closed.
     */
    public function dismissKey(): string
    {
        return 'ns.popup.'.$this->getKey().'.'.$this->updated_at?->timestamp;
    }

    public function dismissLabel(): string
    {
        return $this->t('dismiss_label') ?: __('popup.dismiss');
    }
}
