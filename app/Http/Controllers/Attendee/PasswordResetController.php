<?php

namespace App\Http\Controllers\Attendee;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Services\Messaging\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * A student who forgot their password: phone, a WhatsApp code, then a new one.
 *
 * Structurally the same three-step shape as SignInController's badge flow —
 * phone in, code out, same registration found either way — but ending in a
 * new password and a sign-in instead of a bare sign-in. Kept as its own
 * controller and its own session keys so this flow and ordinary sign-in
 * can never finish each other's session.
 *
 * Same privacy invariant as SignInController::send(): the response never
 * says whether a phone matched an account, and it is scoped to accounts
 * that actually have a password to reset — a parent or visitor row is
 * treated exactly like no match at all.
 */
class PasswordResetController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    public function show(): View
    {
        return view('attendee.password-forgot', [
            'navKey' => null,
            'title' => __('attendee.password.forgot_title').' — '.config('nextstep.event.name'),
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

        $candidates = Registration::query()->active()->wherePhone($data['phone'])->get()
            ->filter(fn (Registration $r) => $r->isStudentAccount());

        $registration = $candidates->first(fn (Registration $r) => $r->isConfirmed())
            ?? $candidates->sortByDesc('id')->first();

        if ($registration) {
            $this->otp->send($registration);
            $request->session()->put('attendee.reset_id', $registration->id);
        }

        // Same response either way — see the class comment.
        return redirect()->route('attendee.password.code')
            ->with('phone', $data['phone_country'].' '.ns_mask_phone($data['phone']));
    }

    public function showCode(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('attendee.reset_id') && ! session()->has('phone')) {
            return redirect()->route('attendee.password.forgot');
        }

        $registration = $this->pending($request);

        return view('attendee.password-code', [
            'navKey' => null,
            'title' => __('attendee.password.code_title').' — '.config('nextstep.event.name'),
            'phone' => session('phone'),
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

        $request->session()->forget('attendee.reset_id');
        $request->session()->put('attendee.reset_verified_id', $registration->id);

        return redirect()->route('attendee.password.reset');
    }

    public function showReset(Request $request): View|RedirectResponse
    {
        if (! $this->verified($request)) {
            return redirect()->route('attendee.password.forgot');
        }

        return view('attendee.password-reset', [
            'navKey' => null,
            'title' => __('attendee.password.new_title').' — '.config('nextstep.event.name'),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $registration = $this->verified($request);

        if (! $registration) {
            return redirect()->route('attendee.password.forgot');
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password.required' => __('attendee.password.errors.required'),
            'password.confirmed' => __('attendee.password.errors.confirmed'),
        ]);

        $registration->password = $data['password'];
        $registration->save();

        $request->session()->forget(['attendee.reset_id', 'attendee.reset_verified_id']);

        return SignInController::completeSignIn($request, $registration)
            ->with('status', __('attendee.password.changed'));
    }

    private function pending(Request $request): ?Registration
    {
        $id = $request->session()->get('attendee.reset_id');

        return $id ? Registration::find($id) : null;
    }

    private function verified(Request $request): ?Registration
    {
        $id = $request->session()->get('attendee.reset_verified_id');

        return $id ? Registration::find($id) : null;
    }
}
