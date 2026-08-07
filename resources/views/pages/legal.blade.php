<x-layouts.site :title="$title" :description="$description" :navKey="$navKey">
    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :kicker="$page->t('kicker')" :title="$page->t('title')" :lead="$page->t('standfirst')">
            <div class="ns-meta -mt-6 mb-10">
                {{ $page->updated_on ? __('site.common.last_updated', ['date' => ns_format_date($page->updated_on)]) : '' }}
            </div>

            {{-- Wrapping flex: the body column keeps its measure at every width. --}}
            <div class="flex flex-wrap gap-14 items-start border-t border-[rgba(5,7,8,0.14)] pt-9">
                <aside class="flex-[1_1_240px] max-w-[300px] min-w-0">
                    <div class="ns-eyebrow !text-[10.5px] mb-[14px]">{{ __('site.common.on_this_page') }}</div>
                    <nav class="flex flex-col gap-[2px] mb-7">
                        @foreach ($sections as $section)
                            <a href="#s{{ $section['n'] }}"
                               class="font-[family-name:var(--ns-body)] text-sm font-semibold text-ink hover:text-magenta py-[10px] border-b border-[rgba(5,7,8,0.1)]">
                                <span class="ns-num">{{ $section['n'] }}</span> · {{ $section['h'] }}
                            </a>
                        @endforeach
                    </nav>

                    @if (! empty($aside['title']))
                        <div class="ns-card !p-6">
                            <div class="ns-eyebrow !text-[10.5px] mb-[10px]">{{ $aside['title'] }}</div>
                            <div class="font-[family-name:var(--ns-body)] text-sm leading-[1.6] text-body-soft mb-[14px]">{{ $aside['body'] }}</div>
                            <a href="mailto:{{ $aside['contact'] }}" class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">{{ $aside['contact'] }}</a>
                        </div>
                    @endif
                </aside>

                <div class="flex-[1_1_480px] min-w-0">
                    @foreach ($sections as $section)
                        <section id="s{{ $section['n'] }}" class="mb-10 scroll-mt-28">
                            <div class="flex items-baseline gap-[14px] mb-[14px]">
                                <span class="ns-num font-[family-name:var(--ns-display)] text-[13px] font-bold text-magenta">{{ $section['n'] }}</span>
                                <h2 class="font-[family-name:var(--ns-display)] text-[28px] font-semibold leading-[1.15]">{{ $section['h'] }}</h2>
                            </div>
                            @foreach ($section['p'] as $paragraph)
                                <p class="font-[family-name:var(--ns-body)] text-[16.5px] leading-[1.75] text-body max-w-[68ch] mb-[18px]">{{ $paragraph }}</p>
                            @endforeach
                            @if ($section['list'])
                                <ul class="list-none m-0 p-0 flex flex-col gap-[10px] mt-1">
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
            </div>

            {{-- Press kit downloads --}}
            @isset($pressAssets)
                <section class="border-t border-[rgba(5,7,8,0.14)] mt-5 pt-11">
                    <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)] mb-2">{{ __('site.common.downloads') }}</h2>
                    <div class="ns-meta text-[14.5px] mb-8">{{ __('site.pages.news.press_body') }}</div>

                    <div class="ns-hairgrid sm:grid-cols-2 xl:grid-cols-3 mb-10">
                        @foreach ($pressAssets as $asset)
                            <a href="{{ $asset->url() ?: '#' }}" class="text-ink hover:text-ink px-[26px] pt-7 pb-[30px] flex flex-col gap-3 border-t-[5px]"
                               style="border-color:{{ $asset->accent ?: '#B64698' }}">
                                <div class="ns-eyebrow !text-[10.5px]">{{ $asset->t('kind') }}</div>
                                <div class="font-[family-name:var(--ns-display)] text-[22px] font-semibold leading-[1.15]">{{ $asset->t('name') }}</div>
                                <div class="font-[family-name:var(--ns-body)] text-sm leading-[1.6] text-slate">{{ $asset->t('description') }}</div>
                                <div class="mt-auto font-[family-name:var(--ns-body)] text-[13px] font-bold text-magenta">
                                    {{ __('site.cta.download') }} · <span class="ns-num">{{ $asset->size_label }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="grid gap-8 lg:grid-cols-2">
                        <div class="ns-card">
                            <div class="ns-eyebrow !text-[11px] mb-4">{{ __('site.pages.news.press_title') }}</div>
                            <div class="flex flex-col">
                                @foreach ($pressFacts as $fact)
                                    <div class="flex justify-between gap-4 font-[family-name:var(--ns-body)] text-[14.5px] border-b border-[rgba(5,7,8,0.1)] py-3">
                                        <span class="text-slate">{{ $fact['k'] }}</span>
                                        <span class="font-bold text-end">{{ $fact['v'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="ns-panel-ink flex flex-col">
                            <div class="ns-eyebrow !text-white/50 !text-[11px] mb-[14px]">{{ __('site.cta.accreditation') }}</div>
                            <h3 class="font-[family-name:var(--ns-display)] text-[26px] font-semibold leading-[1.15] mb-[14px]">
                                {{ __('site.pages.news.press_title') }}
                            </h3>
                            <p class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.65] text-white/78 mb-6">{{ __('site.pages.news.press_body') }}</p>
                            <div class="mt-auto flex flex-col gap-[10px]">
                                <a href="{{ route('register.conference') }}" class="ns-btn ns-btn-magenta !justify-start">{{ __('site.cta.accreditation') }}</a>
                                <div class="font-[family-name:var(--ns-body)] text-sm text-white/80 pt-2">
                                    {{ config('nextstep.contact.media') }} · <span class="ns-num">{{ config('nextstep.contact.media_phone') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endisset

            @if (($isPolicy ?? false) && $page->t('foot_note'))
                <div class="border-t border-[rgba(5,7,8,0.14)] mt-5 pt-9 flex items-center justify-between gap-6 flex-wrap">
                    <div class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.6] text-body-soft max-w-[56ch]">{{ $page->t('foot_note') }}</div>
                    <div class="flex gap-3 flex-wrap">
                        <a href="{{ route('privacy') }}" class="ns-btn ns-btn-ghost ns-btn-sm">{{ __('site.footer.privacy') }}</a>
                        <a href="{{ route('terms') }}" class="ns-btn ns-btn-ghost ns-btn-sm">{{ __('site.footer.terms') }}</a>
                    </div>
                </div>
            @endif
        </x-ns.page-head>
    </div>
</x-layouts.site>
