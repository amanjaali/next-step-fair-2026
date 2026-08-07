<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Message;
use App\Models\OtpVerification;
use App\Models\Registration;
use Database\Seeders\MessageTemplateSeeder;
use Database\Seeders\ProgrammeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FairRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProgrammeSeeder::class);
        $this->seed(MessageTemplateSeeder::class);
    }

    /** A student: the short form, plus the account it creates. */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'student',
            'full_name' => 'Hemin Karim Salih',
            'date_of_birth' => '2008-04-12',
            'phone_country' => '+964',
            'phone' => '7704112288',
            'city' => 'Sulaimani',
            'locale' => 'ku',
            'education_stage' => 'grade12',
            'school_name' => 'Sulaimani Preparatory School for Boys',
            'email' => 'hemin@example.com',
            'password' => 'a-good-password',
            'consent_terms' => '1',
        ], $overrides);
    }

    /** A parent: no account, so no email and no password. */
    private function parentPayload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'parent',
            'full_name' => 'Sara Hama Amin',
            'phone_country' => '+964',
            'phone' => '7704119911',
            'city' => 'Erbil',
            'locale' => 'ku',
            'consent_terms' => '1',
        ], $overrides);
    }

    public function test_a_student_registration_creates_a_pending_record_and_sends_an_otp(): void
    {
        Queue::fake();

        $response = $this->post('/en/register/fair', $this->payload());

        $registration = Registration::firstOrFail();

        $this->assertSame(Registration::TRACK_FAIR, $registration->track);
        $this->assertSame(Registration::STATUS_AWAITING_OTP, $registration->status);
        $this->assertSame('7704112288', $registration->phone);
        // Nobody picks days any more: a badge is valid for the whole run.
        $this->assertSame([1, 2, 3], $registration->dayList());

        // No badge before the number has answered.
        $this->assertNull($registration->badge_generated_at);

        $response->assertRedirect(route('register.fair.verify', ['locale' => 'en', 'registration' => $registration->ticket_id]));

        $this->assertDatabaseCount('otp_verifications', 1);
        Queue::assertPushed(SendWhatsAppMessage::class);
    }

    /**
     * Until the WhatsApp templates are approved nothing is delivered, so the
     * verify page shows the pending code. It must vanish the moment either guard
     * flips — a live site would otherwise print anyone's OTP on the page.
     */
    public function test_the_test_mode_code_is_shown_only_when_nothing_can_be_sent(): void
    {
        $this->post('/en/register/fair', $this->payload());
        $registration = Registration::firstOrFail();
        $url = '/en/register/fair/verify/'.$registration->ticket_id;

        config(['whatsapp.driver' => 'log', 'app.debug' => true]);
        $this->get($url)->assertOk()->assertSee('Test mode');

        config(['app.debug' => false]);
        $this->get($url)->assertOk()->assertDontSee('Test mode');

        config(['app.debug' => true, 'whatsapp.driver' => 'cloud_api']);
        $this->get($url)->assertOk()->assertDontSee('Test mode');
    }

    public function test_the_phone_number_is_encrypted_at_rest_but_still_searchable(): void
    {
        $this->post('/en/register/fair', $this->payload());

        $stored = \DB::table('registrations')->value('phone');
        $this->assertStringNotContainsString('7704112288', (string) $stored);

        $this->assertTrue(Registration::wherePhone('7704112288')->exists());
    }

    public function test_verifying_the_otp_issues_a_badge_and_queues_the_confirmation(): void
    {
        $this->post('/en/register/fair', $this->payload());
        $registration = Registration::firstOrFail();

        // Replace the hashed code with one we know.
        OtpVerification::where('registration_id', $registration->id)
            ->update(['code_hash' => Hash::make('123456')]);

        $this->post("/en/register/fair/verify/{$registration->ticket_id}", ['code' => '123456'])
            ->assertRedirect(route('register.fair.done', ['locale' => 'en', 'registration' => $registration->ticket_id]));

        $registration->refresh();

        $this->assertSame(Registration::STATUS_CONFIRMED, $registration->status);
        $this->assertNotNull($registration->verified_at);
        $this->assertNotNull($registration->badge_generated_at);

        $this->assertTrue(
            Message::where('registration_id', $registration->id)
                ->where('template_key', 'registration_confirmed_student')
                ->exists()
        );
    }

    public function test_a_wrong_code_does_not_issue_a_badge(): void
    {
        $this->post('/en/register/fair', $this->payload());
        $registration = Registration::firstOrFail();

        $this->post("/en/register/fair/verify/{$registration->ticket_id}", ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertSame(Registration::STATUS_AWAITING_OTP, $registration->refresh()->status);
    }

    public function test_a_second_registration_on_the_same_number_is_blocked(): void
    {
        $this->post('/en/register/fair', $this->payload());
        Registration::first()->forceFill(['status' => Registration::STATUS_CONFIRMED])->save();

        $this->post('/en/register/fair', $this->payload(['full_name' => 'Someone Else']))
            ->assertSessionHas('duplicate');

        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_consent_is_required(): void
    {
        $this->post('/en/register/fair', $this->payload(['consent_terms' => null]))
            ->assertSessionHasErrors('consent_terms');

        $this->assertDatabaseCount('registrations', 0);
    }

    /**
     * A student registration is also an account, and the account is the thing the
     * scholarship, the agenda and every service afterwards hang off.
     */
    public function test_a_student_registration_creates_the_next_step_account(): void
    {
        $this->post('/en/register/fair', $this->payload());

        $registration = Registration::firstOrFail();

        $this->assertTrue($registration->isStudentAccount());
        $this->assertSame('hemin@example.com', $registration->email);
        $this->assertTrue(Hash::check('a-good-password', $registration->password));
        $this->assertSame('grade12', $registration->education_stage);
    }

    public function test_a_student_needs_an_email_a_password_and_a_stage(): void
    {
        $this->post('/en/register/fair', $this->payload([
            'email' => null, 'password' => null, 'education_stage' => null,
        ]))->assertSessionHasErrors(['email', 'password', 'education_stage']);

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_one_account_per_email_address(): void
    {
        $this->post('/en/register/fair', $this->payload());

        $this->post('/en/register/fair', $this->payload(['phone' => '7704110000']))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('registrations', 1);
    }

    /** A parent is not opening an account, so none of that is asked for. */
    public function test_a_parent_gets_a_badge_and_no_account(): void
    {
        $this->post('/en/register/fair', $this->parentPayload())->assertRedirect();

        $registration = Registration::firstOrFail();

        $this->assertSame(Registration::TYPE_PARENT, $registration->type);
        $this->assertFalse($registration->isStudentAccount());
        $this->assertNull($registration->password);
        $this->assertNull($registration->email);
        $this->assertSame([1, 2, 3], $registration->dayList());
    }

    public function test_the_honeypot_field_rejects_bots(): void
    {
        $this->post('/en/register/fair', $this->payload(['ns_hp' => 'spam ltd']))
            ->assertRedirect();

        $this->assertDatabaseCount('registrations', 0);
    }

    /**
     * The decoy is dropped, never argued with.
     *
     * It used to be a `size:0` rule on a field named `company`, which browsers
     * and password managers autofill — so real people registering were stopped
     * dead by "The company field must be 0 characters", about a field they could
     * not see. No honeypot may ever put a validation error in front of a person.
     */
    public function test_the_honeypot_never_shows_anybody_an_error(): void
    {
        $this->post('/en/register/fair', $this->payload(['ns_hp' => 'spam ltd']))
            ->assertSessionHasNoErrors();
    }

    /** A name nothing autofills, and display:none so autofill cannot reach it. */
    public function test_the_decoy_field_is_hidden_from_autofill(): void
    {
        $html = $this->get('/en/register/fair')->assertOk()->getContent();

        $this->assertStringContainsString('name="ns_hp"', $html);
        $this->assertStringNotContainsString('name="company"', $html);
        $this->assertMatchesRegularExpression('/name="ns_hp"[^>]*class="hidden"/', $html);
    }
}
