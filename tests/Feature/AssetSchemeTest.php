<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Which scheme the site builds its own URLs with.
 *
 * A page can return 200 and still arrive broken: if the HTML is served over http
 * but its stylesheet is linked as https, and the server has no certificate, the
 * browser is refused on port 443 and renders the markup with no styling at all.
 * That is what happened on the first deployment, so the rule now has a test.
 */
class AssetSchemeTest extends TestCase
{
    private function schemeFor(string $appUrl): string
    {
        config(['app.url' => $appUrl]);
        URL::forceScheme(null);
        URL::useOrigin($appUrl);

        (new AppServiceProvider($this->app))->boot();

        return parse_url(asset('build/assets/app.css'), PHP_URL_SCHEME) ?: '';
    }

    public function test_a_site_served_over_plain_http_links_its_assets_over_http(): void
    {
        $this->assertSame('http', $this->schemeFor('http://13.49.238.82'));
    }

    public function test_a_site_with_a_certificate_still_forces_https(): void
    {
        $this->assertSame('https', $this->schemeFor('https://nextstepfair.com'));
    }

    /** The shipped example points at the real domain, which will have a certificate. */
    public function test_the_shipped_example_expects_https(): void
    {
        $this->assertStringContainsString('APP_URL=https://', file_get_contents(base_path('.env.example')));
    }

    /**
     * An uploaded file is addressed relative to whoever is serving the page.
     *
     * Built from APP_URL instead, the dashboard's own upload preview is fetched
     * from an address that may not be listening — a laptop on port 8001, a
     * server whose APP_URL was never corrected. The file uploads, the box sits
     * on "waiting for size" for ever, and it reads as uploading being broken.
     */
    public function test_an_uploaded_file_url_does_not_depend_on_app_url(): void
    {
        config(['app.url' => 'http://a-host-that-is-not-listening:9999']);

        $url = Storage::disk('public')->url('brand/logo.png');

        $this->assertSame('/storage/brand/logo.png', $url);
        $this->assertStringNotContainsString('9999', $url);
    }
}
