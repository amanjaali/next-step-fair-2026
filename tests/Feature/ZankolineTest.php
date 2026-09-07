<?php

namespace Tests\Feature;

use App\Filament\Pages\ZankolineCentres;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The Zankoline guidance desks.
 *
 * Two things have to hold. The page is complete the day it goes up, from the
 * shipped list, because a page of empty cards is worse than no page. And every
 * word and every desk on it can be changed from the dashboard afterwards, because
 * a phone number that needs a developer is a phone number that stays wrong.
 */
class ZankolineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function editor(): User
    {
        return User::where('email', 'editor@nextstepfair.com')->firstOrFail();
    }

    /* --------------------------------------------------------- the page ----- */

    public function test_the_page_opens_with_the_seven_areas_and_nothing_configured(): void
    {
        $page = $this->get('/en/zankoline')->assertOk();

        foreach (['Sulaymaniyah', 'Erbil', 'Halabja', 'Chamchamal', 'Garmian', 'Raparin', 'Soran'] as $area) {
            $page->assertSee($area);
        }

        $page->assertSee(__('zankoline.title'));
    }

    public function test_it_reads_in_all_three_languages(): void
    {
        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get('/'.$locale.'/zankoline')
                ->assertOk()
                ->assertSee(__('zankoline.title', [], $locale), false)
                ->assertSee(__('zankoline.where.title', [], $locale), false);
        }

        // Each area's own name, not the English one transliterated.
        $this->get('/ku/zankoline')->assertSee('سلێمانی', false);
        $this->get('/ar/zankoline')->assertSee('السليمانية', false);
    }

    /** Every marker has to land inside the panel it is drawn on. */
    public function test_every_centre_is_placed_on_the_map(): void
    {
        foreach (ns_zankoline_centres() as $centre) {
            $this->assertNotNull($centre['x'], $centre['label'].' has no position');
            $this->assertNotNull($centre['y'], $centre['label'].' has no position');
            $this->assertGreaterThan(0, $centre['x']);
            $this->assertLessThan(100, $centre['x']);
            $this->assertGreaterThan(0, $centre['y']);
            $this->assertLessThan(100, $centre['y']);
        }
    }

    /**
     * North is up and east is right, which is the whole claim the map makes.
     * A projection that flips either way would look plausible and be wrong.
     */
    public function test_the_map_is_the_right_way_round(): void
    {
        $centres = collect(ns_zankoline_centres())->keyBy('slug');

        // Soran is the northernmost, Garmian the southernmost.
        $this->assertLessThan($centres['garmian']['y'], $centres['soran']['y']);

        // Halabja is east of Erbil.
        $this->assertGreaterThan($centres['erbil']['x'], $centres['halabja']['x']);
    }

    /* ------------------------------------------------- edited in the dashboard */

    public function test_the_dashboard_list_replaces_the_shipped_one(): void
    {
        Setting::put('zankoline_centres', [[
            'slug' => 'duhok',
            'name' => ['en' => 'Duhok', 'ku' => 'دهۆک', 'ar' => 'دهوك'],
            'address' => ['en' => 'Opposite the university gate'],
            'person' => 'Ranj Ahmed',
            'phone' => '0770 123 4567',
            'lat' => 36.867, 'lng' => 42.988,
        ]], 'zankoline');

        $this->get('/en/zankoline')
            ->assertOk()
            ->assertSee('Duhok')
            ->assertSee('Opposite the university gate')
            ->assertSee('Ranj Ahmed')
            ->assertSee('0770 123 4567')
            ->assertDontSee('Chamchamal');
    }

    public function test_a_centre_with_no_details_says_so_rather_than_showing_an_empty_card(): void
    {
        $this->get('/en/zankoline')
            ->assertOk()
            ->assertSee(__('zankoline.where.soon'));
    }

    /** An empty row is somebody who pressed "add" and changed their mind. */
    public function test_an_unnamed_row_is_not_a_centre(): void
    {
        Setting::put('zankoline_centres', [
            ['slug' => 'real', 'name' => ['en' => 'A real place'], 'lat' => 35.5, 'lng' => 45.4],
            ['slug' => 'blank', 'name' => ['en' => '', 'ku' => '', 'ar' => '']],
        ], 'zankoline');

        $this->assertCount(1, ns_zankoline_centres());
    }

    public function test_edited_wording_replaces_the_shipped_wording(): void
    {
        Setting::put('zankoline_content', [
            'lead' => ['en' => 'Come and see us before you press submit.'],
        ], 'zankoline');

        $this->get('/en/zankoline')
            ->assertOk()
            ->assertSee('Come and see us before you press submit.')
            ->assertDontSee(__('zankoline.lead'));

        // A language nobody filled in still reads as written.
        $this->get('/ar/zankoline')->assertOk()->assertSee(__('zankoline.lead', [], 'ar'), false);
    }

    /* ------------------------------------------------------- the screen ------ */

    public function test_an_editor_can_open_the_screen_and_the_gate_staff_cannot(): void
    {
        $this->actingAs($this->editor())->get('/admin/zankoline-centres')->assertOk();

        $gate = User::where('email', 'gate@nextstepfair.com')->firstOrFail();
        $this->actingAs($gate)->get('/admin/zankoline-centres')->assertForbidden();
    }

    public function test_saving_the_screen_changes_the_public_page(): void
    {
        $this->actingAs($this->editor());

        Livewire::test(ZankolineCentres::class)
            ->fillForm([
                'centres' => [[
                    'slug' => 'kalar',
                    'name' => ['en' => 'Kalar', 'ku' => 'کەلار', 'ar' => 'كلار'],
                    'address' => ['en' => 'Main street, beside the bazaar'],
                    'person' => 'Shilan Aziz',
                    'phone' => '0751 000 1122',
                    'lat' => 34.63,
                    'lng' => 45.32,
                ]],
            ])
            ->call('save');

        $this->get('/en/zankoline')
            ->assertOk()
            ->assertSee('Kalar')
            ->assertSee('Shilan Aziz')
            ->assertSee('Main street, beside the bazaar');
    }

    /* ------------------------------------------------------- finding it ------ */

    public function test_it_is_reachable_from_the_menu_and_the_footer(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();

        // The menu panel and the footer column.
        $this->assertGreaterThanOrEqual(2, substr_count($html, route('zankoline', ['locale' => 'en'])));
        $this->assertStringContainsString(__('zankoline.nav'), $html);
    }
}
