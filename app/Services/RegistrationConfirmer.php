<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Registration;
use App\Services\Messaging\MessageDispatcher;
use App\Support\WhatsAppLog;

/**
 * Turning a filled-in form into a badge in somebody's hand.
 *
 * Fair student, parent and visitor registrations are confirmed, badged, and queued
 * for WhatsApp immediately. Conference RSVP (rsvp_confirmed) waits for backend
 * approval in the admin panel.
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

        WhatsAppLog::info('registration.confirmed', [
            'registration_id' => $registration->id,
            'ticket_id' => $registration->ticket_id,
            'track' => $registration->track,
            'type' => $registration->type,
            'locale' => $registration->locale,
            'phone' => $registration->maskedPhone(),
        ]);

        $this->sendFairConfirmationWhatsApp($registration);
    }

    /** Queues the fair student, parent or visitor confirmation with the badge attached. */
    public function sendFairConfirmationWhatsApp(Registration $registration): ?Message
    {
        if (! $registration->isFair()) {
            WhatsAppLog::info('whatsapp.skipped_not_fair', [
                'registration_id' => $registration->id,
                'track' => $registration->track,
            ]);

            return null;
        }

        $templateKey = $this->fairConfirmationTemplateKey($registration);

        if (! $templateKey) {
            WhatsAppLog::info('whatsapp.skipped_no_template', [
                'registration_id' => $registration->id,
                'type' => $registration->type,
            ]);

            return null;
        }

        return $this->dispatcher->whatsapp(
            $registration,
            $templateKey,
            [
                'name' => $registration->firstName(),
            ],
            withBadge: true,
        );
    }

    private function fairConfirmationTemplateKey(Registration $registration): ?string
    {
        return match ($registration->type) {
            Registration::TYPE_STUDENT => 'registration_confirmed_student',
            Registration::TYPE_PARENT => 'registration_confirmed_parent',
            Registration::TYPE_VISITOR => 'registration_confirmed_visitor',
            default => null,
        };
    }
}
