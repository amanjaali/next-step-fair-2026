<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\RedirectResponse;

/**
 * The short address that goes out on WhatsApp: /b/{ticket}.
 *
 * It exists because a WhatsApp URL button is approved with a fixed prefix and
 * given only a tail at send time, so the tail has to be short, and because the
 * page it opens differs by track and by language. Sending the long address
 * would mean approving one template per track per language, and picking the
 * wrong one would land a delegate on a student's page.
 *
 * The ticket id is a UUID, so the address cannot be guessed by trying numbers,
 * and it is the same identifier the badge page itself already uses. What it
 * opens is that page: the QR, the download buttons, and the delivery status —
 * which is what somebody who has lost the message is actually looking for.
 */
class BadgeLinkController extends Controller
{
    public function __invoke(string $ticket): RedirectResponse
    {
        $registration = Registration::where('ticket_id', $ticket)->firstOrFail();

        // Their own language, not the language of whoever opens the link.
        $locale = array_key_exists((string) $registration->locale, config('nextstep.locales', []))
            ? $registration->locale
            : config('app.locale');

        $route = $registration->isConference() ? 'register.conference.done' : 'register.fair.done';

        return redirect()->route($route, ['locale' => $locale, 'registration' => $registration->ticket_id]);
    }
}
