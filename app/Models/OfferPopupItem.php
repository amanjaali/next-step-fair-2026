<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One offer inside the popup.
 *
 * Deliberately free text rather than a link to an Opportunity: the popup is
 * rewritten most days, and having to create a full opportunity record first
 * would make the quick thing slow.
 */
class OfferPopupItem extends Model
{
    use HasTranslatableContent;

    public array $translatable = ['title', 'body', 'action_label'];

    protected $guarded = ['id'];

    public function popup(): BelongsTo
    {
        return $this->belongsTo(OfferPopup::class, 'offer_popup_id');
    }

    public function actionLabel(): string
    {
        return $this->t('action_label') ?: __('popup.action');
    }
}
