<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Services\BadgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
