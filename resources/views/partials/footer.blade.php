@php
    // Somebody who has already registered does not need the two registration
    // links; they need the two pages those links used to lead them towards.
    $attend = auth('attendee')->check()
        ? [
            ['label' => __('site.home.signed_in.my_badge'), 'url' => route('me')],
            ['label' => __('site.nav.opportunities'), 'url' => route('opportunities')],
        ]
        : [
            ['label' => __('site.footer.links.register_fair'), 'url' => route('register.fair')],
            ['label' => __('site.footer.links.conference_rsvp'), 'url' => route('register.conference')],
            // Someone who registered on a school computer and came back on a
            // phone is signing in, not registering a second time. Registering
            // twice is the mistake this link prevents.
            ['label' => __('attendee.nav.sign_in'), 'url' => route('attendee.signin')],
        ];

    $footerCols = [
        __('site.footer.attend') => [
            ...$attend,
            ['label' => __('zankoline.nav'), 'url' => route('zankoline')],
            ['label' => __('site.footer.links.agenda'), 'url' => route('agenda')],
            ['label' => __('site.footer.links.speakers'), 'url' => route('speakers')],
            ['label' => __('site.footer.links.seminars'), 'url' => route('seminars')],
            ['label' => __('site.footer.links.venue'), 'url' => route('contact')],
        ],
        __('site.footer.organisation') => [
            ['label' => __('site.footer.links.about'), 'url' => route('about')],
            ['label' => __('site.nav.news'), 'url' => route('news')],
            ['label' => __('site.footer.links.sdg'), 'url' => route('sdg')],
            ['label' => __('site.footer.links.partners'), 'url' => route('sponsors')],
            ['label' => __('site.footer.links.archive'), 'url' => route('archive')],
            ['label' => __('site.footer.links.reports'), 'url' => route('reports')],
            ['label' => __('site.footer.links.contact'), 'url' => route('contact')],
        ],
    ];
@endphp

