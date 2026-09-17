<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\BoothScan;
use App\Models\Interaction;
use App\Models\Organization;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The QR on an exhibitor's desk. /v/{code} counts the scan and sends the
 * visitor to the homepage — with their identity attached if they happen to be
 * signed in as an attendee, anonymous otherwise. Unlike a campaign QR scan,
 * every tap is counted: there is no cooldown or "already counted" state.
 */
class BoothScanController extends Controller
{
    public function __invoke(Request $request, string $code): RedirectResponse
    {
        $organization = Organization::where('qr_code', $code)->first();

        if (! $organization) {
            return redirect()->route('home', ['locale' => $this->resolveLocale($request)]);
        }

        $registrationId = Auth::guard('attendee')->id();
        $day = Registration::currentEventDay();

        BoothScan::create([
            'organization_id' => $organization->id,
            'registration_id' => $registrationId,
            'day' => $day,
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
            'scanned_at' => now(),
        ]);

        $organization->increment('qr_scan_count');

        // A known visitor also feeds the institution's existing lead list —
        // deduped per day there, same as a staff-scanned badge would be.
        if ($registrationId) {
            Interaction::firstOrCreate([
                'registration_id' => $registrationId,
                'organization_id' => $organization->id,
                'type' => Interaction::TYPE_BOOTH_SCAN,
                'day' => $day,
            ], [
                'occurred_at' => now(),
            ]);
        }

        return redirect()
            ->route('home', ['locale' => $this->resolveLocale($request)])
            ->with('status', __('site.home.booth_checked_in', ['org' => $organization->t('name')]));
    }

    /** Same cookie-first resolution as the bare "/" redirect, without a second hop that would drop the flash. */
    private function resolveLocale(Request $request): string
    {
        $supported = array_keys(config('nextstep.locales'));
        $cookie = $request->cookie(SetLocale::COOKIE);

        return is_string($cookie) && in_array($cookie, $supported, true)
            ? $cookie
            : config('app.locale', 'en');
    }
}
