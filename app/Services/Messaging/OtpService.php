<?php

namespace App\Services\Messaging;

use App\Models\OtpVerification;
use App\Models\Registration;
use Illuminate\Support\Facades\Hash;

/**
 * Phone verification for the fair track.
 *
 * OTP delivery is not sent over WhatsApp — conference RSVP is the only track
 * that uses OTPIQ. In local debug mode the code is shown on the verify screen.
 */
class OtpService
{
    /** Issues a fresh code. */
    public function send(Registration $registration): OtpVerification
    {
        $code = $this->generateCode();

        $verification = OtpVerification::create([
            'registration_id' => $registration->id,
            'phone_hash' => Registration::hashValue($registration->phone),
            'code_hash' => Hash::make($code),
            'channel' => 'whatsapp',
            'sent_at' => now(),
            'expires_at' => now()->addMinutes((int) config('whatsapp.otp.ttl_minutes')),
        ]);

        if ($this->inTestMode()) {
            cache()->put($this->previewKey($verification), $code, now()->addMinutes(15));
        }

        return $verification;
    }

    /** The pending code, only while running in local test mode. */
    public function testingCode(Registration $registration): ?string
    {
        if (! $this->inTestMode()) {
            return null;
        }

        $verification = $registration->otpVerifications()
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        return $verification ? cache()->get($this->previewKey($verification)) : null;
    }

    /** True if a new code may be requested, i.e. the cooldown has elapsed. */
    public function canResend(Registration $registration): bool
    {
        $last = $registration->otpVerifications()->latest('sent_at')->first();

        if (! $last?->sent_at) {
            return true;
        }

        return $last->sent_at->addSeconds((int) config('whatsapp.otp.resend_cooldown_seconds'))->isPast();
    }

    public function secondsUntilResend(Registration $registration): int
    {
        $last = $registration->otpVerifications()->latest('sent_at')->first();

        if (! $last?->sent_at) {
            return 0;
        }

        $ready = $last->sent_at->addSeconds((int) config('whatsapp.otp.resend_cooldown_seconds'));

        return max(0, (int) ceil(now()->diffInSeconds($ready, false)));
    }

    /**
     * Verifies a submitted code.
     *
     * @return 'verified'|'invalid'|'expired'|'locked'
     */
    public function verify(Registration $registration, string $code): string
    {
        $verification = $registration->otpVerifications()
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $verification) {
            return 'expired';
        }

        if ($verification->attempts >= (int) config('whatsapp.otp.max_attempts')) {
            return 'locked';
        }

        $verification->increment('attempts');

        if ($verification->isExpired()) {
            return 'expired';
        }

        if (! $verification->matches($code)) {
            return 'invalid';
        }

        $verification->forceFill(['verified_at' => now()])->save();

        return 'verified';
    }

    private function generateCode(): string
    {
        $length = (int) config('whatsapp.otp.length', 6);

        return str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    private function inTestMode(): bool
    {
        return (bool) config('app.debug');
    }

    private function previewKey(OtpVerification $verification): string
    {
        return "registration-otp-preview:{$verification->id}";
    }
}
