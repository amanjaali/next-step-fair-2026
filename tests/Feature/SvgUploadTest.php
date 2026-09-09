<?php

namespace Tests\Feature;

use App\Filament\Pages\BrandImages;
use App\Models\Setting;
use App\Models\User;
use App\Support\Svg;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Logos arrive as SVG, and an SVG is a document rather than a picture.
 *
 * It can carry script, event handlers, a reference to somebody else's server and
 * a block of HTML inside a foreignObject — and these files are served from our
 * own origin, so a partner logo is a place to hide stored cross-site scripting.
 * Refusing SVG outright was the first attempt and it was wrong: it is what
 * designers send, and it is the only format that stays sharp in a header. So it
 * is accepted and cleaned as it lands.
 */
class SvgUploadTest extends TestCase
{
    use RefreshDatabase;

    private const HOSTILE = <<<'SVG'
    <?xml version="1.0" encoding="UTF-8"?>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 80" width="200" height="80">
      <script>alert('stored xss')</script>
      <defs><linearGradient id="g"><stop offset="0" stop-color="#14408c"/></linearGradient></defs>
      <rect width="200" height="80" fill="url(#g)" onload="alert(1)"/>
      <image href="https://evil.example.com/pixel.png" x="0" y="0" width="10" height="10"/>
      <foreignObject width="100" height="50"><body xmlns="http://www.w3.org/1999/xhtml">HTML</body></foreignObject>
      <a xlink:href="javascript:alert(3)"><text x="16" y="48">KSA</text></a>
    </svg>
    SVG;

    /* --------------------------------------------------- what is taken out -- */

    public function test_script_and_handlers_and_outside_references_are_removed(): void
    {
        $clean = Svg::clean(self::HOSTILE);

        foreach (['script', 'onload', 'foreignObject', 'evil.example.com', 'javascript:'] as $attack) {
            $this->assertStringNotContainsString($attack, $clean, "{$attack} survived the filter");
        }
    }

    /** A filter that eats the logo is no use: the drawing has to come through. */
    public function test_the_drawing_survives(): void
    {
        $clean = Svg::clean(self::HOSTILE);

        $this->assertStringContainsString('<linearGradient', $clean);
        $this->assertStringContainsString('stop-color="#14408c"', $clean);
        $this->assertStringContainsString('<rect', $clean);
        $this->assertStringContainsString('fill="url(#g)"', $clean);
        $this->assertStringContainsString('KSA', $clean);
    }

    /**
     * Camel-cased element names are the trap here.
     *
     * linearGradient, clipPath, feGaussianBlur — compared against a lowercase
     * allowlist they match; against a camel-case one they match nothing, and the
     * gradient quietly disappears from every logo.
     */
    public function test_camel_cased_elements_are_kept(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><defs><clipPath id="c"><rect width="10" height="10"/></clipPath>'
            .'<radialGradient id="r"><stop offset="0" stop-color="#fff"/></radialGradient></defs>'
            .'<g clip-path="url(#c)"><circle cx="5" cy="5" r="5" fill="url(#r)"/></g></svg>';

        $clean = Svg::clean($svg);

        $this->assertStringContainsString('<clipPath', $clean);
        $this->assertStringContainsString('<radialGradient', $clean);
        $this->assertStringContainsString('<circle', $clean);
    }

    public function test_a_file_that_is_not_an_svg_is_refused(): void
    {
        $this->assertSame('', Svg::clean('<html><body><script>alert(1)</script></body></html>'));
        $this->assertSame('', Svg::clean('not markup at all'));
        $this->assertSame('', Svg::clean(''));
    }

    /** An external entity is how a file on the server gets read out. */
    public function test_an_entity_cannot_reach_into_the_filesystem(): void
    {
        $xxe = '<?xml version="1.0"?><!DOCTYPE svg [<!ENTITY xxe SYSTEM "file:///etc/passwd">]>'
            .'<svg xmlns="http://www.w3.org/2000/svg"><text>&xxe;</text></svg>';

        $clean = Svg::clean($xxe);

        $this->assertStringNotContainsString('root:', $clean);
        $this->assertStringNotContainsString('DOCTYPE', $clean);
    }

    /* ------------------------------------------------- through the dashboard */

    /**
     * A partner mark is refused as SVG, on purpose.
     *
     * It has to be drawn onto the badge picture as well as shown on a page, and
     * the renderer that composes that badge cannot read SVG on most machines.
     * Accepting one produces a logo that is perfect everywhere except the thing
     * three thousand people are sent, which is the worst place to be wrong.
     */
    public function test_a_partner_mark_will_not_take_an_svg(): void
    {
        $this->seed();
        Storage::fake('public');

        $this->actingAs(User::where('email', 'editor@nextstepfair.com')->firstOrFail());

        Livewire::test(BrandImages::class)
            ->set('data.brand.ksa', UploadedFile::fake()->createWithContent('ksa.svg', self::HOSTILE))
            ->call('save')
            ->assertHasErrors('data.brand.ksa');

        $this->assertArrayNotHasKey('ksa', Setting::get('brand_images', []));
    }

    /**
     * The Next Step logo itself still takes an SVG — it is only drawn by
     * browsers, where SVG is the better file.
     */
    public function test_a_logo_uploaded_as_svg_is_stored_clean(): void
    {
        $this->seed();
        Storage::fake('public');

        $this->actingAs(User::where('email', 'editor@nextstepfair.com')->firstOrFail());

        Livewire::test(BrandImages::class)
            ->set('data.brand.logo_light', UploadedFile::fake()->createWithContent('logo.svg', self::HOSTILE))
            ->call('save')
            ->assertHasNoErrors();

        $path = Setting::get('brand_images')['logo_light'] ?? null;

        $this->assertNotNull($path, 'the SVG was refused instead of being cleaned');
        $this->assertStringEndsWith('.svg', $path);

        $stored = Storage::disk('public')->get($path);

        $this->assertStringNotContainsString('<script', $stored);
        $this->assertStringNotContainsString('onload', $stored);
        $this->assertStringContainsString('KSA', $stored);
    }

    /** Nothing but script in it: there is no logo there, so nothing is kept. */
    public function test_an_svg_with_no_drawing_left_is_not_stored(): void
    {
        $this->seed();
        Storage::fake('public');

        $this->actingAs(User::where('email', 'editor@nextstepfair.com')->firstOrFail());

        Livewire::test(BrandImages::class)
            ->set('data.brand.ksa', UploadedFile::fake()->createWithContent('bad.svg', '<html><script>alert(1)</script></html>'))
            ->call('save');

        $this->assertArrayNotHasKey('ksa', Setting::get('brand_images', []));
    }
}
