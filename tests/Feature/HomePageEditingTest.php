<?php

namespace Tests\Feature;

use App\Filament\Pages\BrandImages;
use App\Filament\Pages\HomePage;
use App\Models\Organization;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Speaker;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The front page and the logos, edited from the dashboard.
 *
 * The rule that makes this safe to hand to an editor: an empty box means "leave
 * it as written", never "publish nothing". Every test here is either that rule
 * or the thing it protects.
 */
class HomePageEditingTest extends TestCase
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

    /* --------------------------------------------------------- the screens -- */

    public function test_both_screens_open_for_someone_who_edits_content(): void
    {
        $this->actingAs($this->editor())->get('/admin/home-page')->assertOk();
        $this->actingAs($this->editor())->get('/admin/brand-images')->assertOk();
    }

    public function test_they_are_closed_to_the_check_in_staff(): void
    {
        $gate = User::where('email', 'gate@nextstepfair.com')->firstOrFail();

        $this->assertFalse(HomePage::canAccess() && $gate->can('manage-content'));
        $this->assertFalse(BrandImages::canAccess() && $gate->can('manage-content'));
    }

    /* ------------------------------------------------------------- wording -- */

    public function test_what_is_typed_in_the_dashboard_is_what_the_page_shows(): void
    {
        $this->actingAs($this->editor());

        Livewire::test(HomePage::class)
            ->set('data.home_content.about_title.en', 'A heading written by the team')
            ->set('data.home_content.fair_body.en', 'A paragraph written by the team')
            ->call('save')
            ->assertHasNoErrors();

        $this->get('/en')
            ->assertOk()
            ->assertSee('A heading written by the team')
            ->assertSee('A paragraph written by the team');
    }

    public function test_an_empty_box_falls_back_to_the_wording_the_site_shipped_with(): void
    {
        $this->actingAs($this->editor());

        Livewire::test(HomePage::class)->set('data.home_content.about_title.en', 'Temporary')->call('save');
        $this->get('/en')->assertOk()->assertSee('Temporary');

        Livewire::test(HomePage::class)->set('data.home_content.about_title.en', '')->call('save');

        $this->get('/en')
            ->assertOk()
            ->assertSee(__('site.home.about_title'))
            ->assertDontSee('Temporary');
    }

    /** A language left unfilled keeps its original wording, not English. */
    public function test_filling_in_one_language_does_not_empty_the_others(): void
    {
        $this->actingAs($this->editor());

        Livewire::test(HomePage::class)
            ->set('data.home_content.about_title.en', 'Only English was changed')
            ->call('save');

        $this->get('/en')->assertOk()->assertSee('Only English was changed');
        $this->get('/ku')->assertOk()->assertSee(trans('site.home.about_title', [], 'ku'));
    }

    public function test_the_button_wording_can_be_rewritten(): void
    {
        $this->actingAs($this->editor());

        Livewire::test(HomePage::class)
            ->set('data.home_content.cta_fair.en', 'Get your free badge')
            ->call('save');

        $this->get('/en')->assertOk()->assertSee('Get your free badge');
    }

    /* -------------------------------------------------------- photographs -- */

    public function test_the_hero_is_plain_black_until_a_photograph_is_uploaded(): void
    {
        $this->get('/en')->assertOk()->assertDontSee('background-image:url(', false);
    }

    public function test_a_photograph_uploaded_in_the_dashboard_appears_behind_the_hero(): void
    {
        Storage::fake('public');
        $this->actingAs($this->editor());

        Livewire::test(HomePage::class)
            ->set('data.images.home_hero', UploadedFile::fake()->image('hero.jpg', 1600, 900))
            ->call('save')
            ->assertHasNoErrors();

        $stored = Setting::get('site_images')['home_hero'];
        Storage::disk('public')->assertExists($stored);

        $this->get('/en')
            ->assertOk()
            ->assertSee("background-image:url('/storage/{$stored}')", false);
    }

    /** Removing it returns the page to the black it was designed with. */
    public function test_clearing_the_photograph_gives_the_black_hero_back(): void
    {
        Storage::fake('public');
        $this->actingAs($this->editor());

        Livewire::test(HomePage::class)
            ->set('data.images.home_hero', UploadedFile::fake()->image('hero.jpg'))
            ->call('save');
        $this->get('/en')->assertOk()->assertSee('background-image:url(', false);

        Livewire::test(HomePage::class)->set('data.images.home_hero', null)->call('save');
        $this->get('/en')->assertOk()->assertDontSee('background-image:url(', false);
    }

    /* -------------------------------------------------------- event facts -- */

    /**
     * The whole reason these are overlaid on the config: one edit has to reach
     * every place the name is printed, not just the page it was typed on.
     */
    public function test_renaming_the_event_changes_it_everywhere_at_once(): void
    {
        $this->actingAs($this->editor());

        Livewire::test(HomePage::class)
            ->set('data.event.name', 'Next Step Fair 2027')
            ->set('data.event.venue_city', 'Duhok')
            ->call('save');

        $this->bootTheOverlay();

        $this->assertSame('Next Step Fair 2027', config('nextstep.event.name'));
        $this->assertSame('Duhok', config('nextstep.event.venue.city'));

        $this->get('/en')->assertOk()->assertSee('Next Step Fair 2027');
    }

    /** An empty box must never blank a fact the whole site depends on. */
    public function test_an_empty_box_leaves_the_shipped_fact_alone(): void
    {
        $this->actingAs($this->editor());

        Livewire::test(HomePage::class)->set('data.event.name', '')->call('save');

        $this->assertArrayNotHasKey('name', Setting::get('event_overrides', []));
        $this->assertSame('Next Step Fair 2026', config('nextstep.event.name'));
    }

    /* -------------------------------------------------------------- logos -- */

    public function test_the_header_shows_the_shipped_logo_until_one_is_uploaded(): void
    {
        $this->get('/en')->assertOk()->assertSee('/assets/brand/nextstep-transparent-sm.png', false);
    }

    public function test_a_logo_uploaded_in_the_dashboard_replaces_it_on_every_page(): void
    {
        Storage::fake('public');
        $this->actingAs($this->editor());

        Livewire::test(BrandImages::class)
            ->set('data.brand.logo_dark', UploadedFile::fake()->image('logo.png'))
            ->call('save')
            ->assertHasNoErrors();

        $stored = Setting::get('brand_images')['logo_dark'];

        foreach (['/en', '/en/news', '/en/scholarship'] as $page) {
            $this->get($page)
                ->assertOk()
                ->assertSee('/storage/'.$stored, false)
                ->assertDontSee('/assets/brand/nextstep-transparent-sm.png', false);
        }
    }

    /** Clearing a logo must give the header back, not leave a hole in it. */
    public function test_clearing_the_upload_restores_the_shipped_logo(): void
    {
        Storage::fake('public');
        $this->actingAs($this->editor());

        Livewire::test(BrandImages::class)
            ->set('data.brand.logo_dark', UploadedFile::fake()->image('logo.png'))
            ->call('save');

        Livewire::test(BrandImages::class)->set('data.brand.logo_dark', null)->call('save');

        $this->get('/en')->assertOk()->assertSee('/assets/brand/nextstep-transparent-sm.png', false);
    }

    /* ------------------------------------------------------- picture URLs -- */

    /** Every picture URL has to survive the site changing hostname. */
    public function test_no_picture_is_addressed_through_a_hostname(): void
    {
        config(['app.url' => 'https://nextstepfair.com']);

        $speaker = Speaker::query()->firstOrFail();
        $speaker->update(['photo_path' => 'speakers/portrait.jpg']);

        $post = Post::query()->firstOrFail();
        $post->update(['cover_path' => 'posts/cover.jpg']);

        $this->assertSame('/storage/speakers/portrait.jpg', $speaker->photoUrl());
        $this->assertSame('/storage/posts/cover.jpg', $post->coverUrl());
    }

    /**
     * Partner logos come from two places: the ones committed with the site sit
     * under public/assets, the ones uploaded in the dashboard on the public disk.
     * Asking for an uploaded one from /assets is a 404.
     */
    public function test_a_partner_logo_is_served_from_wherever_it_actually_is(): void
    {
        $shipped = Organization::where('logo_path', 'brand/mohe.png')->first();

        if ($shipped) {
            $this->assertSame('/assets/brand/mohe.png', $shipped->logoUrl());
        }

        $uploaded = Organization::create([
            'slug' => 'a-university-that-uploaded-its-logo',
            'kind' => Organization::KIND_UNIVERSITY,
            'name' => ['en' => 'A university that uploaded its logo'],
            'logo_path' => 'logos/some-university.png',
        ]);

        $this->assertSame('/storage/logos/some-university.png', $uploaded->logoUrl());
    }

    /** Re-runs the boot-time overlay against whatever is saved now. */
    private function bootTheOverlay(): void
    {
        $provider = new AppServiceProvider($this->app);
        $method = new \ReflectionMethod($provider, 'applyEditedEventFacts');
        $method->setAccessible(true);
        $method->invoke($provider);
    }
}
