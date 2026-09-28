<?php

namespace Tests\Feature;

use App\Jobs\SendMetaConversionEvent;
use App\Models\Registration;
use App\Models\ScholarshipApplication;
use Database\Seeders\MessageTemplateSeeder;
use Database\Seeders\ProgrammeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

/**
 * CompleteRegistration and SubmitApplication: each fires once, from the Pixel
 * and the Conversions API, under the same event ID.
 */
class MetaEventsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProgrammeSeeder::class);
        $this->seed(MessageTemplateSeeder::class);

        Http::fake([
            'https://demi.nextstepfair.com/*' => Http::response('', 200, ['Content-Type' => 'image/png']),
            'https://www.nextstepfair.com/*' => Http::response('', 200, ['Content-Type' => 'image/png']),
            'https://graph.facebook.com/*' => Http::response(['events_received' => 1], 200),
        ]);

        config([
            'nextstep.analytics.meta_pixel' => '111222333',
            'nextstep.analytics.meta_capi_token' => 'capi-token',
        ]);
    }

    private function register(): Registration
    {
        $response = $this->withUnencryptedCookies(['_fbp' => 'fb.1.1700000000.123', '_fbc' => 'fb.1.1700000000.abc'])
            ->post('/en/register/fair', array_merge([
                'type' => 'student',
                'full_name' => 'Pixel Student',
                'date_of_birth' => '2008-04-12',
                'phone_country' => '+964',
                'phone' => '07704112288',
                'city' => 'Sulaimani',
                'locale' => 'en',
                'education_stage' => 'grade12',
                'school_name' => 'Sulaimani Preparatory',
                'email' => 'Pixel.Student@Example.com',
                'password' => 'a-good-password',
                'consent_terms' => '1',
            ], $this->captcha()));

        $response->assertRedirect();
        $this->followingRedirect = $response->headers->get('Location');

        return Registration::latest('id')->firstOrFail();
    }

    private ?string $followingRedirect = null;

    private function submitApplication(Registration $student): ScholarshipApplication
    {
        $application = ScholarshipApplication::create([
            'registration_id' => $student->id,
            'cycle' => config('scholarship.cycle'),
            'status' => ScholarshipApplication::STATUS_DRAFT,
            'step' => 4,
            'eligibility' => ['year' => 'y', 'funded' => 'n'],
            'eligibility_passed_at' => now(),
            'region_code' => 'SLM',
            'district' => 'Chamchamal',
            'exam_status' => 'published',
            'exam_average' => 90,
        ]);

        $this->actingAs($student, 'attendee')
            ->post('/en/scholarship/apply/submit', ['confirm' => '1'])
            ->assertRedirect(route('scholarship.status', ['locale' => 'en']));

        return $application->refresh();
    }

    public function test_complete_registration_fires_once_with_matching_pixel_and_server_ids(): void
    {
        Bus::fake();
        $registration = $this->register();

        $landing = $this->get($this->followingRedirect)->assertOk();
        $landing->assertSee("fbq('track', \"CompleteRegistration\"", false);
        $landing->assertSee('"reg_'.$registration->id.'"', false);
        $this->assertSame(1, substr_count($landing->getContent(), "fbq('track', \"CompleteRegistration\""));
        $this->assertStringNotContainsString("fbq('track', 'CompleteRegistration', value)", $landing->getContent());

        // The badge page opened again: nothing fires.
        $this->get($this->followingRedirect)->assertOk()->assertDontSee('CompleteRegistration', false);

        Bus::assertDispatched(SendMetaConversionEvent::class, 1);
        Bus::assertDispatched(SendMetaConversionEvent::class, function (SendMetaConversionEvent $job) use ($registration) {
            $e = $job->event;

            return $e['event_name'] === 'CompleteRegistration'
                && $e['event_id'] === 'reg_'.$registration->id
                && $e['action_source'] === 'website'
                && is_int($e['event_time'])
                && $e['custom_data']['track'] === 'fair'
                && $e['user_data']['em'] === [hash('sha256', 'pixel.student@example.com')]
                && $e['user_data']['ph'] === [hash('sha256', '9647704112288')]
                && $e['user_data']['external_id'] === [hash('sha256', (string) $registration->id)]
                && $e['user_data']['fbp'] === 'fb.1.1700000000.123'
                && $e['user_data']['fbc'] === 'fb.1.1700000000.abc'
                && filled($e['user_data']['client_ip_address'])
                && filled($e['user_data']['client_user_agent'])
                && filled($e['event_source_url']);
        });
    }

    public function test_the_badge_page_on_its_own_never_fires_complete_registration(): void
    {
        Bus::fake();
        $registration = $this->register();
        $this->get($this->followingRedirect);

        $this->get(route('register.fair.done', $registration->ticket_id))
            ->assertOk()
            ->assertDontSee('CompleteRegistration', false);
    }

    public function test_registering_from_the_scholarship_page_still_counts(): void
    {
        Bus::fake();
        $this->get('/en/register/fair?type=student&next=scholarship')->assertOk();
        $registration = $this->register();

        $this->assertStringContainsString('/scholarship/apply', $this->followingRedirect);
        $this->get($this->followingRedirect)->assertOk()
            ->assertSee('"reg_'.$registration->id.'"', false);
        Bus::assertDispatched(SendMetaConversionEvent::class, 1);
    }

    public function test_submit_application_fires_once_with_matching_pixel_and_server_ids(): void
    {
        Bus::fake();
        $student = $this->register();
        $this->get($this->followingRedirect);

        $application = $this->submitApplication($student);

        $page = $this->actingAs($student, 'attendee')->get('/en/scholarship/my-application')->assertOk();
        $page->assertSee("fbq('track', \"SubmitApplication\"", false);
        $page->assertSee('"app_'.$application->id.'"', false);
        $page->assertSee('National Scholarship Program 2026-2027');

        $this->actingAs($student, 'attendee')->get('/en/scholarship/my-application')
            ->assertOk()->assertDontSee('SubmitApplication', false);

        Bus::assertDispatched(SendMetaConversionEvent::class, function (SendMetaConversionEvent $job) use ($application, $student) {
            $e = $job->event;

            return $e['event_name'] === 'SubmitApplication'
                && $e['event_id'] === 'app_'.$application->id
                && $e['custom_data'] === ['track' => 'scholarship']
                && $e['user_data']['external_id'] === [hash('sha256', (string) $student->id)];
        });
    }

    public function test_without_a_token_only_the_pixel_fires(): void
    {
        Bus::fake();
        config(['nextstep.analytics.meta_capi_token' => null]);
        $registration = $this->register();

        $this->get($this->followingRedirect)->assertSee('"reg_'.$registration->id.'"', false);
        Bus::assertNotDispatched(SendMetaConversionEvent::class);
    }

    public function test_without_a_pixel_nothing_meta_fires(): void
    {
        Bus::fake();
        config(['nextstep.analytics.meta_pixel' => null]);
        $this->register();

        $this->get($this->followingRedirect)
            ->assertDontSee('connect.facebook.net', false)
            ->assertDontSee('eventID', false);
        Bus::assertNotDispatched(SendMetaConversionEvent::class);
    }

    public function test_the_job_posts_to_the_conversions_api(): void
    {
        config(['nextstep.analytics.meta_capi_test_code' => 'TEST123']);

        (new SendMetaConversionEvent(['event_name' => 'SubmitApplication', 'event_id' => 'app_7']))->handle(app(\App\Services\Analytics\MetaConversionsClient::class));

        Http::assertSent(function (ClientRequest $request) {
            return str_starts_with($request->url(), 'https://graph.facebook.com/v21.0/111222333/events')
                && $request['access_token'] === 'capi-token'
                && $request['test_event_code'] === 'TEST123'
                && $request['data'][0]['event_id'] === 'app_7';
        });
    }

    public function test_a_rejected_call_is_logged_not_thrown(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['https://graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid token']], 400)]);
        \Illuminate\Support\Facades\Log::spy();

        (new SendMetaConversionEvent(['event_name' => 'SubmitApplication', 'event_id' => 'app_9']))->handle(app(\App\Services\Analytics\MetaConversionsClient::class));

        \Illuminate\Support\Facades\Log::shouldHaveReceived('error')->withArgs(
            fn (string $message, array $context) => $message === 'meta_capi.failed' && $context['events'] === ['SubmitApplication:app_9'],
        );
    }
}
