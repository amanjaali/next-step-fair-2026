<?php

namespace Tests\Feature;

use App\Models\HomeTrackPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTrackPointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_shows_seeded_track_points(): void
    {
        $this->seed(\Database\Seeders\SiteContentSeeder::class);

        $this->get('/en')
            ->assertOk()
            ->assertSee('32 universities and institutes')
            ->assertSee('Seminars')
            ->assertSee('Scholarships')
            ->assertSee('Free entry · No appointment needed')
            ->assertSee('Day 1 programme')
            ->assertSee('Themes')
            ->assertSee('KU · AR · EN')
            ->assertSee('Official letter of invitation');
    }

    public function test_editing_a_point_changes_the_home_page(): void
    {
        HomeTrackPoint::create([
            'track' => HomeTrackPoint::TRACK_FAIR,
            'sort' => 1,
            'published' => true,
            'label' => [
                'en' => 'Forty universities on the floor',
                'ku' => 'چل زانکۆ',
                'ar' => 'أربعون جامعة',
            ],
        ]);

        $this->get('/en')->assertOk()->assertSee('Forty universities on the floor');
        $this->get('/ku')->assertOk()->assertSee('چل زانکۆ', false);
        $this->get('/ar')->assertOk()->assertSee('أربعون جامعة', false);
    }

    public function test_unpublished_points_are_hidden(): void
    {
        HomeTrackPoint::create([
            'track' => HomeTrackPoint::TRACK_FAIR,
            'sort' => 1,
            'published' => false,
            'label' => ['en' => 'Hidden bullet', 'ku' => 'شاردراو', 'ar' => 'مخفي'],
        ]);

        HomeTrackPoint::create([
            'track' => HomeTrackPoint::TRACK_FAIR,
            'sort' => 2,
            'published' => true,
            'label' => ['en' => 'Visible bullet', 'ku' => 'دیار', 'ar' => 'ظاهر'],
        ]);

        $this->get('/en')
            ->assertOk()
            ->assertSee('Visible bullet')
            ->assertDontSee('Hidden bullet');
    }

    public function test_empty_table_falls_back_to_shipped_wording(): void
    {
        $this->assertSame(0, HomeTrackPoint::count());

        $this->get('/en')
            ->assertOk()
            ->assertSee('32 universities and institutes')
            ->assertSee('Day 1 programme');
    }

    public function test_content_editors_can_open_the_resource(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $editor = User::where('email', 'editor@nextstepfair.com')->firstOrFail();

        $this->actingAs($editor)
            ->get('/admin/home-track-points')
            ->assertOk();
    }
}
