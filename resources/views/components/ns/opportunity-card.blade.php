@props(['opportunity'])

@php
    $daysLeft = $opportunity->daysLeft();
@endphp

<a href="{{ route('opportunities.show', $opportunity->slug) }}"
   {{-- The top rule turns crimson too, so a card with a deadline reads as urgent
        from across the grid, not only once you have found the small pill. --}}
   class="bg-white border border-[rgba(5,7,8,0.1)] border-t-4 {{ $opportunity->isClosingSoon() ? 'border-t-crimson' : 'border-t-magenta' }} ns-radius ns-shadow-sm p-6 flex flex-col no-underline transition-shadow duration-200 hover:border-t-ink">

    <div class="flex items-start justify-between gap-4 mb-4">
        <span class="ns-eyebrow !text-[9.5px] !text-magenta">{{ __("opportunities.kinds.{$opportunity->kind}") }}</span>

        {{-- Only said when it is nearly true. A countdown on something six months
             away is noise; on something closing next week it is the whole point. --}}
        @if ($opportunity->isClosingSoon())
            <span class="ns-urgent-pill ns-num font-[family-name:var(--ns-body)] text-[11.5px]">
                {{ trans_choice('opportunities.closing_in', max($daysLeft, 0), ['count' => max($daysLeft, 0)]) }}
            </span>
        @endif
    </div>

    <div class="font-[family-name:var(--ns-display)] text-[19px] font-semibold leading-[1.25] text-ink mb-2">
        {{ $opportunity->t('title') }}
    </div>

    <p class="ns-body !text-[14px] text-body-soft mb-5 flex-1">{{ $opportunity->t('summary') }}</p>

    <div class="flex items-center gap-3 pt-4 border-t border-[rgba(5,7,8,0.1)] mt-auto">
        @if ($opportunity->logo())
            <img src="{{ $opportunity->logo() }}" alt="" class="h-7 w-auto max-w-[76px] object-contain shrink-0">
        @endif
        <span class="ns-meta text-[12px] min-w-0">{{ $opportunity->partner() }}</span>

        @if ($opportunity->places)
            <span class="ns-meta text-[12px] ns-num ms-auto whitespace-nowrap">
                {{ trans_choice('opportunities.places', $opportunity->places, ['count' => $opportunity->places]) }}
            </span>
        @endif
    </div>
</a>
