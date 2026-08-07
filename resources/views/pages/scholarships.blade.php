<x-layouts.site :title="$title" :description="$description" :navKey="$navKey">
    {{-- Two pages could say "scholarship" and mean different things: the offers
         universities bring to the expo, and Next Step's own forty-seat programme.
         Say which this is, and point at the other. --}}
    <div class="bg-ink text-white">
        <div class="ns-wrap flex items-center justify-between gap-6 flex-wrap py-4">
            <p class="font-[family-name:var(--ns-body)] text-[14.5px] text-white/80 max-w-[62ch] m-0">
                {{ __('site.pages.scholarships.programme_pointer') }}
            </p>
            <a href="{{ route('scholarship.home') }}" class="ns-btn ns-btn-magenta ns-btn-sm">{{ __('scholarship.name') }}</a>
        </div>
    </div>

    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :kicker="$page->t('kicker')" :title="$page->t('title')" :lead="$page->t('standfirst')">
            <div class="grid gap-12 lg:grid-cols-[1.2fr_1fr] items-start border-t border-[rgba(5,7,8,0.14)] pt-9">
                <div>
                    @foreach ($sections as $section)
                        <section class="mb-8 max-w-[68ch]">
                            <h2 class="font-[family-name:var(--ns-display)] text-[26px] font-semibold leading-[1.15] mb-4">{{ $section['h'] }}</h2>
                            @foreach ($section['p'] as $paragraph)
                                <p class="ns-body mb-[18px]">{{ $paragraph }}</p>
                            @endforeach
                        </section>
                    @endforeach
                </div>

                <div class="ns-panel-ink">
                    <div class="ns-eyebrow !text-white/50 !text-[11px] mb-[14px]">{{ __('site.pages.scholarships.title') }}</div>
                    <p class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.65] text-white/80 mb-6">{{ __('site.pages.scholarships.bridge') }}</p>
                    <a href="{{ config('nextstep.links.scholarships') }}" target="_blank" rel="noopener" class="ns-btn ns-btn-magenta w-full">
                        {{ __('site.pages.scholarships.open') }}
                    </a>
                </div>
            </div>
        </x-ns.page-head>
    </div>
</x-layouts.site>
