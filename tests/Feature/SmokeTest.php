<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\InstitutionUser;
use App\Models\MediaAlbum;
use App\Models\MessageTemplate;
use App\Models\Organization;
use App\Models\Post;
use App\Models\Registration;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every page, as every kind of visitor. Nothing may return an error.
 *
 * This exists because a broken page is worse than a missing feature: it is the
 * one thing a visitor cannot work around. The other test classes check that
 * things work correctly; this one checks that nothing explodes.
 *
 * Run it against MySQL as well before a release — see the note on
 * test_no_query_relies_on_sqlite_leniency below for why SQLite alone is not
 * enough to trust.
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public static function publicPages(): array
    {
        return array_map(fn ($p) => [$p], [
            '', 'about', 'fair', 'conference', 'agenda', 'seminars', 'speakers',
            'universities', 'exhibitors', 'floor-plan', 'partners', 'sponsors',
            'news', 'blog', 'media', 'media/photos', 'media/videos', 'archive',
            'sdg', 'reports', 'scholarships', 'privacy', 'terms', 'press-kit',
            'contact', 'exhibit', 'register/fair', 'register/fair?type=parent',
            'register/quick', 'register/conference', 'register/conference?type=official',
            'signin', 'join', 'portal/signin', 'portal/register',
            'agenda?day=2', 'agenda/export.ics', 'agenda/export.pdf',
        ]);
    }

    #[DataProvider('publicPages')]
    public function test_public_pages_open_in_every_language(string $path): void
    {
        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get(trim("/{$locale}/{$path}", '/'))
                ->assertSuccessful();
        }
    }

    public function test_detail_pages_open(): void
    {
        $paths = array_filter([
            ($post = Post::published()->first()) ? 'news/'.$post->slug : null,
            ($speaker = Speaker::first()) ? 'speakers/'.$speaker->slug : null,
            ($edition = Edition::first()) ? 'archive/'.$edition->year : null,
            ($album = MediaAlbum::first()) ? 'media/photos/'.$album->slug : null,
        ]);

        foreach ($paths as $path) {
            $this->get("/en/{$path}")->assertSuccessful();
        }
    }

    /**
     * The attendee area, including the check-in history that reads a column with
     * a name nobody guesses right first time.
     */
    public function test_the_attendee_area_opens_with_real_data(): void
    {
        $student = Registration::fair()->active()->firstOrFail();

        // A check-in on file, so the attendance section actually renders a row
        // rather than the empty state that hides column mistakes.
        DB::table('check_ins')->updateOrInsert(
            ['registration_id' => $student->id, 'day' => 1],
            [
                'checked_in_at' => now(),
                'gate' => 'A',
                'method' => 'scan',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        foreach (['en', 'ku', 'ar'] as $locale) {
            foreach (['me', 'me/agenda', 'me/interests', 'me/matches'] as $path) {
                $this->actingAs($student, 'attendee')
                    ->get("/{$locale}/{$path}")
                    ->assertSuccessful();
            }
        }
    }

    public function test_the_exhibitor_portal_opens(): void
    {
        $organization = Organization::whereIn('kind', ['university', 'institute'])->firstOrFail();
        $staff = InstitutionUser::create([
            'organization_id' => $organization->id,
            'name' => 'Smoke Test',
            'email' => 'smoke@test.edu',
            'role' => InstitutionUser::ROLE_OWNER,
            'locale' => 'en',
        ]);

        foreach (['en', 'ku', 'ar'] as $locale) {
            foreach (['portal', 'portal/profile', 'portal/students', 'portal/leads', 'portal/scanner'] as $path) {
                $this->actingAs($staff, 'institution')->get("/{$locale}/{$path}")->assertSuccessful();
            }
        }
    }

    public static function adminPages(): array
    {
        return array_map(fn ($p) => [$p], [
            'admin', 'admin/insights', 'admin/registrations', 'admin/conference-rsvps',
            'admin/messages', 'admin/message-templates', 'admin/broadcasts', 'admin/posts',
            'admin/speakers', 'admin/event-sessions', 'admin/organizations', 'admin/editions',
            'admin/pages', 'admin/media-albums', 'admin/downloads', 'admin/sdg-goals',
            'admin/feature-cards', 'admin/halls', 'admin/qr-campaigns', 'admin/leads',
            'admin/newsletter-subscribers', 'admin/users',
        ]);
    }

    #[DataProvider('adminPages')]
    public function test_admin_pages_open(string $path): void
    {
        $admin = User::where('email', 'admin@nextstepfair.com')->firstOrFail();

        $this->actingAs($admin)->get('/'.$path)->assertSuccessful();
    }

    public function test_language_neutral_routes_open(): void
    {
        $this->get('/')->assertRedirect('/en');
        $this->get('/checkin/login')->assertSuccessful();

        if ($ticket = Registration::whereNotNull('badge_generated_at')->first()) {
            $this->get('/verify/'.$ticket->ticket_id)->assertSuccessful();
            $this->get('/ticket/'.$ticket->ticket_id.'/qr.svg')->assertSuccessful();
            $this->get('/ticket/'.$ticket->ticket_id.'/calendar.ics')->assertSuccessful();
        }
    }

    /**
     * Every WhatsApp message the application can send has real copy behind it.
     *
     * A missing key does not fail: the dispatcher falls back to `__()`, which
     * returns the key itself, so the registrant receives the literal string
     * "notifications.whatsapp.registration_confirmed_visitor". That is exactly how
     * the visitor pass shipped its badge message before this test existed.
     */
    public function test_every_whatsapp_template_has_copy_in_every_language(): void
    {
        $keys = [
            'otp',
            'registration_confirmed_student',
            'registration_confirmed_parent',
            'registration_confirmed_visitor',
            'event_reminder_3days',
            'event_reminder_1day',
            'day_of_directions',
            'session_reminder',
            'post_event_thankyou_survey',
        ];

        foreach ($keys as $key) {
            $this->assertNotNull(
                config("whatsapp.templates.{$key}"),
                "whatsapp.templates.{$key} is missing, so nothing can be submitted to Meta for it."
            );

            foreach (['en', 'ku', 'ar'] as $locale) {
                $line = __("notifications.whatsapp.{$key}", [], $locale);

                $this->assertNotSame(
                    "notifications.whatsapp.{$key}",
                    $line,
                    "notifications.whatsapp.{$key} has no {$locale} copy — recipients would be sent the key."
                );

                $this->assertTrue(
                    MessageTemplate::where('key', $key)->where('channel', 'whatsapp')->where('locale', $locale)->exists(),
                    "No {$locale} WhatsApp template row for {$key}; the admin cannot edit or resubmit it."
                );
            }
        }
    }

    /**
     * SQLite accepts an unknown column in ORDER BY by treating it as a string
     * literal; MySQL rejects it. That difference hid a real error on the profile
     * page through an entire test suite, so the column names the application
     * orders by are asserted directly rather than trusted to the query engine.
     */
    public function test_no_query_relies_on_sqlite_leniency(): void
    {
        $columns = [
            'check_ins' => ['checked_in_at', 'day', 'gate', 'method'],
            'interactions' => ['occurred_at', 'type', 'follow_up'],
            'matches' => ['score', 'computed_at'],
            'qr_scans' => ['scanned_at'],
            'registrations' => ['degree_level', 'budget_band', 'share_with_institutions'],
            'organizations' => ['tuition_min', 'min_grade_band', 'claim_status'],
        ];

        foreach ($columns as $table => $expected) {
            foreach ($expected as $column) {
                $this->assertTrue(
                    \Schema::hasColumn($table, $column),
                    "{$table}.{$column} is referenced by the application but does not exist."
                );
            }
        }
    }
}
