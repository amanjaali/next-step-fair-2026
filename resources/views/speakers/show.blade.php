<x-layouts.site :title="$title" :description="$description" :navKey="$navKey"
                :track="$speaker->track">

    <div class="pb-[clamp(72px,10vw,140px)]">
        <div class="ns-wrap pt-7">
            <nav class="ns-meta flex gap-2 items-center flex-wrap" aria-label="Breadcrumb">
                <a href="{{ route('speakers') }}">{{ __('site.pages.speakers.title') }}</a>
                <span aria-hidden="true">/</span>
                <span>{{ $speaker->t('name') }}</span>
            </nav>
        </div>

        <section class="ns-wrap pt-9">
            {{-- Wrapping flex, so the portrait rail can never starve the bio column. --}}
            <div class="flex flex-wrap gap-14 items-start">
                <div class="flex-[1_1_300px] max-w-[400px] min-w-0">
                    <x-ns.frame :src="$speaker->photoUrl()" :alt="$speaker->t('name')" ratio="1/1"
                                :label="__('site.pages.speakers.title')" class="mb-5" />

                    <div class="ns-card !p-[26px]">
                        @foreach ([
                            __('site.common.track') => $speaker->track === 'conference' ? __('site.nav.conference') : __('site.nav.expo'),
                            __('site.common.role') => $speaker->speaker_type,
                            __('site.common.institution') => $speaker->t('organization'),
                            __('site.common.country') => $speaker->countryName(),
                            __('site.common.sessions') => (string) $speaker->sessions->count(),
                        ] as $key => $value)
                            <div class="flex justify-between gap-[14px] font-[family-name:var(--ns-body)] text-[13.5px] border-b border-[rgba(5,7,8,0.1)] py-[10px]">
                                <span class="text-slate">{{ $key }}</span>
                                <span class="font-bold text-end">{{ $value }}</span>
                            </div>
                        @endforeach

                        @if ($speaker->links)
                            <div class="flex gap-2 flex-wrap pt-[18px]">
                                @foreach ($speaker->links as $link)
                                    <a href="{{ $link['url'] ?? '#' }}" target="_blank" rel="noopener"
                                       class="font-[family-name:var(--ns-body)] text-[12.5px] font-bold text-ink border border-[rgba(5,7,8,0.2)] px-3 py-[9px] hover:border-magenta">
                                        {{ $link['label'] ?? '' }} ↗
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex-[1_1_480px] min-w-0">
                    <div class="flex items-center gap-3 mb-5 flex-wrap">
                        <span class="w-[26px] h-2" style="background:{{ $speaker->accent() }}"></span>
                        <span class="ns-eyebrow" style="color:{{ $speaker->accent() }}">
                            {{ $speaker->track === 'conference' ? __('site.nav.conference') : __('site.nav.expo') }} · {{ $speaker->speaker_type }}
                        </span>
                    </div>

                    <h1 class="ns-h1 !text-[clamp(36px,5.5vw,60px)] max-w-[22ch] mb-4">{{ $speaker->t('name') }}</h1>
                    <div class="font-[family-name:var(--ns-display)] text-2xl font-semibold leading-[1.25] mb-[6px]">{{ $speaker->t('role') }}</div>
                    <div class="font-[family-name:var(--ns-body)] text-[17px] text-slate mb-9">{{ $speaker->t('organization') }}</div>

                    <div class="border-t border-[rgba(5,7,8,0.14)] pt-[30px] mb-10">
                        <div class="ns-eyebrow !text-[11px] mb-[18px]">{{ __('site.common.biography') }}</div>
                        <div class="ns-prose">
                            @if (trim(strip_tags($speaker->t('bio'))))
                                {!! ns_rich($speaker->t('bio')) !!}
                            @else
                                @foreach ($speaker->bioParagraphs() as $paragraph)
                                    <p>{{ strip_tags($paragraph) }}</p>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    @if ($speaker->sessions->isNotEmpty())
                        <div class="border-t border-[rgba(5,7,8,0.14)] pt-[30px] mb-10">
                            <div class="ns-eyebrow !text-[11px] mb-[18px]">{{ __('site.pages.speakers.sessions_2026') }}</div>
                            <div class="flex flex-col">
                                @foreach ($speaker->sessions as $session)
                                    <a href="{{ route('agenda', ['day' => $session->day]) }}"
                                       class="text-ink hover:text-ink bg-white border border-[rgba(5,7,8,0.14)] px-6 py-[22px] mb-3 flex flex-wrap gap-x-[22px] gap-y-[14px] items-center"
                                       style="border-inline-start:6px solid {{ $speaker->accent() }}">
                                        <div class="flex-none">
                                            <div class="ns-num font-[family-name:var(--ns-display)] text-lg font-semibold">{{ $session->timeLabel() }}</div>
                                            <div class="ns-meta text-[12.5px] mt-[3px]">{{ __('site.common.day', ['n' => $session->day]) }} · {{ ns_day_date($session->day) }}</div>
                                        </div>
                                        <div class="flex-[1_1_260px] min-w-0">
                                            <div class="font-[family-name:var(--ns-body)] text-[16.5px] font-bold leading-[1.35] mb-[5px]">{{ $session->t('title') }}</div>
                                            <div class="ns-meta">{{ $session->hallLabel() }} · {{ $session->pivot->role }}</div>
                                        </div>
                                        <span class="font-[family-name:var(--ns-body)] text-[13px] font-bold text-cobalt whitespace-nowrap flex-none">
                                            {{ __('site.cta.view_in_agenda') }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($speaker->topics)
                        <div class="border-t border-[rgba(5,7,8,0.14)] pt-[30px]">
                            <div class="ns-eyebrow !text-[11px] mb-[18px]">{{ __('site.common.topics') }}</div>
                            <div class="flex gap-2 flex-wrap">
                                @foreach (($speaker->topics[app()->getLocale()] ?? $speaker->topics['en'] ?? $speaker->topics) as $topic)
                                    @if (is_string($topic))
                                        <span class="font-[family-name:var(--ns-body)] text-[13px] font-semibold text-ink border border-[rgba(5,7,8,0.2)] px-[13px] py-[9px]">{{ $topic }}</span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        @if ($related->isNotEmpty())
            <section class="bg-white border-t border-[rgba(5,7,8,0.12)] mt-20">
                <div class="ns-wrap py-16">
                    <div class="flex items-baseline justify-between gap-6 flex-wrap mb-8">
                        <h2 class="ns-h2 !text-[clamp(24px,2.6vw,32px)]">{{ __('site.pages.speakers.also_speaking') }}</h2>
                        <a href="{{ route('speakers') }}" class="ns-btn ns-btn-ghost ns-btn-sm">{{ __('site.cta.all_speakers', ['count' => $related->count() + 1]) }}</a>
                    </div>
                    <div class="grid gap-7 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($related as $other)
                            <x-ns.speaker-card :speaker="$other" :showSessions="false" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>

</x-layouts.site>
