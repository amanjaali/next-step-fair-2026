<x-layouts.site :title="$title" :description="$description" :navKey="$navKey" :ogType="$ogType">
    <article class="pb-[clamp(72px,10vw,140px)]">
        <div class="ns-wrap pt-7">
            <nav class="ns-meta flex gap-2 items-center flex-wrap" aria-label="Breadcrumb">
                <a href="{{ route('blog') }}">{{ __('site.pages.blog.title') }}</a>
                <span aria-hidden="true">/</span>
                <span>{{ $post->category?->t('name') }}</span>
            </nav>
        </div>

        <div class="ns-wrap pt-9">
            <div class="flex items-center gap-3 mb-[22px] flex-wrap">
                @if ($post->category)
                    <span class="ns-typechip border !text-[10px] px-[9px] py-[5px]"
                          style="color:{{ $post->accent() }};border-color:{{ $post->accentBorder() }}">{{ $post->category->t('name') }}</span>
                @endif
                <span class="ns-meta ns-num">{{ $post->published_at ? ns_format_date($post->published_at) : '' }} · {{ __('site.common.min_read', ['n' => $post->readingTime()]) }}</span>
            </div>

            <h1 class="ns-h1 !text-[clamp(32px,4.6vw,52px)] max-w-[26ch] mb-6 text-pretty">{{ $post->t('title') }}</h1>
            <p class="ns-lead max-w-[60ch] mb-12">{{ $post->t('standfirst') ?: $post->t('excerpt') }}</p>

            <div class="flex flex-wrap gap-14 items-start">
                @if ($toc)
                    <aside class="flex-[1_1_240px] max-w-[300px] min-w-0 order-2 lg:order-1">
                        <div class="ns-card !p-[26px] sticky top-28">
                            <div class="ns-eyebrow !text-[10.5px] mb-[14px]">{{ __('site.pages.blog.contents') }}</div>
                            <nav class="flex flex-col gap-2">
                                @foreach ($toc as $entry)
                                    <a href="#{{ $entry['anchor'] }}" class="font-[family-name:var(--ns-body)] text-sm font-semibold text-ink hover:text-magenta">{{ $entry['title'] }}</a>
                                @endforeach
                            </nav>
                        </div>
                    </aside>
                @endif

                <div class="flex-[1_1_480px] min-w-0 order-1 lg:order-2">
                    <div class="ns-prose">{!! ns_rich($post->bodyWithAnchors()) !!}</div>

                    @if ($post->author_bio)
                        <div class="ns-card mt-12">
                            <div class="ns-eyebrow !text-[10.5px] mb-3">{{ __('site.pages.blog.about_author') }}</div>
                            <div class="font-[family-name:var(--ns-body)] text-base font-bold mb-2">{{ $post->author_name }}</div>
                            <p class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.65] text-slate">{{ $post->author_bio }}</p>
                        </div>
                    @endif

                    <div class="bg-magenta text-white p-[clamp(24px,3vw,34px)] mt-8">
                        <div class="ns-eyebrow !text-white/75 !text-[11px] mb-3">{{ __('site.common.newsletter') }}</div>
                        <h2 class="font-[family-name:var(--ns-display)] text-[24px] font-semibold leading-[1.15] mb-4">{{ __('site.pages.news.newsletter_title') }}</h2>
                        <form method="POST" action="{{ route('newsletter.store') }}" class="flex">
                            @csrf
                            <input type="hidden" name="source" value="blog">
                            <input type="text" name="ns_hp" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                            <label for="blog-newsletter" class="sr-only">{{ __('site.common.email_address') }}</label>
                            <input id="blog-newsletter" type="email" name="email" required placeholder="{{ __('site.newsletter.placeholder') }}"
                                   class="flex-1 min-w-0 font-[family-name:var(--ns-body)] text-[15px] p-4 border border-white/50 bg-transparent text-white placeholder:text-white/72">
                            <button type="submit" class="font-[family-name:var(--ns-body)] text-sm font-bold bg-white text-magenta border-0 px-[22px] py-4 cursor-pointer">{{ __('site.cta.sign_up') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if ($related->isNotEmpty())
            <section class="bg-white border-t border-[rgba(5,7,8,0.12)] mt-20">
                <div class="ns-wrap py-16">
                    <h2 class="ns-h2 !text-[clamp(24px,2.6vw,32px)] mb-8">{{ __('site.pages.blog.related') }}</h2>
                    <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($related as $other)
                            <x-ns.post-card :post="$other" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </article>
</x-layouts.site>
