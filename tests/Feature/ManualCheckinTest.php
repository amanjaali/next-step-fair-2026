<?php

namespace Tests\Feature;

use App\Models\CheckIn;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Checking someone in from the search, which is what the desk falls back to
 * when a badge will not scan — a lost phone, a dead screen, a family sharing
 * one number. It is the busiest path at the gate and had no test at all.
 */
class ManualCheckinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['nextstep.scanner.secret_key' => 'gate-secret-key']);
    }

    public function test_staff_can_check_someone_in_from_the_search(): void
    {
        $registration = $this->confirmed();

        $this->actingAs($this->staff())
            ->postJson("/checkin/manual/{$registration->id}", ['day' => 1, 'gate' => 'A'])
            ->assertOk()
            ->assertJsonPath('state', 'valid');

        $this->assertSame(1, CheckIn::where('registration_id', $registration->id)->count());
    }

    public function test_the_key_gated_scanner_can_too(): void
    {
        $registration = $this->confirmed();

        $this->postJson("/s/gate-secret-key/manual/{$registration->id}", ['day' => 1, 'gate' => 'A'])
            ->assertOk()
            ->assertJsonPath('state', 'valid');

        $this->assertSame(1, CheckIn::where('registration_id', $registration->id)->count());
    }

    public function test_a_wrong_key_cannot_check_anybody_in(): void
    {
        $registration = $this->confirmed();

        $this->postJson("/s/not-the-key/manual/{$registration->id}", ['day' => 1])
            ->assertNotFound();

        $this->assertSame(0, CheckIn::where('registration_id', $registration->id)->count());
    }

    /** Everyone on a shared number can be checked in, one after another. */
    public function test_a_family_on_one_number_all_get_through(): void
    {
        $family = collect(['Aram Hiwa Salih', 'Dilan Hiwa Salih', 'Hiwa Salih Ahmad'])
            ->map(fn (string $name) => Registration::create([
                'track' => Registration::TRACK_FAIR,
                'type' => Registration::TYPE_STUDENT,
                'status' => Registration::STATUS_CONFIRMED,
                'locale' => 'en',
                'full_name' => $name,
                'phone' => '7701234567',
                'phone_country' => '+964',
                'city' => 'Sulaimani',
                'days' => [1, 2, 3],
                'confirmed_at' => now(),
                'badge_generated_at' => now(),
            ]));

        $staff = $this->staff();

        $this->actingAs($staff)
            ->getJson('/checkin/search?q=7701234567')
            ->assertOk()
            ->assertJsonCount(3, 'results');

        foreach ($family as $member) {
            $this->actingAs($staff)
                ->postJson("/checkin/manual/{$member->id}", ['day' => 1, 'gate' => 'A'])
                ->assertOk()
                ->assertJsonPath('state', 'valid');
        }

        $this->assertSame(3, CheckIn::whereIn('registration_id', $family->pluck('id'))->count());
    }

    private function staff(): User
    {
        return User::where('email', 'gate@nextstepfair.com')->firstOrFail();
    }

    private function confirmed(): Registration
    {
        return Registration::create([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'Rozh Karwan Aziz',
            'phone' => '7709990123',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'days' => [1, 2, 3],
            'confirmed_at' => now(),
            'badge_generated_at' => now(),
        ]);
    }
}
