<?php

namespace App\Services;

use App\Models\Registration;

/**
 * Signed, opaque ticket tokens.
 *
 * The QR encodes {app}/verify/{ticketId}?sig={hmac} and nothing else. No name,
 * no phone number, no ID: a leaked badge image resolves to a registrant only on
 * our server, and only if the signature checks out.
 */
class TicketService
{
    public function signature(string $ticketId): string
    {
        return substr(
            hash_hmac('sha256', $ticketId, (string) config('nextstep.qr.secret')),
            0,
            (int) config('nextstep.qr.signature_length')
        );
    }

    public function verifyUrl(Registration $registration): string
    {
        return route('ticket.verify', [
            'ticket' => $registration->ticket_id,
            'sig' => $this->signature($registration->ticket_id),
        ]);
    }

    /** Constant-time comparison, so a bad signature cannot be brute-forced by timing. */
    public function check(string $ticketId, ?string $signature): bool
    {
        if (! $signature) {
            return false;
        }

        return hash_equals($this->signature($ticketId), $signature);
    }

    /** Resolves a scanned token to a registration, or null if it was not ours. */
    public function resolve(string $ticketId, ?string $signature): ?Registration
    {
        if (! $this->check($ticketId, $signature)) {
            return null;
        }

        return Registration::with('checkIns')->where('ticket_id', $ticketId)->first();
    }
}
