<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Lutka leads the scholarship home's university cards, in every language. */
class ScholarshipHomeOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_lutka_is_the_first_card_in_english_kurdish_and_arabic(): void
    {
        $this->seed();

        // As it is set up live: an Opportunity whose slug does not say "lutka",
        // with Kurdish and Arabic names that use other spellings entirely.
        $organization = Organization::create([
            'slug' => 'peak-institute',
            'kind' => 'university',
            'name' => [
                'en' => 'Lutka Technical and Vocational Institute',
                'ku' => 'پەیمانگەی تەکنیکی و پیشەیی لووتكه',
                'ar' => 'معهد القمة التقني والمهني',
            ],
            'city' => 'Sulaimani',
            'year' => 2026,
        ]);

        $lutka = Opportunity::create([
            'slug' => 'peak-technical-scholarship',
            'kind' => Opportunity::KIND_SCHOLARSHIP,
            'audience' => Opportunity::AUDIENCE_GRADE12,
            'organization_id' => $organization->id,
            'published' => true,
            'title' => ['en' => 'Scholarship'],
            'summary' => ['en' => 'Seats.'],
            'departments' => [['name' => 'Computer Science', 'seats' => 120]],
        ]);

        $card = 'scholarship/universities/opportunity-'.$lutka->slug;
        $this->assertGreaterThan(4, count(ns_scholarship_universities()), 'Lutka must start outside the first four.');

        foreach (['en', 'ku', 'ar'] as $locale) {
            $html = $this->get("/{$locale}/scholarship")->assertOk()->getContent();

            preg_match_all('#scholarship/universities/[^"?]+#', $html, $m);
            $this->assertSame($card, $m[0][0] ?? null, "Lutka is not the first card in {$locale}.");
            $this->assertSame(app()->getLocale(), $locale, 'The page must not leave the locale switched.');
        }
    }
}
