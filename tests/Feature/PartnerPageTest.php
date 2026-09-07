<?php

namespace Tests\Feature;

use App\Filament\Resources\Partners\PartnerResource;
use App\Models\Organization;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The page behind a partnership mark.
 *
 * The ministry's logo sits at the top of every page and the students'
 * association beside it, and both were pictures that went nowhere — a
 * partnership asserted and never explained. The rule these tests hold to is
 * that a mark links to a page when there is a page, and stays a picture when
 * there is not: an empty page behind a ministry's logo is worse than no link.
 */
class PartnerPageTest extends TestCase
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

    private function mohe(): Organization
    {
        return Organization::where('slug', 'mohe')->firstOrFail();
    }

    /* ---------------------------------------------------------- the page --- */

    public function test_a_partner_has_a_page_of_their_own(): void
    {
        $mohe = $this->mohe();

        $this->get('/en/partners/mohe')
            ->assertOk()
            ->assertSee($mohe->t('name'))
            ->assertSee(__('site.pages.partner.about'))
            ->assertSee(__('site.pages.partner.partnership'))
            ->assertSee('accredits programmes', false);
    }

    public function test_it_reads_in_all_three_languages(): void
    {
        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->get('/'.$locale.'/partners/ksa')
                ->assertOk()
                ->assertSee(__('site.pages.partner.partnership', [], $locale), false);
        }

        $this->get('/ku/partners/ksa')->assertSee('کۆمەڵەی خوێندکارانی کوردستان', false);
        $this->get('/ar/partners/ksa')->assertSee('جمعية طلبة كوردستان', false);
    }

    /** Website, email and phone are things to press, not things to copy out. */
    public function test_the_contact_details_are_links(): void
    {
        $this->mohe()->update([
            'website' => 'https://mhe-krg.org',
            'public_email' => 'info@example.gov',
            'public_phone' => '0770 123 4567',
        ]);

        $page = $this->get('/en/partners/mohe')->assertOk();

        $page->assertSee('href="https://mhe-krg.org"', false);
        $page->assertSee('href="mailto:info@example.gov"', false);
        $page->assertSee('href="tel:07701234567"', false);
        $page->assertSee('0770 123 4567');
    }

    public function test_a_partner_with_nothing_written_has_no_page(): void
    {
        $blank = Organization::create([
            'slug' => 'a-new-partner',
            'kind' => Organization::KIND_STRATEGIC,
            'name' => ['en' => 'A new partner'],
            'published' => true,
        ]);

        $this->assertFalse($blank->hasPartnerPage());
        $this->assertNull($blank->partnerUrl());
        $this->get('/en/partners/a-new-partner')->assertNotFound();
    }

    /** A university is an exhibitor, not a partnership. */
    public function test_the_exhibitor_directory_does_not_get_partner_pages(): void
    {
        $university = Organization::create([
            'slug' => 'a-university',
            'kind' => Organization::KIND_UNIVERSITY,
            'name' => ['en' => 'A university'],
            'about' => ['en' => '<p>Founded long ago.</p>'],
            'published' => true,
        ]);

        $this->assertFalse($university->hasPartnerPage());
        $this->get('/en/partners/a-university')->assertNotFound();
    }

    public function test_an_unpublished_partner_is_not_reachable(): void
    {
        $this->mohe()->update(['published' => false]);

        $this->get('/en/partners/mohe')->assertNotFound();
    }

    /* ------------------------------------------------------- the marks ----- */

    public function test_the_marks_in_the_header_and_the_footer_link_to_the_partner(): void
    {
        Setting::put('brand_images', ['ksa' => 'brand/ksa.png'], 'images');

        $html = $this->get('/en')->assertOk()->getContent();

        foreach (['mohe', 'krg', 'ksa'] as $slug) {
            $this->assertStringContainsString(
                'href="'.route('partner', ['locale' => 'en', 'partner' => $slug]).'"',
                $html,
                "the {$slug} mark does not link anywhere",
            );
        }
    }

    /** Emptying the wording takes the link away again, rather than orphaning it. */
    public function test_a_mark_stops_linking_when_its_page_is_emptied(): void
    {
        $this->mohe()->update(['about' => null, 'partnership' => null]);

        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertStringNotContainsString(
            'href="'.route('partner', ['locale' => 'en', 'partner' => 'mohe']).'"',
            $html,
        );

        // The mark itself is still there — it is the link that goes, not the logo.
        $this->assertStringContainsString('/assets/brand/mohe.png', $html);
    }

    public function test_the_partners_page_leads_into_each_partner(): void
    {
        $this->get('/en/sponsors')
            ->assertOk()
            ->assertSee(route('partner', ['locale' => 'en', 'partner' => 'mohe']), false)
            ->assertSee(route('partner', ['locale' => 'en', 'partner' => 'ksa']), false);
    }

    /* --------------------------------------------------- the dashboard ----- */

    public function test_the_partnerships_screen_holds_the_partners_and_not_the_exhibitors(): void
    {
        Organization::create([
            'slug' => 'some-university',
            'kind' => Organization::KIND_UNIVERSITY,
            'name' => ['en' => 'Some university'],
            'published' => true,
        ]);

        $this->actingAs($this->editor())->get(PartnerResource::getUrl('index'))->assertOk();

        $listed = PartnerResource::getEloquentQuery()->pluck('slug')->all();

        $this->assertContains('mohe', $listed);
        $this->assertContains('ksa', $listed);
        $this->assertNotContains('some-university', $listed);
    }

    public function test_the_check_in_staff_cannot_edit_partnerships(): void
    {
        $gate = User::where('email', 'gate@nextstepfair.com')->firstOrFail();

        $this->actingAs($gate)->get(PartnerResource::getUrl('index'))->assertForbidden();
    }

    public function test_what_is_written_in_the_dashboard_is_what_the_page_says(): void
    {
        $this->actingAs($this->editor());

        Livewire::test(PartnerResource::getPages()['edit']->getPage(), [
            'record' => $this->mohe()->getRouteKey(),
        ])
            ->fillForm([
                'partnership' => ['en' => '<p>They chair the accreditation session on day two.</p>'],
                'public_email' => 'partners@example.gov',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/en/partners/mohe')
            ->assertOk()
            ->assertSee('They chair the accreditation session on day two.')
            ->assertSee('partners@example.gov');
    }

    /**
     * The page prints editor-written HTML unescaped, so it goes through the same
     * filter as every other rich field on the site.
     */
    public function test_a_script_pasted_into_a_partner_page_is_not_published(): void
    {
        $this->mohe()->update([
            'about' => ['en' => '<p>Fine</p><script>alert(1)</script><img src=x onerror=alert(1)>'],
        ]);

        $html = $this->get('/en/partners/mohe')->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringContainsString('Fine', $html);
    }
}
