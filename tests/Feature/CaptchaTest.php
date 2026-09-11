<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Services\CaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The picture code that replaced the WhatsApp code on the registration forms.
 *
 * The thing worth guarding is not that a picture appears — it is that the
 * challenge is different every time and spent after one attempt. A captcha that
 * is the same for everybody, or that can be replayed, is a decoration.
 */
class CaptchaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public static function forms(): array
    {
        return [
            'fair' => ['/en/register/fair'],
            'quick pass' => ['/en/register/quick'],
            'conference' => ['/en/register/conference'],
        ];
    }

    #[DataProvider('forms')]
    public function test_every_registration_form_shows_the_code_when_enabled(string $url): void
    {
        config(['nextstep.captcha.enabled' => true]);

        $this->get($url)->assertOk()
            ->assertSee(__('register.captcha.label'))
            ->assertSee('name="captcha"', false)
            ->assertSee(route('captcha'), false);
    }

    #[DataProvider('forms')]
    public function test_the_code_field_is_hidden_when_captcha_is_disabled(string $url): void
    {
        config(['nextstep.captcha.enabled' => false]);

        $this->get($url)->assertOk()
            ->assertDontSee(__('register.captcha.label'))
            ->assertDontSee('name="captcha"', false);
    }

    public function test_the_fair_form_registers_without_captcha_when_disabled(): void
    {
        config(['nextstep.captcha.enabled' => false]);

        $before = Registration::count();

        $this->post('/en/register/fair', [
            'type' => 'student', 'full_name' => 'Nma Salar', 'date_of_birth' => '2008-02-02',
            'phone_country' => '+964', 'phone' => '07704118800', 'city' => 'Sulaimani',
            'locale' => 'en', 'education_stage' => 'grade12',
            'email' => 'nma-disabled-captcha@example.com', 'password' => 'a-good-password', 'consent_terms' => '1',
        ])->assertRedirect();

        $this->assertSame($before + 1, Registration::count());
    }

    public function test_the_picture_is_a_png_and_is_never_cached(): void
    {
        $response = $this->get(route('captcha'))->assertOk();

        $this->assertSame('image/png', $response->headers->get('content-type'));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('cache-control'));

        // Really a PNG, not an error page with the wrong header on it.
        $this->assertStringStartsWith("\x89PNG", $response->getContent());
    }

    /**
     * The point of the whole exercise: one code for everybody would be posted a
     * thousand times by the first script that read it once.
     */
    public function test_a_new_code_is_drawn_for_every_request(): void
    {
        $service = app(CaptchaService::class);

        $codes = collect(range(1, 12))->map(fn () => $service->issue())->unique();

        $this->assertGreaterThan(8, $codes->count(), 'The code barely changes between requests.');
    }

    public function test_the_right_code_passes_and_a_wrong_one_does_not(): void
    {
        $service = app(CaptchaService::class);

        $code = $service->issue();
        $this->assertTrue($service->check($code));

        $service->issue();
        $this->assertFalse($service->check('ZZZZZ'));
    }

    /** Typed on a phone, so lower case and a stray space are forgiven. */
    public function test_case_and_spacing_do_not_matter(): void
    {
        $service = app(CaptchaService::class);

        $code = $service->issue();

        $this->assertTrue($service->check(' '.strtolower($code).' '));
    }

    /**
     * Single use, right or wrong. Otherwise one solved picture is a key to the
     * form for as long as the session lives.
     */
    public function test_a_code_cannot_be_used_twice(): void
    {
        $service = app(CaptchaService::class);

        $code = $service->issue();

        $this->assertTrue($service->check($code));
        $this->assertFalse($service->check($code));
    }

    public function test_a_failed_attempt_also_spends_the_code(): void
    {
        $service = app(CaptchaService::class);

        $code = $service->issue();

        $this->assertFalse($service->check('WRONG'));
        $this->assertFalse($service->check($code));
    }

    public function test_an_expired_code_is_refused(): void
    {
        $service = app(CaptchaService::class);

        $code = $service->issue();

        $this->travel(21)->minutes();

        $this->assertFalse($service->check($code));
    }

    public function test_answering_without_being_shown_a_picture_fails(): void
    {
        $this->assertFalse(app(CaptchaService::class)->check('ABC12'));
    }

    /** A form posted with no code at all is refused before anything is written. */
    public function test_the_fair_form_will_not_register_anybody_without_the_code(): void
    {
        $before = Registration::count();

        $this->post('/en/register/fair', [
            'type' => 'student', 'full_name' => 'Nma Salar', 'date_of_birth' => '2008-02-02',
            'phone_country' => '+964', 'phone' => '07704118800', 'city' => 'Sulaimani',
            'locale' => 'en', 'education_stage' => 'grade12',
            'email' => 'nma@example.com', 'password' => 'a-good-password', 'consent_terms' => '1',
        ])->assertSessionHasErrors('captcha');

        $this->assertSame($before, Registration::count(), 'A registration was written despite the missing code.');
    }

    public function test_the_conference_form_will_not_register_anybody_without_the_code(): void
    {
        $before = Registration::count();

        $this->post('/en/register/conference', [
            'type' => 'government', 'full_name' => 'Dr. Rezan Ahmed Kareem',
            'position' => 'Director General', 'organization' => 'MOHE',
            'email' => 'r.kareem@mhe.krd', 'phone_country' => '+964',
            'phone' => '7513004412', 'city' => 'Erbil', 'locale' => 'en', 'consent_terms' => '1',
        ])->assertSessionHasErrors('captcha');

        $this->assertSame($before, Registration::count(), 'A registration was written despite the missing code.');
    }

    /**
     * The picture must not become "the page you came from".
     *
     * The browser fetches it last when the form loads, so the session's idea of
     * the previous URL would be the PNG — and the redirect that carries "wrong
     * code" back to the form would land the visitor on a picture of a code with
     * no form under it. It did, until the image request started declaring itself
     * a background fetch.
     */
    public function test_a_failed_submission_goes_back_to_the_form_not_to_the_picture(): void
    {
        $this->get('/en/register/quick')->assertOk();
        $this->get(route('captcha'))->assertOk();

        $this->post('/en/register/quick', [
            'full_name' => 'Test Person', 'phone_country' => '+964',
            'phone' => '07704118811', 'consent_terms' => '1', 'captcha' => 'ZZZZZ',
        ])
            ->assertRedirect('/en/register/quick')
            ->assertSessionHasErrors('captcha');
    }

    /** The session never carries the answer, only something to check it against. */
    public function test_the_session_does_not_hold_the_code_in_the_clear(): void
    {
        $code = app(CaptchaService::class)->issue();

        $this->assertStringNotContainsString($code, json_encode(session('captcha')) ?: '');
    }
}
