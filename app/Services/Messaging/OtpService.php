<?php

namespace App\Services\Messaging;

use App\Models\OtpVerification;
use App\Models\Registration;
use Illuminate\Support\Facades\Hash;

/**
 * Phone verification for the fair track.
 *
 * A ticket is only issued once the number has answered a code, which is what
 * guarantees the badge can actually reach the registrant before we promise it.
 */
class OtpService
{
    public function __construct(private readonly MessageDispatcher $dispatcher) {}

    /** Issues a fresh code and queues it to WhatsApp. */
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

        $this->dispatcher->whatsapp($registration, 'otp', ['code' => $code]);

        return $verification;
    }

    /**
     * The pending code, but only while no real message can be delivered.
     *
     * The code itself is hashed, so this reads it back out of the rendered message
     * in the delivery log. Both guards must hold: the gateway must be the `log`
     * driver, which sends nothing, and the app must be in debug mode. Setting
     * WHATSAPP_DRIVER=cloud_api or APP_DEBUG=false turns it off, so it cannot
     * follow the site to production.
     */
    public function testingCode(Registration $registration): ?string
    {
        if (config('whatsapp.driver') !== 'log' || ! config('app.debug')) {
            return null;
        }

        $message = $registration->messages()
            ->where('template_key', 'otp')
            ->latest('id')
            ->first();

        $length = (int) config('whatsapp.otp.length');

        return $message && preg_match('/\b(\d{'.$length.'})\b/', (string) $message->preview, $m)
            ? $m[1]
            : null;
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
}
