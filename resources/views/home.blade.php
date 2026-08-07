<x-layouts.site :title="$title" :navKey="$navKey">

    {{-- ------------------------------------------------------------- hero -- --}}
    <section class="bg-ink text-white relative overflow-hidden ns-track-rule">
        <div class="absolute inset-0 bg-ink-800 flex items-center justify-center">
            <span class="ns-eyebrow !text-white/25">{{ __('site.home.hero_media') }}</span>
        </div>
        <div class="absolute inset-0"
             style="background:linear-gradient(90deg, rgba(5,7,8,0.95) 0%, rgba(5,7,8,0.84) 55%, rgba(5,7,8,0.55) 100%)"></div>

        <div class="ns-wrap relative pt-[clamp(64px,9vw,112px)] pb-[clamp(56px,8vw,96px)]">
            <div class="flex items-center gap-[14px] mb-7 flex-wrap">
                <span class="ns-eyebrow !text-magenta">{{ __('site.common.edition_4') }}</span>
                <span class="w-9 h-px bg-white/30"></span>
                <span class="ns-eyebrow !text-white/70">{{ __('site.common.location') }}</span>
            </div>

            <h1 class="ns-display max-w-[14ch] mb-7">{{ config('nextstep.event.name') }}</h1>

            <p class="ns-lead !text-white/80 max-w-[56ch] mb-10">
                {{ __('site.home.hero_lead', ['universities' => 32, 'sessions' => 26]) }}
            </p>

            <div class="flex gap-9 items-start mb-11 flex-wrap">
                {{-- `isolate` forces a left-to-right run. It belongs on the opening
                     hours, which RTL bidi otherwise reorders into "20:00–10:00", but
                     not on the dates, where the month name is Arabic and the line
                     should follow the paragraph direction. --}}
                @foreach ([
                    ['label' => __('site.common.dates'), 'value' => ns_event_dates(), 'isolate' => false],
                    ['label' => __('site.common.venue'), 'value' => config('nextstep.event.venue.name').', '.config('nextstep.event.venue.city'), 'isolate' => false],
                    ['label' => __('site.common.hours'), 'value' => config('nextstep.event.opening_hours'), 'isolate' => true],
                ] as $fact)
                    <div>
                        <div class="ns-eyebrow !text-white/50 mb-2">{{ $fact['label'] }}</div>
                        <div @class([
                            'font-[family-name:var(--ns-display)] text-[23px] font-semibold',
                            'ns-num' => $fact['isolate'],
                        ])>{{ $fact['value'] }}</div>
                    </div>
                @endforeach
            </div>

            {{-- Registered already: the pitch is spent. What they need now is the
                 way in to the things registering was for. --}}
            @if ($attendee)
                <div class="flex gap-[14px] flex-wrap">
                    <a href="{{ route('me') }}" class="ns-btn ns-btn-lg ns-btn-magenta">{{ __('site.home.signed_in.my_badge') }}</a>
                    <a href="{{ route('agenda') }}" class="ns-btn ns-btn-lg ns-btn-ghost !text-white !border-white/40 hover:!bg-white/10">
                        {{ __('site.home.signed_in.browse_programme') }}
                    </a>
                </div>
            @else
                <div class="flex gap-[14px] flex-wrap">
                    <a href="{{ route('register.fair') }}" class="ns-btn ns-btn-lg ns-btn-magenta">{{ __('site.cta.register_fair') }}</a>
                    <a href="{{ route('register.conference') }}" class="ns-btn ns-btn-lg ns-btn-cobalt">{{ __('site.cta.conference_rsvp') }}</a>
                </div>
            @endif
        </div>
    </section>

    @if ($attendee)
        {{-- ------------------------------------------- what to do next -- --}}
        <section class="bg-bone-200 border-b border-[rgba(5,7,8,0.14)]">
            <div class="ns-wrap py-[clamp(32px,4.5vw,56px)]">
                <div class="flex items-end justify-between gap-6 flex-wrap mb-7">
                    <div>
                        <div class="ns-eyebrow !text-magenta mb-2">{{ __('site.home.signed_in.kicker') }}</div>
                        <h2 class="ns-h2 !text-[clamp(24px,3vw,34px)]">
                            {{ __('site.home.signed_in.welcome', ['name' => $attendee->firstName()]) }}
                        </h2>
                    </div>
                    <div class="ns-meta text-[13px] ns-num">
                        {{ __('site.home.signed_in.ticket', ['ticket' => $attendee->ticket_ref]) }}
                    </div>
                </div>

                @php
                    // Only offer what this person can actually use. A parent has no
                    // scholarship to apply for, and a card that leads to a locked
                    // page is worse than no card.
                    $isStudent = $attendee->type === \App\Models\Registration::TYPE_STUDENT;

                    $next = array_values(array_filter([
                        [
                            'title' => __('site.home.signed_in.cards.agenda'),
                            'note' => $savedCount
                                ? trans_choice('site.home.signed_in.cards.agenda_saved', $savedCount, ['count' => $savedCount])
                                : __('site.home.signed_in.cards.agenda_empty'),
                            'href' => $isStudent ? route('me.agenda') : route('agenda'),
                            'accent' => 'border-magenta',
                        ],
                        $attendee->canApplyForScholarship() ? [
                            'title' => __('site.home.signed_in.cards.scholarship'),
                            'note' => __('site.home.signed_in.cards.scholarship_note'),
                            'href' => route('scholarship.apply'),
                            'accent' => 'border-magenta',
                        ] : null,
                        [
                            'title' => __('site.home.signed_in.cards.opportunities'),
                            'note' => __('site.home.signed_in.cards.opportunities_note'),
                            'href' => route('opportunities'),
                            'accent' => 'border-teal',
                        ],
                        [
                            'title' => __('site.home.signed_in.cards.zankoline'),
                            'note' => __('site.home.signed_in.cards.zankoline_note'),
                            'href' => route('scholarships'),
                            'accent' => 'border-cobalt',
                        ],
                        [
                            'title' => __('site.home.signed_in.cards.universities'),
                            'note' => __('site.home.signed_in.cards.universities_note'),
                            'href' => route('universities'),
                            'accent' => 'border-cobalt',
                        ],
                        [
                            'title' => __('site.home.signed_in.cards.seminars'),
                            // The real count, not a number written into the copy that
                            // goes stale the first time a session is added.
                            'note' => __('site.home.signed_in.cards.seminars_note', ['count' => $sessionCount]),
                            'href' => route('seminars'),
                            'accent' => 'border-teal',
                        ],
                    ]));
                @endphp

                <div class="grid gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($next as $card)
                        <a href="{{ $card['href'] }}" class="bg-white px-6 py-[22px] block no-underline hover:bg-bone-50 border-s-4 {{ $card['accent'] }}">
                            <div class="font-[family-name:var(--ns-display)] text-[18px] font-semibold text-ink mb-1">{{ $card['title'] }}</div>
                            <div class="ns-meta text-[12.5px]">{{ $card['note'] }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- --------------------------------------------- opportunities -- --}}
        @if ($opportunities->isNotEmpty())
            <section class="ns-wrap pt-[clamp(44px,6vw,80px)]">
                <div class="flex items-end justify-between gap-6 flex-wrap mb-7">
                    <div>
                        <div class="ns-eyebrow !text-magenta mb-2">{{ __('opportunities.kicker') }}</div>
                        <h2 class="ns-h2 !text-[clamp(24px,3vw,36px)]">{{ __('opportunities.home_title') }}</h2>
                        <p class="ns-body !text-[15px] text-body-soft max-w-[56ch] mt-2">{{ __('opportunities.home_lead') }}</p>
                    </div>
                    <a href="{{ route('opportunities') }}" class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">
                        {{ __('opportunities.see_all') }}
                    </a>
                </div>

                <div class="grid gap-5 md:grid-cols-3">
                    @foreach ($opportunities as $opportunity)
                        <x-ns.opportunity-card :opportunity="$opportunity" />
                    @endforeach
                </div>
            </section>
        @endif
    @endif

    {{-- --------------------------------------------------------- counters -- --}}
    <section class="bg-ink-900 text-white border-t border-white/10">
        <div class="ns-wrap ns-counters">
            @foreach ($counters as $counter)
                <div>
                    <div class="ns-stat ns-num text-[clamp(30px,4vw,44px)] mb-2">{{ $counter['value'] }}</div>
                    <div class="ns-eyebrow !text-white/55">{{ $counter['label'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ------------------------------------------------------- two tracks -- --}}
    <section class="ns-wrap pt-[clamp(56px,7vw,96px)]">
        <div class="flex items-baseline gap-5 mb-9">
            <span class="ns-eyebrow">{{ __('site.home.two_tracks') }}</span>
            <span class="ns-rule"></span>
        </div>

        <div class="ns-hairgrid md:grid-cols-2">
            <div class="p-[clamp(28px,4vw,52px)] flex flex-col gap-[22px] border-t-[6px] border-magenta">
                <span class="ns-eyebrow !text-magenta">{{ __('site.home.fair_kicker') }}</span>
                <h2 class="ns-h2 !text-[clamp(26px,3.2vw,40px)]">{{ __('site.home.fair_title') }}</h2>
                <p class="ns-body max-w-[46ch]">{{ __('site.home.fair_body') }}</p>
                <ul class="list-none m-0 p-0 flex flex-col gap-[10px]">
                    @foreach ([
                        __('site.home.universities_title', ['count' => 32]),
                        __('site.footer.links.seminars'),
                        __('site.pages.scholarships.title'),
                        __('site.common.free_entry'),
                    ] as $point)
                        <li class="font-[family-name:var(--ns-body)] text-[14.5px] flex gap-3 items-start">
                            <span class="ns-bar bg-magenta mt-2"></span><span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-auto pt-[14px]">
                    @if ($attendee)
                        <a href="{{ route('fair') }}" class="ns-btn ns-btn-magenta">{{ __('site.cta.explore_expo') }}</a>
                    @else
                        <a href="{{ route('register.fair') }}" class="ns-btn ns-btn-magenta">{{ __('site.cta.register_fair') }}</a>
                    @endif
                </div>
            </div>

            <div class="p-[clamp(28px,4vw,52px)] flex flex-col gap-[22px] border-t-[6px] border-cobalt">
                <span class="ns-eyebrow !text-cobalt">{{ __('site.home.conf_kicker') }}</span>
                <h2 class="ns-h2 !text-[clamp(26px,3.2vw,40px)]">{{ __('site.home.conf_title') }}</h2>
                <p class="ns-body max-w-[46ch]">{{ __('site.home.conf_body') }}</p>
                <ul class="list-none m-0 p-0 flex flex-col gap-[10px]">
                    @foreach ([
                        __('site.pages.conference.programme'),
                        __('site.pages.conference.themes'),
                        'KU · AR · EN',
                        __('rsvp.step2.letter'),
                    ] as $point)
                        <li class="font-[family-name:var(--ns-body)] text-[14.5px] flex gap-3 items-start">
                            <span class="ns-bar bg-cobalt mt-2"></span><span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-auto pt-[14px]">
                    @if ($attendee)
                        <a href="{{ route('conference') }}" class="ns-btn ns-btn-cobalt">{{ __('site.cta.explore_conference') }}</a>
                    @else
                        <a href="{{ route('register.conference') }}" class="ns-btn ns-btn-cobalt">{{ __('site.cta.conference_rsvp') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ------------------------------------------------------------ about -- --}}
    <section class="ns-wrap ns-section">
        <div class="grid gap-16 lg:grid-cols-2 items-start">
            <div class="ns-rise">
                <span class="ns-eyebrow">{{ __('site.home.about_kicker') }}</span>
                <h2 class="ns-h2 !text-[clamp(30px,4vw,48px)] mt-[22px] mb-[26px]">{{ __('site.home.about_title') }}</h2>
                @foreach (['about_p1', 'about_p2', 'about_p3'] as $paragraph)
                    <p class="ns-body max-w-[62ch] mb-[18px]">{{ __('site.home.'.$paragraph) }}</p>
                @endforeach
                <a href="{{ route('about') }}" class="ns-link">{{ __('site.cta.about_next_step') }}</a>
            </div>
            <x-ns.frame :label="__('site.home.about_photo')" center height="470px" class="ns-rise" />
        </div>
    </section>

    {{-- ------------------------------------------------------- why attend -- --}}
    <section class="bg-white border-y border-[rgba(5,7,8,0.12)]">
        <div class="ns-wrap ns-section-tight">
            <div class="flex items-baseline justify-between gap-6 flex-wrap mb-11">
                <h2 class="ns-h2">{{ __('site.home.why_attend') }}</h2>
                <span class="ns-meta text-[13.5px]">{{ __('site.common.free_entry') }}</span>
            </div>

            <div class="ns-hairgrid sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($featureCards as $card)
                    <div class="px-[34px] pt-[38px] pb-[42px] ns-rise">
                        {{-- Bar composition from the logo's Step element, one magenta bar each. --}}
                        <svg viewBox="0 0 24 24" width="34" height="34" class="block mb-6" aria-hidden="true">
                            <path d="{{ $card->icon_path }}" fill="#050708"></path>
                            <path d="{{ $card->icon_accent }}" fill="#B64698"></path>
                        </svg>
                        <h3 class="font-[family-name:var(--ns-body)] text-xl font-bold mb-3 tracking-[-0.01em]">{{ $card->t('title') }}</h3>
                        <p class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.65] text-slate">{{ $card->t('body') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- -------------------------------------------------------------- SDG -- --}}
    <section class="ns-wrap ns-section">
        <div class="flex items-baseline gap-5 mb-[14px]">
            <span class="ns-eyebrow">{{ __('site.home.sdg_kicker') }}</span>
            <span class="ns-rule"></span>
        </div>

        <div class="grid gap-14 lg:grid-cols-[1.1fr_1fr] items-end mb-9">
            <h2 class="ns-h2">{{ __('site.home.sdg_title') }}</h2>
            <p class="ns-body">{{ __('site.home.sdg_body') }}</p>
        </div>

        <div class="grid gap-3 grid-cols-2 lg:grid-cols-5 mb-7">
            @foreach ($sdgGoals as $goal)
                <div class="text-white px-5 pt-[22px] pb-[26px] min-h-[190px] flex flex-col"
                     style="background:{{ $goal->color }}">
                    <div class="font-[family-name:var(--ns-display)] text-[11px] font-bold tracking-[0.16em] mb-[10px] ns-num">
                        SDG {{ $goal->number }}
                    </div>
                    <div class="font-[family-name:var(--ns-display)] text-[22px] font-semibold leading-[1.1] mb-auto">
                        {{ $goal->t('title') }}
                    </div>
                    <div class="font-[family-name:var(--ns-body)] text-[12.5px] font-medium leading-[1.45] text-white/90 pt-4">
                        {{ $goal->t('metric') }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex items-center gap-[18px] flex-wrap border-t border-[rgba(5,7,8,0.14)] pt-[26px]">
            <a href="{{ route('sdg') }}" class="ns-btn ns-btn-ink">{{ __('site.cta.our_sdg') }}</a>
            <a href="{{ config('nextstep.links.act4sdgs') }}" target="_blank" rel="noopener"
               class="font-[family-name:var(--ns-body)] text-[13.5px] font-bold text-cobalt border-b-2 border-cobalt pb-[2px]">
                {{ __('site.cta.verify_act4sdgs') }}
            </a>
        </div>
    </section>

    {{-- ------------------------------------------------- featured speakers -- --}}
    <section class="bg-ink text-white">
        <div class="ns-wrap ns-section-tight">
            <div class="flex items-baseline justify-between gap-6 flex-wrap mb-11">
                <h2 class="ns-h2">{{ __('site.home.featured_speakers') }}</h2>
                <a href="{{ route('speakers') }}" class="ns-btn ns-btn-ghost-light ns-btn-sm">
                    {{ __('site.cta.all_speakers', ['count' => $speakers->count()]) }}
                </a>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($speakers as $speaker)
                    <x-ns.speaker-card :speaker="$speaker" dark :showSessions="false" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- --------------------------------------------------- agenda preview -- --}}
    <section class="ns-wrap ns-section">
        <div class="flex items-baseline justify-between gap-6 flex-wrap mb-8">
            <h2 class="ns-h2">{{ __('site.home.agenda_preview') }}</h2>
            <a href="{{ route('agenda') }}" class="ns-btn ns-btn-ghost ns-btn-sm">{{ __('site.cta.full_agenda') }}</a>
        </div>

        <div class="ns-tabs mb-7">
            @foreach (config('nextstep.event.days') as $number => $meta)
                <a href="{{ route('home', ['day' => $number]) }}#agenda"
                   @class(['ns-tab', 'is-on' => $agendaDay === $number])>{{ __('site.common.day', ['n' => $number]) }}</a>
            @endforeach
        </div>

        <div id="agenda" class="ns-hairgrid lg:grid-cols-2">
            @foreach ($agendaPreview as $session)
                <div class="p-[26px] px-7 flex gap-6">
                    <div class="ns-num font-[family-name:var(--ns-display)] text-[19px] font-semibold min-w-[96px]">
                        {{ $session->timeLabel() }}
                    </div>
                    <div>
                        <div class="flex items-center gap-[9px] mb-2 flex-wrap">
                            <span class="ns-typechip {{ $session->chipClass() }}">{{ $session->type }}</span>
                            <span class="ns-meta text-xs">{{ $session->hallLabel() }}</span>
                        </div>
                        <div class="font-[family-name:var(--ns-body)] text-base font-bold leading-[1.35] mb-[6px]">
                            {{ $session->t('title') }}
                        </div>
                        <div class="ns-meta">{{ $session->t('who') }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ----------------------------------------------------- universities -- --}}
    <section class="bg-white border-t border-[rgba(5,7,8,0.12)]">
        <div class="ns-wrap ns-section-tight">
            <div class="flex items-baseline justify-between gap-6 flex-wrap mb-9">
                <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)]">
                    {{ __('site.home.universities_title', ['count' => $universities->count()]) }}
                </h2>
                <a href="{{ route('universities') }}" class="ns-btn ns-btn-ghost ns-btn-sm">{{ __('site.cta.who_is_exhibiting') }}</a>
            </div>

            <div class="ns-hairgrid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($universities as $university)
                    <div class="h-[104px] flex items-center justify-center p-[14px] text-center">
                        @if ($university->logo_path)
                            <img src="{{ asset('storage/'.$university->logo_path) }}" alt="{{ $university->t('name') }}"
                                 class="max-h-[64px] w-auto" loading="lazy">
                        @else
                            <span class="font-[family-name:var(--ns-body)] text-[11.5px] font-medium leading-[1.35] text-muted">
                                {{ $university->t('name') }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ------------------------------------------------ partners/sponsors -- --}}
    <section class="ns-wrap ns-section-tight">
        <div class="flex items-baseline justify-between gap-6 flex-wrap mb-10">
            <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)]">{{ __('site.home.partners_title') }}</h2>
            <a href="{{ route('sponsors') }}" class="ns-btn ns-btn-ghost ns-btn-sm">
                {{ __('site.cta.all_partners', ['count' => collect($sponsorTiers)->sum(fn ($t) => $t['items']->count())]) }}
            </a>
        </div>

        @foreach ($sponsorTiers as $tier)
            @continue($tier['items']->isEmpty())
            <div class="border-t border-[rgba(5,7,8,0.14)] pt-[26px] pb-[34px] grid gap-10 lg:grid-cols-[220px_1fr] items-start">
                <div>
                    <div class="ns-eyebrow !text-ink !tracking-[0.18em] mb-[7px]">{{ $tier['tier'] }}</div>
                    <div class="ns-meta leading-[1.5]">{{ $tier['note'] }}</div>
                </div>
                <div class="flex gap-[14px] flex-wrap">
                    @foreach ($tier['items'] as $item)
                        <div class="bg-bone-200 flex items-center justify-center p-[10px] text-center"
                             style="height:{{ $tier['height'] }};width:{{ $tier['width'] }}">
                            @if ($item->logo_path)
                                <img src="{{ asset('assets/'.$item->logo_path) }}" alt="{{ $item->t('name') }}"
                                     class="max-h-full max-w-full object-contain" loading="lazy">
                            @else
                                <span class="font-[family-name:var(--ns-body)] text-[11.5px] font-medium text-muted leading-[1.3]">
                                    {{ $item->t('name') }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="border-t border-[rgba(5,7,8,0.14)] pt-[30px] flex gap-[14px] flex-wrap">
            <a href="{{ route('sponsors') }}" class="ns-btn ns-btn-cobalt">{{ __('site.cta.become_sponsor') }}</a>
            <a href="{{ route('exhibit') }}" class="ns-btn ns-btn-ghost">{{ __('site.cta.exhibit') }}</a>
        </div>
    </section>

    {{-- ------------------------------------------------------------- news -- --}}
    <section class="bg-white border-t border-[rgba(5,7,8,0.12)]">
        <div class="ns-wrap ns-section-tight">
            <div class="flex items-baseline justify-between gap-6 flex-wrap mb-9">
                <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)]">{{ __('site.home.news_title') }}</h2>
                <a href="{{ route('news') }}" class="ns-link">{{ __('site.cta.all_news') }}</a>
            </div>

            <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($news as $post)
                    <x-ns.post-card :post="$post" :showReadTime="false" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------------------------------------------- media gallery -- --}}
    <section class="ns-wrap ns-section-tight">
        <div class="flex items-baseline justify-between gap-6 flex-wrap mb-7">
            <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)]">{{ __('site.home.media_title') }}</h2>
            <a href="{{ route('media') }}" class="ns-link">{{ __('site.cta.photos_videos') }}</a>
        </div>

        <div class="grid gap-3 grid-cols-2 lg:grid-cols-4 auto-rows-[180px]">
            @foreach ($mediaStrip->take(5) as $index => $item)
                <a href="{{ route('media.photos') }}"
                   @class(['ns-frame', 'row-span-2' => $index === 0])>
                    <span>{{ $item->category }}</span>
                </a>
            @endforeach

            @if ($featuredVideo)
                <a href="{{ route('media.videos') }}"
                   class="bg-ink row-span-2 flex flex-col justify-between p-5 text-white hover:text-white">
                    <span class="ns-eyebrow !text-white/50">{{ __('site.pages.media.videos') }}</span>
                    <div>
                        <div class="w-[34px] h-[34px] border-2 border-white flex items-center justify-center mb-3">
                            <span class="text-xs">▶</span>
                        </div>
                        <span class="font-[family-name:var(--ns-display)] text-lg font-semibold">{{ $featuredVideo->t('alt') }}</span>
                    </div>
                </a>
            @endif
        </div>
    </section>

    {{-- ---------------------------------------------------- past editions -- --}}
    <section class="bg-white border-t border-[rgba(5,7,8,0.12)]">
        <div class="ns-wrap ns-section-tight">
            <div class="flex items-baseline justify-between gap-6 flex-wrap mb-9">
                <h2 class="ns-h2 !text-[clamp(26px,3.2vw,38px)]">{{ __('site.home.archive_title') }}</h2>
                <span class="ns-meta text-[13.5px] max-w-[60ch]">{{ __('site.home.archive_note') }}</span>
            </div>

            <div class="grid gap-7 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($editions as $edition)
                    <a href="{{ route('archive.show', $edition->year) }}"
                       class="text-ink hover:text-ink border border-[rgba(5,7,8,0.14)] block group">
                        <x-ns.frame :label="$edition->year.' cover'" ratio="16/9" center />
                        <div class="p-[26px]">
                            <div class="ns-stat ns-num text-[34px] mb-[14px] group-hover:text-magenta">{{ $edition->year }}</div>
                            <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold mb-[6px]">
                                {{ collect($edition->stats)->take(2)->map(fn ($s) => $s['v'].' '.($s['k'][app()->getLocale()] ?? $s['k']['en'] ?? ''))->implode(' · ') }}
                            </div>
                            <div class="ns-meta text-[13.5px]">{{ $edition->t('headline') }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ------------------------------------------------------------ venue -- --}}
    <section class="ns-wrap ns-section-tight">
        <div class="grid gap-14 lg:grid-cols-[1fr_1.15fr] items-stretch">
            <div>
                <span class="ns-eyebrow">{{ __('site.home.venue_kicker') }}</span>
                <h2 class="ns-h2 mt-5 mb-6">{{ __('site.home.venue_title') }}</h2>

                <div class="flex flex-col gap-[18px] mb-[26px]">
                    @foreach (config('nextstep.event.venue.address') as $code => $address)
                        <div>
                            <div class="ns-eyebrow !text-[10.5px] mb-[5px]">{{ config("nextstep.locales.$code.label") }}</div>
                            <div dir="{{ config("nextstep.locales.$code.dir") }}"
                                 class="font-[family-name:{{ $code === 'en' ? 'var(--ns-body)' : "'Noto Sans Arabic'" }}] text-[15.5px] leading-[1.6]">
                                {{ $address }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-[rgba(5,7,8,0.14)] pt-5 flex flex-col gap-2">
                    @foreach (__('site.home.venue_notes') as $note)
                        <div class="font-[family-name:var(--ns-body)] text-sm text-body-soft">{{ $note }}</div>
                    @endforeach
                </div>
            </div>

            <a href="{{ config('nextstep.event.venue.map_url') }}" target="_blank" rel="noopener"
               class="ns-frame ns-frame-center min-h-[400px] relative">
                <span>{{ __('site.home.venue_map') }}</span>
                <span class="absolute left-1/2 top-1/2 w-[14px] h-[14px] bg-magenta"></span>
            </a>
        </div>
    </section>

    @push('schema')
        @php
            // Event + Organization JSON-LD. Built in PHP because Blade parses
            // an @-prefixed string inside a directive as a directive.
            $eventSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'Event',
                'name' => config('nextstep.event.name'),
                'startDate' => config('nextstep.event.start_date'),
                'endDate' => config('nextstep.event.end_date'),
                'eventStatus' => 'https://schema.org/EventScheduled',
                'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                'inLanguage' => ['en', 'ckb', 'ar'],
                'image' => [asset('assets/brand/nextstep-transparent-sm.png')],
                'description' => __('site.seo.default_description'),
                'location' => [
                    '@type' => 'Place',
                    'name' => config('nextstep.event.venue.name'),
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => config('nextstep.event.venue.address.en'),
                        'addressLocality' => config('nextstep.event.venue.city'),
                        'addressCountry' => 'IQ',
                    ],
                    'geo' => [
                        '@type' => 'GeoCoordinates',
                        'latitude' => config('nextstep.event.venue.latitude'),
                        'longitude' => config('nextstep.event.venue.longitude'),
                    ],
                ],
                'organizer' => [
                    '@type' => 'Organization',
                    'name' => config('nextstep.event.organisation'),
                    'url' => config('app.url'),
                    'logo' => asset('assets/brand/nextstep-transparent-sm.png'),
                    'email' => config('nextstep.contact.general'),
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'IQD',
                    'availability' => 'https://schema.org/InStock',
                    'url' => route('register.fair'),
                    'validFrom' => now()->toIso8601String(),
                ],
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($eventSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endpush

</x-layouts.site>
