<?php

namespace App\Http\Controllers;

use App\Models\Booth;
use App\Models\Hall;
use App\Models\Organization;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DirectoryController extends Controller
{
    public function universities(Request $request): View
    {
        return $this->directory('universities');
    }

    public function exhibitors(Request $request): View
    {
        return $this->directory('exhibitors');
    }

    private function directory(string $tab): View
    {
        $universities = Organization::published()->ofKind(['university', 'institute'])
            ->forYear(2026)->orderBy('sort')->get();
        $exhibitors = Organization::published()->ofKind('exhibitor')
            ->forYear(2026)->orderBy('sort')->get();

        return view('directory.index', [
            'navKey' => 'universities',
            'title' => __('site.pages.directory.title').' — '.config('nextstep.event.name'),
            'tab' => $tab,
            'universities' => $universities,
            'exhibitors' => $exhibitors,
            'list' => $tab === 'universities' ? $universities : $exhibitors,
            'halls' => Hall::whereIn('code', ['A', 'B', 'C'])->orderBy('sort')->get(),
        ]);
    }

    /** The dedicated floor plan: clickable halls, zone key and booth list. */
    public function floorPlan(Request $request): View
    {
        $code = strtoupper($request->string('hall')->toString() ?: 'A');
        $halls = Hall::orderBy('sort')->get()->keyBy('code');
        $hall = $halls[$code] ?? $halls['A'];

        return view('directory.floorplan', [
            'navKey' => 'universities',
            'title' => __('site.pages.floorplan.title').' — '.config('nextstep.event.name'),
            'halls' => $halls,
            'hall' => $hall->load('booths'),
            'servicePoints' => ($halls['S'] ?? null)?->booths ?? collect(),
            'boothCount' => Booth::count(),
        ]);
    }

    public function partners(Request $request): View
    {
        return $this->sponsors($request);
    }

    public function sponsors(Request $request): View
    {
        $all = Organization::published()->forYear(2026)
            ->whereIn('kind', ['strategic', 'supporter', 'sponsor', 'media'])
            ->orderBy('sort')->get();

        $sponsors = $all->where('kind', 'sponsor');

        $sections = [
            [
                'tier' => __('site.pages.sponsors.kicker'),
                'accent' => '#2C4BE0',
                'note' => __('site.footer.partnership_line'),
                'cols' => 5, 'logoHeight' => '92px',
                'items' => $all->where('kind', 'supporter'),
            ],
            [
                'tier' => 'Platinum',
                'accent' => '#B64698',
                'note' => __('site.pages.sponsors.become_p1', ['count' => 32]),
                'cols' => 3, 'logoHeight' => '110px',
                'items' => $sponsors->where('tier', 'platinum'),
            ],
            [
                'tier' => 'Gold & silver',
                'accent' => '#B64698',
                'note' => __('site.home.partners_title'),
                'cols' => 4, 'logoHeight' => '84px',
                'items' => $sponsors->whereIn('tier', ['gold', 'silver']),
            ],
            [
                'tier' => __('site.common.press'),
                'accent' => '#4A4B4D',
                'note' => __('site.common.press'),
                'cols' => 4, 'logoHeight' => '76px',
                'items' => $all->where('kind', 'media'),
            ],
        ];

        return view('directory.sponsors', [
            'navKey' => 'sponsors',
            'title' => __('site.pages.sponsors.title').' — '.config('nextstep.event.name'),
            'strategic' => $all->where('kind', 'strategic'),
            'sections' => $sections,
            'stats' => [
                ['value' => $all->count(), 'label' => __('site.pages.sponsors.kicker')],
                ['value' => $all->where('since_year', '<=', 2025)->count(), 'label' => __('site.common.attended')],
                ['value' => Organization::published()->ofKind('exhibitor')->forYear(2026)->count(), 'label' => __('site.pages.directory.exhibitors_tab', ['count' => ''])],
                ['value' => '0 IQD', 'label' => __('site.common.free_entry')],
            ],
            'tierTable' => Setting::get('sponsor_tier_table', ['head' => [], 'rows' => []]),
        ]);
    }
}
