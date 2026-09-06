<x-layouts.site :title="$title" :navKey="$navKey">

    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :title="__('site.pages.directory.title')"
                        :lead="__('site.pages.directory.lead', ['universities' => $universities->count(), 'exhibitors' => $exhibitors->count()])">

            <div class="ns-tabs mb-8">
                <a href="{{ route('universities') }}" @class(['ns-tab', 'is-on' => $tab === 'universities'])>
                    {{ __('site.pages.directory.universities_tab', ['count' => $universities->count()]) }}
                </a>
                <a href="{{ route('exhibitors') }}" @class(['ns-tab', 'is-on' => $tab === 'exhibitors'])>
                    {{ __('site.pages.directory.exhibitors_tab', ['count' => $exhibitors->count()]) }}
                </a>
            </div>

            <div class="ns-hairgrid md:grid-cols-2 xl:grid-cols-3 mb-11">
                @foreach ($list as $org)
                    <div class="p-[26px] flex gap-5 items-start">
                        <div class="w-16 h-16 bg-bone-200 flex-none flex items-center justify-center">
                            @if ($org->logo_path)
                                <img src="{{ $org->logoUrl() }}" alt="{{ $org->t('name') }}" class="max-w-[52px] max-h-[52px] object-contain" loading="lazy">
                            @endif
                        </div>
                        <div>
                            <div class="font-[family-name:var(--ns-body)] text-base font-bold leading-[1.3] mb-[6px]">{{ $org->t('name') }}</div>
                            <div class="ns-meta leading-[1.5] mb-[10px]">{{ $org->t('description') }}</div>
                            <div class="flex gap-2 items-center flex-wrap">
                                @if ($org->booth)
                                    <span class="ns-typechip border border-[rgba(182,70,152,0.4)] text-magenta !tracking-[0.14em] !text-[10.5px]">
                                        {{ $org->hall?->code ? __('site.common.day', ['n' => '']) : '' }}{{ $org->booth }}
                                    </span>
                                @endif
                                @if ($org->website)
                                    <a href="{{ $org->website }}" target="_blank" rel="noopener"
                                       class="font-[family-name:var(--ns-body)] text-[12.5px] font-bold text-cobalt">{{ __('site.cta.website') }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Floor plan teaser: the whole block opens the dedicated page. --}}
            <div class="ns-card">
                <div class="flex justify-between items-baseline gap-5 flex-wrap mb-[22px]">
                    <h2 class="ns-h2 !text-[clamp(24px,2.8vw,30px)]">{{ __('site.pages.directory.floor_plan') }}</h2>
                    <span class="ns-meta">{{ __('site.pages.directory.halls_note') }}</span>
                </div>

                <a href="{{ route('floorplan') }}" class="grid gap-3 sm:grid-cols-[1.3fr_1fr_1fr] h-[260px] text-white hover:text-white">
                    @foreach ($halls as $hall)
                        <div class="p-5 flex flex-col justify-between text-start" style="background:{{ $hall->color }}">
                            <span class="font-[family-name:var(--ns-display)] text-[11px] font-bold tracking-[0.2em]">{{ __('site.common.day', ['n' => '']) }}HALL {{ $hall->code }}</span>
                            <span class="font-[family-name:var(--ns-display)] text-2xl font-semibold leading-tight">{{ $hall->t('name') }}</span>
                        </div>
                    @endforeach
                </a>

                <div class="flex items-center justify-between gap-5 flex-wrap mt-[18px]">
                    <span class="ns-meta">{{ __('site.pages.directory.colour_note') }}</span>
                    <a href="{{ route('floorplan') }}" class="ns-btn ns-btn-ink ns-btn-sm">{{ __('site.cta.open_floorplan') }}</a>
                </div>
            </div>
        </x-ns.page-head>
    </div>

</x-layouts.site>
