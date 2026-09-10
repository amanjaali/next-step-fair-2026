<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConferenceRsvpRequest;
use App\Models\Registration;
use App\Services\BadgeService;
use App\Services\Messaging\MessageDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Conference RSVP: one short form, then the badge on WhatsApp.
 *
 * Four audiences — a ministry, a public body, a company, or a person coming on
 * their own account. The form is the same for all of them, and only an individual
 * is spared the question about which organisation they represent.
 *
 * Institutional addresses are confirmed straight away; free-mail addresses stay
 * pending for the protocol team, but the badge and WhatsApp still go to the
 * phone on the form at submit time.
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

        $this->badges->generate($registration);

        $this->sendConfirmation($registration);

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

    private function sendConfirmation(Registration $registration): void
    {
        $this->dispatcher->whatsapp(
            $registration,
            'rsvp_confirmed',
            [
                'name' => $registration->firstName(),
                'ticket' => $registration->ticket_ref,
            ],
            withBadge: true,
        );
    }
}
