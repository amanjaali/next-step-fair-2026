<?php

namespace App\Services;

use App\Models\Registration;
use App\Services\Messaging\MessageDispatcher;

/**
 * Turning a filled-in form into a badge in somebody's hand.
 *
 * Fair and visitor registrations are confirmed and badged here, then queued for
 * WhatsApp. Conference RSVP confirms in ConferenceRsvpController instead.
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
        $this->queueWhatsAppConfirmation($registration);
    }

    private function queueWhatsAppConfirmation(Registration $registration): void
    {
        if (! $registration->isFair()) {
            return;
        }

        $templateKey = match ($registration->type) {
            Registration::TYPE_STUDENT => 'registration_confirmed_student',
            Registration::TYPE_PARENT => 'registration_confirmed_parent',
            default => null,
        };

        if (! $templateKey) {
            return;
        }

        $this->dispatcher->whatsapp(
            $registration,
            $templateKey,
            [
                'name' => $registration->firstName(),
                'days' => $registration->daysLabel(),
                'ticket' => $registration->ticket_ref,
            ],
            withBadge: true,
        );
    }
}
