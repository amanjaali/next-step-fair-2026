<?php

namespace Tests\Feature;

use App\Filament\Pages\BrandImages;
use App\Models\Setting;
use App\Models\User;
use App\Support\StorageLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Uploading a partner logo from the dashboard, and being able to see it after.
 *
 * The saving half of this already worked. What did not was the half nobody
 * tested: a file can be uploaded, stored, saved into the setting and rendered
 * into the markup with the right URL, and still be a blank box on every page,
 * because uploads are served through public/storage and that link is created
 * once per install. Miss it and the marks that ship with the site keep working
 * while every uploaded one silently does not — which reads as a broken image
 * file rather than a broken install.
 */
class BrandImageSaveTest extends TestCase
{
    use RefreshDatabase;

    private function editor(): void
    {
        $this->seed();
        $this->actingAs(User::where('email', 'editor@nextstepfair.com')->firstOrFail());
    }

    /* --------------------------------------------------------- the saving -- */

    public function test_a_png_uploaded_for_the_association_is_saved(): void
    {
        $this->editor();
        Storage::fake('public');

        Livewire::test(BrandImages::class)
            ->set('data.brand.ksa', UploadedFile::fake()->image('ksa.png', 400, 160))
            ->call('save')
            ->assertHasNoErrors();

        $path = Setting::get('brand_images')['ksa'] ?? null;

        $this->assertNotNull($path, 'the PNG never reached the setting');
        Storage::disk('public')->assertExists($path);
    }

    /** Filling one slot must not clear the others: they are saved as one value. */
    public function test_uploading_one_mark_does_not_wipe_another(): void
    {
        $this->editor();
        Storage::fake('public');

        Livewire::test(BrandImages::class)
            ->set('data.brand.ksa', UploadedFile::fake()->image('ksa.png', 400, 160))
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(BrandImages::class)
            ->set('data.brand.mohe', UploadedFile::fake()->image('mohe.png', 400, 160))
            ->call('save')
            ->assertHasNoErrors();

        $saved = Setting::get('brand_images');

        $this->assertArrayHasKey('ksa', $saved);
        $this->assertArrayHasKey('mohe', $saved);
    }

    /* ------------------------------------------------- being able to see it -- */

    /** A link left behind by a previous release, aimed at a path that is gone. */
    public function test_a_link_pointing_somewhere_else_is_not_called_healthy(): void
    {
        $link = StorageLink::link();
        $saved = is_link($link) ? readlink($link) : null;

        try {
            @unlink($link);
            @symlink(sys_get_temp_dir(), $link);
            clearstatcache(true, $link);

            $this->assertFalse(StorageLink::isHealthy(), 'a link to the wrong folder passed as healthy');
            $this->assertNotNull(StorageLink::problem());
        } finally {
            @unlink($link);
            if ($saved !== null) {
                @symlink($saved, $link);
            }
            clearstatcache(true, $link);
        }
    }

    /**
     * The repair, which is what an editor actually gets: they open the screen
     * and the site has already put the link back.
     */
    public function test_a_missing_link_is_put_back(): void
    {
        $link = StorageLink::link();
        $saved = is_link($link) ? readlink($link) : null;

        try {
            @unlink($link);
            clearstatcache(true, $link);
            $this->assertFalse(StorageLink::isHealthy());

            $this->assertTrue(StorageLink::repair(), 'the link could not be recreated');
            $this->assertTrue(StorageLink::isHealthy());
        } finally {
            if (! StorageLink::isHealthy() && $saved !== null) {
                @unlink($link);
                @symlink($saved, $link);
            }
            clearstatcache(true, $link);
        }
    }

    /**
     * A real folder where the link should be — a deployment script that copied
     * instead of linking. Repair must refuse rather than delete somebody's
     * files, and must say what it found.
     */
    public function test_a_real_folder_in_the_way_is_reported_rather_than_deleted(): void
    {
        $link = StorageLink::link();
        $saved = is_link($link) ? readlink($link) : null;

        try {
            @unlink($link);
            @mkdir($link, 0o755, true);
            file_put_contents($link.'/keep-me.txt', 'not yours to delete');
            clearstatcache(true, $link);

            $this->assertFalse(StorageLink::repair());
            $this->assertFileExists($link.'/keep-me.txt');
            $this->assertStringContainsString('real folder', (string) StorageLink::problem());
        } finally {
            @unlink($link.'/keep-me.txt');
            @rmdir($link);
            if ($saved !== null) {
                @symlink($saved, $link);
            }
            clearstatcache(true, $link);
        }
    }

    /** Opening the screen repairs it, so an editor never sees the blank box. */
    public function test_opening_the_screen_repairs_the_link(): void
    {
        $this->editor();

        $link = StorageLink::link();
        $saved = is_link($link) ? readlink($link) : null;

        try {
            @unlink($link);
            clearstatcache(true, $link);

            Livewire::test(BrandImages::class);

            $this->assertTrue(StorageLink::isHealthy(), 'the screen did not put the link back');
        } finally {
            if (! StorageLink::isHealthy() && $saved !== null) {
                @unlink($link);
                @symlink($saved, $link);
            }
            clearstatcache(true, $link);
        }
    }

    /**
     * End to end, without a faked disk: upload through the dashboard, then ask
     * for the file over HTTP the way a browser does. This is the assertion that
     * would have caught the live bug — every other one passed throughout.
     */
    public function test_an_uploaded_mark_can_then_be_fetched_over_http(): void
    {
        $this->editor();

        Livewire::test(BrandImages::class)
            ->set('data.brand.ksa', UploadedFile::fake()->image('ksa-http.png', 400, 160))
            ->call('save')
            ->assertHasNoErrors();

        $path = Setting::get('brand_images')['ksa'] ?? null;
        $this->assertNotNull($path);

        try {
            // ns_brand() builds exactly this URL, and the browser asks for it
            // as a plain file — so what matters is that it resolves on disk
            // through public/, which is what the link exists to do.
            $served = public_path('storage/'.ltrim($path, '/'));

            $this->assertFileExists($served, 'the uploaded logo is not reachable through public/storage');
            $this->assertGreaterThan(0, (int) filesize($served));
        } finally {
            Storage::disk('public')->delete($path);
        }
    }
}
