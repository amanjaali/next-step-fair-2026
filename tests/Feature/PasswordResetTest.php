<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * A student who forgot their password: phone, a WhatsApp code, a new one.
 *
 * The same privacy invariant as ordinary sign-in applies throughout — an
 * unknown phone, and a phone that belongs to someone with no password to
 * reset, must be indistinguishable from a real match at every step.
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function student(array $attributes = []): Registration
    {
        return Registration::create(array_merge([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'Forgetful Student',
            'phone' => '7701234567',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'days' => [1, 2, 3],
            'email' => 'forgetful@example.com',
            'password' => 'the-old-password',
            'education_stage' => 'grade12',
            'verified_at' => now(),
            'confirmed_at' => now(),
        ], $attributes));
    }

    /** Reads the code the way the real flow issues it, straight off the row. */
    private function codeFor(Registration $registration): string
    {
        $verification = $registration->otpVerifications()->latest('id')->first();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $verification->forceFill(['code_hash' => Hash::make($code)])->save();

        return $code;
    }

    private function requestCode(string $phone = '7701234567'): void
    {
        $this->post('/en/signin/forgot', ['phone_country' => '+964', 'phone' => $phone])
            ->assertRedirect(route('attendee.password.code', ['locale' => 'en']));
    }

    public function test_a_student_can_reset_a_forgotten_password(): void
    {
        $student = $this->student();
        $this->requestCode();
        $code = $this->codeFor($student);

        $this->post('/en/signin/forgot/code', ['code' => $code])
            ->assertRedirect(route('attendee.password.reset', ['locale' => 'en']));

        $this->post('/en/signin/forgot/reset', [
            'password' => 'a-brand-new-password',
            'password_confirmation' => 'a-brand-new-password',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($student, 'attendee');
        $this->assertTrue(Hash::check('a-brand-new-password', $student->refresh()->password));
    }

    /** The core fix: the code has to actually go out, not just sit in a log line. */
    public function test_the_code_is_actually_sent_over_whatsapp(): void
    {
        $student = $this->student();
        $this->requestCode();

        $this->assertDatabaseHas('messages', [
            'registration_id' => $student->id,
            'channel' => 'whatsapp',
            'template_key' => 'otp',
        ]);
    }

    public function test_through_otpiq_the_code_goes_out_as_a_verification_send_and_verifies(): void
    {
        config([
            'whatsapp.driver' => 'otpiq',
            'whatsapp.otpiq.api_key' => 'sk_test_key',
        ]);
        Http::fake(['*' => Http::response(['smsId' => 'otpiq-otp'], 200)]);

        $student = $this->student();
        $this->requestCode();

        $sent = null;
        Http::assertSent(function (ClientRequest $request) use (&$sent) {
            $sent = json_decode($request->body(), true);

            return $request->url() === 'https://api.otpiq.com/api/sms'
                && $sent['smsType'] === 'verification'
                && $sent['phoneNumber'] === '9647701234567'
                && preg_match('/^\d{6}$/', $sent['verificationCode']) === 1;
        });

        $this->assertDatabaseHas('messages', [
            'registration_id' => $student->id,
            'template_key' => 'otp',
            'status' => Message::STATUS_SENT,
            'provider_message_id' => 'otpiq-otp',
        ]);

        $this->post('/en/signin/forgot/code', ['code' => $sent['verificationCode']])
            ->assertRedirect(route('attendee.password.reset', ['locale' => 'en']));
    }

    public function test_an_unknown_phone_gets_the_same_response_as_a_known_one(): void
    {
        $this->student();

        $this->requestCode('7709998877');

        $this->assertDatabaseMissing('messages', ['template_key' => 'otp']);
    }

    /** A parent/visitor row matches the phone but has no password to reset. */
    public function test_an_account_with_no_password_is_treated_as_no_match(): void
    {
        Registration::create([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_PARENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'Just A Parent',
            'phone' => '7705554433',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'days' => [1],
        ]);

        $this->requestCode('7705554433');

        $this->assertDatabaseMissing('messages', ['template_key' => 'otp']);
        $this->assertDatabaseCount('otp_verifications', 0);
    }

    public function test_a_wrong_code_is_rejected(): void
    {
        $student = $this->student();
        $this->requestCode();
        $this->codeFor($student);

        $this->from('/en/signin/forgot/code')
            ->post('/en/signin/forgot/code', ['code' => '000000'])
            ->assertRedirect('/en/signin/forgot/code')
            ->assertSessionHasErrors('code');

        $this->assertGuest('attendee');
        $this->assertTrue(Hash::check('the-old-password', $student->refresh()->password));
    }

    public function test_the_reset_screen_is_unreachable_without_a_verified_code(): void
    {
        $this->get('/en/signin/forgot/reset')
            ->assertRedirect(route('attendee.password.forgot', ['locale' => 'en']));
    }

    public function test_a_short_password_is_rejected(): void
    {
        $student = $this->student();
        $this->requestCode();
        $code = $this->codeFor($student);
        $this->post('/en/signin/forgot/code', ['code' => $code]);

        $this->from('/en/signin/forgot/reset')
            ->post('/en/signin/forgot/reset', [
                'password' => 'short',
                'password_confirmation' => 'short',
            ])->assertSessionHasErrors('password');

        $this->assertGuest('attendee');
        $this->assertTrue(Hash::check('the-old-password', $student->refresh()->password));
    }

    public function test_a_mismatched_confirmation_is_rejected(): void
    {
        $student = $this->student();
        $this->requestCode();
        $code = $this->codeFor($student);
        $this->post('/en/signin/forgot/code', ['code' => $code]);

        $this->from('/en/signin/forgot/reset')
            ->post('/en/signin/forgot/reset', [
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'does-not-match',
            ])->assertSessionHasErrors('password');

        $this->assertGuest('attendee');
    }

    public function test_the_link_is_reachable_from_the_signin_page(): void
    {
        $this->get('/en/signin')
            ->assertOk()
            ->assertSee(route('attendee.password.forgot', ['locale' => 'en']), false);
    }
}
