<?php

namespace App\Services;

use App\Models\Registration;
use App\Services\Messaging\MessageDispatcher;

/**
 * Turning a filled-in form into a badge in somebody's hand.
 *
 * Three things have to happen together and in this order: the record becomes
 * confirmed, the QR badge is drawn, and it goes out on WhatsApp. Doing any two
 * of them is a bug somebody only finds at the door — a confirmed attendee with
 * no badge, or a badge nobody was sent.
 *
 * It lives here rather than in a controller because there are two ways in. The
 * long form issues a badge, and so does the visitor pass; since the WhatsApp
 * code was dropped in favour of the picture code, neither has a verification
 * step left to distinguish them.
 */
class RegistrationConfirmer
{
    public function __construct(
        private readonly BadgeService $badges,
        private readonly MessageDispatcher $dispatcher,
    ) {}

    public function confirm(Registration $registration): void
    {
        $registration->forceFill([
            'status' => Registration::STATUS_CONFIRMED,
            // Kept if it is already set: completing a visitor pass confirms a
            // record that was confirmed days ago, and that first moment is the
            // one the reports count.
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
}
