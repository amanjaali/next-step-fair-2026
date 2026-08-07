<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConferenceRsvpRequest;
use App\Mail\RsvpConfirmation;
use App\Models\Registration;
use App\Services\BadgeService;
use App\Services\Messaging\MessageDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Conference RSVP: one short form, e-mail confirmation.
 *
 * Four audiences — a ministry, a public body, a company, or a person coming on
 * their own account. The form is the same for all of them, and only an individual
 * is spared the question about which organisation they represent.
 *
 * Institutional addresses are confirmed straight away; free-mail addresses go to
 * the protocol team, and the badge follows the approval rather than the RSVP.
 */
class ConferenceRsvpController extends Controller
{
    public function __construct(
        private readonly BadgeService $badges,
        private readonly MessageDispatcher $dispatcher,
    ) {}

    public function create(Request $request): View
    {
        $requested = $request->string('type')->toString();
        $type = in_array($requested, Registration::conferenceTypes(), true)
            ? $requested
            : Registration::TYPE_GOVERNMENT;

        return view('register.conference', [
            'navKey' => 'conference',
            'title' => __('rsvp.title').' — '.config('nextstep.event.name'),
            'track' => 'conference',
            'type' => $type,
        ]);
    }

    public function store(StoreConferenceRsvpRequest $request): RedirectResponse
    {
        if ($existing = $request->existingRegistration()) {
            return redirect()->route('register.conference', ['type' => $request->input('type')])
                ->with('duplicate', $existing->ticket_id);
        }

        $needsReview = $request->isFreeMail();

        $registration = Registration::create([
            'track' => Registration::TRACK_CONFERENCE,
            'type' => $request->input('type'),
            'status' => $needsReview ? Registration::STATUS_PENDING : Registration::STATUS_CONFIRMED,
            'locale' => $request->input('locale'),
            'full_name' => $request->input('full_name'),
            'position' => $request->input('position'),
            'organization' => $request->input('organization'),
            'email' => $request->input('email'),
            'phone' => $request->normalisedPhone(),
            'phone_country' => $request->input('phone_country', '+964'),
            'city' => $request->input('city'),
            'days' => [1],
            'consents' => [
                'terms' => ['given' => true, 'at' => now()->toIso8601String(), 'ip' => $request->ip()],
                'delegate_list' => ['given' => true, 'at' => now()->toIso8601String(), 'ip' => $request->ip()],
            ],
            'consented_at' => now(),
            'consent_ip' => $request->ip(),
            'confirmed_at' => $needsReview ? null : now(),
            'approved_at' => $needsReview ? null : now(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        if (! $needsReview) {
            $this->badges->generate($registration);
        }

        $this->sendConfirmation($registration, $needsReview);

        return redirect()->route('register.conference.done', $registration->ticket_id);
    }

    public function done(string $registration): View
    {
        $record = Registration::conference()->where('ticket_id', $registration)->firstOrFail();

        return view('register.conference-done', [
            'navKey' => 'conference',
            'title' => __('rsvp.done.kicker').' — '.config('nextstep.event.name'),
            'track' => 'conference',
            'registration' => $record,
            'pending' => $record->status === Registration::STATUS_PENDING,
            'qrUrl' => route('ticket.qr', $record->ticket_id),
            'conversion' => [
                'track' => 'conference',
                'type' => $record->type,
                'value' => 0,
                'currency' => 'IQD',
            ],
        ]);
    }

    private function sendConfirmation(Registration $registration, bool $pending): void
    {
        $subjectKey = $pending ? 'rsvp_subject_pending' : 'rsvp_subject';

        $message = $this->dispatcher->logEmail(
            $registration,
            $pending ? 'rsvp_pending' : 'rsvp_confirmed',
            __("notifications.email.$subjectKey", [], $registration->locale),
            __($pending ? 'notifications.email.rsvp_pending' : 'notifications.email.rsvp_confirmed', [], $registration->locale),
        );

        Mail::to($registration->email)
            ->queue(new RsvpConfirmation($registration, $pending, $message->id));
    }
}
