<?php

namespace Tests\Feature;

use App\Filament\Filters\DateRangeFilter;
use App\Filament\Resources\ConferenceRsvps\Pages\ListConferenceRsvps;
use App\Filament\Resources\Registrations\Pages\ListRegistrations;
use App\Models\Registration;
use App\Models\User;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** The date filter on every page with an Export button, and the export following it. */
class ExportDateFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@nextstepfair.com')->firstOrFail();
    }

    private function registration(string $track, string $name, string $phone, \DateTimeInterface $at): Registration
    {
        $registration = Registration::create([
            'track' => $track,
            'type' => $track === Registration::TRACK_FAIR ? Registration::TYPE_STUDENT : 'government',
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en', 'full_name' => $name, 'phone' => $phone, 'phone_country' => '+964',
            'city' => 'Sulaimani', 'days' => [1], 'email' => strtolower(str_replace(' ', '.', $name)).'@example.com',
            'confirmed_at' => $at,
        ]);
        $registration->forceFill(['created_at' => $at])->save();

        return $registration;
    }

    public function test_fair_registrations_filter_by_date_and_the_export_follows(): void
    {
        Storage::fake('local');
        $new = $this->registration(Registration::TRACK_FAIR, 'Fresh Student', '7702000001', now()->subDay());
        $old = $this->registration(Registration::TRACK_FAIR, 'Early Student', '7702000002', now()->subDays(40));

        $page = Livewire::actingAs($this->admin())
            ->test(ListRegistrations::class)
            ->filterTable('registered_between', ['period' => 'last_7'])
            ->assertCanSeeTableRecords([$new])
            ->assertCanNotSeeTableRecords([$old]);

        $page->callAction('export', data: ['format' => 'csv']);

        $export = Export::latest('id')->firstOrFail();
        $csv = collect(Storage::disk($export->file_disk)->allFiles($export->getFileDirectory()))
            ->filter(fn (string $f) => str_ends_with($f, '.csv'))
            ->map(fn (string $f) => Storage::disk($export->file_disk)->get($f))
            ->implode('');

        $this->assertStringContainsString('Fresh Student', $csv);
        $this->assertStringNotContainsString('Early Student', $csv);
    }

    public function test_conference_rsvps_filter_by_date(): void
    {
        $new = $this->registration(Registration::TRACK_CONFERENCE, 'Fresh Delegate', '7702000003', now()->startOfDay()->addHour());
        $old = $this->registration(Registration::TRACK_CONFERENCE, 'Early Delegate', '7702000004', now()->subDays(3));

        Livewire::actingAs($this->admin())
            ->test(ListConferenceRsvps::class)
            ->set('activeTab', 'all')
            ->filterTable('rsvp_between', ['period' => 'today'])
            ->assertCanSeeTableRecords([$new])
            ->assertCanNotSeeTableRecords([$old]);
    }

    public function test_the_periods_cover_the_right_days(): void
    {
        $this->travelTo('2026-09-29 15:00:00');

        [$from, $until] = DateRangeFilter::range(['period' => 'yesterday']);
        $this->assertSame('2026-09-28 00:00:00', $from->toDateTimeString());
        $this->assertSame('2026-09-28 23:59:59', $until->toDateTimeString());

        [$from, $until] = DateRangeFilter::range(['period' => 'last_7']);
        $this->assertSame('2026-09-23 00:00:00', $from->toDateTimeString());
        $this->assertNull($until);

        [$from, $until] = DateRangeFilter::range(['period' => 'custom', 'from' => '2026-09-01', 'until' => '2026-09-10']);
        $this->assertSame('2026-09-01 00:00:00', $from->toDateTimeString());
        $this->assertSame('2026-09-10 23:59:59', $until->toDateTimeString());

        $this->assertSame([null, null], DateRangeFilter::range([]));
    }
}
