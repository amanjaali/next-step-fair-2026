<?php

namespace Tests\Feature;

use App\Models\CheckIn;
use App\Models\QrCampaign;
use App\Models\Registration;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketAndCheckinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function staff(): User
    {
        return User::where('email', 'gate@nextstepfair.com')->firstOrFail();
    }

    private function confirmed(): Registration
    {
        return Registration::fair()->where('status', Registration::STATUS_CONFIRMED)->firstOrFail();
    }

    public function test_the_qr_encodes_a_signed_token_and_no_personal_data(): void
    {
        $registration = $this->confirmed();
        $url = app(TicketService::class)->verifyUrl($registration);

        $this->assertStringContainsString($registration->ticket_id, $url);
        $this->assertStringContainsString('sig=', $url);
        $this->assertStringNotContainsString($registration->full_name, $url);
        $this->assertStringNotContainsString((string) $registration->phone, $url);
    }

    public function test_verification_rejects_a_forged_signature(): void
    {
        $registration = $this->confirmed();

        $this->get("/verify/{$registration->ticket_id}?sig=deadbeef")
            ->assertOk()
            ->assertSee(__('checkin.not_recognised'))
            ->assertDontSee($registration->full_name);
    }

    public function test_verification_accepts_a_valid_signature(): void
    {
        $registration = $this->confirmed();
        $signature = app(TicketService::class)->signature($registration->ticket_id);

        $this->get("/verify/{$registration->ticket_id}?sig={$signature}")
            ->assertOk()
            ->assertSee($registration->full_name);
    }

    public function test_badge_downloads_are_produced_on_demand(): void
    {
        $registration = $this->confirmed();

        $this->get("/ticket/{$registration->ticket_id}/badge.pdf")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get("/ticket/{$registration->ticket_id}/qr.svg")
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml');

        $this->get("/ticket/{$registration->ticket_id}/calendar.ics")
            ->assertOk()
            ->assertSee('BEGIN:VCALENDAR');
    }

    public function test_the_badge_png_is_served_from_storage_for_meta_to_fetch_quickly(): void
    {
        $registration = $this->confirmed();
        $path = 'badges/'.$registration->ticket_id.'.png';
        $bytes = "\x89PNG\r\n\x1a\nstored-badge";

        Storage::disk(config('filesystems.default'))->put($path, $bytes);
        $registration->forceFill(['badge_png_path' => $path])->save();

        $this->get("/ticket/{$registration->ticket_id}/badge.png")
            ->assertOk()
            ->assertHeader('content-type', 'image/png')
            ->assertHeader('content-disposition', 'inline; filename="next-step-badge-'.$registration->ticket_ref.'.png"')
            ->assertContent($bytes);
    }

    public function test_the_scanner_is_staff_only(): void
    {
        $this->get('/checkin')->assertRedirect('/checkin/login');
    }

    public function test_a_valid_scan_checks_the_registrant_in(): void
    {
        $registration = Registration::fair()
            ->where('status', Registration::STATUS_CONFIRMED)
            ->whereDoesntHave('checkIns')
            ->firstOrFail();

        $day = $registration->dayList()[0];
        $signature = app(TicketService::class)->signature($registration->ticket_id);

        $response = $this->actingAs($this->staff())->postJson('/checkin/scan', [
            'ticket' => $registration->ticket_id,
            'sig' => $signature,
            'day' => $day,
        ]);

        $response->assertOk()->assertJsonPath('state', 'valid');
        $this->assertDatabaseHas('check_ins', ['registration_id' => $registration->id, 'day' => $day]);
    }

    public function test_a_second_scan_on_the_same_day_returns_amber(): void
    {
        $registration = Registration::fair()->has('checkIns')->firstOrFail();
        $checkIn = $registration->checkIns()->firstOrFail();

        $this->actingAs($this->staff())->postJson('/checkin/scan', [
            'ticket' => $registration->ticket_id,
            'sig' => app(TicketService::class)->signature($registration->ticket_id),
            'day' => $checkIn->day,
        ])->assertOk()->assertJsonPath('state', 'already');
    }

    public function test_an_unsigned_token_is_rejected_at_the_gate(): void
    {
        $registration = $this->confirmed();

        $this->actingAs($this->staff())->postJson('/checkin/scan', [
            'ticket' => $registration->ticket_id,
            'sig' => 'nope',
            'day' => 1,
        ])->assertOk()->assertJsonPath('state', 'invalid');
    }

    public function test_a_full_verify_url_scans_as_well_as_a_bare_token(): void
    {
        $registration = Registration::fair()
            ->where('status', Registration::STATUS_CONFIRMED)
            ->whereDoesntHave('checkIns')
            ->firstOrFail();

        $this->actingAs($this->staff())->postJson('/checkin/scan', [
            'ticket' => app(TicketService::class)->verifyUrl($registration),
            'day' => $registration->dayList()[0],
        ])->assertOk()->assertJsonPath('state', 'valid');
    }

    public function test_staff_can_search_by_name_and_by_phone(): void
    {
        $registration = $this->confirmed();

        $this->actingAs($this->staff())
            ->getJson('/checkin/search?q='.urlencode($registration->full_name))
            ->assertOk()
            ->assertJsonPath('results.0.name', $registration->full_name);

        $this->actingAs($this->staff())
            ->getJson('/checkin/search?q='.$registration->phone)
            ->assertOk()
            ->assertJsonPath('results.0.ticket', $registration->ticket_ref);
    }

    public function test_the_offline_queue_syncs_without_double_counting(): void
    {
        $registration = Registration::fair()
            ->where('status', Registration::STATUS_CONFIRMED)
            ->whereDoesntHave('checkIns')
            ->firstOrFail();

        $payload = ['scans' => [[
            'ticket' => $registration->ticket_id,
            'day' => 2,
            'scanned_at' => now()->subMinutes(20)->toIso8601String(),
            'device_id' => 'dev-abc',
        ]]];

        $this->actingAs($this->staff())->postJson('/checkin/sync', $payload)
            ->assertOk()->assertJsonPath('accepted', 1);

        // Replaying the same queue must not create a second check-in.
        $this->actingAs($this->staff())->postJson('/checkin/sync', $payload)
            ->assertOk()->assertJsonPath('accepted', 0);

        $this->assertSame(1, CheckIn::where('registration_id', $registration->id)->where('day', 2)->count());
    }

    public function test_the_offline_manifest_carries_no_phone_numbers(): void
    {
        $response = $this->actingAs($this->staff())->getJson('/checkin/offline-manifest');

        $response->assertOk();
        $this->assertStringNotContainsString('7704112288', $response->getContent());
    }

    public function test_the_pwa_manifest_and_service_worker_are_served(): void
    {
        $this->actingAs($this->staff())->getJson('/checkin/manifest.json')
            ->assertOk()
            ->assertJsonPath('display', 'standalone');

        $this->get('/checkin/sw.js')
            ->assertOk()
            ->assertHeader('content-type', 'application/javascript');
    }

    public function test_a_campaign_qr_counts_the_scan_and_forwards_with_utm(): void
    {
        $campaign = QrCampaign::firstOrFail();
        $before = $campaign->scans()->count();

        $this->get('/q/'.$campaign->code)
            ->assertRedirectContains('utm_campaign='.$campaign->code);

        $this->assertSame($before + 1, $campaign->scans()->count());
    }
}
