<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[820px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <a href="{{ route('opportunities') }}" class="ns-meta text-[13px] mb-6 inline-block">← {{ __('opportunities.title') }}</a>

        <div class="flex items-center gap-3 mb-4 flex-wrap">
            <span class="ns-eyebrow !text-magenta">{{ __("opportunities.kinds.{$opportunity->kind}") }}</span>
            @if ($opportunity->isClosingSoon())
                <span class="ns-urgent-pill ns-num font-[family-name:var(--ns-body)] text-[12px]">
                    {{ trans_choice('opportunities.closing_in', max($opportunity->daysLeft(), 0), ['count' => max($opportunity->daysLeft(), 0)]) }}
                </span>
            @endif
        </div>

        <h1 class="ns-h1 !text-[clamp(28px,3.8vw,42px)] mb-4">{{ $opportunity->t('title') }}</h1>
        <p class="ns-body !text-[clamp(16px,1.6vw,18px)] max-w-[60ch] mb-8">{{ $opportunity->t('summary') }}</p>

        <div class="flex items-center gap-4 pb-8 mb-8 border-b border-[rgba(5,7,8,0.14)]">
            @if ($opportunity->logo())
                <img src="{{ $opportunity->logo() }}" alt="" class="h-11 w-auto max-w-[130px] object-contain">
            @endif
            <div>
                <div class="ns-eyebrow !text-[9.5px] mb-1">{{ __('opportunities.offered_by') }}</div>
                <div class="font-[family-name:var(--ns-body)] text-[15px] font-bold">{{ $opportunity->partner() }}</div>
            </div>
        </div>

        <div class="grid gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] sm:grid-cols-3 mb-9">
            @foreach (array_filter([
                __('opportunities.closes') => $opportunity->closes_at ? ns_format_date($opportunity->closes_at) : __('opportunities.no_deadline'),
                __('opportunities.places_label') => $opportunity->places,
                __('opportunities.who') => __("opportunities.audiences.{$opportunity->audience}"),
            ]) as $label => $value)
                <div class="bg-white px-5 py-4">
                    <div class="ns-eyebrow !text-[9px] mb-1">{{ $label }}</div>
                    <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold ns-num">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        @if ($opportunity->t('body'))
            <div class="ns-prose max-w-[62ch] mb-9">{!! $opportunity->t('body') !!}</div>
        @endif

        @if ($opportunity->t('eligibility'))
            <h2 class="ns-h2 !text-[clamp(20px,2.3vw,26px)] mb-3">{{ __('opportunities.eligibility') }}</h2>
            <div class="ns-prose max-w-[62ch] mb-9">{!! $opportunity->t('eligibility') !!}</div>
        @endif

        @if ($opportunity->action_url)
            @if ($attendee)
                <a href="{{ route('opportunities.go', $opportunity->slug) }}" target="_blank" rel="noopener"
                   class="ns-btn ns-btn-lg ns-btn-magenta">{{ $opportunity->actionLabel() }}</a>
                <p class="ns-meta text-[12.5px] mt-4 max-w-[54ch]">{{ __('opportunities.action_note') }}</p>
            @else
                <div class="ns-card border-s-[6px] !border-s-magenta">
                    <p class="ns-body !text-[15px] mb-5 max-w-[56ch]">{{ __('opportunities.locked_body') }}</p>
                    <a href="{{ route('register.fair', ['type' => 'student']) }}" class="ns-btn ns-btn-magenta ns-btn-sm">{{ __('opportunities.locked_cta') }}</a>
                </div>
            @endif
        @endif

        @if ($related->isNotEmpty())
            <h2 class="ns-h2 !text-[clamp(20px,2.3vw,26px)] mt-14 mb-6">{{ __('opportunities.related') }}</h2>
            <div class="grid gap-5 md:grid-cols-3">
                @foreach ($related as $other)
                    <x-ns.opportunity-card :opportunity="$other" />
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.site>
