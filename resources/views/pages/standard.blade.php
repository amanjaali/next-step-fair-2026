<x-layouts.site :title="$title" :description="$description" :navKey="$navKey">
    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :kicker="$page->t('kicker')" :title="$page->t('title')" :lead="$page->t('standfirst')">
            <div class="flex flex-wrap gap-14 items-start border-t border-[rgba(5,7,8,0.14)] pt-9">
                <div class="flex-[1_1_480px] min-w-0">
                    @foreach ($sections as $section)
                        <section class="mb-10">
                            <div class="flex items-baseline gap-[14px] mb-[14px]">
                                <span class="ns-num font-[family-name:var(--ns-display)] text-[13px] font-bold text-magenta">{{ $section['n'] }}</span>
                                <h2 class="font-[family-name:var(--ns-display)] text-[28px] font-semibold leading-[1.15]">{{ $section['h'] }}</h2>
                            </div>
                            @foreach ($section['p'] as $paragraph)
                                <p class="font-[family-name:var(--ns-body)] text-[16.5px] leading-[1.75] text-body max-w-[68ch] mb-[18px]">{{ $paragraph }}</p>
                            @endforeach
                            @if ($section['list'])
                                <ul class="list-none m-0 p-0 flex flex-col gap-[10px]">
                                    @foreach ($section['list'] as $item)
                                        <li class="font-[family-name:var(--ns-body)] text-base leading-[1.65] text-body-soft flex gap-[14px] max-w-[68ch]">
                                            <span class="ns-bar bg-ink mt-[9px]"></span><span>{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </section>
                    @endforeach
                </div>

                <aside class="flex-[1_1_260px] max-w-[320px] min-w-0">
                    @if (! empty($aside['title']))
                        <div class="ns-card !p-6 mb-5">
                            <div class="ns-eyebrow !text-[10.5px] mb-[10px]">{{ $aside['title'] }}</div>
                            <div class="font-[family-name:var(--ns-body)] text-sm leading-[1.6] text-body-soft mb-[14px]">{{ $aside['body'] }}</div>
                            <a href="mailto:{{ $aside['contact'] }}" class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">{{ $aside['contact'] }}</a>
                        </div>
                    @endif
                    <div class="ns-panel-ink !p-6">
                        <div class="ns-eyebrow !text-white/50 !text-[10.5px] mb-3">{{ __('site.cta.register_fair') }}</div>
                        <p class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.6] text-white/80 mb-4">{{ __('site.common.free_entry') }}</p>
                        <a href="{{ route('register.fair') }}" class="ns-btn ns-btn-magenta w-full">{{ __('site.cta.register_fair') }}</a>
                    </div>
                </aside>
            </div>
        </x-ns.page-head>
    </div>
</x-layouts.site>