{{-- The footer renders on every page, in every language. --}}
<footer class="bg-ink text-white">
    <div class="ns-wrap pt-[clamp(48px,6vw,72px)] pb-10">

        <div class="grid gap-12 pb-12 border-b border-[rgba(255,255,255,0.14)] md:grid-cols-2 xl:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
            <div>
                <img src="{{ ns_brand('logo_light', 'assets/brand/nextstep-white-sm.png') }}" alt="{{ __('site.header.logo_alt') }}"
                     class="h-[58px] w-auto block mb-[18px]">
                <p class="font-[family-name:var(--ns-body)] text-sm leading-[1.65] text-white/60 mb-5 max-w-[34ch]">
                    {{ __('site.footer.blurb') }}
                </p>
                <a href="{{ config('nextstep.links.act4sdgs') }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-[10px] border border-white/30 py-[10px] px-[14px] text-white hover:text-white">
                    <span class="w-[18px] h-[18px] bg-sdg-17 shrink-0"></span>
                    <span class="ns-eyebrow !text-[10.5px] !text-white/80">{{ __('site.footer.sdg_badge') }}</span>
                </a>
            </div>

            @foreach ($footerCols as $title => $links)
                <div>
                    <div class="ns-eyebrow !text-[10.5px] !text-white/45 mb-4">{{ $title }}</div>
                    <div class="flex flex-col gap-[10px]">
                        @foreach ($links as $link)
                            <a href="{{ $link['url'] }}"
                               class="font-[family-name:var(--ns-body)] text-sm text-white/80 hover:text-white">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div>
                <div class="ns-eyebrow !text-[10.5px] !text-white/45 mb-4">{{ __('site.common.newsletter') }}</div>
                <p class="font-[family-name:var(--ns-body)] text-sm leading-[1.6] text-white/60 mb-[14px]">
                    {{ __('site.footer.newsletter_blurb') }}
                </p>
                <form method="POST" action="{{ route('newsletter.store') }}" class="flex">
                    @csrf
                    <label for="footer-newsletter" class="sr-only">{{ __('site.common.email_address') }}</label>
                    <input id="footer-newsletter" type="email" name="email" required
                           placeholder="{{ __('site.newsletter.placeholder') }}"
                           class="flex-1 min-w-0 font-[family-name:var(--ns-body)] text-sm p-[14px] border border-white/30 bg-transparent text-white placeholder:text-white/70">
                    {{-- Honeypot: real people leave this empty. --}}
                    <input type="text" name="ns_hp" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                    <button type="submit"
                            class="font-[family-name:var(--ns-body)] text-[13px] font-bold bg-magenta text-white border-0 py-[14px] px-[18px] cursor-pointer">
                        {{ __('site.cta.sign_up') }}
                    </button>
                </form>
                @if (session('newsletter'))
                    <p class="font-[family-name:var(--ns-body)] text-[13px] text-white/80 mt-3">{{ session('newsletter') }}</p>
                @endif
                <div class="font-[family-name:var(--ns-body)] text-sm text-white/80 mt-[22px] flex flex-col gap-[6px]">
                    <a href="mailto:{{ config('nextstep.contact.general') }}" class="text-white/80 hover:text-white">{{ config('nextstep.contact.general') }}</a>
                    <span class="ns-num">{{ config('nextstep.contact.phone') }}</span>
                </div>
            </div>
        </div>

        {{-- Partnership band: the marks on white, each separated by a 1px rule.
             KSA joins them as soon as its logo is uploaded. --}}
        <div class="py-8 border-b border-[rgba(255,255,255,0.14)] flex items-center gap-8 flex-wrap">
            <div class="ns-eyebrow !text-[10.5px] !text-white/45 max-w-[16ch] leading-[1.5]">
                {{ __('site.footer.partnership_label') }}
            </div>
            <div class="flex items-center gap-5 bg-white py-[14px] px-5 flex-wrap">
                <x-ns.partner-mark slug="mohe" :src="ns_brand('mohe', 'assets/brand/mohe.png')"
                                   :name="__('site.header.mohe')" class="h-[54px] w-auto block" />
                <span class="w-px h-12 bg-[rgba(5,7,8,0.18)]"></span>
                <x-ns.partner-mark slug="krg" :src="ns_brand('krg', 'assets/brand/krg.png')"
                                   :name="__('site.header.krg_alt')" class="h-[54px] w-auto block" />
                @if ($ksaMark = ns_brand('ksa'))
                    <span class="w-px h-12 bg-[rgba(5,7,8,0.18)]"></span>
                    <x-ns.partner-mark slug="ksa" :src="$ksaMark"
                                       :name="__('site.header.ksa')" class="h-[54px] w-auto block" />
                @endif
            </div>
            <div class="font-[family-name:var(--ns-body)] text-[13.5px] leading-[1.6] text-white/70 max-w-[38ch]">
                {{ __('site.footer.partnership_line') }}
            </div>
        </div>

        <div class="pt-[26px] flex justify-between gap-5 flex-wrap">
            <span class="font-[family-name:var(--ns-body)] text-[12.5px] text-white/45">
                {{ __('site.footer.copyright', ['year' => config('nextstep.event.year')]) }}
            </span>
            <div class="flex gap-[22px]">
                <a href="{{ route('privacy') }}" class="font-[family-name:var(--ns-body)] text-[12.5px] text-white/60 hover:text-white">{{ __('site.footer.privacy') }}</a>
                <a href="{{ route('terms') }}" class="font-[family-name:var(--ns-body)] text-[12.5px] text-white/60 hover:text-white">{{ __('site.footer.terms') }}</a>
                <a href="{{ route('press') }}" class="font-[family-name:var(--ns-body)] text-[12.5px] text-white/60 hover:text-white">{{ __('site.footer.press') }}</a>
            </div>
        </div>
    </div>
</footer>
