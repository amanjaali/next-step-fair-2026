<?php

namespace App\Http\Controllers;

use App\Models\OfferPopupItem;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Where an offer in the popup leads.
 *
 * Going out through us rather than straight to the link is what tells the team
 * whether a day's popup was worth the interruption — which is the only honest
 * way to decide whether to keep running one.
 */
class OfferPopupController extends Controller
{
    public function go(OfferPopupItem $item): RedirectResponse
    {
        if (blank($item->action_url)) {
            throw new NotFoundHttpException;
        }

        $item->incrementQuietly('click_count');
        $item->popup?->incrementQuietly('view_count');

        return str_starts_with($item->action_url, 'http')
            ? redirect()->away($item->action_url)
            : redirect($item->action_url);
    }
}
