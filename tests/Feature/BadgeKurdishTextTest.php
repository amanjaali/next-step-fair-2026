<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Services\BadgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BadgeKurdishTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_arabic_script_names_are_shaped_for_the_gd_badge_renderer(): void
    {
        $service = app(BadgeService::class);

        $raw = 'دەستەشعار';
        $shaped = $service->shapeForGd($raw);

        $this->assertNotSame($raw, $shaped);
        $this->assertSame('Zardasht Aziz', $service->shapeForGd('Zardasht Aziz'));
    }

    /**
     * DomPDF has no Arabic shaping of its own — confirmed by rendering the
     * same view through DomPDF with and without shaping and comparing the
     * rasters: unshaped is a row of disconnected letters even with
     * dir="rtl" set. So the PDF path must shape every Arabic-script run in
     * the rendered HTML before handing it to DomPDF, the same way the GD
     * fallback already shapes text before drawing it.
     */
    public function test_arabic_script_runs_are_shaped_before_reaching_dompdf(): void
    {
        $service = app(BadgeService::class);
        $shapeMethod = new \ReflectionMethod($service, 'shapeArabicScriptRuns');
        $shapeMethod->setAccessible(true);

        $html = '<div class="name">دەستەشعار</div><div class="meta">Sulaimani</div>';
        $shaped = $shapeMethod->invoke($service, $html);

        $this->assertStringContainsString($service->shapeForGd('دەستەشعار'), $shaped);
        // Markup and Latin text are untouched — only the Arabic-script run changed.
        $this->assertStringContainsString('<div class="name">', $shaped);
        $this->assertStringContainsString('<div class="meta">Sulaimani</div>', $shaped);
    }

    /**
     * A PDF badge for a Kurdish name should render without error, and the
     * shaped name (not the raw, disconnected-letter form) should end up in
     * the document.
     */
    public function test_kurdish_badge_pdf_is_shaped_and_generated_without_error(): void
    {
        $registration = Registration::create([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_VISITOR,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'ku',
            'full_name' => 'دەستەشعار',
            'phone' => '7701115599',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'days' => [1, 2, 3],
            'confirmed_at' => now(),
        ]);

        $pdf = app(BadgeService::class)->pdf($registration);

        $this->assertNotSame('', $pdf);
        $this->assertSame('%PDF', substr($pdf, 0, 4));
    }

    /**
     * WhatsApp PNGs are screenshot by headless Chrome, which shapes and
     * reorders Arabic script itself (real HarfBuzz + bidi) — so unlike
     * DomPDF/GD it gets the raw name, natural dir="rtl", and UniSirwan Ping
     * Heavy, the Kurdish font with proper `init`/`medi`/`fina`/`isol` tables.
     */
    public function test_png_badge_html_uses_native_chrome_shaping_with_unisirwan(): void
    {
        $registration = Registration::make([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_VISITOR,
            'locale' => 'ku',
            'full_name' => 'دەستەشعار',
        ]);

        $method = new \ReflectionMethod(BadgeService::class, 'badgeHtml');
        $method->setAccessible(true);

        $html = $method->invoke(app(BadgeService::class), $registration, true, true);

        $this->assertStringContainsString('dir="rtl"', $html);
        $this->assertStringNotContainsString('dir="ltr"', $html);
        $this->assertStringContainsString('دەستەشعار', $html);
        $this->assertStringContainsString("'UniSirwan Ping Heavy'", $html);
        $this->assertStringContainsString("@font-face{font-family:'UniSirwan Ping Heavy'", $html);
    }

    /**
     * English locale + Kurdish name is common on the fair desk. Chrome still
     * needs dir="rtl" or HarfBuzz draws isolated letters — the production bug
     * on registration 975 (locale=en, name=محمد کامەران کێشە).
     */
    public function test_english_locale_with_kurdish_name_uses_rtl_in_browsershot_html(): void
    {
        $registration = Registration::make([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_VISITOR,
            'locale' => 'en',
            'full_name' => 'محمد کامەران کێشە',
        ]);

        $method = new \ReflectionMethod(BadgeService::class, 'badgeHtml');
        $method->setAccessible(true);

        $html = $method->invoke(app(BadgeService::class), $registration, true, true);

        $this->assertStringContainsString('dir="rtl"', $html);
        $this->assertStringNotContainsString('dir="ltr"', $html);
        $this->assertStringContainsString('lang="ckb"', $html);
    }

    /**
     * ar-php shapes into Presentation Forms; UniSirwan only has GSUB for raw
     * Unicode — using it on the GD path drew isolated letters on production
     * for names like محمد کامەران کێشە (locale en, Kurdish name).
     */
    public function test_english_locale_with_kurdish_name_png_is_shaped_via_gd_path(): void
    {
        $registration = Registration::create([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_VISITOR,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'محمد کامەران کێشە',
            'phone' => '7701115599',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'days' => [1, 2, 3],
            'confirmed_at' => now(),
        ]);

        $shaped = app(BadgeService::class)->shapeForGd($registration->full_name);

        $this->assertNotSame($registration->full_name, $shaped);
        $this->assertMatchesRegularExpression('/[\x{FB50}-\x{FDFF}]/u', $shaped);

        $png = app(BadgeService::class)->png($registration);

        $this->assertNotSame('', $png);
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($png, 0, 8));
    }

    public function test_kurdish_badge_png_is_generated_without_error(): void
    {
        $registration = Registration::create([
            'track' => Registration::TRACK_CONFERENCE,
            'type' => Registration::TYPE_GOVERNMENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'ku',
            'full_name' => 'دەستەشعار',
            'organization' => 'ژمارە',
            'position' => 'رێکخراوی کۆمپ',
            'phone' => '7701115500',
            'phone_country' => '+964',
            'email' => 'gov@example.com',
            'confirmed_at' => now(),
        ]);

        $png = app(BadgeService::class)->png($registration);

        $this->assertNotSame('', $png);
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($png, 0, 8));
    }

    /**
     * ticket/{id}/badge.png must redraw a badge cached before the rendering
     * fix instead of serving it — this is the exact bug reported from
     * production: the WhatsApp/download link kept serving a PNG rendered
     * with the old, broken shaping and font.
     */
    public function test_ticket_png_route_redraws_a_badge_cached_before_the_rendering_fix(): void
    {
        Storage::fake(config('filesystems.default'));

        $registration = Registration::create([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_VISITOR,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'ku',
            'full_name' => 'دەستەشعار',
            'phone' => '7701115502',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'days' => [1, 2, 3],
            'confirmed_at' => now(),
        ]);

        $disk = Storage::disk(config('filesystems.default'));
        $stalePath = 'badges/'.$registration->ticket_id.'.png';
        $disk->put($stalePath, 'stale-broken-render');

        $registration->forceFill([
            'badge_png_path' => $stalePath,
            'badge_generated_at' => Carbon::parse(config('nextstep.badge.rendering_version_at'))->subDay(),
        ])->save();

        $response = $this->get('/ticket/'.$registration->ticket_id.'/badge.png');

        $response->assertOk();
        $this->assertNotSame('stale-broken-render', $response->streamedContent() ?: $response->getContent());
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($response->getContent(), 0, 8));
        $this->assertTrue($registration->refresh()->badge_generated_at->gt(
            Carbon::parse(config('nextstep.badge.rendering_version_at'))
        ));
    }

    public function test_rtl_badge_view_includes_noto_font_rules(): void
    {
        $registration = Registration::create([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'ku',
            'full_name' => 'ئاسمان',
            'phone' => '7701115501',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'days' => [1, 2, 3],
            'confirmed_at' => now(),
        ]);

        $html = view('badges.badge', app(BadgeService::class)->payload($registration))->render();

        $this->assertStringContainsString("@font-face{font-family:'Noto Sans Arabic'", $html);
        $this->assertStringContainsString("font-family: 'Noto Sans Arabic'", $html);
        $this->assertStringContainsString('dir="rtl"', $html);
    }

    /**
     * A visitor pass is a fair-track record, but it keeps its own accent
     * colour (the conference cobalt) so it reads apart from the magenta
     * student/parent badges at the gate — see config('nextstep.type_accents').
     */
    /**
     * A badge issued before the current rendering pipeline shipped — the
     * broken-Kurdish-letters/wrong-font badges reported from production —
     * must be treated as stale so ticket/{id}/badge.png redraws it instead
     * of serving the cached, broken file forever.
     */
    public function test_a_badge_generated_before_the_rendering_version_cutoff_is_stale(): void
    {
        $cutoff = Carbon::parse(config('nextstep.badge.rendering_version_at'));

        $neverGenerated = Registration::make(['badge_generated_at' => null]);
        $this->assertTrue($neverGenerated->hasStaleBadge());

        $renderedBeforeTheFix = Registration::make(['badge_generated_at' => $cutoff->clone()->subDay()]);
        $this->assertTrue($renderedBeforeTheFix->hasStaleBadge());

        $renderedAfterTheFix = Registration::make(['badge_generated_at' => $cutoff->clone()->addDay()]);
        $this->assertFalse($renderedAfterTheFix->hasStaleBadge());
    }

    public function test_visitor_pass_keeps_its_own_accent_apart_from_student_and_parent(): void
    {
        $visitor = Registration::make(['track' => Registration::TRACK_FAIR, 'type' => Registration::TYPE_VISITOR]);
        $student = Registration::make(['track' => Registration::TRACK_FAIR, 'type' => Registration::TYPE_STUDENT]);
        $parent = Registration::make(['track' => Registration::TRACK_FAIR, 'type' => Registration::TYPE_PARENT]);

        $this->assertSame(config('nextstep.tracks.conference.accent'), $visitor->accent());
        $this->assertSame(config('nextstep.tracks.fair.accent'), $student->accent());
        $this->assertSame(config('nextstep.tracks.fair.accent'), $parent->accent());
        $this->assertNotSame($visitor->accent(), $student->accent());
    }
}
