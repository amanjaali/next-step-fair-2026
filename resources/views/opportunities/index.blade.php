<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[1080px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('opportunities.kicker') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,46px)] mb-3">{{ __('opportunities.title') }}</h1>
        <p class="ns-body max-w-[60ch] mb-10">{{ __('opportunities.lead') }}</p>

        @if (! $attendee)
            {{-- Not signed in. Say what is behind it in one sentence rather than
                 showing a list they cannot use. --}}
            <div class="ns-card border-s-[6px] !border-s-magenta">
                <div class="font-[family-name:var(--ns-display)] text-[22px] font-semibold mb-3">{{ __('opportunities.locked_title') }}</div>
                <p class="ns-body mb-6 max-w-[58ch]">{{ __('opportunities.locked_body') }}</p>
                <div class="flex gap-3 flex-wrap">
                    <a href="{{ route('register.fair', ['type' => 'student']) }}" class="ns-btn ns-btn-magenta">{{ __('opportunities.locked_cta') }}</a>
                    <a href="{{ route('attendee.signin') }}" class="ns-btn ns-btn-ghost">{{ __('attendee.signin.title') }}</a>
                </div>
            </div>
        @else
            @if ($closingSoon->isNotEmpty())
                <div class="border-s-[6px] border-crimson bg-white px-6 py-5 mb-9">
                    <div class="font-[family-name:var(--ns-display)] text-[18px] font-semibold mb-2">{{ __('opportunities.closing_title') }}</div>
                    <ul class="list-none m-0 p-0 flex flex-col gap-2">
                        @foreach ($closingSoon as $opportunity)
                            <li class="font-[family-name:var(--ns-body)] text-[14.5px]">
                                <a href="{{ route('opportunities.show', $opportunity->slug) }}" class="font-bold">{{ $opportunity->t('title') }}</a>
                                <span class="ns-meta text-[12.5px] ns-num ms-2">
                                    {{ trans_choice('opportunities.closing_in', max($opportunity->daysLeft(), 0), ['count' => max($opportunity->daysLeft(), 0)]) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($opportunities->isNotEmpty())
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($opportunities as $opportunity)
                        <x-ns.opportunity-card :opportunity="$opportunity" />
                    @endforeach
                </div>
            @else
                <div class="ns-card">
                    <div class="font-[family-name:var(--ns-display)] text-[20px] font-semibold mb-2">{{ __('opportunities.empty_title') }}</div>
                    <p class="ns-body max-w-[58ch]">{{ __('opportunities.empty_body') }}</p>
                </div>
            @endif
        @endif
    </div>
</x-layouts.site>
