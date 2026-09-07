<?php

namespace Tests\Feature;

use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Getting back in, from another computer.
 *
 * Signing in has always worked; it was reachable from the mobile menu and from
 * a handful of pages that happened to need it, and from nowhere on a desktop
 * front page. A student who registered on a school computer and came back on a
 * phone had no way to say "I already have an account" — so they registered a
 * second time, which is how one person ends up with two tickets.
 */
class SignInReachableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function student(): Registration
    {
        return Registration::create([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'Returning Student',
            'phone' => '7701122334',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'email' => 'returning@example.com',
            'password' => 'a-good-password',
            'education_stage' => 'grade12',
            'days' => [1, 2, 3],
            'verified_at' => now(),
            'confirmed_at' => now(),
        ]);
    }

    /** Both halves of the chrome, on the page every visitor lands on. */
    public function test_the_front_page_offers_a_way_back_in(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();

        // The header, the mobile menu and the footer.
        $this->assertGreaterThanOrEqual(3, substr_count($html, route('attendee.signin', ['locale' => 'en'])));
        $this->assertStringContainsString(__('attendee.nav.sign_in'), $html);
    }

    public function test_it_is_offered_in_every_language(): void
    {
        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get('/'.$locale)
                ->assertOk()
                ->assertSee(route('attendee.signin', ['locale' => $locale]), false);
        }
    }

    /** Not only on the home page — the chrome is the same everywhere. */
    public function test_it_is_offered_on_the_pages_a_student_lands_on(): void
    {
        foreach (['/en/scholarship', '/en/agenda', '/en/universities'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee(route('attendee.signin', ['locale' => 'en']), false);
        }
    }

    /** Somebody already signed in is offered their account, not a sign-in form. */
    public function test_a_signed_in_student_is_not_asked_to_sign_in_again(): void
    {
        $html = $this->actingAs($this->student(), 'attendee')
            ->get('/en')->assertOk()->getContent();

        $this->assertStringNotContainsString(route('attendee.signin', ['locale' => 'en']), $html);
        $this->assertStringContainsString(__('attendee.nav.my_next_step'), $html);
    }

    /** The page itself still does what it is linked for. */
    public function test_a_student_can_sign_in_with_the_account_they_registered_with(): void
    {
        $student = $this->student();

        $this->post('/en/signin', [
            'email' => 'returning@example.com',
            'password' => 'a-good-password',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($student, 'attendee');
    }

    public function test_a_wrong_password_says_nothing_about_who_exists(): void
    {
        $this->student();

        $this->from('/en/signin')->post('/en/signin', [
            'email' => 'returning@example.com',
            'password' => 'not-the-password',
        ])->assertRedirect('/en/signin')->assertSessionHasErrors('email');

        $this->from('/en/signin')->post('/en/signin', [
            'email' => 'nobody-at-all@example.com',
            'password' => 'not-the-password',
        ])->assertRedirect('/en/signin')->assertSessionHasErrors('email');

        $this->assertGuest('attendee');
    }
}
