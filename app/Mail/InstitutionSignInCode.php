<?php

namespace App\Mail;

use App\Models\InstitutionUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** The sign-in code for a university's exhibitor portal. */
class InstitutionSignInCode extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public InstitutionUser $user,
        public string $code,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('institution.mail.subject', ['code' => $this->code], $this->user->locale),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.institution-code',
            text: 'emails.institution-code-text',
            with: ['user' => $this->user, 'code' => $this->code],
        );
    }
}
