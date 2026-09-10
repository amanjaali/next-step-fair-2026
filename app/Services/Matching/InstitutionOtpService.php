<?php

namespace App\Services\Matching;

use App\Models\InstitutionOtp;
use App\Models\InstitutionUser;
use Illuminate\Support\Facades\Hash;

/**
 * Sign-in codes for university staff.
 *
 * Codes are shown in the test-mode panel when mail is not configured. Production
 * delivery is handled outside this app — no outbound e-mail is sent from here.
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

    /** The pending code, only while running in local test mode. */
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
        return config('app.debug');
    }

    private function previewKey(InstitutionOtp $otp): string
    {
        return "institution-otp-preview:{$otp->id}";
    }
}
