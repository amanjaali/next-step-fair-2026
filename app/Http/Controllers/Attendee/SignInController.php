<?php

namespace App\Http\Controllers\Attendee;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Services\Messaging\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Two ways back in, for two different needs.
 *
 * A student signs in with the email and password from their Next Step ID. The
 * scholarship application runs for months and has to be reachable from a school
 * computer or a sibling's laptop, not only from the phone that once received a
 * code.
 *
 * Anyone else — a parent, a visitor, someone who has simply lost the WhatsApp
 * message — asks for their badge by phone number and gets a code. There is
 * nothing to remember and no account to have forgotten.
 *
 * Neither route ever confirms or denies that an address or a number is on file:
 * an unknown one gets the same screen as a known one, so this cannot be used to
 * discover who has registered.
 */
class SignInController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    /** Email and password: the student account. */
    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => __('attendee.signin.errors.email'),
            'email.email' => __('attendee.signin.errors.email'),
            'password.required' => __('attendee.signin.errors.password'),
        ]);

        $registration = Registration::query()
            ->active()
            ->whereEmail($data['email'])
            ->latest('id')
            ->first();

        // One message for a wrong address and a wrong password alike, so neither
        // can be used to find out which accounts exist.
        if (! $registration?->isStudentAccount() || ! Hash::check($data['password'], (string) $registration->password)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('attendee.signin.errors.no_match')]);
        }

        return self::completeSignIn($request, $registration);
    }

    public function show(): View|RedirectResponse
    {
        if (Auth::guard('attendee')->check()) {
            return redirect()->route('me');
        }

        return view('attendee.signin', [
            'navKey' => null,
            'title' => __('attendee.signin.title').' — '.config('nextstep.event.name'),
        ]);
    }

    /** Looks the number up and sends a code, without saying whether it matched. */
    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone_country' => ['required', 'string', 'max:8'],
            'phone' => ['required', 'string', 'regex:/^0?[0-9]{9,12}$/'],
        ], [
            'phone.required' => __('attendee.signin.errors.phone'),
            'phone.regex' => __('attendee.signin.errors.phone'),
        ]);

        $registration = Registration::query()
            ->active()
            ->wherePhone($data['phone'])
            ->latest('id')
            ->first();

        if ($registration) {
            $this->otp->send($registration);
            $request->session()->put('attendee.signin_id', $registration->id);
        }

        // Same response either way — see the class comment.
        return redirect()->route('attendee.signin.code')
            ->with('phone', $data['phone_country'].' '.ns_mask_phone($data['phone']));
    }

    public function showCode(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('attendee.signin_id') && ! session()->has('phone')) {
            return redirect()->route('attendee.signin');
        }

        $registration = $this->pending($request);

        return view('attendee.signin-code', [
            'navKey' => null,
            'title' => __('attendee.signin.code_title').' — '.config('nextstep.event.name'),
            'phone' => session('phone'),
            // Only present while nothing can actually be delivered.
            'testingCode' => $registration ? $this->otp->testingCode($registration) : null,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:'.config('whatsapp.otp.length')],
        ], [
            'code.required' => __('register.errors.otp'),
            'code.digits' => __('register.errors.otp'),
        ]);

        $registration = $this->pending($request);

        // No pending registration means the number was never registered. Fail with
        // the same wording a wrong code gets, so the two are indistinguishable.
        if (! $registration || $this->otp->verify($registration, (string) $request->input('code')) !== 'verified') {
            return back()->withErrors(['code' => __('register.step4.wrong_code')]);
        }

        $request->session()->forget('attendee.signin_id');

        return $this->completeSignIn($request, $registration);
    }

    public function signOut(Request $request): RedirectResponse
    {
        Auth::guard('attendee')->logout();
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', __('attendee.signin.signed_out'));
    }

    /**
     * Signs the attendee in and honours whatever they were trying to do.
     *
     * Shared with registration: verifying the code there signs the new registrant
     * in too, so someone who came to build an agenda lands back on it rather than
     * being asked to sign in again a second after proving the same phone number.
     */
    public static function completeSignIn(Request $request, Registration $registration): RedirectResponse
    {
        Auth::guard('attendee')->login($registration, remember: true);
        $request->session()->regenerate();
        $registration->forceFill(['last_signed_in_at' => now()])->save();

        if ($sessionId = $request->session()->pull('attendee.pending_session')) {
            $registration->savedSessions()->syncWithoutDetaching([$sessionId]);

            return redirect()->route('me.agenda')->with('status', __('attendee.agenda.added'));
        }

        return redirect()->intended(route('me'));
    }

    private function pending(Request $request): ?Registration
    {
        $id = $request->session()->get('attendee.signin_id');

        return $id ? Registration::find($id) : null;
    }
}
