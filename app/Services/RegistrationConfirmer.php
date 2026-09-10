<?php

namespace App\Services;

use App\Models\Registration;

/**
 * Turning a filled-in form into a badge in somebody's hand.
 *
 * Fair and visitor registrations are confirmed and badged here. WhatsApp via
 * OTPIQ is reserved for the conference RSVP form — see ConferenceRsvpController.
 */
class RegistrationConfirmer
{
    public function __construct(private readonly BadgeService $badges) {}

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
    }
}
