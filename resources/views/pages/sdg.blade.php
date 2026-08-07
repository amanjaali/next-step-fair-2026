<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="pb-[clamp(72px,10vw,140px)]">
        <div class="ns-wrap pt-[clamp(28px,4vw,56px)]">
            <div class="grid gap-16 lg:grid-cols-[1.2fr_1fr] items-start mb-14">
                <div>
                    <span class="ns-eyebrow">{{ __('site.pages.sdg.kicker') }}</span>
                    <h1 class="ns-h1 mt-5 mb-6">{{ __('site.pages.sdg.title') }}</h1>
                    <p class="ns-body !text-[17px] mb-[18px]">{{ __('site.pages.sdg.p1') }}</p>
                    <p class="ns-body !text-[17px]">{{ __('site.pages.sdg.p2') }}</p>
                </div>

                <div class="bg-white border border-[rgba(5,7,8,0.2)] p-8">
                    <div class="w-11 h-11 bg-sdg-17 mb-[18px]"></div>
                    <div class="font-[family-name:var(--ns-display)] text-[23px] font-semibold leading-[1.2] mb-3">{{ __('site.pages.sdg.verified_title') }}</div>
                    <p class="font-[family-name:var(--ns-body)] text-sm leading-[1.6] text-slate mb-5">{{ __('site.pages.sdg.verified_body') }}</p>
                    <a href="{{ config('nextstep.links.act4sdgs') }}" target="_blank" rel="noopener" class="ns-btn ns-btn-ink">
                        {{ __('site.cta.verify_act4sdgs') }}
                    </a>
                </div>
            </div>

            {{-- The five goals, each with what we do and a measurable indicator. --}}
            <div class="flex flex-col">
                @foreach ($goals as $goal)
                    <div class="border-t border-[rgba(5,7,8,0.14)] py-[34px] grid gap-10 lg:grid-cols-[220px_minmax(0,1fr)_260px] items-start">
                        <div class="text-white p-5 min-h-[130px] flex flex-col justify-between" style="background:{{ $goal->color }}">
                            <span class="ns-num font-[family-name:var(--ns-display)] text-[11px] font-bold tracking-[0.16em]">SDG {{ $goal->number }}</span>
                            <span class="font-[family-name:var(--ns-display)] text-[21px] font-semibold leading-[1.1]">{{ $goal->t('title') }}</span>
                        </div>
                        <div>
                            <div class="font-[family-name:var(--ns-display)] text-[25px] font-semibold leading-[1.15] mb-3">{{ $goal->t('what') }}</div>
                            <p class="font-[family-name:var(--ns-body)] text-[15.5px] leading-[1.7] text-body-soft max-w-[64ch]">{{ $goal->t('detail') }}</p>
                        </div>
                        <div class="lg:border-s border-[rgba(5,7,8,0.14)] lg:ps-[26px]">
                            <div class="ns-eyebrow !text-[10.5px] mb-[10px]">{{ __('site.pages.sdg.indicator', ['year' => 2025]) }}</div>
                            <div class="ns-stat ns-num text-[32px] mb-2">{{ $goal->figure }}</div>
                            <div class="ns-meta text-[13.5px] leading-[1.5]">{{ $goal->t('metric') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-[rgba(5,7,8,0.14)] mt-5 pt-11 grid gap-12 lg:grid-cols-2">
                <div>
                    <h2 class="font-[family-name:var(--ns-display)] text-[32px] font-semibold mb-[18px]">{{ __('site.pages.sdg.green_title') }}</h2>
                    <ul class="list-none m-0 p-0 flex flex-col gap-3">
                        @foreach ($greenPractices as $practice)
                            <li class="font-[family-name:var(--ns-body)] text-[15.5px] leading-[1.6] text-body-soft flex gap-[14px]">
                                <span class="ns-bar bg-sdg-13 mt-[9px]"></span><span>{{ $practice }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="ns-card">
                    <div class="ns-eyebrow !text-[11px] mb-[14px]">{{ __('site.common.reports') }}</div>
                    <div class="flex flex-col gap-3">
                        @foreach ($reports as $report)
                            <a href="{{ $report->url() ?: '#' }}" class="ns-btn ns-btn-ghost !justify-start !text-start">
                                {{ $report->t('name') }} — <span class="ns-num">{{ $report->size_label }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.site>
