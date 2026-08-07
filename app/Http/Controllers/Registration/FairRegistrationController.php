<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFairRegistrationRequest;
use App\Models\Registration;
use App\Services\BadgeService;
use App\Services\Messaging\MessageDispatcher;
use App\Services\Messaging\OtpService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Expo registration: one short form for students, a shorter one for parents.
 *
 * The flow is deliberately: submit → verify the phone by code → issue the ticket.
 * A badge is never created for a number that has not answered.
 *
 * A student is also creating their Next Step ID, and the badge and the account are
 * issued by the same code — one form, one verification, and from then on the expo,
 * the panels, the seminars, the workshops, Zankoline and the scholarship all know
 * who they are.
 */
class FairRegistrationController extends Controller
{
    public function __construct(
        private readonly OtpService $otp,
        private readonly BadgeService $badges,
        private readonly MessageDispatcher $dispatcher,
        private readonly TicketService $tickets,
    ) {}

    public function hub(): RedirectResponse
    {
        return redirect()->route('register.fair');
    }

    public function create(Request $request): View
    {
        $type = $request->string('type')->toString() === Registration::TYPE_PARENT
            ? Registration::TYPE_PARENT
            : Registration::TYPE_STUDENT;

        return view('register.fair', [
            'navKey' => 'home',
            'title' => __('register.title').' — '.config('nextstep.event.name'),
            'type' => $type,
            // Set when a visitor pass is signed in: the form then continues that
            // registration instead of starting a blank one.
            'upgrade' => $this->upgradable($request),
        ]);
    }

