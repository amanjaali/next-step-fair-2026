<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\EventSession;
use App\Models\FeatureCard;
use App\Models\MediaItem;
use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\Post;
use App\Models\Registration;
use App\Models\SdgGoal;
use App\Models\Speaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $day = (int) $request->integer('day', 1);
        $day = in_array($day, [1, 2, 3], true) ? $day : 1;

        /*
         * Somebody who has registered does not need to be sold registration. The
         * top of the page becomes what they can do next — their badge, their
         * agenda, the scholarship, the opportunities partners have brought — and
         * the pitch further down stays for everyone else.
         */
        $attendee = Auth::guard('attendee')->user();
        $attendee = $attendee instanceof Registration ? $attendee : null;

        return view('home', [
            'navKey' => 'home',
            'attendee' => $attendee,
            'opportunities' => $attendee
                ? Opportunity::live()->for($attendee)->ranked()->with('organization')->take(3)->get()
                : collect(),
            'savedCount' => $attendee?->savedSessions()->count() ?? 0,
            'sessionCount' => $this->sessionCount(),
            'title' => config('nextstep.event.name').' — '.__('site.common.edition_4'),
            'counters' => $this->counters(),
            'featureCards' => FeatureCard::where('group', 'why_attend')->orderBy('sort')->get(),
            'sdgGoals' => SdgGoal::orderBy('sort')->get(),
            'speakers' => Speaker::published()->forYear(2026)->where('featured', true)->orderBy('sort')->take(8)->get(),
            'agendaDay' => $day,
            'agendaPreview' => EventSession::published()->forYear(2026)->forDay($day)
                ->with('hall')->orderBy('starts_at')->take(4)->get(),
            'universities' => Organization::published()->ofKind(['university', 'institute'])->forYear(2026)
                ->orderBy('sort')->get(),
            'sponsorTiers' => $this->sponsorTiers(),
            'news' => Post::published()->news()->with('category')->latest('published_at')->take(3)->get(),
            'mediaStrip' => MediaItem::where('type', 'photo')->orderByDesc('year')->take(5)->get(),
            'featuredVideo' => MediaItem::where('type', 'video')->orderByDesc('year')->first(),
            'editions' => Edition::where('published', true)->orderBy('year')->get(),
        ]);
    }

    /**
     * The live counters bar. Registered attendees is a real count, cached for a
     * minute so the home page never queries it once per visitor during a rush.
     */
    private function counters(): array
    {
        $registered = Cache::remember('ns.counters.registered', now()->addMinute(), function () {
            return Registration::active()->count();
        });

        $universities = Cache::remember('ns.counters.universities', now()->addHour(), function () {
            return Organization::published()->ofKind(['university', 'institute'])->forYear(2026)->count();
        });

        $sessions = $this->sessionCount();

        return [
            ['value' => ns_days_until(), 'label' => __('site.home.counters.days_until')],
            ['value' => $universities, 'label' => __('site.home.counters.universities')],
            ['value' => number_format($registered), 'label' => __('site.home.counters.registered')],
            ['value' => $sessions, 'label' => __('site.home.counters.sessions')],
        ];
    }

    /** Bookable sessions across the three days — the counter and the card share it. */
    private function sessionCount(): int
    {
        return Cache::remember('ns.counters.sessions', now()->addHour(), function () {
            return EventSession::published()->forYear(2026)->where('bookable', true)->count();
        });
    }

    /** Tiered logo wall: strategic → institutional → sponsors → media. */
    private function sponsorTiers(): array
    {
        $byKind = Organization::published()->forYear(2026)
            ->whereIn('kind', ['strategic', 'supporter', 'sponsor', 'media'])
            ->orderBy('sort')->get()->groupBy('kind');

        return [
            [
                'tier' => __('site.pages.sponsors.strategic'),
                'note' => __('site.footer.partnership_label'),
                'height' => '110px', 'width' => '190px',
                'items' => $byKind->get('strategic', collect()),
            ],
            [
                'tier' => __('site.pages.sponsors.kicker'),
                'note' => __('site.home.partners_title'),
                'height' => '86px', 'width' => '150px',
                'items' => $byKind->get('supporter', collect()),
            ],
            [
                'tier' => __('site.nav.sponsors'),
                'note' => __('site.pages.sponsors.kicker'),
                'height' => '72px', 'width' => '128px',
                'items' => $byKind->get('sponsor', collect()),
            ],
            [
                'tier' => __('site.common.press'),
                'note' => __('site.common.press'),
                'height' => '64px', 'width' => '112px',
                'items' => $byKind->get('media', collect()),
            ],
        ];
    }
}
