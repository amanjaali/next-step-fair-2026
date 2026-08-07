<?php

namespace App\Services\Matching;

use App\Mail\InstitutionSignInCode;
use App\Models\InstitutionOtp;
use App\Models\InstitutionUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Sign-in codes for university staff.
 *
 * The attendee equivalent goes to WhatsApp because a student has a phone. This one
 * goes to the institutional e-mail address, because that is the credential a
 * university controls and the thing that proves someone speaks for it.
 */
class InstitutionOtpService
{
    public function send(InstitutionUser $user): InstitutionOtp
    {
        $code = str_pad((string) random_int(0, 999999), (int) config('whatsapp.otp.length'), '0', STR_PAD_LEFT);

        $otp = InstitutionOtp::create([
            'institution_user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'sent_at' => now(),
            'expires_at' => now()->addMinutes((int) config('whatsapp.otp.ttl_minutes')),
        ]);

        Mail::to($user->email)->queue(new InstitutionSignInCode($user, $code));

        // While mail cannot leave the machine there is no inbox to read, so keep
        // the code in the cache for the test-mode panel. See testingCode().
        if ($this->inTestMode()) {
            cache()->put($this->previewKey($otp), $code, now()->addMinutes(15));
        }

        return $otp;
    }

    /** @return 'verified'|'invalid'|'expired'|'locked' */
    public function verify(InstitutionUser $user, string $code): string
    {
        $otp = $user->otps()->whereNull('verified_at')->latest('id')->first();

        if (! $otp) {
            return 'expired';
        }

        if ($otp->attempts >= (int) config('whatsapp.otp.max_attempts')) {
            return 'locked';
        }

        if ($otp->isExpired()) {
            return 'expired';
        }

        $otp->increment('attempts');

        if (! Hash::check($code, $otp->code_hash)) {
            return 'invalid';
        }

        $otp->forceFill(['verified_at' => now()])->save();

        return 'verified';
    }

    /**
     * The pending code, but only while mail cannot actually leave the machine.
     *
     * Same two guards as the attendee side: the `log` mailer delivers nothing, and
     * debug mode is off in production. Either flipping removes the panel, and the
     * cached copy is never written in the first place.
     */
    public function testingCode(InstitutionUser $user): ?string
    {
        if (! $this->inTestMode()) {
            return null;
        }

        $otp = $user->otps()->whereNull('verified_at')->latest('id')->first();

        return $otp ? cache()->get($this->previewKey($otp)) : null;
    }

    private function inTestMode(): bool
    {
        return config('mail.default') === 'log' && config('app.debug');
    }

    private function previewKey(InstitutionOtp $otp): string
    {
        return "institution-otp-preview:{$otp->id}";
    }
}
