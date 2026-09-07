@props([
    'application',
    'link' => true,
])

{{--
    The mark of a scholarship holder.

    Forty places out of thousands of applications: the one thing on this site
    worth drawing as an award rather than as a row in a table. It is dark, it
    carries a seal, and it says what it is without being read closely — which is
    what a student screenshots and sends to their family.
--}}
<div {{ $attributes->merge(['class' => 'relative overflow-hidden bg-ink text-white px-[clamp(20px,3vw,34px)] py-[clamp(20px,3vw,28px)] flex items-center gap-[clamp(16px,2.4vw,28px)] flex-wrap']) }}>

    {{-- A quiet wash so the card is not a flat black box. --}}
    <span aria-hidden="true"
          class="absolute -top-[70px] -end-[70px] w-[220px] h-[220px] rounded-full opacity-[0.18]"
          style="background:radial-gradient(circle,var(--color-teal) 0%,transparent 70%)"></span>

    <span class="relative w-[76px] h-[76px] shrink-0 rounded-full bg-teal flex items-center justify-center">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 2.5l2.6 5.5 6 .85-4.35 4.2 1.05 6L12 16.2 6.7 19.05l1.05-6L3.4 8.85l6-.85L12 2.5z"
                  fill="#fff"/>
        </svg>
    </span>

    <div class="relative min-w-0 flex-1">
        <div class="ns-eyebrow !text-white/55 mb-[7px]">{{ __('scholarship.status.award.eyebrow') }}</div>
        <div class="font-[family-name:var(--ns-display)] text-[clamp(21px,2.5vw,29px)] font-semibold leading-[1.12]">
            {{ __('scholarship.status.award.title') }}
        </div>
        <div class="font-[family-name:var(--ns-body)] text-[13px] text-white/60 mt-[7px]">
            <span class="ns-num">{{ __('scholarship.status.award.note', ['cycle' => $application->cycle]) }}</span>
            @if ($application->decided_at)
                <span aria-hidden="true"> · </span>
                <span class="ns-num">{{ $application->decided_at->format('j M Y') }}</span>
            @endif
        </div>
    </div>

    @if ($link)
        <a href="{{ route('scholarship.status') }}"
           class="relative ns-btn ns-btn-sm !bg-white !text-ink !border-white shrink-0">
            {{ __('scholarship.status.award.link') }}
        </a>
    @endif
</div>
