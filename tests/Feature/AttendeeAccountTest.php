<?php

namespace Tests\Feature;

use App\Models\EventSession;
use App\Models\Registration;
use App\Services\Messaging\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The attendee's own area: passwordless sign-in, the personal agenda, and the
 * link between the two that means pressing "add to my agenda" as a guest is not
 * a wasted click.
 */
class AttendeeAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function registrant(array $attributes = []): Registration
    {
        return Registration::create(array_merge([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'Rezan Kamal',
            'phone' => '7719990001',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'days' => [1, 2, 3],
            'email' => 'rezan@example.com',
            'password' => 'a-good-password',
            'education_stage' => 'grade12',
            'verified_at' => now(),
            'confirmed_at' => now(),
            'badge_generated_at' => now(),
        ], $attributes));
    }

    /** Issues a code the way the real flow does, and returns it. */
    private function codeFor(Registration $registration): string
    {
        app(OtpService::class)->send($registration);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $registration->otpVerifications()->latest('id')->first()
            ->forceFill(['code_hash' => Hash::make($code)])->save();

        return $code;
    }

    public function test_a_student_signs_in_with_their_email_and_password(): void
    {
        $registration = $this->registrant();

        $this->post('/en/signin', ['email' => 'rezan@example.com', 'password' => 'a-good-password'])
            ->assertRedirect('/en/me');

        $this->assertAuthenticatedAs($registration, 'attendee');
    }

    /**
     * A wrong address and a wrong password fail the same way, so the form cannot
     * be used to find out which addresses have accounts.
     */
    public function test_a_wrong_password_and_an_unknown_address_fail_alike(): void
    {
        $this->registrant();

        $wrongPassword = $this->post('/en/signin', ['email' => 'rezan@example.com', 'password' => 'nope']);
        $unknown = $this->post('/en/signin', ['email' => 'nobody@example.com', 'password' => 'nope']);

        $wrongPassword->assertSessionHasErrors('email');
        $unknown->assertSessionHasErrors('email');
        $this->assertSame(
            session('errors')->first('email'),
            __('attendee.signin.errors.no_match')
        );
        $this->assertGuest('attendee');
    }

    /** A parent has no account, so no password can open one. */
    public function test_a_parent_cannot_sign_in_with_a_password(): void
    {
        $this->registrant([
            'type' => Registration::TYPE_PARENT,
            'phone' => '7719990099',
            'email' => 'parent@example.com',
            'password' => null,
        ]);

        $this->post('/en/signin', ['email' => 'parent@example.com', 'password' => 'a-good-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest('attendee');
    }

    public function test_a_registrant_gets_their_badge_back_with_a_code(): void
    {
        $registration = $this->registrant();

        $this->post('/en/signin/badge', ['phone_country' => '+964', 'phone' => '07719990001'])
            ->assertRedirect('/en/signin/code');

        $code = $this->codeFor($registration);

        $this->post('/en/signin/code', ['code' => $code])->assertRedirect('/en/me');
        $this->assertAuthenticatedAs($registration, 'attendee');

        $this->get('/en/me')->assertOk()->assertSee($registration->firstName());
    }

    /**
     * The sign-in screen must not become a way to find out who has registered,
     * so an unknown number gets exactly the same screens as a known one.
     */
    public function test_an_unknown_number_is_indistinguishable_from_a_known_one(): void
    {
        $this->post('/en/signin/badge', ['phone_country' => '+964', 'phone' => '07700000000'])
            ->assertRedirect('/en/signin/code')
            ->assertSessionHasNoErrors();

        $this->post('/en/signin/code', ['code' => '123456'])
            ->assertSessionHasErrors('code');

        $this->assertGuest('attendee');
    }

    public function test_a_wrong_code_does_not_sign_anyone_in(): void
    {
        $registration = $this->registrant();
        $this->post('/en/signin/badge', ['phone_country' => '+964', 'phone' => '07719990001']);
        $this->codeFor($registration);

        $this->post('/en/signin/code', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertGuest('attendee');
    }

    public function test_the_profile_needs_a_session(): void
    {
        $this->get('/en/me')->assertRedirect('/en/signin');
        $this->get('/en/me/agenda')->assertRedirect('/en/signin');
    }

    /**
     * The whole point of the connection: a guest presses add, registers, and the
     * session is waiting for them afterwards.
     */
    public function test_a_guests_choice_survives_registration(): void
    {
        $session = EventSession::where('bookable', true)->firstOrFail();

        $this->post("/en/agenda/save/{$session->id}")
            ->assertRedirect('/en/join');

        $this->get('/en/join')->assertOk()->assertSee($session->t('title'));

        // Register from there. One submission: no code screen in between.
        $this->post('/en/register/fair', $this->captcha() + [
            'type' => 'student', 'full_name' => 'Nma Salar', 'date_of_birth' => '2008-02-02',
            'phone_country' => '+964', 'phone' => '07719990009', 'city' => 'Sulaimani',
            'locale' => 'en', 'education_stage' => 'grade12',
            'email' => 'nma@example.com', 'password' => 'a-good-password', 'consent_terms' => '1',
        ])->assertRedirect();

        $registration = Registration::wherePhone('7719990009')->firstOrFail();

        $this->assertAuthenticatedAs($registration, 'attendee');
        $this->assertTrue(
            $registration->fresh()->savedSessions()->where('event_sessions.id', $session->id)->exists(),
            'The session the guest pressed before registering was not saved.'
        );
    }

    public function test_a_signed_in_attendee_adds_and_removes_sessions(): void
    {
        $registration = $this->registrant();
        $session = EventSession::where('bookable', true)->firstOrFail();

        $this->actingAs($registration, 'attendee');

        $this->post("/en/agenda/save/{$session->id}");
        $this->assertTrue($registration->savedSessions()->where('event_sessions.id', $session->id)->exists());

        $this->post("/en/agenda/save/{$session->id}");
        $this->assertFalse($registration->savedSessions()->where('event_sessions.id', $session->id)->exists());
    }

    public function test_signing_out_ends_the_session(): void
    {
        $this->actingAs($this->registrant(), 'attendee')
            ->post('/en/signout')->assertRedirect('/en');

        $this->assertGuest('attendee');
    }

    /* ------------------------------------------------------- quick pass -- */

    public function test_the_quick_pass_asks_for_a_name_and_a_number_only(): void
    {
        $this->post('/en/register/quick', $this->captcha() + [
            'full_name' => 'Hemin Star',
            'phone_country' => '+964',
            'phone' => '07719990002',
            'consent_terms' => '1',
        ])->assertRedirect();

        $pass = Registration::wherePhone('7719990002')->firstOrFail();

        $this->assertSame(Registration::TYPE_VISITOR, $pass->type);
        $this->assertTrue($pass->isQuickPass());
        $this->assertSame(Registration::STATUS_CONFIRMED, $pass->status);
        // Valid for the whole run: there is nothing for a visitor to choose.
        $this->assertSame([1, 2, 3], $pass->dayList());
    }

    /** The pass is the badge: nothing stands between the button and the QR. */
    public function test_the_quick_pass_issues_the_badge_on_submission(): void
    {
        $this->post('/en/register/quick', $this->captcha() + [
            'full_name' => 'Hemin Star', 'phone_country' => '+964',
            'phone' => '07719990003', 'consent_terms' => '1',
        ])->assertRedirect();

        $pass = Registration::wherePhone('7719990003')->firstOrFail();

        $this->assertNotNull($pass->badge_generated_at);
        $this->assertTrue($pass->messages()->where('template_key', 'registration_confirmed_visitor')->exists());
        $this->assertAuthenticatedAs($pass, 'attendee');
    }

    /** And the picture code is what stands between the form and a script. */
    public function test_the_quick_pass_refuses_a_wrong_picture_code(): void
    {
        $this->captcha();

        $this->post('/en/register/quick', [
            'full_name' => 'Hemin Star', 'phone_country' => '+964',
            'phone' => '07719990017', 'consent_terms' => '1', 'captcha' => 'WRONG',
        ])->assertSessionHasErrors('captcha');

        $this->assertSame(0, Registration::wherePhone('7719990017')->count());
    }

    public function test_a_quick_pass_is_offered_the_upgrade_rather_than_an_empty_agenda(): void
    {
        $pass = $this->registrant([
            'type' => Registration::TYPE_VISITOR,
            'phone' => '7719990004',
            'full_name' => 'Hemin Star',
        ]);

        $this->actingAs($pass, 'attendee')
            ->get('/en/me')
            ->assertOk()
            ->assertSee(__('attendee.profile.quick_pass'))
            ->assertDontSee(__('attendee.profile.edit_agenda'));
    }

    public function test_the_quick_pass_does_not_duplicate_an_existing_registration(): void
    {
        $existing = $this->registrant(['phone' => '7719990005']);

        // Back to the form with the reason on it. Forwarding straight to the badge
        // used to read as "it registered me a second time", which is the one thing
        // the check exists to prevent.
        $this->post('/en/register/quick', $this->captcha() + [
            'full_name' => 'Someone Else', 'phone_country' => '+964',
            'phone' => '07719990005', 'consent_terms' => '1',
        ])
            ->assertRedirect('/en/register/quick')
            ->assertSessionHas('duplicate', true);

        $this->assertSame(1, Registration::wherePhone('7719990005')->count());

        // The ticket stays server-side: typing a stranger's number must not hand
        // back a link to their badge.
        $this->assertSame($existing->ticket_id, session('duplicate_ticket'));

        $this->followingRedirects()
            ->post('/en/register/quick', $this->captcha() + [
                'full_name' => 'Someone Else', 'phone_country' => '+964',
                'phone' => '07719990005', 'consent_terms' => '1',
            ])
            ->assertOk()
            ->assertSee(__('register.duplicate.title'))
            ->assertSee(__('register.duplicate.resend'))
            ->assertDontSee($existing->ticket_id);

        // And the button sends that badge to the number on the record.
        $this->followingRedirects()
            ->post('/en/register/duplicate/resend')
            ->assertOk()
            ->assertSee(__('register.duplicate.resent'));

        $this->assertTrue(
            $existing->messages()->where('template_key', 'registration_confirmed_student')->exists()
        );
    }

    /* --------------------------------------------- completing a quick pass -- */

    public function test_the_wizard_starts_from_the_visitor_pass_instead_of_blank(): void
    {
        $pass = $this->registrant([
            'type' => Registration::TYPE_VISITOR,
            'phone' => '7719990010',
            'full_name' => 'Hemin Star',
            'city' => 'Erbil',
        ]);

        $this->actingAs($pass, 'attendee')
            ->get('/en/register/fair')
            ->assertOk()
            ->assertSee(__('register.upgrade.title'))
            ->assertSee(__('register.upgrade.phone_locked'))
            // The name they already gave, and the number they already proved.
            ->assertSee('value="Hemin Star"', escape: false)
            ->assertSee('7719990010');
    }

    public function test_completing_a_visitor_pass_upgrades_it_in_place(): void
    {
        $pass = $this->registrant([
            'type' => Registration::TYPE_VISITOR,
            'phone' => '7719990011',
            'full_name' => 'Hemin Star',
        ]);

        $ticketId = $pass->ticket_id;
        $ticketRef = $pass->ticket_ref;

        $this->actingAs($pass, 'attendee')
            ->post('/en/register/fair', $this->fairAnswers([
                // Tampering with the locked field must not move the badge.
                'phone' => '07770000000',
            ]))
            ->assertRedirect("/en/register/fair/done/{$ticketId}");

        // One record, still theirs: same row, same ticket, same QR.
        $this->assertSame(1, Registration::fair()->where('full_name', 'Rezan Kamal')->count());

        $upgraded = $pass->fresh();

        $this->assertSame(Registration::TYPE_STUDENT, $upgraded->type);
        $this->assertFalse($upgraded->isQuickPass());
        $this->assertSame($ticketRef, $upgraded->ticket_ref);
        $this->assertSame('7719990011', $upgraded->phone);
        $this->assertSame('Sulaimani', $upgraded->city);
        $this->assertSame(Registration::STATUS_CONFIRMED, $upgraded->status);
        // No second code: the number was verified when the pass was issued.
        $this->assertSame(0, $upgraded->otpVerifications()->count());
    }

    public function test_completing_a_pass_is_not_treated_as_a_duplicate_of_itself(): void
    {
        $pass = $this->registrant([
            'type' => Registration::TYPE_VISITOR,
            'phone' => '7719990012',
        ]);

        $this->actingAs($pass, 'attendee')
            ->post('/en/register/fair', $this->fairAnswers(['phone' => '07719990012']))
            ->assertSessionMissing('duplicate');
    }

    public function test_a_completed_registration_no_longer_sees_the_upgrade_prompt(): void
    {
        $pass = $this->registrant([
            'type' => Registration::TYPE_VISITOR,
            'phone' => '7719990013',
        ]);

        $this->actingAs($pass, 'attendee')->post('/en/register/fair', $this->fairAnswers());

        $this->actingAs($pass->fresh(), 'attendee')
            ->get('/en/me')
            ->assertOk()
            ->assertDontSee(__('attendee.profile.quick_pass_upgrade'))
            ->assertSee(__('attendee.services.title'));
    }

    /** A complete, valid four-step submission. */
    private function fairAnswers(array $overrides = []): array
    {
        return array_merge([
            'type' => 'student',
            'full_name' => 'Rezan Kamal',
            'date_of_birth' => '2007-04-11',
            'phone_country' => '+964',
            'phone' => '07719990011',
            'city' => 'Sulaimani',
            'locale' => 'en',
            'education_stage' => 'grade12',
            'email' => 'upgraded@example.com',
            'password' => 'a-good-password',
            'consent_terms' => '1',
        ], $this->captcha(), $overrides);
    }

    public function test_the_attendee_area_renders_in_every_language(): void
    {
        $registration = $this->registrant();

        // Guest pages first: a signed-in attendee is sent past /signin to /me,
        // so the two sets cannot share one session.
        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get("/{$locale}/signin")->assertOk();
            $this->get("/{$locale}/join")->assertOk();
            $this->get("/{$locale}/register/quick")->assertOk();
        }

        $this->actingAs($registration, 'attendee');

        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get("/{$locale}/me")->assertOk();
            $this->get("/{$locale}/me/agenda")->assertOk();
            // And the switcher never strands them outside their own area.
            $this->get("/{$locale}/signin")->assertRedirect("/{$locale}/me");
        }
    }
}
