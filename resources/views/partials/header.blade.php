@php
    /**
     * Six things at the top level, not eleven.
     *
     * The four pages that are all "what happens at the fair" — the two halves of
     * the event and the two ways of reading its programme — sit inside one group.
     * News and the SDG page live in the footer instead: both are read once
     * rather than being part of deciding whether to come. That is a shorter menu
     * to scan, and the width it gives back is what lets the partnership mark stay
     * in the header at every size and the type stay readable at 15px.
     */
    $navItems = [
        ['key' => 'home', 'label' => __('site.nav.home'), 'route' => 'home'],
        [
            'key' => 'event',
            'label' => __('site.nav.the_next_step'),
            'children' => [
                ['key' => 'fair', 'label' => __('site.nav.expo'), 'route' => 'fair'],
                ['key' => 'conference', 'label' => __('site.nav.conference'), 'route' => 'conference'],
                ['key' => 'agenda', 'label' => __('site.nav.agenda'), 'route' => 'agenda'],
                ['key' => 'speakers', 'label' => __('site.nav.speakers'), 'route' => 'speakers'],
                // Inside the group rather than beside it: the top row is full,
                // and a fifth item in a panel costs no width at all.
                ['key' => 'zankoline', 'label' => __('zankoline.nav'), 'route' => 'zankoline'],
            ],
        ],
        ['key' => 'scholarship', 'label' => __('site.nav.scholarship'), 'route' => 'scholarship.home'],
        ['key' => 'opportunities', 'label' => __('site.nav.opportunities'), 'route' => 'opportunities'],
        ['key' => 'universities', 'label' => __('site.nav.universities'), 'route' => 'universities'],
        ['key' => 'sponsors', 'label' => __('site.nav.sponsors'), 'route' => 'sponsors'],
    ];

    $current = $navKey ?? null;

    // A group reads as the current section when the page open is one of its own.
    $isCurrentGroup = fn (array $item) => isset($item['children'])
        && in_array($current, array_column($item['children'], 'key'), true);
@endphp

