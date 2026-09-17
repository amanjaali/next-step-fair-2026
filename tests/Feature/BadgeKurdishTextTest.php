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
}
