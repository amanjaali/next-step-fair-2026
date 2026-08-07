<x-layouts.site :title="$title" :description="$description" :navKey="$navKey">
    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :kicker="$page->t('kicker')" :title="$page->t('title')" :lead="$page->t('standfirst')">
            <div class="flex gap-3 flex-wrap mb-11">
                <a href="{{ route('register.fair') }}" class="ns-btn ns-btn-magenta">{{ __('site.cta.register_fair') }}</a>
                <a href="{{ route('universities') }}" class="ns-btn ns-btn-ghost">{{ __('site.cta.who_is_exhibiting') }}</a>
                <a href="{{ route('floorplan') }}" class="ns-btn ns-btn-ghost">{{ __('site.pages.floorplan.title') }}</a>
            </div>

            <div class="flex flex-wrap gap-14 items-start border-t border-[rgba(5,7,8,0.14)] pt-9">
                <div class="flex-[1_1_520px] min-w-0">
                    {{-- Numbered sections, paragraphs and bullet lists — the same shape
                         the admin edits, so an editor sees exactly what they type. --}}
                    @foreach ($sections as $section)
                        <section class="mb-11">
                            <div class="flex items-baseline gap-[14px] mb-[14px]">
                                <span class="ns-num font-[family-name:var(--ns-display)] text-[13px] font-bold text-magenta">{{ $section['n'] }}</span>
                                <h2 class="font-[family-name:var(--ns-display)] text-[28px] font-semibold leading-[1.15]">{{ $section['h'] }}</h2>
                            </div>
                            @foreach ($section['p'] as $paragraph)
                                <p class="ns-body mb-[18px] max-w-[68ch]">{{ $paragraph }}</p>
                            @endforeach
                            @if ($section['list'])
                                <ul class="list-none m-0 p-0 flex flex-col gap-[10px] mt-5">
                                    @foreach ($section['list'] as $item)
                                        <li class="font-[family-name:var(--ns-body)] text-base leading-[1.65] text-body-soft flex gap-[14px] max-w-[68ch]">
                                            <span class="ns-bar bg-magenta mt-[9px]"></span><span>{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </section>
                    @endforeach
                </div>

                <aside class="flex-[1_1_260px] max-w-[320px] min-w-0">
                    <div class="ns-card !p-6 mb-5">
                        <div class="ns-eyebrow !text-[10.5px] mb-[10px]">{{ __('site.common.location') }}</div>
                        <dl class="m-0">
                            {{-- Only the hours are isolated left-to-right; the dates carry
                                 a localised month name and follow the text direction. --}}
                            @foreach ([
                                [__('site.common.dates'), ns_event_dates(), false],
                                [__('site.common.venue'), config('nextstep.event.venue.name').', '.config('nextstep.event.venue.city'), false],
                                [__('site.common.hours'), config('nextstep.event.opening_hours'), true],
                            ] as [$label, $value, $isolate])
                                <div class="flex flex-col gap-[3px] py-[10px] border-b border-[rgba(5,7,8,0.1)]">
                                    <dt class="ns-eyebrow !text-[9.5px]">{{ $label }}</dt>
                                    <dd @class(['m-0 font-[family-name:var(--ns-body)] text-sm leading-[1.5] text-body', 'ns-num' => $isolate])>{{ $value }}</dd>
                                </div>
                            @endforeach
                            <div class="flex flex-col gap-[3px] pt-[10px]">
                                <dt class="ns-eyebrow !text-[9.5px]">{{ __('site.common.free_entry') }}</dt>
                                <dd class="m-0 font-[family-name:var(--ns-body)] text-sm leading-[1.5] text-body">{{ config('nextstep.event.venue.address.'.app()->getLocale()) }}</dd>
                            </div>
                        </dl>
                    </div>

                    @if (! empty($aside['title']))
                        <div class="ns-card !p-6 mb-5">
                            <div class="ns-eyebrow !text-[10.5px] mb-[10px]">{{ $aside['title'] }}</div>
                            <div class="font-[family-name:var(--ns-body)] text-sm leading-[1.6] text-body-soft mb-[14px]">{{ $aside['body'] }}</div>
                            <a href="mailto:{{ $aside['contact'] }}" class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">{{ $aside['contact'] }}</a>
                        </div>
                    @endif

                    <div class="ns-panel-ink !p-6">
                        <div class="ns-eyebrow !text-white/50 !text-[10.5px] mb-3">{{ __('site.cta.register_fair') }}</div>
                        <p class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.6] text-white/80 mb-4">{{ __('site.common.free_entry') }}</p>
                        <a href="{{ route('register.fair') }}" class="ns-btn ns-btn-magenta w-full">{{ __('site.cta.register_fair') }}</a>
                    </div>
                </aside>
            </div>

            @if ($sessions->isNotEmpty())
                <h2 class="ns-h2 !text-[clamp(24px,2.6vw,32px)] mb-6 mt-4">{{ __('site.pages.agenda.title') }}</h2>
                <div class="flex flex-col mb-8">
                    @foreach ($sessions as $session)
                        <x-ns.session-row :session="$session" :saveable="false" />
                    @endforeach
                </div>
                <a href="{{ route('agenda') }}" class="ns-btn ns-btn-ghost">{{ __('site.cta.full_agenda') }}</a>
            @endif
        </x-ns.page-head>
    </div>
</x-layouts.site>
