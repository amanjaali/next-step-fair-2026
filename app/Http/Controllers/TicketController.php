<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Services\BadgeService;
use App\Services\CalendarService;
use App\Services\QrCodeService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Public ticket endpoints: verification, badge downloads and RSVP self-service.
 *
 * /verify/{ticket}?sig= is what the QR encodes. Without a valid signature it
 * resolves to nothing, which is the whole point of the signed-token design.
 */
class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $tickets,
        private readonly BadgeService $badges,
        private readonly QrCodeService $qr,
        private readonly CalendarService $calendar,
    ) {}

    /** Human-readable verification page — what a member of the public sees. */
    public function verify(Request $request, string $ticket): View
    {
        $registration = $this->tickets->resolve($ticket, $request->string('sig')->toString());

        return view('ticket.verify', [
            'navKey' => null,
            'title' => __('checkin.title').' — '.config('nextstep.event.name'),
            'registration' => $registration,
            'valid' => $registration !== null && $registration->badgeIssued(),
        ]);
    }

    public function qr(string $ticket): Response
    {
        $registration = $this->findIssued($ticket);

        return response($this->qr->svg($this->tickets->verifyUrl($registration), 320))
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'private, max-age=3600');
    }

    public function png(string $ticket): Response
    {
        $registration = $this->findIssued($ticket);

        return response($this->badges->png($registration))
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="next-step-badge-'.$registration->ticket_ref.'.png"');
    }

    public function pdf(string $ticket): Response
    {
        $registration = $this->findIssued($ticket);

        return response($this->badges->pdf($registration))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="next-step-badge-'.$registration->ticket_ref.'.pdf"');
    }

    public function ics(string $ticket): Response
    {
        $registration = $this->findIssued($ticket);

        return response($this->calendar->forRegistration($registration))
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="next-step-fair-2026.ics"');
    }

    /** Signed link from the confirmation e-mail: no login required. */
    public function manage(string $ticket): View
    {
        $registration = Registration::where('ticket_id', $ticket)->firstOrFail();

        return view('ticket.manage', [
            'navKey' => null,
            'title' => __('rsvp.manage.title'),
            'track' => $registration->track,
            'registration' => $registration,
        ]);
    }

    public function cancel(string $ticket)
    {
        $registration = Registration::where('ticket_id', $ticket)->firstOrFail();

        $registration->forceFill([
            'status' => Registration::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => 'Cancelled by the registrant',
        ])->save();

        return back()->with('status', __('rsvp.manage.cancelled'));
    }

    private function findIssued(string $ticket): Registration
    {
        $registration = Registration::where('ticket_id', $ticket)->firstOrFail();

        abort_unless($registration->badgeIssued(), 404);

        return $registration;
    }
}
