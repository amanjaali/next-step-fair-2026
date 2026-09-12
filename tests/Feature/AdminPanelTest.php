<?php

namespace Tests\Feature;

use App\Filament\Resources\ConferenceRsvps\Pages\ListConferenceRsvps;
use App\Filament\Resources\ConferenceRsvps\Pages\ViewConferenceRsvp;
use App\Filament\Resources\Registrations\Pages\ListRegistrations;
use App\Filament\Resources\Registrations\Pages\ViewRegistration;
use App\Models\Edition;
use App\Models\EventSession;
use App\Models\Page;
use App\Models\Post;
use App\Models\Registration;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPanelTest extends TestCase
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

    public static function listPages(): array
    {
        return array_map(fn ($path) => [$path], [
            '/admin',
            '/admin/registrations',
            '/admin/conference-rsvps',
            '/admin/messages',
            '/admin/message-templates',
            '/admin/broadcasts',
            '/admin/posts',
            '/admin/speakers',
            '/admin/event-sessions',
            '/admin/organizations',
            '/admin/editions',
            '/admin/pages',
            '/admin/media-albums',
            '/admin/downloads',
            '/admin/sdg-goals',
            '/admin/feature-cards',
            '/admin/halls',
            '/admin/qr-campaigns',
            '/admin/leads',
            '/admin/newsletter-subscribers',
            '/admin/users',
        ]);
    }

    #[DataProvider('listPages')]
    public function test_list_pages_load(string $path): void
    {
        $this->actingAs($this->admin())->get($path)->assertOk();
    }

    public static function createPages(): array
    {
        return array_map(fn ($path) => [$path], [
            '/admin/registrations/create',
            '/admin/posts/create',
            '/admin/speakers/create',
            '/admin/event-sessions/create',
            '/admin/organizations/create',
            '/admin/editions/create',
            '/admin/pages/create',
            '/admin/media-albums/create',
            '/admin/downloads/create',
            '/admin/qr-campaigns/create',
            '/admin/message-templates/create',
            '/admin/broadcasts/create',
            '/admin/users/create',
        ]);
    }

    /** The create forms carry the TinyMCE fields and the per-language tabs. */
    #[DataProvider('createPages')]
    public function test_create_forms_render(string $path): void
    {
        $this->actingAs($this->admin())->get($path)->assertOk();
    }

    public function test_edit_forms_render(): void
    {
        $admin = $this->admin();

        $records = [
            '/admin/posts/'.Post::first()->id.'/edit',
            '/admin/speakers/'.Speaker::first()->id.'/edit',
            '/admin/event-sessions/'.EventSession::first()->id.'/edit',
            '/admin/editions/'.Edition::first()->getRouteKey().'/edit',
            '/admin/pages/'.Page::first()->id.'/edit',
        ];

        foreach ($records as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }

    public function test_registration_detail_page_loads(): void
    {
        $registration = Registration::fair()->firstOrFail();

        $this->actingAs($this->admin())
            ->get('/admin/registrations/'.$registration->id)
            ->assertOk()
            ->assertSee($registration->full_name);
    }

    /**
     * The desk searches by phone number constantly. Phone and e-mail are encrypted,
     * so the table search has to go through the keyed hashes to find anyone.
     */
    public function test_registration_search_finds_an_encrypted_phone_and_email(): void
    {
        $registration = Registration::fair()->whereNotNull('phone_hash')->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(ListRegistrations::class)
            ->searchTable('0'.$registration->phone)   // as typed at the desk, with the trunk zero
            ->assertCanSeeTableRecords([$registration]);

        $rsvp = Registration::conference()->whereNotNull('email_hash')->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(ListConferenceRsvps::class)
            ->set('activeTab', 'all')       // the default tab is the approval queue
            ->searchTable(strtoupper($rsvp->email))
            ->assertCanSeeTableRecords([$rsvp]);
    }

    public function test_check_in_staff_cannot_reach_the_admin_panel(): void
    {
        $gate = User::where('email', 'gate@nextstepfair.com')->firstOrFail();

        $this->actingAs($gate)->get('/admin')->assertForbidden();
    }

    public function test_content_editor_cannot_see_registrations(): void
    {
        $editor = User::where('email', 'editor@nextstepfair.com')->firstOrFail();

        $this->actingAs($editor)->get('/admin/registrations')->assertForbidden();
        $this->actingAs($editor)->get('/admin/posts')->assertOk();
    }

    /** The desk can remove a student, a parent, or a conference RSVP. */
    public static function deletableRegistrations(): array
    {
        return [
            'student' => [[
                'track' => Registration::TRACK_FAIR,
                'type' => Registration::TYPE_STUDENT,
                'full_name' => 'Hemin Karim',
                'phone' => '7701110001',
                'email' => 'hemin.delete@example.com',
            ]],
            'parent' => [[
                'track' => Registration::TRACK_FAIR,
                'type' => Registration::TYPE_PARENT,
                'full_name' => 'Shno Aziz',
                'phone' => '7701110002',
                'email' => 'shno.delete@example.com',
            ]],
            'conference RSVP' => [[
                'track' => Registration::TRACK_CONFERENCE,
                'type' => Registration::TYPE_GOVERNMENT,
                'full_name' => 'Dr. Rezan Kareem',
                'phone' => '7513004412',
                'email' => 'r.kareem.delete@mhe.krd',
                'position' => 'Director',
                'organization' => 'Ministry of Higher Education',
            ]],
        ];
    }

    #[DataProvider('deletableRegistrations')]
    public function test_admin_can_delete_a_registration(array $attributes): void
    {
        $registration = Registration::create(array_merge([
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'days' => [1],
            'confirmed_at' => now(),
        ], $attributes));

        $page = $registration->isConference()
            ? ListConferenceRsvps::class
            : ListRegistrations::class;

        $test = Livewire::actingAs($this->admin())->test($page);

        if ($registration->isConference()) {
            $test->set('activeTab', 'all');
        }

        $test->callTableAction('delete', $registration);

        $this->assertSoftDeleted($registration);
    }

    public function test_admin_can_delete_a_registration_from_its_detail_page(): void
    {
        $student = Registration::create([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'Hemin Karim',
            'phone' => '7701110003',
            'phone_country' => '+964',
            'email' => 'hemin.view-delete@example.com',
            'city' => 'Sulaimani',
            'days' => [1],
            'confirmed_at' => now(),
        ]);

        Livewire::actingAs($this->admin())
            ->test(ViewRegistration::class, ['record' => $student->getRouteKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($student);

        $rsvp = Registration::create([
            'track' => Registration::TRACK_CONFERENCE,
            'type' => Registration::TYPE_GOVERNMENT,
            'status' => Registration::STATUS_PENDING,
            'locale' => 'en',
            'full_name' => 'Dr. Rezan Kareem',
            'phone' => '7513004413',
            'phone_country' => '+964',
            'email' => 'r.kareem.view-delete@mhe.krd',
            'position' => 'Director',
            'organization' => 'Ministry of Higher Education',
            'city' => 'Erbil',
            'days' => [1],
        ]);

        Livewire::actingAs($this->admin())
            ->test(ViewConferenceRsvp::class, ['record' => $rsvp->getRouteKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($rsvp);
    }
}
