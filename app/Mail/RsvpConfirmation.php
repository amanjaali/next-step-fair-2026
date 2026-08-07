<?php

namespace App\Mail;

use App\Models\Message;
use App\Models\Registration;
use App\Services\BadgeService;
use App\Services\CalendarService;
use App\Services\QrCodeService;
use App\Services\TicketService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * The conference RSVP confirmation.
 *
 * Table-based HTML for Outlook, a plain-text alternative, RTL support for
 * Kurdish and Arabic, the badge PDF attached, the QR inline, and a calendar
 * invitation. When the RSVP is pending, the badge is withheld until approval.
 */
class RsvpConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Registration $registration,
        public bool $pending = false,
        public ?int $messageId = null,
    ) {}

    public function envelope(): Envelope
    {
        $key = $this->pending ? 'rsvp_subject_pending' : 'rsvp_subject';

        return new Envelope(
            subject: __("notifications.email.$key", [], $this->registration->locale),
            metadata: ['ticket' => $this->registration->ticket_ref],
        );
    }

    public function content(): Content
    {
        $tickets = app(TicketService::class);
        $qr = app(QrCodeService::class);

        return new Content(
            view: 'emails.rsvp-confirmation',
            text: 'emails.rsvp-confirmation-text',
            with: [
                'registration' => $this->registration,
                'locale' => $this->registration->locale,
                'dir' => config("nextstep.locales.{$this->registration->locale}.dir", 'ltr'),
                'pending' => $this->pending,
                'qrDataUri' => $qr->pngDataUri($tickets->verifyUrl($this->registration), 420),
                'manageUrl' => URL::signedRoute('rsvp.manage', [
                    'ticket' => $this->registration->ticket_id,
                ]),
                'sessions' => $this->registration->savedSessions()->orderBy('starts_at')->get(),
            ],
        );
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        if ($this->pending) {
            // No badge until the protocol team approves the RSVP.
            return [];
        }

        $badges = app(BadgeService::class);
        $calendar = app(CalendarService::class);

        return [
            Attachment::fromData(
                fn () => $badges->pdf($this->registration),
                'next-step-conference-2026-badge.pdf'
            )->withMime('application/pdf'),

            Attachment::fromData(
                fn () => $calendar->forRegistration($this->registration),
                'next-step-conference-2026.ics'
            )->withMime('text/calendar'),
        ];
    }

    public function build(): self
    {
        // Mark the delivery-log row as sent once the transport has accepted it.
        return $this->withSymfonyMessage(function () {
            if ($this->messageId) {
                Message::where('id', $this->messageId)->update([
                    'status' => Message::STATUS_SENT,
                    'sent_at' => now(),
                ]);
            }
        });
    }
}
