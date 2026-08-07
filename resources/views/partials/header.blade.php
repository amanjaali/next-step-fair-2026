@php
    // Nav order follows the design: Home, The Expo, The Conference, Agenda,
    // Speakers, Universities, Sponsors, News, SDG. News sits before SDG.
    $navItems = [
        ['key' => 'home', 'label' => __('site.nav.home'), 'route' => 'home'],
        ['key' => 'fair', 'label' => __('site.nav.expo'), 'route' => 'fair'],
        ['key' => 'conference', 'label' => __('site.nav.conference'), 'route' => 'conference'],
        ['key' => 'scholarship', 'label' => __('site.nav.scholarship'), 'route' => 'scholarship.home'],
        ['key' => 'agenda', 'label' => __('site.nav.agenda'), 'route' => 'agenda'],
        ['key' => 'speakers', 'label' => __('site.nav.speakers'), 'route' => 'speakers'],
        ['key' => 'universities', 'label' => __('site.nav.universities'), 'route' => 'universities'],
        ['key' => 'sponsors', 'label' => __('site.nav.sponsors'), 'route' => 'sponsors'],
        ['key' => 'news', 'label' => __('site.nav.news'), 'route' => 'news'],
        ['key' => 'sdg', 'label' => __('site.nav.sdg'), 'route' => 'sdg'],
    ];
    // Opportunities is only worth a menu slot for somebody who can open them.
    // For everyone else it would be a link to a locked page, and an eleventh item
    // is exactly what breaks the header onto two rows.
    if (auth('attendee')->check()) {
        array_splice($navItems, 4, 0, [[
            'key' => 'opportunities',
            'label' => __('site.nav.opportunities'),
            'route' => 'opportunities',
        ]]);
    }

    $current = $navKey ?? null;
@endphp

<header class="sticky top-0 z-50 bg-bone border-b border-[rgba(5,7,8,0.14)]" x-data="nsNav">
    <div class="ns-wrap flex items-center justify-between gap-[clamp(12px,2vw,32px)] py-3 min-h-[76px] flex-wrap">

        {{-- Brand lockup: Next Step, a 1px rule, then the MOHE partnership mark.
             The header is capped at 1440px, so the spelled-out ministry wordmark and a
             nine-item nav cannot both fit — carrying it here is what pushed the menu
             onto a second line at every desktop width. The ministry logo keeps the
             partnership visible (its alt text names the ministry in full) and the
             wording is spelled out in the footer and on the about page. --}}
        <div class="flex items-center gap-4 shrink-0">
            <a href="{{ route('home') }}" class="block shrink-0">
                <img src="{{ asset('assets/brand/nextstep-transparent-sm.png') }}"
                     alt="{{ __('site.header.logo_alt') }}" class="h-[46px] w-auto block">
            </a>
            {{-- The ministry mark needs about 70px, and between 1280 and 1536 that
                 is exactly what the tenth menu item costs. A menu on two rows is
                 the worse of the two, and the partnership is spelled out in full in
                 the footer and on the about page. --}}
            <span class="w-px h-11 bg-[rgba(5,7,8,0.2)] shrink-0 hidden sm:block xl:hidden 2xl:block"></span>
            <div class="hidden sm:flex xl:hidden 2xl:flex items-center gap-[11px] shrink-0">
                <img src="{{ asset('assets/brand/mohe.png') }}"
                     alt="{{ __('site.header.partnership_kicker') }} — {{ __('site.header.mohe') }}"
                     title="{{ __('site.header.mohe') }}" class="h-10 w-auto block">
            </div>
        </div>

        {{-- Fits on one line from 1280px up, which is where the full nav appears;
             below that it is the mobile panel. It wraps rather than clips if a
             translation runs long, so nothing is ever cut off mid-word. --}}
        {{-- Ten items now that the scholarship is here, and they have to hold one
             line from 1280px up — a menu that wraps to two rows is the thing this
             header was fixed for once already. --}}
        <nav class="hidden xl:flex items-center gap-[clamp(9px,0.92vw,18px)] flex-1 min-w-0 flex-wrap gap-y-2"
             aria-label="{{ __('site.nav.menu') }}">
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   @class([
                       'font-[family-name:var(--ns-body)] text-[13.5px] font-medium py-1 whitespace-nowrap border-b-2',
                       'text-ink border-magenta' => $current === $item['key'],
                       'text-slate border-transparent hover:text-ink' => $current !== $item['key'],
                   ])
                   @if($current === $item['key']) aria-current="page" @endif>{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-3 shrink-0">
            @include('partials.language-switcher')

            {{-- Signed in: straight to their own area. Otherwise the register CTA,
                 which is the more useful button for a first-time visitor. --}}
            @auth('attendee')
                <a href="{{ route('me') }}"
                   class="ns-btn ns-btn-ghost !py-[13px] !px-4 !text-[13.5px] whitespace-nowrap hidden md:inline-flex">
                    {{ __('attendee.nav.my_next_step') }}
                </a>
            @else
                <a href="{{ route('register.fair') }}"
                   class="ns-btn ns-btn-magenta !py-[14px] !px-5 !text-[13.5px] whitespace-nowrap hidden sm:inline-flex">
                    {{ __('site.cta.register_fair') }}
                </a>
            @endauth

            <button type="button" @click="toggle()" class="xl:hidden p-3 -me-2 bg-transparent border-0 cursor-pointer"
                    :aria-expanded="open.toString()" aria-controls="ns-mobile-nav" aria-label="{{ __('site.nav.menu') }}">
                <span class="block w-[22px] h-0.5 bg-ink mb-[5px]"></span>
                <span class="block w-[22px] h-0.5 bg-ink mb-[5px]"></span>
                <span class="block w-[22px] h-0.5 bg-ink"></span>
            </button>
        </div>
    </div>

    {{-- Mobile panel --}}
    <div id="ns-mobile-nav" x-show="open" x-collapse x-cloak class="xl:hidden border-t border-[rgba(5,7,8,0.14)] bg-bone">
        <div class="ns-wrap py-6 flex flex-col gap-1">
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="font-[family-name:var(--ns-body)] text-base font-semibold py-3 border-b border-[rgba(5,7,8,0.08)] {{ $current === $item['key'] ? 'text-magenta' : 'text-ink' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
            <div class="flex flex-col gap-3 pt-5">
                @auth('attendee')
                    <a href="{{ route('me') }}" class="ns-btn ns-btn-magenta w-full">{{ __('attendee.nav.my_next_step') }}</a>
                    <a href="{{ route('me.agenda') }}" class="ns-btn ns-btn-ghost w-full">{{ __('attendee.agenda.title') }}</a>
                @else
                    <a href="{{ route('register.fair') }}" class="ns-btn ns-btn-magenta w-full">{{ __('site.cta.register_fair') }}</a>
                    <a href="{{ route('register.conference') }}" class="ns-btn ns-btn-cobalt w-full">{{ __('site.cta.conference_rsvp') }}</a>
                    <a href="{{ route('attendee.signin') }}" class="ns-btn ns-btn-ghost w-full">{{ __('attendee.nav.sign_in') }}</a>
                @endauth
            </div>
        </div>
    </div>
</header>
