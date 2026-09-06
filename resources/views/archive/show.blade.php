@php $locale = app()->getLocale(); $fallback = config('app.fallback_locale'); @endphp
<x-layouts.site :title="$title" :description="$description" :navKey="$navKey">
    <div class="pb-[clamp(72px,10vw,140px)]">

        {{-- Edition header, dark, with the year tabs and the six-stat band. --}}
        <section class="bg-ink text-white relative overflow-hidden ns-track-rule">
            <div class="ns-wrap pt-[clamp(36px,5vw,52px)]">
                <div class="flex items-baseline gap-[26px] flex-wrap mb-9">
                    <span class="ns-eyebrow !text-white/50">{{ __('site.pages.archive.title') }}</span>
                    <div class="flex gap-[6px] flex-wrap">
                        @foreach ($years as $year)
                            <a href="{{ route('archive.show', $year) }}"
                               @class([
                                   'ns-num font-[family-name:var(--ns-display)] text-sm font-semibold tracking-[0.06em] px-[18px] py-[11px] border',
                                   'bg-white text-ink border-white' => $year === $edition->year,
                                   'bg-transparent text-white/80 border-white/30 hover:text-white' => $year !== $edition->year,
                               ])>{{ $year }}</a>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-14 lg:grid-cols-[1.15fr_1fr] items-end pb-11">
                    <div>
                        <div class="ns-eyebrow !text-magenta mb-[18px]">
                            {{ $edition->t('edition_label') }} · {{ $edition->t('dates_label') }} · {{ $edition->t('venue_label') }}
                        </div>
                        <h1 class="ns-num font-[family-name:var(--ns-display)] text-[clamp(56px,9vw,76px)] font-bold leading-[0.98] tracking-[-0.03em] mb-5">{{ $edition->year }}</h1>
                        <div class="font-[family-name:var(--ns-display)] text-[clamp(22px,3vw,30px)] font-semibold leading-[1.15] max-w-[26ch]">{{ $edition->t('headline') }}</div>
                    </div>
                    <p class="font-[family-name:var(--ns-body)] text-[16.5px] leading-[1.75] text-white/78">{{ $edition->t('summary') }}</p>
                </div>
            </div>

            <div class="border-t border-white/14 bg-ink-900">
                <div class="ns-wrap grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6">
                    @foreach ($edition->stats as $stat)
                        <div class="py-[26px] ps-[22px] border-s border-white/12">
                            <div class="ns-stat ns-num text-[32px] mb-2">{{ $stat['v'] }}</div>
                            <div class="ns-eyebrow !text-white/55 !text-[10px]">{{ $stat['k'][$locale] ?? $stat['k'][$fallback] ?? '' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Themes --}}
        @if ($edition->themes)
            <section class="ns-wrap pt-[clamp(48px,7vw,80px)]">
                <div class="flex items-baseline gap-5 mb-3">
                    <span class="ns-eyebrow">{{ __('site.pages.archive.themes') }}</span>
                    <span class="ns-rule"></span>
                </div>
                <h2 class="ns-h2 !text-[clamp(26px,3.4vw,40px)] mt-[18px] mb-8 max-w-[22ch]">{{ $edition->t('theme_title') }}</h2>

                <div class="ns-hairgrid md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($edition->themes as $theme)
                        <div class="px-8 pt-[34px] pb-[38px] border-t-[6px]" style="border-color:{{ $theme['accent'] ?? '#B64698' }}">
                            <div class="ns-eyebrow !text-[10.5px] mb-[14px]">{{ __('site.pages.archive.theme', ['n' => $theme['num'] ?? '']) }}</div>
                            <h3 class="ns-h3 mb-3">{{ $theme['title'][$locale] ?? $theme['title'][$fallback] ?? '' }}</h3>
                            <p class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.65] text-slate mb-[18px]">
                                {{ $theme['body'][$locale] ?? $theme['body'][$fallback] ?? '' }}
                            </p>
                            <div class="font-[family-name:var(--ns-body)] text-[13px] font-bold" style="color:{{ $theme['accent'] ?? '#B64698' }}">
                                {{ $theme['outcome'][$locale] ?? $theme['outcome'][$fallback] ?? '' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Opening address --}}
        <section class="bg-white border-y border-[rgba(5,7,8,0.12)] mt-20">
            <div class="ns-wrap py-[clamp(48px,6vw,72px)]">
                <div class="flex items-baseline gap-5 mb-9">
                    <span class="ns-eyebrow">{{ __('site.pages.archive.opening_address') }}</span>
                    <span class="ns-rule"></span>
                </div>

                <div class="grid gap-12 lg:grid-cols-[300px_minmax(0,1fr)] items-start">
                    <div>
                        <x-ns.frame label="Photo — at the podium" ratio="4/5" class="mb-4" />
                        <div class="font-[family-name:var(--ns-body)] text-base font-bold mb-1">{{ $edition->organizer_name }}</div>
                        <div class="ns-meta text-[13.5px] leading-[1.5] mb-[14px]">{{ $edition->t('organizer_role') }}</div>
                        <div class="ns-meta text-[12.5px]">{{ $edition->t('speech_where') }}</div>
                    </div>

                    <div>
                        <blockquote class="mb-[30px] ps-[30px] border-s-8 border-magenta max-w-[44ch]">
                            <p class="font-[family-name:var(--ns-display)] text-[clamp(22px,3vw,32px)] font-semibold leading-[1.2] tracking-[-0.02em]">{{ $edition->t('speech_quote') }}</p>
                        </blockquote>
                        <div class="ns-prose">{!! ns_rich($edition->t('speech')) !!}</div>
                        @if ($edition->recap_video)
                            <div class="flex gap-[14px] flex-wrap pt-3">
                                <a href="{{ $edition->recap_video }}" class="ns-btn ns-btn-ink">{{ __('site.pages.archive.watch_opening') }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- Speakers --}}
        @if ($edition->speakers)
            <section class="ns-wrap pt-[clamp(48px,7vw,80px)]">
                <div class="flex items-baseline justify-between gap-6 flex-wrap mb-8">
                    <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)]">{{ __('site.pages.archive.speakers', ['year' => $edition->year]) }}</h2>
                    <span class="ns-meta text-[13.5px]">{{ $edition->t('speaker_count') }}</span>
                </div>
                <div class="grid gap-7 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($edition->speakers as $speaker)
                        <div class="bg-white border border-[rgba(5,7,8,0.12)]">
                            <x-ns.frame label="Headshot" ratio="1/1" center />
                            <div class="p-[22px] pb-6">
                                <div class="flex items-center gap-2 mb-[10px]">
                                    <span class="w-4 h-[5px]" style="background:{{ $speaker['accent'] ?? '#B64698' }}"></span>
                                    <span class="ns-eyebrow !text-[10px]">{{ $speaker['role2'] ?? '' }}</span>
                                </div>
                                <div class="font-[family-name:var(--ns-display)] text-[19px] font-semibold leading-[1.15] mb-[7px]">{{ $speaker['name'] ?? '' }}</div>
                                <div class="font-[family-name:var(--ns-body)] text-[13.5px] leading-[1.5] text-body-soft">{{ $speaker['role'] ?? '' }}</div>
                                <div class="font-[family-name:var(--ns-body)] text-[13.5px] leading-[1.5] text-slate">{{ $speaker['org'] ?? '' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Panels and seminars --}}
        @if ($edition->panels)
            <section class="ns-wrap pt-[clamp(48px,6vw,72px)]">
                <div class="flex items-baseline justify-between gap-6 flex-wrap mb-2">
                    <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)]">{{ __('site.pages.archive.panels') }}</h2>
                    <span class="ns-meta text-[13.5px]">{{ $edition->t('panel_note') }}</span>
                </div>
                <div class="flex flex-col">
                    @foreach ($edition->panels as $panel)
                        <div class="grid gap-7 lg:grid-cols-[120px_minmax(0,1fr)_200px] border-t border-[rgba(5,7,8,0.14)] py-6 items-start">
                            <div>
                                <div class="font-[family-name:var(--ns-display)] text-[19px] font-semibold">{{ $panel['day'] ?? '' }}</div>
                                <div class="ns-meta ns-num text-[12.5px] mt-1">{{ $panel['time'] ?? '' }}</div>
                            </div>
                            <div>
                                <div class="flex items-center gap-[9px] mb-2 flex-wrap">
                                    <span class="ns-typechip {{ in_array($panel['type'] ?? '', ['Panel', 'Roundtable', 'Ceremony']) ? 'ns-typechip-conf' : 'ns-typechip-fair' }}">{{ $panel['type'] ?? '' }}</span>
                                    <span class="ns-meta text-xs">{{ $panel['hall'] ?? '' }}</span>
                                </div>
                                <div class="font-[family-name:var(--ns-display)] text-[22px] font-semibold leading-[1.18] mb-[7px]">{{ $panel['title'] ?? '' }}</div>
                                <div class="ns-meta text-sm leading-[1.6] max-w-[64ch]">{{ $panel['who'] ?? '' }}</div>
                            </div>
                            <div class="lg:text-end">
                                <div class="ns-stat ns-num text-[22px]">{{ $panel['attendance'] ?? '' }}</div>
                                <div class="ns-eyebrow !text-[10px] mt-1">{{ __('site.common.attended') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Partners and sponsors for that year --}}
        @if ($edition->sponsor_tiers)
            <section class="bg-white border-t border-[rgba(5,7,8,0.12)] mt-[clamp(48px,6vw,72px)]">
                <div class="ns-wrap py-[clamp(48px,6vw,72px)]">
                    <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)] mb-2">{{ __('site.pages.archive.partners', ['year' => $edition->year]) }}</h2>
                    <div class="ns-meta text-[14.5px] mb-9">{{ $edition->t('sponsor_note') }}</div>

                    @foreach ($edition->sponsor_tiers as $tier)
                        <div class="border-t border-[rgba(5,7,8,0.14)] pt-6 pb-8 grid gap-10 lg:grid-cols-[200px_minmax(0,1fr)] items-start">
                            <div>
                                <div class="ns-eyebrow !tracking-[0.18em] mb-[7px]" style="color:{{ $tier['accent'] ?? '#2C4BE0' }}">
                                    {{ $tier['tier'][$locale] ?? $tier['tier'][$fallback] ?? '' }}
                                </div>
                                <div class="ns-meta leading-[1.5]">{{ $tier['note'][$locale] ?? $tier['note'][$fallback] ?? '' }}</div>
                            </div>
                            <div class="flex gap-[14px] flex-wrap">
                                @foreach ($tier['logos'] ?? [] as $logo)
                                    <div class="bg-bone-200 flex items-center justify-center p-[10px] text-center h-[78px] w-[140px]">
                                        <span class="font-[family-name:var(--ns-body)] text-[11.5px] font-semibold text-muted leading-[1.3]">{{ $logo }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Gallery and impact report --}}
        <section class="ns-wrap pt-[clamp(48px,6vw,72px)]">
            <div class="grid gap-8 lg:grid-cols-[1.3fr_1fr] items-stretch">
                <div>
                    <h2 class="ns-h2 !text-[clamp(24px,2.6vw,32px)] mb-6">{{ __('site.pages.archive.gallery', ['year' => $edition->year]) }}</h2>
                    <div class="grid gap-3 grid-cols-2 sm:grid-cols-3 auto-rows-[150px]">
                        @foreach ($albums as $index => $album)
                            <a href="{{ route('media.album', $album) }}"
                               @class(['ns-frame', 'col-span-2 row-span-2' => $index === 0])>
                                <span>{{ $album->category }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="bg-magenta text-white p-[clamp(24px,3vw,36px)] flex flex-col">
                    <div class="ns-eyebrow !text-white/75 !text-[11px] mb-[14px]">{{ __('site.pages.archive.impact_kicker') }}</div>
                    <h3 class="font-[family-name:var(--ns-display)] text-[28px] font-semibold leading-[1.15] mb-[14px]">{{ __('site.pages.archive.impact_title', ['year' => $edition->year]) }}</h3>
                    <p class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.65] text-white/88 mb-6">{{ __('site.pages.archive.impact_body') }}</p>
                    <div class="mt-auto flex flex-col gap-[10px]">
                        <a href="{{ $edition->report_path ? asset('storage/'.$edition->report_path) : route('reports') }}"
                           class="ns-btn ns-btn-white !justify-start">{{ __('site.pages.archive.download_impact', ['year' => $edition->year]) }}</a>
                        <a href="{{ route('news', ['year' => $edition->year]) }}" class="ns-btn ns-btn-ghost-light !justify-start">
                            {{ __('site.pages.archive.press_coverage', ['year' => $edition->year]) }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-[rgba(5,7,8,0.14)] mt-16 pt-9 flex items-center justify-between gap-6 flex-wrap">
                <div class="font-[family-name:var(--ns-display)] text-[28px] font-semibold tracking-[-0.02em] max-w-[30ch]">{{ __('site.pages.archive.cta') }}</div>
                <div class="flex gap-[14px] flex-wrap">
                    <a href="{{ route('register.fair') }}" class="ns-btn ns-btn-magenta">{{ __('site.cta.register_fair') }}</a>
                    <a href="{{ route('register.conference') }}" class="ns-btn ns-btn-cobalt">{{ __('site.cta.conference_rsvp') }}</a>
                </div>
            </div>
        </section>
    </div>
</x-layouts.site>
