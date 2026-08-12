<x-layouts.site :title="$title" :navKey="$navKey">

    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :title="__('site.pages.agenda.title')"
                        :lead="__('site.pages.agenda.lead', ['count' => $totalCount])">

            {{-- Day tabs --}}
            <div class="ns-tabs mb-[22px]">
                @foreach (config('nextstep.event.days') as $number => $meta)
                    <a href="{{ request()->fullUrlWithQuery(['day' => $number]) }}"
                       @class(['ns-tab', 'is-on' => $day === $number])
                       @if ($day === $number) aria-current="page" @endif>
                        {{ __('site.common.day', ['n' => $number]) }}
                        <span class="ns-num font-normal text-[13px] ms-2 opacity-70">{{ ns_day_date($number) }}</span>
                    </a>
                @endforeach
            </div>

            {{-- One filter row, and it says what it filters.
                 There were two unlabelled rows here, each opening with an
                 identical "All" chip, and the words Conference and Expo appeared
                 in both meaning different things — a programme in the first row,
                 a kind of session in the second. Nobody could be expected to read
                 that. The track row is gone; the sections below replace it. --}}
            <div class="flex items-baseline gap-3 flex-wrap mb-2">
                <span class="ns-meta text-[12.5px] shrink-0">{{ __('site.pages.agenda.filter_by') }}</span>
                <x-ns.filter-chips param="type" :active="$activeType" :options="$typeOptions" />
            </div>

            <div class="flex items-center justify-between gap-4 flex-wrap mb-[26px]">
                <span class="ns-meta">{{ __('site.pages.agenda.export', ['count' => $sessions->count()]) }}</span>
                <div class="flex gap-2">
                    <a href="{{ route('agenda.pdf') }}" class="ns-btn ns-btn-ghost ns-btn-sm">PDF</a>
                    <a href="{{ route('agenda.ics') }}" class="ns-btn ns-btn-ghost ns-btn-sm">.ics</a>
                </div>
            </div>

            @if ($sessions->isEmpty())
                <div class="ns-card max-w-[64ch]">
                    <p class="ns-body">{{ __('site.pages.agenda.empty') }}</p>
                    <a href="{{ route('agenda', ['day' => $day]) }}" class="ns-btn ns-btn-ink mt-6">{{ __('site.common.show_all') }}</a>
                </div>
            @else
                @foreach ($groups as $track => $trackSessions)
                    @if ($showTrackHeadings)
                        {{-- Which programme this is, and who it is for. Day 1 runs
                             both, and telling them apart is the point of the page. --}}
                        <div class="mt-[clamp(28px,3vw,44px)] mb-1 pt-6 border-t-2 {{ $track === 'conference' ? 'border-cobalt' : 'border-magenta' }}">
                            <div class="flex items-baseline gap-x-4 gap-y-1 flex-wrap">
                                <h2 class="font-[family-name:var(--ns-display)] text-[clamp(20px,2.4vw,26px)] font-semibold leading-tight">
                                    {{ $track === 'conference' ? __('site.nav.conference') : __('site.nav.expo') }}
                                </h2>
                                <span class="ns-eyebrow !text-[9.5px] {{ $track === 'conference' ? '!text-cobalt' : '!text-magenta' }}">
                                    {{ $track === 'conference' ? __('site.pages.agenda.tracks.conference_who') : __('site.pages.agenda.tracks.expo_who') }}
                                </span>
                            </div>
                            <p class="ns-body !text-[14.5px] text-body-soft max-w-[68ch] mt-2">
                                {{ $track === 'conference' ? __('site.pages.agenda.tracks.conference_note') : __('site.pages.agenda.tracks.expo_note') }}
                                <a href="{{ $track === 'conference' ? route('conference') : route('fair') }}" class="ns-link !text-[14.5px]">
                                    {{ $track === 'conference' ? __('site.pages.agenda.tracks.conference_link') : __('site.pages.agenda.tracks.expo_link') }}
                                </a>
                            </p>
                        </div>
                    @endif

                    <div class="flex flex-col">
                        @foreach ($trackSessions as $session)
                            <x-ns.session-row :session="$session" />
                        @endforeach
                    </div>
                @endforeach
            @endif
        </x-ns.page-head>
    </div>

</x-layouts.site>
