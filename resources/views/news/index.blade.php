<x-layouts.site :title="$title" :navKey="$navKey">

    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :kicker="__('site.pages.news.kicker')" :title="__('site.pages.news.title')"
                        :lead="__('site.pages.news.lead', ['blog' => __('site.pages.news.blog_link')])">

            @if ($pinned)
                <article class="border-y border-[rgba(5,7,8,0.14)] mb-11">
                    <div class="grid lg:grid-cols-[1.25fr_1fr] gap-px bg-[rgba(5,7,8,0.14)]">
                        <x-ns.frame :label="$pinned->cover_placeholder" :src="$pinned->coverUrl()"
                                    :alt="$pinned->t('title')" class="min-h-[420px] !p-6" />
                        <div class="bg-ink text-white p-[clamp(28px,4vw,44px)] flex flex-col">
                            <div class="flex items-center gap-3 mb-[22px] flex-wrap">
                                <span class="ns-typechip bg-magenta text-white !text-[10px] !tracking-[0.2em] font-bold">{{ __('site.pages.news.pinned') }}</span>
                                <span class="ns-eyebrow !text-white/65 !text-[10px]">{{ $pinned->category?->t('name') }}</span>
                            </div>
                            <h2 class="ns-h2 !text-[clamp(26px,3.4vw,38px)] mb-[18px] text-pretty">{{ $pinned->t('title') }}</h2>
                            <p class="font-[family-name:var(--ns-body)] text-base leading-[1.7] text-white/78 mb-[26px] max-w-[48ch]">{{ $pinned->t('excerpt') }}</p>
                            <div class="mt-auto flex items-center gap-5 flex-wrap pt-5 border-t border-white/16">
                                <span class="font-[family-name:var(--ns-body)] text-[13px] text-white/60 ns-num">
                                    {{ $pinned->published_at ? ns_format_date($pinned->published_at) : '' }} · {{ __('site.common.min_read', ['n' => $pinned->readingTime()]) }}
                                </span>
                                <a href="{{ route('news.show', $pinned) }}"
                                   class="font-[family-name:var(--ns-body)] text-sm font-bold text-white hover:text-white border-b-2 border-magenta pb-[3px]">
                                    {{ __('site.pages.news.read_announcement') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            @endif

            {{-- One labelled row of topics, and the year as a plain menu.
                 Two unlabelled chip rows side by side, each opening with its own
                 "All", read as one long undifferentiated strip — the same thing
                 that made the agenda unreadable. --}}
            <div class="flex items-center justify-between gap-x-6 gap-y-3 flex-wrap mb-4">
                <div class="flex items-baseline gap-3 flex-wrap">
                    <span class="ns-meta text-[12.5px] shrink-0">{{ __('site.pages.agenda.filter_by') }}</span>
                    <x-ns.filter-chips param="category" :active="$activeCategory"
                                       :options="collect(['all' => __('site.common.all')])->merge($categories->mapWithKeys(fn ($c) => [$c->slug => $c->t('name')]))->all()" />
                </div>

                <form method="GET" class="flex items-center gap-2 shrink-0">
                    @if ($activeCategory !== 'all')
                        <input type="hidden" name="category" value="{{ $activeCategory }}">
                    @endif
                    <label for="ns-news-year" class="ns-meta text-[12.5px]">{{ __('site.pages.news.year') }}</label>
                    <select id="ns-news-year" name="year" class="ns-select !w-auto !min-h-10 !py-0 !h-10 !text-[13.5px] cursor-pointer"
                            onchange="this.form.submit()">
                        <option value="">{{ __('site.common.all_years') }}</option>
                        @foreach ($years as $y)
                            <option value="{{ $y }}" @selected((string) $activeYear === (string) $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="ns-meta border-b border-[rgba(5,7,8,0.14)] pb-5 mb-9">
                {{ trans_choice(__('site.pages.news.count', ['count' => $posts->total()]), $posts->total()) }}
            </div>

            @if ($posts->isEmpty())
                <div class="ns-card max-w-[64ch] mb-14">
                    <div class="font-[family-name:var(--ns-display)] text-[26px] font-semibold mb-3">
                        {{ __('site.pages.news.empty_title', ['filter' => $activeCategory === 'all' ? $activeYear : $activeCategory]) }}
                    </div>
                    <p class="ns-body mb-6">{{ __('site.pages.news.empty_body') }}</p>
                    <a href="{{ route('news') }}" class="ns-btn ns-btn-ink">{{ __('site.common.show_all') }}</a>
                </div>
            @else
                <div class="grid gap-x-8 gap-y-9 md:grid-cols-2 xl:grid-cols-3 mb-14">
                    @foreach ($posts as $post)
                        <x-ns.post-card :post="$post" />
                    @endforeach
                </div>
                {{ $posts->links() }}
            @endif

            <div class="grid gap-8 lg:grid-cols-[1.2fr_1fr] border-t border-[rgba(5,7,8,0.14)] pt-11">
                <div class="ns-card">
                    <div class="ns-eyebrow !text-[11px] mb-[14px]">{{ __('site.common.press') }}</div>
                    <h2 class="font-[family-name:var(--ns-display)] text-[30px] font-semibold leading-[1.12] mb-[14px]">{{ __('site.pages.news.press_title') }}</h2>
                    <p class="font-[family-name:var(--ns-body)] text-[15.5px] leading-[1.7] text-body-soft mb-6 max-w-[52ch]">{{ __('site.pages.news.press_body') }}</p>
                    <div class="flex gap-3 flex-wrap">
                        <a href="{{ route('press') }}" class="ns-btn ns-btn-ink">{{ __('site.cta.press_kit') }}</a>
                        <a href="{{ route('contact') }}" class="ns-btn ns-btn-ghost !text-cobalt !border-[rgba(44,75,224,0.5)]">{{ __('site.cta.accreditation') }}</a>
                    </div>
                    <div class="ns-meta text-sm mt-[22px] pt-5 border-t border-[rgba(5,7,8,0.12)]">
                        {{ config('nextstep.contact.media') }} · <span class="ns-num">{{ config('nextstep.contact.media_phone') }}</span>
                    </div>
                </div>

                <div class="bg-magenta text-white p-[clamp(24px,3vw,38px)] flex flex-col">
                    <div class="ns-eyebrow !text-white/75 !text-[11px] mb-[14px]">{{ __('site.common.newsletter') }}</div>
                    <h2 class="font-[family-name:var(--ns-display)] text-[28px] font-semibold leading-[1.12] mb-3">{{ __('site.pages.news.newsletter_title') }}</h2>
                    <p class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.65] text-white/85 mb-6">{{ __('site.pages.news.newsletter_body') }}</p>
                    <form method="POST" action="{{ route('newsletter.store') }}" class="mt-auto flex">
                        @csrf
                        <input type="hidden" name="source" value="news">
                        <input type="text" name="ns_hp" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                        <label for="news-newsletter" class="sr-only">{{ __('site.common.email_address') }}</label>
                        <input id="news-newsletter" type="email" name="email" required placeholder="{{ __('site.newsletter.placeholder') }}"
                               class="flex-1 min-w-0 font-[family-name:var(--ns-body)] text-[15px] p-4 border border-white/50 bg-transparent text-white placeholder:text-white/72">
                        <button type="submit" class="font-[family-name:var(--ns-body)] text-sm font-bold bg-white text-magenta border-0 px-[22px] py-4 cursor-pointer">
                            {{ __('site.cta.sign_up') }}
                        </button>
                    </form>
                    @if (session('newsletter'))
                        <p class="font-[family-name:var(--ns-body)] text-[13px] text-white/90 mt-3">{{ session('newsletter') }}</p>
                    @endif
                </div>
            </div>
        </x-ns.page-head>
    </div>

</x-layouts.site>
