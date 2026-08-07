<?php

namespace App\Http\Controllers;

use App\Models\Download;
use App\Models\EventSession;
use App\Models\Page;
use App\Models\SdgGoal;
use App\Models\Setting;
use App\Models\Speaker;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.standard', $this->page('about', 'about'));
    }

    public function fair(): View
    {
        return view('pages.fair', $this->page('fair', 'fair') + [
            'sessions' => EventSession::published()->forYear(2026)->where('track', 'fair')
                ->with('hall')->orderBy('day')->orderBy('starts_at')->take(6)->get(),
        ]);
    }

    public function conference(): View
    {
        return view('pages.conference', $this->page('conference', 'conference') + [
            'sessions' => EventSession::published()->forYear(2026)->conference()
                ->with(['hall', 'speakers'])->orderBy('starts_at')->get(),
            'speakers' => Speaker::published()->forYear(2026)->where('track', 'conference')
                ->orderBy('sort')->get(),
            'track' => 'conference',
        ]);
    }

    public function sdg(): View
    {
        return view('pages.sdg', [
            'navKey' => 'sdg',
            'title' => __('site.pages.sdg.title').' — '.config('nextstep.event.name'),
            'goals' => SdgGoal::orderBy('sort')->get(),
            'greenPractices' => $this->localisedList(Setting::get('green_practices', [])),
            'reports' => Download::where('published', true)->whereIn('group', ['report'])->orderBy('sort')->get(),
        ]);
    }

    public function reports(): View
    {
        return view('pages.reports', [
            'navKey' => 'archive',
            'title' => __('site.pages.reports.title').' — '.config('nextstep.event.name'),
            'reports' => Download::where('published', true)->where('group', 'report')->orderBy('sort')->get(),
        ]);
    }

    public function scholarships(): View
    {
        return view('pages.scholarships', $this->page('scholarships', 'scholarships'));
    }

    public function privacy(): View
    {
        return view('pages.legal', $this->page('privacy', 'privacy') + ['isPolicy' => true]);
    }

    public function terms(): View
    {
        return view('pages.legal', $this->page('terms', 'terms') + ['isPolicy' => true]);
    }

    public function pressKit(): View
    {
        return view('pages.legal', $this->page('press', 'press') + [
            'isPolicy' => false,
            'pressAssets' => Download::where('published', true)->where('group', 'press')->orderBy('sort')->get(),
            'pressFacts' => $this->localisedFacts(Setting::get('press_facts', [])),
        ]);
    }

    /** @return array<string, mixed> */
    private function page(string $key, string $navKey): array
    {
        $page = Page::where('key', $key)->where('published', true)->firstOrFail();

        return [
            'navKey' => $navKey,
            'page' => $page,
            'title' => $page->t('title').' — '.config('nextstep.event.name'),
            'description' => strip_tags($page->t('standfirst')),
            'sections' => $page->localisedSections(),
            'aside' => $page->localisedAside(),
        ];
    }

    /** Settings hold {en: [...], ku: [...], ar: [...]}; pick the current language. */
    private function localisedList(array $value): array
    {
        return $value[app()->getLocale()] ?? $value[config('app.fallback_locale')] ?? [];
    }

    private function localisedFacts(array $facts): array
    {
        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale');

        return collect($facts)->map(fn ($fact) => [
            'k' => is_array($fact['k'] ?? null) ? ($fact['k'][$locale] ?? $fact['k'][$fallback] ?? '') : ($fact['k'] ?? ''),
            'v' => $fact['v'] ?? '',
        ])->all();
    }
}
