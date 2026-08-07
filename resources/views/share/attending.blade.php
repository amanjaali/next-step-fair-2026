@php
    /**
     * Where a shared link lands.
     *
     * Somebody clicked because a person they know said they are going. The page
     * has one job: say what the thing is, and let them do the same. It is not
     * the home page — the home page opens with a video loop and eleven sections,
     * and a visitor who arrived on a promise deserves the promise first.
     */
    $accent = $who === 'delegate' ? 'cobalt' : 'magenta';
@endphp

<x-layouts.site
    :title="$title"
    :description="$description"
    :ogImage="$ogImage"
    ogType="article"
    :navKey="null">

    <section class="bg-ink text-white">
        <div class="ns-wrap py-[clamp(48px,7vw,96px)] grid gap-[clamp(32px,5vw,72px)] lg:grid-cols-[1.05fr_1fr] items-center">

            <div>
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-[26px] h-2 bg-{{ $accent }}"></span>
                    <span class="ns-eyebrow !text-white/60">{{ __('share.landing.kicker') }}</span>
                </div>

                <h1 class="ns-h1 !text-[clamp(32px,5vw,58px)] !text-white mb-5">
                    {{ __('share.landing.title') }}
                </h1>

                <p class="ns-body !text-[clamp(16px,1.8vw,19px)] !text-white/75 max-w-[46ch] mb-8">
                    {{ __("share.landing.lines.{$who}") }}
                </p>

                <div class="grid gap-6 sm:grid-cols-3 mb-9 pt-7 border-t border-white/20">
                    @foreach ([
                        ['label' => __('site.common.dates'), 'value' => ns_event_dates(), 'num' => true],
                        ['label' => __('site.common.venue'), 'value' => config('nextstep.event.venue.name').', '.config('nextstep.event.venue.city'), 'num' => false],
                        ['label' => __('site.common.entry'), 'value' => __('site.common.free_entry'), 'num' => false],
                    ] as $fact)
                        <div>
                            <div class="ns-eyebrow !text-white/45 !text-[10px] mb-2">{{ $fact['label'] }}</div>
                            <div @class([
                                'font-[family-name:var(--ns-display)] text-[17px] font-semibold leading-[1.3]',
                                'ns-num' => $fact['num'],
                            ])>{{ $fact['value'] }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="flex gap-[14px] flex-wrap">
                    <a href="{{ route('register.fair') }}" class="ns-btn ns-btn-lg ns-btn-magenta">
                        {{ __('share.landing.cta') }}
                    </a>
                    <a href="{{ route('home') }}"
                       class="ns-btn ns-btn-lg ns-btn-ghost !text-white !border-white/40 hover:!bg-white/10">
                        {{ __('share.landing.cta_secondary') }}
                    </a>
                </div>
            </div>

            {{-- The same card they saw in the feed, so the page they land on is
                 visibly the thing they clicked. --}}
            <img src="{{ $card }}" alt="" width="1080" height="1080"
                 class="w-full max-w-[440px] h-auto block justify-self-center border border-white/15">
        </div>
    </section>

    <section class="ns-wrap py-[clamp(44px,6vw,80px)]">
        <div class="ns-hairgrid md:grid-cols-3 border border-[rgba(5,7,8,0.14)]">
            @foreach ([
                ['title' => __('share.landing.what.expo'), 'body' => __('share.landing.what.expo_note'), 'href' => route('fair')],
                ['title' => __('share.landing.what.scholarship'), 'body' => __('share.landing.what.scholarship_note'), 'href' => route('scholarship.home')],
                ['title' => __('share.landing.what.conference'), 'body' => __('share.landing.what.conference_note'), 'href' => route('conference')],
            ] as $panel)
                <a href="{{ $panel['href'] }}" class="p-7 block no-underline hover:bg-bone-50">
                    <div class="font-[family-name:var(--ns-display)] text-[19px] font-semibold text-ink mb-2">{{ $panel['title'] }}</div>
                    <p class="ns-body !text-[14.5px] text-body-soft">{{ $panel['body'] }}</p>
                </a>
            @endforeach
        </div>
    </section>
</x-layouts.site>