<header class="sticky top-0 z-50 bg-bone border-b border-[rgba(5,7,8,0.1)]" x-data="nsNav">
    <div class="ns-wrap flex items-center justify-between gap-[clamp(10px,1.1vw,20px)] py-3 min-h-[76px] flex-wrap">

        {{-- Brand lockup: Next Step, a hairline, then the partnership marks in
             the agreed order — MOHE, then the Kurdistan Students Association.
             The marks are the visible half of "in partnership with"; the wording
             itself is spelled out in the footer and on the about page.

             KSA is drawn only once its logo has been uploaded on the Brand
             images screen, so the header never carries a broken image while the
             file is still being prepared. --}}
        <div class="flex items-center gap-4 shrink-0">
            <a href="{{ route('home') }}" class="block shrink-0">
                <img src="{{ ns_brand('logo_dark', 'assets/brand/nextstep-transparent-sm.png') }}"
                     alt="{{ __('site.header.logo_alt') }}" class="h-[46px] w-auto block">
            </a>
            {{-- Hidden in one narrow band, 1280 to 1399.

                 That is where the full menu has just appeared and the bar is at
                 its tightest; carrying the marks through it pushes the menu onto
                 a second row in English and Kurdish. They are already hidden
                 below 640 for the same reason, and 1400 up — which is nearly
                 every desktop — carries them as before. --}}
            <span class="w-px h-11 bg-[rgba(5,7,8,0.16)] shrink-0 hidden sm:block [@media(min-width:1280px)_and_(max-width:1399px)]:!hidden"></span>
            <div class="hidden sm:flex [@media(min-width:1280px)_and_(max-width:1399px)]:!hidden items-center gap-[11px] shrink-0">
                <img src="{{ ns_brand('mohe', 'assets/brand/mohe.png') }}"
                     alt="{{ __('site.header.partnership_kicker') }} — {{ __('site.header.mohe') }}"
                     title="{{ __('site.header.mohe') }}" class="h-10 w-auto block">
                {{-- Capped in width as well as height. A seal-shaped mark is
                     about 50px wide at this height and a wide wordmark is 120px;
                     measured in Chromium, English at 1280 takes the menu onto a
                     second row somewhere between 68px and 76px, so the cap is
                     64px. Whatever file is uploaded, the menu stays on one line.
                     Re-measure 1280/1440/1600/1920 × en/ku/ar if this changes. --}}
                @if ($ksaMark = ns_brand('ksa'))
                    <img src="{{ $ksaMark }}"
                         alt="{{ __('site.header.partnership_kicker') }} — {{ __('site.header.ksa') }}"
                         title="{{ __('site.header.ksa') }}"
                         class="h-10 w-auto block" style="max-width:64px;object-fit:contain">
                @endif
            </div>
        </div>

        {{-- One line from 1280px up. Check 1280, 1440, 1600 and 1920 after adding
             an item: the trap is that it fits at 1440 and breaks at both ends. --}}
        <nav class="hidden xl:flex items-center gap-[clamp(0px,0.15vw,6px)] flex-1 min-w-0 flex-wrap gap-y-2"
             aria-label="{{ __('site.nav.menu') }}">
            @foreach ($navItems as $item)
                @if (isset($item['children']))
                    @php $groupOpen = $isCurrentGroup($item); @endphp
                    {{-- Hover opens it and moving away closes it; the click is a
                         toggle for anyone who clicks rather than hovers. The two
                         must not both fire on one gesture — mouseenter followed
                         by click would open then immediately close it — so the
                         pointer state is tracked and the click defers to it. --}}
                    <div class="relative" x-data="nsNavGroup"
                         @mouseenter="enter()" @mouseleave="leave()"
                         @click.outside="close()" @keydown.escape.window="close()">
                        <button type="button" @click="press()"
                                :aria-expanded="open.toString()"
                                @class([
                                    'ns-navlink inline-flex items-center gap-1.5 bg-transparent border-0 cursor-pointer',
                                    'is-current' => $groupOpen,
                                ])>
                            {{ $item['label'] }}
                            <svg width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true"
                                 class="transition-transform duration-200 shrink-0" :class="open && 'rotate-180'">
                                <path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.6"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        {{-- The 8px below the button is padding on the wrapper, not
                             a margin on the panel: as a margin it is a gap that
                             belongs to neither element, and the pointer crossing
                             it on the way down counts as leaving the menu. --}}
                        <div x-show="open" x-cloak style="display: none"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute top-full start-0 pt-2 min-w-[228px] z-10">
                            <div class="bg-white ns-radius ns-shadow border border-[rgba(5,7,8,0.08)] p-2 flex flex-col">
                                @foreach ($item['children'] as $child)
                                    <a href="{{ route($child['route']) }}"
                                       @class([
                                           'ns-navsub',
                                           'is-current' => $current === $child['key'],
                                       ])
                                       @if($current === $child['key']) aria-current="page" @endif>{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route($item['route']) }}"
                       @class(['ns-navlink', 'is-current' => $current === $item['key']])
                       @if($current === $item['key']) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endif
            @endforeach
        </nav>

        <div class="flex items-center gap-3 shrink-0">
            @include('partials.language-switcher')

            {{-- Signed in: straight to their own area. Otherwise the register CTA,
                 which is the more useful button for a first-time visitor. --}}
            @auth('attendee')
                {{-- A committee decision is worth a mark on every page, not only
                     on the account page nobody visits without a reason to. --}}
                @php $unread = auth('attendee')->user()->unreadUpdates(); @endphp
                <a href="{{ route('me') }}"
                   class="ns-btn ns-btn-sm ns-btn-ghost !text-[13.5px] whitespace-nowrap hidden md:inline-flex items-center gap-2"
                   @if ($unread) aria-label="{{ __('attendee.nav.my_next_step') }} — {{ trans_choice('updates.unread', $unread, ['count' => $unread]) }}" @endif>
                    {{ __('attendee.nav.my_next_step') }}
                    @if ($unread)
                        <span class="ns-num inline-flex items-center justify-center min-w-[19px] h-[19px] px-[5px] rounded-full bg-magenta text-white text-[11px] font-bold leading-none">{{ $unread }}</span>
                    @endif
                </a>
            @else
                {{-- Sign in, in the header, for everybody.

                     It used to be reachable only from the mobile menu and from
                     a handful of pages that happened to need it, so a student
                     who registered at school and came back on a phone had
                     nowhere on the front page to say "I already have an
                     account" — and registered a second time. Plain text rather
                     than a second button: two buttons side by side compete, and
                     registering is still the ask for a first-time visitor. --}}
                <a href="{{ route('attendee.signin') }}"
                   class="font-[family-name:var(--ns-body)] text-[13.5px] font-bold text-ink hover:text-magenta whitespace-nowrap hidden sm:inline-flex px-1">
                    {{ __('attendee.nav.sign_in') }}
                </a>
                <a href="{{ route('register.fair') }}"
                   class="ns-btn ns-btn-sm ns-btn-magenta !text-[13.5px] !px-[18px] whitespace-nowrap hidden sm:inline-flex">
                    {{ __('site.cta.register_fair') }}
                </a>
            @endauth

            <button type="button" @click="toggle()" class="xl:hidden p-3 -me-2 bg-transparent border-0 cursor-pointer"
                    :aria-expanded="open.toString()" aria-controls="ns-mobile-nav" aria-label="{{ __('site.nav.menu') }}">
                <span class="block w-[22px] h-0.5 bg-ink mb-[5px] rounded-full"></span>
                <span class="block w-[22px] h-0.5 bg-ink mb-[5px] rounded-full"></span>
                <span class="block w-[22px] h-0.5 bg-ink rounded-full"></span>
            </button>
        </div>
    </div>

    {{-- Mobile panel. The group is shown open rather than as a second tap: on a
         panel you scroll anyway, hiding four links behind an accordion buys
         nothing and costs a tap. --}}
    <div id="ns-mobile-nav" x-show="open" x-collapse x-cloak class="xl:hidden border-t border-[rgba(5,7,8,0.1)] bg-bone">
        <div class="ns-wrap py-6 flex flex-col gap-1">
            @foreach ($navItems as $item)
                @if (isset($item['children']))
                    <div class="font-[family-name:var(--ns-body)] text-[12px] font-bold uppercase tracking-[0.12em] text-muted pt-5 pb-2">
                        {{ $item['label'] }}
                    </div>
                    @foreach ($item['children'] as $child)
                        <a href="{{ route($child['route']) }}"
                           class="font-[family-name:var(--ns-body)] text-base font-bold py-3 ps-4 border-b border-[rgba(5,7,8,0.07)] {{ $current === $child['key'] ? 'text-magenta' : 'text-ink' }}">
                            {{ $child['label'] }}
                        </a>
                    @endforeach
                @else
                    <a href="{{ route($item['route']) }}"
                       class="font-[family-name:var(--ns-body)] text-base font-bold py-3 border-b border-[rgba(5,7,8,0.07)] {{ $current === $item['key'] ? 'text-magenta' : 'text-ink' }}">
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
            <div class="flex flex-col gap-3 pt-5">
                @auth('attendee')
                    <a href="{{ route('me') }}" class="ns-btn ns-btn-magenta w-full">
                        {{ __('attendee.nav.my_next_step') }}
                        @if ($unread = auth('attendee')->user()->unreadUpdates())
                            <span class="ns-num ms-2 inline-flex items-center justify-center min-w-[19px] h-[19px] px-[5px] rounded-full bg-white text-magenta text-[11px] font-bold leading-none">{{ $unread }}</span>
                        @endif
                    </a>
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