    public function store(StoreFairRegistrationRequest $request): RedirectResponse
    {
        // Someone finishing their visitor pass is completing a registration, not
        // making a new one.
        if ($pass = $request->upgrading()) {
            return $this->completeQuickPass($request, $pass);
        }

        // One phone number, one registration, one badge.
        if ($existing = $request->existingRegistration()) {
            return redirect()->route('register.fair', ['type' => $request->input('type')])
                ->with('duplicate', $this->rememberDuplicate($request, $existing));
        }

        // And one account per address, so a second sign-up cannot orphan the first.
        if ($request->existingEmail()) {
            return back()->withInput()->withErrors(['email' => __('register.errors.email_taken')]);
        }

        $registration = Registration::create($this->answers($request) + [
            'track' => Registration::TRACK_FAIR,
            'status' => Registration::STATUS_AWAITING_OTP,
            'phone' => $request->normalisedPhone(),
            'phone_country' => $request->input('phone_country', '+964'),
            // Every registration covers the whole run. Choosing days was a question
            // nobody needed to answer to walk through the door.
            'days' => array_keys(config('nextstep.event.days')),
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
            'referrer' => $request->headers->get('referer'),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        $this->otp->send($registration);

        return redirect()->route('register.fair.verify', $registration->ticket_id);
    }

    /**
     * Finish a visitor pass rather than starting again.
     *
     * The pass already carries a ticket, a QR and a phone number that answered an
     * OTP — which is precisely what signed its holder in. So the answers are written
     * onto that record, the badge is reissued under the new name, and nobody is
     * asked to prove the same number twice.
     */
    private function completeQuickPass(StoreFairRegistrationRequest $request, Registration $pass): RedirectResponse
    {
        // The phone is taken from the pass, never from the form. The field is shown
        // locked, and a tampered value must not be able to move a live badge onto
        // somebody else's number.
        $pass->fill($this->answers($request))->save();

        if ($pass->status !== Registration::STATUS_CONFIRMED) {
            $this->otp->send($pass);

            return redirect()->route('register.fair.verify', $pass->ticket_id);
        }

        $this->confirm($pass);

        return redirect()->route('register.fair.done', $pass->ticket_id)
            ->with('status', __('register.upgrade.done'));
    }

    /**
     * What the form asks for — the same whether the record is being created or a
     * visitor pass is being completed.
     *
     * A parent submits no email, no password and no school, so those keys are only
     * present for a student and a parent record simply has nothing in them.
     */
    private function answers(StoreFairRegistrationRequest $request): array
    {
        $answers = [
            'type' => $request->input('type'),
            'locale' => $request->input('locale'),
            'full_name' => $request->input('full_name'),
            'city' => $request->input('city'),
            'consents' => $this->consents($request),
            'consented_at' => now(),
            'consent_ip' => $request->ip(),
        ];

        if (! $request->isStudent()) {
            return $answers;
        }

        return $answers + array_filter([
            'email' => $request->input('email'),
            'date_of_birth' => $request->input('date_of_birth'),
            'education_stage' => $request->input('education_stage'),
            'school_name' => $request->input('school_name'),
            // Left out when a returning student is completing a pass without
            // choosing a new one, so the existing hash survives.
            'password' => $request->input('password'),
        ]);
    }

    /** The signed-in visitor pass this form would complete, if there is one. */
    private function upgradable(Request $request): ?Registration
    {
        $attendee = $request->user('attendee');

        return $attendee instanceof Registration && $attendee->isQuickPass() ? $attendee : null;
    }

    public function showVerify(string $registration): View
    {
        $record = $this->findByTicket($registration);

        // Already verified: skip straight to the badge.
        if ($record->status === Registration::STATUS_CONFIRMED) {
            return $this->done($registration);
        }

        return view('register.verify', [
            'navKey' => 'home',
            'title' => __('register.step4.heading').' — '.config('nextstep.event.name'),
            'registration' => $record,
            'cooldown' => $this->otp->secondsUntilResend($record),
            // Null unless the log driver is active and debug is on — see OtpService.
            'testingCode' => $this->otp->testingCode($record),
        ]);
    }

    public function verify(Request $request, string $registration): RedirectResponse
    {
        $record = $this->findByTicket($registration);

        $request->validate(['code' => ['required', 'digits:'.config('whatsapp.otp.length')]], [
            'code.required' => __('register.errors.otp'),
            'code.digits' => __('register.errors.otp'),
        ]);

        $result = $this->otp->verify($record, (string) $request->input('code'));

        if ($result !== 'verified') {
            return back()->withErrors([
                'code' => $result === 'expired' ? __('register.step4.expired') : __('register.step4.wrong_code'),
            ]);
        }

        $this->confirm($record);

        /*
         * Verifying the code proves the phone number, which is exactly what signing
         * in proves — so the new registrant is signed in here rather than being
         * asked for the same number again a moment later. Anyone who arrived by
         * pressing "add to my agenda" is returned to that session, now saved.
         */
        Auth::guard('attendee')->login($record, remember: true);
        $request->session()->regenerate();
        $record->forceFill(['last_signed_in_at' => now()])->save();

        if ($sessionId = $request->session()->pull('attendee.pending_session')) {
            $record->savedSessions()->syncWithoutDetaching([$sessionId]);
        }

        return redirect()->route('register.fair.done', $record->ticket_id);
    }

    /**
     * Note that a number is already registered, without saying whose badge it is.
     *
     * The ticket goes into the session, never into the page. Printing it in a form
     * action would mean that typing a stranger's phone number into the form hands
     * back a link to their badge.
     *
     * @return true so the caller can flash it as the "show the panel" flag
     */
    private function rememberDuplicate(Request $request, Registration $existing): bool
    {
        $request->session()->put('duplicate_ticket', $existing->ticket_id);

        return true;
    }

    /**
     * Send the badge that already exists on that number, to that number.
     *
     * Nothing is echoed back to whoever pressed the button: the message goes to the
     * WhatsApp number on the record, which is the only place it is any use.
     */
    public function resendDuplicate(Request $request): RedirectResponse
    {
        $ticket = $request->session()->get('duplicate_ticket');

        $record = $ticket
            ? Registration::fair()->active()->where('ticket_id', $ticket)->first()
            : null;

        if (! $record) {
            return back();
        }

        if ($record->status === Registration::STATUS_CONFIRMED) {
            $this->dispatcher->whatsapp(
                $record,
                'registration_confirmed_'.$record->type,
                [
                    'name' => $record->firstName(),
                    'days' => $record->daysLabel(),
                    'ticket' => $record->ticket_ref,
                ],
                withBadge: true,
            );
        } else {
            // Never verified, so there is no badge to send yet — the code that
            // issues one is the useful thing to resend.
            $this->otp->send($record);
        }

        return back()->with('status', __('register.duplicate.resent'))->with('duplicate', true);
    }

    public function resend(string $registration): RedirectResponse
    {
        $record = $this->findByTicket($registration);

        if (! $this->otp->canResend($record)) {
            return back()->withErrors(['code' => __('register.step4.resend_in', [
                'seconds' => $this->otp->secondsUntilResend($record),
            ])]);
        }

        $this->otp->send($record);

        return back()->with('status', __('register.step4.sent'));
    }

    public function done(string $registration): View
    {
        $record = $this->findByTicket($registration);

        abort_unless($record->badgeIssued(), 404);

        return view('register.done', [
            'navKey' => 'home',
            'title' => __('register.done.kicker').' — '.config('nextstep.event.name'),
            'registration' => $record,
            'qrUrl' => route('ticket.qr', $record->ticket_id),
            'conversion' => [
                'track' => 'fair',
                'type' => $record->type,
                'value' => 0,
                'currency' => 'IQD',
            ],
        ]);
    }

    /** Issues the ticket: badge artwork, then the WhatsApp confirmation. */
    private function confirm(Registration $registration): void
    {
        $registration->forceFill([
            'status' => Registration::STATUS_CONFIRMED,
            // Kept when they already exist: completing a visitor pass reissues the
            // badge, it does not re-prove the phone number.
            'verified_at' => $registration->verified_at ?? now(),
            'confirmed_at' => $registration->confirmed_at ?? now(),
        ])->save();

        $this->badges->generate($registration);

        $this->dispatcher->whatsapp(
            $registration,
            'registration_confirmed_'.$registration->type,
            [
                'name' => $registration->firstName(),
                'days' => $registration->daysLabel(),
                'ticket' => $registration->ticket_ref,
            ],
            withBadge: true,
        );
    }

    private function consents(Request $request): array
    {
        $stamp = now()->toIso8601String();

        return [
            'terms' => ['given' => true, 'at' => $stamp, 'ip' => $request->ip()],
            'whatsapp' => ['given' => true, 'at' => $stamp, 'ip' => $request->ip()],
            'photography' => [
                'given' => (bool) $request->boolean('consent_photography'),
                'at' => $stamp,
                'ip' => $request->ip(),
            ],
        ];
    }

    private function findByTicket(string $ticket): Registration
    {
        return Registration::fair()->where('ticket_id', $ticket)->firstOrFail();
    }
}
