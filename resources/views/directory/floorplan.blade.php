<x-layouts.site :title="$title" :navKey="$navKey">

    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :kicker="config('nextstep.event.venue.name').', '.config('nextstep.event.venue.city')"
                        :title="__('site.pages.floorplan.title')"
                        :lead="__('site.pages.floorplan.lead', ['booths' => $boothCount])"
                        :breadcrumb="[
                            ['label' => __('site.pages.directory.title'), 'url' => route('universities')],
                            ['label' => __('site.pages.floorplan.title')],
                        ]">

            <div class="flex gap-2 flex-wrap mb-6">
                @foreach ($halls as $code => $h)
                    <a href="{{ route('floorplan', ['hall' => $code]) }}"
                       @class(['ns-chip ns-chip-ink', 'is-on' => $hall->code === $code])>{{ $h->t('name') }}</a>
                @endforeach
            </div>

            {{-- The plan itself: halls sized by footprint, each one selectable. --}}
            <div class="ns-card mb-8">
                <div class="grid gap-[10px] sm:grid-cols-[1.35fr_1fr] grid-rows-[200px_150px] mb-[10px]">
                    @foreach (['A', 'B', 'C'] as $code)
                        @php $h = $halls[$code] ?? null; @endphp
                        @continue(! $h)
                        @php $on = $hall->code === $code; @endphp
                        <a href="{{ route('floorplan', ['hall' => $code]) }}"
                           @class(['p-[22px] flex flex-col justify-between text-start', 'row-span-2' => $code === 'A'])
                           style="background:{{ $on ? $h->color : $h->color.'1f' }};color:{{ $on ? '#fff' : '#050708' }};border:2px solid {{ $h->color }}{{ $on ? '' : '59' }}">
                            <div class="flex justify-between items-start gap-3">
                                <span class="font-[family-name:var(--ns-display)] text-xs font-bold tracking-[0.2em]">HALL {{ $h->code }}</span>
                                <span class="font-[family-name:var(--ns-body)] text-xs opacity-85">{{ $h->t('meta') }}</span>
                            </div>
                            <div>
                                <div class="font-[family-name:var(--ns-display)] text-[clamp(18px,2vw,26px)] font-semibold leading-[1.1] mb-2">{{ $h->t('name') }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="grid gap-[10px] sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($servicePoints as $point)
                        <div class="bg-bone-200 px-[18px] py-4">
                            <div class="ns-eyebrow !text-[10.5px] mb-[7px]">{{ $point->code }}</div>
                            <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold leading-[1.3]">{{ $point->t('name') }}</div>
                            <div class="ns-meta text-[12.5px] mt-1">{{ $point->t('kind') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Zone key: colour, letter and label, so colour is never the only cue. --}}
            <div class="flex gap-5 flex-wrap items-center border-y border-[rgba(5,7,8,0.14)] py-[18px] mb-10">
                <span class="ns-eyebrow !text-[10.5px]">{{ __('site.pages.floorplan.zone_key') }}</span>
                @foreach ($halls as $code => $h)
                    <span class="flex items-center gap-[9px] font-[family-name:var(--ns-body)] text-[13px]">
                        <span class="w-4 h-4 flex-none" style="background:{{ $h->color }}"></span>
                        <span class="font-[family-name:var(--ns-display)] font-bold text-xs">{{ $code }}</span>
                        <span>{{ $h->t('name') }}</span>
                    </span>
                @endforeach
                <span class="ns-meta text-[12.5px] ms-auto">{{ __('site.pages.floorplan.magenta_note') }}</span>
            </div>

            <section class="mb-11">
                <div class="flex items-baseline justify-between gap-6 flex-wrap mb-2">
                    <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)]">{{ $hall->t('name') }}</h2>
                    <span class="ns-meta text-[13.5px]">{{ $hall->t('meta') }}</span>
                </div>
                <p class="ns-body max-w-[70ch] mb-7">{{ $hall->t('description') }}</p>

                <div class="ns-hairgrid sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($hall->booths as $booth)
                        <div class="p-5 pb-[22px] flex gap-[14px] items-start">
                            <span class="font-[family-name:var(--ns-display)] text-xs font-bold tracking-[0.06em] text-white px-2 py-[6px] flex-none"
                                  style="background:{{ $hall->color }}">{{ $booth->code }}</span>
                            <div class="min-w-0">
                                <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold leading-[1.3] mb-1">{{ $booth->t('name') }}</div>
                                <div class="ns-meta text-[12.5px] leading-[1.45]">{{ $booth->t('kind') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="grid gap-8 lg:grid-cols-2">
                <div class="ns-card">
                    <div class="ns-eyebrow !text-[11px] mb-4">{{ __('site.pages.floorplan.getting_in') }}</div>
                    <div class="flex flex-col gap-3">
                        @foreach (\App\Models\Setting::get('access_notes', [])[app()->getLocale()] ?? \App\Models\Setting::get('access_notes', [])['en'] ?? [] as $note)
                            <div class="flex gap-[14px] items-start">
                                <span class="ns-bar bg-magenta mt-[9px]"></span>
                                <span class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.6] text-body-soft">{{ $note }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="ns-panel-ink flex flex-col">
                    <div class="ns-eyebrow !text-white/50 !text-[11px] mb-[14px]">{{ __('site.cta.download') }}</div>
                    <h3 class="font-[family-name:var(--ns-display)] text-[26px] font-semibold leading-[1.15] mb-[14px]">{{ __('site.pages.floorplan.offline_title') }}</h3>
                    <p class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.65] text-white/78 mb-6">{{ __('site.pages.floorplan.offline_body') }}</p>
                    <div class="mt-auto flex flex-col gap-[10px]">
                        @php $plan = \App\Models\Download::where('group', 'floorplan')->where('published', true)->first(); @endphp
                        <a href="{{ $plan?->url() ?: '#' }}" class="ns-btn ns-btn-magenta !justify-start">{{ __('site.pages.floorplan.download') }}</a>
                        <a href="{{ route('universities') }}" class="ns-btn ns-btn-ghost-light !justify-start">{{ __('site.cta.exhibitor_list') }}</a>
                    </div>
                </div>
            </div>
        </x-ns.page-head>
    </div>

</x-layouts.site>
