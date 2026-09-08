<?php

namespace App\Http\Controllers\Attendee;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Rules\Captcha;
use App\Services\RegistrationConfirmer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * The quick pass: a name, a phone number, a QR.
 *
 * Plenty of people do not want an account. They want to walk in. This asks for the
 * two things the event genuinely needs — someone to put on the badge, and a number
 * that can receive it — and nothing else. No agenda, no profile, no follow-up
 * beyond the badge itself.
 *
 * It is a real registration underneath, so the gate scanner, the headcount and the
 * capacity figures all still work, and the same number can be upgraded to a full
 * registration later without losing the pass.
 *
 * The badge is issued on submission. What stands between this form and a script
 * is the code drawn on the picture, not a code sent to the number: the number is
 * where the badge is delivered, and somebody who mistypes it simply does not
 * receive it.
 */
class QuickPassController extends Controller
{
    public function __construct(private readonly RegistrationConfirmer $confirmer) {}

    public function create(): View
    {
        return view('register.quick', [
            'navKey' => null,
            'title' => __('register.quick.title').' — '.config('nextstep.event.name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'min:3', 'max:120'],
            'phone_country' => ['required', 'string', 'max:8'],
            'phone' => ['required', 'string', 'regex:/^0?[0-9]{9,12}$/'],
            'consent_terms' => ['accepted'],
            'captcha' => ['required', 'string', new Captcha],
        ], [
            'full_name.required' => __('register.errors.name'),
            'phone.required' => __('register.errors.phone'),
            'phone.regex' => __('register.errors.phone'),
            'consent_terms.accepted' => __('register.errors.terms'),
            'captcha.required' => __('register.errors.captcha'),
        ]);

        $phone = ltrim(preg_replace('/\D/', '', $data['phone']), '0');

        /*
         * One number, one badge. Silently forwarding to the existing ticket used to
         * look like a second registration had just been made, so say plainly that
         * the number is already on the list and offer to send that badge again.
         */
        if ($existing = Registration::fair()->active()->wherePhone($phone)->first()) {
            // Which ticket it is stays in the session: entering somebody else's
            // number must not hand back a link to their badge.
            $request->session()->put('duplicate_ticket', $existing->ticket_id);

            return redirect()->route('register.quick')
                ->withInput($request->except('phone'))
                ->with('duplicate', true);
        }

        $registration = Registration::create([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_VISITOR,
            // Draft for the length of one statement: if issuing the badge fails,
            // the number is left free to try again rather than held by a pass
            // that was never sent.
            'status' => Registration::STATUS_DRAFT,
            'locale' => app()->getLocale(),
            'full_name' => $data['full_name'],
            'phone' => $phone,
            'phone_country' => $data['phone_country'],
            // A visitor pass is valid for the whole run; there is nothing to choose.
            'days' => array_keys(config('nextstep.event.days')),
            'consents' => ['terms' => true, 'whatsapp' => true],
            'consented_at' => now(),
            'consent_ip' => $request->ip(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        $this->confirmer->confirm($registration);

        /*
         * Signed in on the spot. The pass used to sign somebody in by proving the
         * number over WhatsApp; with the picture code in its place there is no
         * proof of the number, so this signs in the person who just filled the
         * form in on this device — and only them, since a number already on the
         * list is turned away above and never reaches this line.
         */
        Auth::guard('attendee')->login($registration, remember: true);
        $request->session()->regenerate();
        $registration->forceFill(['last_signed_in_at' => now()])->save();

        return redirect()->route('register.fair.done', $registration->ticket_id);
    }
}
