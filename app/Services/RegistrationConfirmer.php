<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Registration;
use App\Services\Messaging\MessageDispatcher;
use App\Support\WhatsAppLog;
use Throwable;

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

        $this->generateBadge($registration);

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
    public function sendFairConfirmationWhatsApp(Registration $registration, bool $immediate = false): ?Message
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
            immediate: $immediate,
        );
    }

    /** Re-sends the confirmation WhatsApp from the admin desk (student, parent, visitor, RSVP). */
    public function resendConfirmationWhatsApp(Registration $registration): ?Message
    {
        if (! $registration->badgeIssued() || ! $registration->msisdn()) {
            return null;
        }

        if ($registration->isConference()) {
            return $this->dispatcher->whatsapp(
                $registration,
                'rsvp_confirmed',
                ['name' => $registration->firstName()],
                withBadge: true,
                immediate: true,
            );
        }

        return $this->sendFairConfirmationWhatsApp($registration, immediate: true);
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

    /**
     * The slow part of confirming a registration is Chrome, not the database.
     * pdf()/png() launch a real (if headless) browser to shape and screenshot
     * the badge, which routinely costs 1-3+ seconds — dead time a registrant
     * would otherwise spend staring at a spinner on the confirmation page.
     *
     * Deferring the render to run after the HTTP response is flushed (the
     * same app()->terminating() trick MessageDispatcher already uses for the
     * WhatsApp send, for the same reason: no persistent queue worker) makes
     * the page feel instant without changing what gets generated. This runs
     * before sendFairConfirmationWhatsApp() is called so the badge PNG exists
     * on disk by the time that job's assertBadgeImageReachable() fetches it —
     * terminating() callbacks fire in registration order, not concurrently.
     */
    private function generateBadge(Registration $registration): void
    {
        if (app()->runningUnitTests() || app()->runningInConsole() || config('queue.default') === 'sync') {
            $this->badges->generate($registration);

            return;
        }

        app()->terminating(function () use ($registration) {
            try {
                $this->badges->generate($registration->fresh());
            } catch (Throwable $e) {
                report($e);
            }
        });
    }
}
