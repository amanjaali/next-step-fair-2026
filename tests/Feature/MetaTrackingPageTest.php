<?php

namespace Tests\Feature;

use App\Filament\Pages\MetaTracking;
use App\Models\Registration;
use App\Models\ScholarshipApplication;
use App\Models\Setting;
use App\Models\User;
use App\Services\Analytics\MetaSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class MetaTrackingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['nextstep.analytics.meta_capi_token' => null]);
        Http::fake(['https://graph.facebook.com/*' => Http::response(['events_received' => 1], 200)]);
    }

    private function superAdmin(): User
    {
        return User::where('email', 'admin@nextstepfair.com')->firstOrFail();
    }

    private function submitted(string $phone, \DateTimeInterface $at): ScholarshipApplication
    {
        $student = Registration::create([
            'track' => Registration::TRACK_FAIR, 'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED, 'locale' => 'en', 'full_name' => 'Backfill '.$phone,
            'phone' => $phone, 'phone_country' => '+964', 'city' => 'Sulaimani', 'days' => [1],
            'email' => "s{$phone}@example.com", 'password' => 'a-good-password', 'confirmed_at' => $at,
            'ip_address' => '5.6.7.8', 'user_agent' => 'Mozilla/5.0 test',
        ]);

        return ScholarshipApplication::create([
            'registration_id' => $student->id, 'cycle' => config('scholarship.cycle'),
            'status' => ScholarshipApplication::STATUS_SUBMITTED, 'step' => 4, 'submitted_at' => $at,
        ]);
    }

    public function test_only_super_admins_can_open_it(): void
    {
        $this->actingAs($this->superAdmin())->get('/admin/meta-tracking')->assertOk()->assertSee('Conversions API');
        $denied = $this->actingAs(User::where('email', 'committee@nextstepfair.com')->firstOrFail())->get('/admin/meta-tracking');
        $this->assertContains($denied->status(), [302, 403]);
        $this->assertStringNotContainsString('Conversions API access token', $denied->getContent());
    }

    public function test_saving_the_token_the_first_time_sends_the_last_7_days_automatically(): void
    {
        $recent = $this->submitted('7701000001', now()->subDays(2));
        $old = $this->submitted('7701000002', now()->subDays(10));

        Livewire::actingAs($this->superAdmin())
            ->test(MetaTracking::class)
            ->set('data.token', 'EAAB-secret-token')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('EAAB-secret-token', MetaSettings::token());
        $this->assertStringNotContainsString('EAAB-secret-token', (string) DB::table('settings')->where('key', 'meta_capi_token')->value('value'));

        Http::assertSent(function (ClientRequest $r) use ($recent, $old) {
            $ids = collect($r['data'] ?? [])->pluck('event_id');

            return str_contains($r->url(), '/1136661341740528/events')
                && $r['access_token'] === 'EAAB-secret-token'
                && $ids->contains('app_'.$recent->id)
                && $ids->contains('reg_'.$recent->registration_id)
                && ! $ids->contains('app_'.$old->id)
                && collect($r['data'])->firstWhere('event_id', 'app_'.$recent->id)['user_data']['client_user_agent'] === 'Mozilla/5.0 test';
        });
        Http::assertSentCount(1);
    }

    public function test_the_catch_up_skips_desk_walk_ins_and_visitor_passes(): void
    {
        $online = $this->submitted('7701000011', now()->subDay())->registration;
        $walkIn = Registration::create([
            'track' => Registration::TRACK_FAIR, 'type' => Registration::TYPE_STUDENT, 'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en', 'full_name' => 'Walk In', 'phone' => '7701000012', 'phone_country' => '+964', 'city' => 'Sulaimani',
            'days' => [1], 'is_walk_in' => true, 'created_by' => $this->superAdmin()->id, 'confirmed_at' => now()->subDay(),
        ]);
        $visitor = Registration::create([
            'track' => Registration::TRACK_FAIR, 'type' => Registration::TYPE_VISITOR, 'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en', 'full_name' => 'Gate Pass', 'phone' => '7701000013', 'phone_country' => '+964', 'city' => 'Sulaimani',
            'days' => [1], 'confirmed_at' => now()->subDay(),
        ]);

        MetaSettings::saveToken('token');
        $result = app(\App\Services\Analytics\MetaBackfill::class)->run();

        $this->assertTrue($result['ok']);
        Http::assertSent(function (ClientRequest $r) use ($online, $walkIn, $visitor) {
            $ids = collect($r['data'] ?? [])->pluck('event_id');

            return $ids->contains('reg_'.$online->id)
                && ! $ids->contains('reg_'.$walkIn->id)
                && ! $ids->contains('reg_'.$visitor->id);
        });
    }

    public function test_a_failed_first_catch_up_is_retried_on_the_next_save(): void
    {
        $recent = $this->submitted('7701000021', now()->subDay());
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['https://graph.facebook.com/*' => Http::sequence()
            ->push(['error' => ['message' => 'Invalid OAuth access token']], 400)
            ->push(['events_received' => 2], 200)]);

        $page = Livewire::actingAs($this->superAdmin())->test(MetaTracking::class);
        $page->set('data.token', 'wrong-token')->call('save');
        $this->assertNull(Setting::get('meta_backfill_done_at'));

        $page->set('data.token', 'right-token')->call('save');
        $this->assertNotNull(Setting::get('meta_backfill_done_at'));
        Http::assertSentCount(2);

        $page->call('save');
        Http::assertSentCount(2);
    }

    public function test_saving_again_does_not_resend(): void
    {
        $this->submitted('7701000003', now()->subDay());
        MetaSettings::saveToken('already-set');
        Setting::put('meta_backfill_done_at', now()->toIso8601String(), 'meta');

        Livewire::actingAs($this->superAdmin())
            ->test(MetaTracking::class)
            ->set('data.test_code', 'TEST42')
            ->call('save');

        Http::assertNothingSent();
        $this->assertSame('already-set', MetaSettings::token());
        $this->assertSame('TEST42', MetaSettings::testCode());
    }

    public function test_the_pixel_id_saved_in_the_admin_is_used_on_the_site(): void
    {
        Livewire::actingAs($this->superAdmin())
            ->test(MetaTracking::class)
            ->set('data.pixel_id', '999888777666')
            ->call('save');

        $this->get('/en')->assertOk()->assertSee("fbq('init', '999888777666')", false);
    }

    public function test_the_test_button_reports_meta_errors(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['https://graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid OAuth access token']], 400)]);
        MetaSettings::saveToken('bad-token');

        Livewire::actingAs($this->superAdmin())
            ->test(MetaTracking::class)
            ->callAction('test')
            ->assertNotified('Meta did not accept the test event');

        $this->assertStringContainsString('Invalid OAuth access token', MetaSettings::status()['error']);
    }
}
