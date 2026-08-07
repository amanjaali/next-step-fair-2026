<x-layouts.site :title="$title" :navKey="$navKey">

    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :kicker="__('site.pages.sponsors.kicker')"
                        :title="__('site.pages.sponsors.title')"
                        :lead="__('site.pages.sponsors.lead', ['count' => 41, 'returning' => 27])">

            <div class="ns-hairgrid grid-cols-2 xl:grid-cols-4 mb-14">
                @foreach ($stats as $stat)
                    <div class="px-6 pt-[26px] pb-7">
                        <div class="ns-stat ns-num text-[36px] mb-[10px]">{{ $stat['value'] }}</div>
                        <div class="ns-eyebrow !text-[10.5px]">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>

            {{-- Strategic partners: largest, top of page, real marks. --}}
            <section class="mb-14">
                <div class="flex items-baseline gap-5 mb-6">
                    <span class="ns-eyebrow">{{ __('site.pages.sponsors.strategic') }}</span>
                    <span class="ns-rule"></span>
                </div>
                <div class="ns-hairgrid lg:grid-cols-2">
                    @foreach ($strategic as $partner)
                        <div class="p-[clamp(24px,3vw,38px)] flex gap-[26px] items-center border-t-[6px] border-cobalt flex-wrap">
                            @if ($partner->logo_path)
                                <img src="{{ asset('assets/'.$partner->logo_path) }}" alt="{{ $partner->t('name') }}" class="h-[86px] w-auto flex-none">
                            @endif
                            <div class="min-w-[200px] flex-1">
                                <div class="font-[family-name:var(--ns-display)] text-[22px] font-semibold leading-[1.15] mb-2">{{ $partner->t('name') }}</div>
                                <div class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.6] text-slate">{{ $partner->t('description') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            @foreach ($sections as $section)
                @continue($section['items']->isEmpty())
                <section class="mb-13" style="margin-bottom:52px">
                    <div class="flex items-baseline gap-5 mb-2 flex-wrap">
                        <span class="ns-eyebrow" style="color:{{ $section['accent'] }}">{{ $section['tier'] }}</span>
                        <span class="ns-rule"></span>
                        <span class="ns-meta text-[12.5px]">{{ $section['items']->count() }}</span>
                    </div>
                    <div class="ns-meta text-sm mb-5 max-w-[80ch]">{{ $section['note'] }}</div>

                    <div class="ns-hairgrid sm:grid-cols-2 lg:grid-cols-{{ min($section['cols'], 4) }}">
                        @foreach ($section['items'] as $item)
                            <a @if ($item->website) href="{{ $item->website }}" target="_blank" rel="noopener" @endif
                               class="p-6 flex flex-col gap-[14px] text-ink hover:text-ink">
                                <div class="bg-bone-200 flex items-center justify-center p-3 text-center"
                                     style="height:{{ $section['logoHeight'] }}">
                                    @if ($item->logo_path)
                                        <img src="{{ asset('assets/'.$item->logo_path) }}" alt="{{ $item->t('name') }}" class="max-h-full max-w-full object-contain" loading="lazy">
                                    @else
                                        <span class="font-[family-name:var(--ns-body)] text-xs font-semibold text-muted leading-[1.3]">{{ $item->t('name') }}</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-[family-name:var(--ns-body)] text-[15px] font-bold leading-[1.3] mb-[5px]">{{ $item->t('name') }}</div>
                                    <div class="ns-meta leading-[1.5]">{{ $item->t('description') }}</div>
                                </div>
                                <div class="mt-auto flex items-center gap-[9px] flex-wrap">
                                    @if ($item->t('badge'))
                                        <span class="ns-typechip border !text-[10px]" style="color:{{ $section['accent'] }};border-color:{{ $section['accent'] }}66">{{ $item->t('badge') }}</span>
                                    @endif
                                    @if ($item->website)
                                        <span class="font-[family-name:var(--ns-body)] text-[12.5px] font-bold text-cobalt">{{ __('site.cta.website') }}</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach

            {{-- Become a sponsor: benefits table plus a lead form to the sales team. --}}
            <section class="border-t border-[rgba(5,7,8,0.14)] pt-12" id="become-a-sponsor">
                <div class="grid gap-14 lg:grid-cols-2 items-start mb-9">
                    <div>
                        <h2 class="ns-h2 !text-[clamp(28px,3.4vw,40px)] mb-[18px]">{{ __('site.pages.sponsors.become_title') }}</h2>
                        <p class="ns-body mb-4">{{ __('site.pages.sponsors.become_p1', ['count' => 32]) }}</p>
                        <p class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.65] text-slate">{{ __('site.pages.sponsors.become_p2') }}</p>
                    </div>

                    <div class="ns-panel-ink">
                        <div class="ns-eyebrow !text-white/50 !text-[11px] mb-[14px]">{{ __('site.pages.sponsors.talk_to_team') }}</div>
                        <div class="font-[family-name:var(--ns-display)] text-2xl font-semibold leading-[1.2] mb-5 break-words">
                            <a href="mailto:{{ config('nextstep.contact.partnerships') }}" class="text-white hover:text-white">{{ config('nextstep.contact.partnerships') }}</a>
                        </div>
                        <div class="flex flex-col gap-3">
                            @php $deck = \App\Models\Download::where('group', 'deck')->where('published', true)->first(); @endphp
                            <a href="{{ $deck?->url() ?: '#' }}" class="ns-btn ns-btn-magenta !justify-start">{{ __('site.pages.sponsors.download_deck') }}</a>
                            <a href="{{ route('exhibit') }}" class="ns-btn ns-btn-ghost-light !justify-start">{{ __('site.pages.sponsors.request_call') }}</a>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <div class="min-w-[760px] grid gap-px bg-[rgba(5,7,8,0.14)]" style="grid-template-columns:1.4fr repeat(4,1fr)">
                        @foreach ($tierTable['head'] ?? [] as $heading)
                            <div class="bg-ink text-white px-[18px] py-4 ns-eyebrow !text-[10.5px] !text-white flex items-center">{{ $heading }}</div>
                        @endforeach
                        @foreach ($tierTable['rows'] ?? [] as $row)
                            @foreach ($row as $index => $cell)
                                <div @class([
                                        'px-[18px] py-4 font-[family-name:var(--ns-body)] text-[13.5px] flex items-center',
                                        'bg-bone-50 font-bold' => $index === 0,
                                        'bg-white' => $index !== 0,
                                        'text-disabled' => $cell === '—',
                                    ])>{{ $cell }}</div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </section>
        </x-ns.page-head>
    </div>

</x-layouts.site>
