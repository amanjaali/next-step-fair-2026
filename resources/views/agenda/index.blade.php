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

            {{-- Filters: track and type --}}
            <div class="flex gap-2 flex-wrap mb-2">
                <x-ns.filter-chips param="track" :active="$activeTrack" :options="[
                    'all' => __('site.common.all'),
                    'conference' => __('site.nav.conference'),
                    'fair' => __('site.nav.expo'),
                ]" />
                <x-ns.filter-chips param="type" :active="$activeType"
                                   :options="collect(['all' => __('site.common.all')])->merge($types->mapWithKeys(fn ($t) => [$t => $t]))->all()" />
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
                <div class="flex flex-col">
                    @foreach ($sessions as $session)
                        <x-ns.session-row :session="$session" />
                    @endforeach
                </div>
            @endif
        </x-ns.page-head>
    </div>

</x-layouts.site>
