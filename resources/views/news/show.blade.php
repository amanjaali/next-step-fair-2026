<x-layouts.site :title="$title" :description="$description" :navKey="$navKey"
                :ogType="$ogType" :ogImage="$ogImage">

    <article class="pb-[clamp(72px,10vw,140px)]">
        <div class="ns-wrap pt-7">
            <nav class="ns-meta flex gap-2 items-center flex-wrap" aria-label="Breadcrumb">
                <a href="{{ route('news') }}">{{ __('site.pages.news.title') }}</a>
                <span aria-hidden="true">/</span>
                <span>{{ $post->category?->t('name') }}</span>
            </nav>
        </div>

        <div class="ns-wrap pt-9">
            <div class="flex items-center gap-3 mb-[22px] flex-wrap">
                @if ($post->category)
                    <span class="ns-typechip border !text-[10px] !tracking-[0.18em] px-[9px] py-[5px]"
                          style="color:{{ $post->accent() }};border-color:{{ $post->accentBorder() }}">{{ $post->category->t('name') }}</span>
                @endif
                <span class="ns-meta ns-num">{{ $post->published_at ? ns_format_date($post->published_at) : '' }} · {{ __('site.common.min_read', ['n' => $post->readingTime()]) }}</span>
            </div>

            <h1 class="ns-h1 !text-[clamp(34px,5.5vw,60px)] max-w-[26ch] mb-6 text-pretty">{{ $post->t('title') }}</h1>
            <p class="font-[family-name:var(--ns-body)] text-[clamp(17px,1.6vw,21px)] leading-[1.6] text-body-soft max-w-[60ch] mb-9 text-pretty">
                {{ $post->t('standfirst') ?: $post->t('excerpt') }}
            </p>

            <x-ns.frame :label="$post->cover_placeholder" :src="$post->coverUrl()" :alt="$post->t('title')"
                        ratio="21/9" class="mb-3 !p-6" />
            <div class="ns-meta text-[12.5px] mb-14">{{ $post->t('cover_caption') }}</div>

            {{-- Wrapping flex: the body column can never be squeezed by the rails. --}}
            <div class="flex flex-wrap gap-14 items-start">
                <div class="flex-[1_1_100%] flex items-center justify-between gap-6 flex-wrap border-y border-[rgba(5,7,8,0.14)] py-[18px]">
                    <div>
                        <div class="ns-eyebrow !text-[10.5px] mb-[6px]">{{ __('site.common.published') }}</div>
                        <div class="font-[family-name:var(--ns-body)] text-[15px] font-bold">
                            {{ $post->author_name }} · <span class="font-normal text-slate">{{ $post->t('author_role') }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-wrap items-center" x-data="nsCopy">
                        <span class="ns-eyebrow !text-[10.5px]">{{ __('site.common.share') }}</span>
                        <button type="button" @click="copy('{{ url()->current() }}')"
                                class="font-[family-name:var(--ns-body)] text-[12.5px] font-semibold text-ink border border-[rgba(5,7,8,0.2)] px-[11px] py-2 cursor-pointer bg-transparent">
                            <span x-show="!copied">{{ __('site.common.copy_link') }}</span>
                            <span x-show="copied" x-cloak>{{ __('site.common.copied') }}</span>
                        </button>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener"
                           class="font-[family-name:var(--ns-body)] text-[12.5px] font-semibold text-ink border border-[rgba(5,7,8,0.2)] px-[11px] py-2">Facebook</a>
                        <a href="https://x.com/intent/tweet?url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener"
                           class="font-[family-name:var(--ns-body)] text-[12.5px] font-semibold text-ink border border-[rgba(5,7,8,0.2)] px-[11px] py-2">X</a>
                        <a href="https://wa.me/?text={{ urlencode($post->t('title').' '.url()->current()) }}" target="_blank" rel="noopener"
                           class="font-[family-name:var(--ns-body)] text-[12.5px] font-semibold text-ink border border-[rgba(5,7,8,0.2)] px-[11px] py-2">WhatsApp</a>
                    </div>
                </div>

                <div class="flex-[1_1_480px] min-w-0">
                    <div class="ns-prose">{!! $post->t('body') !!}</div>

                    @if ($post->t('quote'))
                        <blockquote class="my-10 ps-7 border-s-8 border-magenta max-w-[60ch]">
                            <p class="font-[family-name:var(--ns-display)] text-[27px] font-semibold leading-[1.25] tracking-[-0.018em] mb-[14px]">{{ $post->t('quote') }}</p>
                            <cite class="ns-meta not-italic text-[13.5px]">{{ $post->t('quote_by') }}</cite>
                        </blockquote>
                    @endif

                    <div class="border-t border-[rgba(5,7,8,0.14)] mt-10 pt-7 flex gap-[14px] flex-wrap">
                        <a href="{{ route('register.conference') }}" class="ns-btn ns-btn-cobalt">{{ __('site.cta.conference_rsvp') }}</a>
                        <a href="{{ route('agenda', ['day' => 1]) }}" class="ns-btn ns-btn-ghost">{{ __('site.pages.conference.programme') }}</a>
                    </div>
                </div>

                <aside class="flex-[1_1_260px] max-w-[320px] min-w-0">
                    @if ($post->facts)
                        <div class="ns-card !p-[26px] mb-5">
                            <div class="ns-eyebrow !text-[10.5px] mb-[14px]">{{ __('site.pages.news.in_this_article') }}</div>
                            <div class="flex flex-col gap-[10px]">
                                @foreach ($post->facts as $fact)
                                    <div class="flex justify-between gap-3 font-[family-name:var(--ns-body)] text-[13.5px] border-b border-[rgba(5,7,8,0.1)] pb-[9px]">
                                        <span class="text-slate">{{ $fact['k'] ?? '' }}</span>
                                        <span class="font-bold text-end">{{ $fact['v'] ?? '' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="ns-panel-ink !p-[26px]">
                        <div class="ns-eyebrow !text-white/50 !text-[10.5px] mb-3">{{ __('site.common.press') }}</div>
                        <div class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.6] text-white/80 mb-4">{{ __('site.pages.news.press_body') }}</div>
                        <a href="{{ route('press') }}" class="font-[family-name:var(--ns-body)] text-[13.5px] font-bold text-white hover:text-white border-b-2 border-magenta pb-[2px]">
                            {{ __('site.footer.press') }}
                        </a>
                    </div>
                </aside>
            </div>
        </div>

        @if ($related->isNotEmpty())
            <section class="bg-white border-t border-[rgba(5,7,8,0.12)] mt-20">
                <div class="ns-wrap py-16">
                    <div class="flex items-baseline justify-between mb-8 flex-wrap gap-4">
                        <h2 class="ns-h2 !text-[clamp(24px,2.6vw,32px)]">{{ __('site.pages.news.related') }}</h2>
                        <a href="{{ route('news') }}" class="ns-link">{{ __('site.cta.all_news') }}</a>
                    </div>
                    <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($related as $other)
                            <x-ns.post-card :post="$other" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </article>

    @push('schema')
        @php
            $articleSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'NewsArticle',
                'headline' => $post->t('title'),
                'description' => strip_tags($post->t('excerpt')),
                'datePublished' => $post->published_at?->toIso8601String(),
                'author' => ['@type' => 'Organization', 'name' => $post->author_name ?: config('nextstep.event.organisation')],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => config('nextstep.event.organisation'),
                    'logo' => ['@type' => 'ImageObject', 'url' => asset('assets/brand/nextstep-transparent-sm.png')],
                ],
                'mainEntityOfPage' => url()->current(),
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endpush

</x-layouts.site>
