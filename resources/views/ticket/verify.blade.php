<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[720px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        @if ($valid)
            <div class="text-white p-[clamp(24px,4vw,40px)]" style="background:{{ $registration->accent() }}">
                <div class="ns-eyebrow !text-white/75 mb-4">{{ __('checkin.states.valid') }}</div>
                <h1 class="ns-h1 !text-[clamp(28px,4vw,42px)] mb-3">{{ $registration->full_name }}</h1>
                @if ($registration->organization)
                    <div class="font-[family-name:var(--ns-body)] text-[16px] font-bold mb-1">{{ $registration->organization }}</div>
                @endif
                <div class="font-[family-name:var(--ns-body)] text-[15px] text-white/85">
                    {{ strtoupper($registration->type) }} · {{ $registration->daysLabel() }}
                </div>
                <div class="ns-num font-[family-name:var(--ns-display)] text-[11px] tracking-[0.12em] text-white/75 mt-6">
                    {{ __('register.done.ticket', ['id' => $registration->ticket_ref]) }}
                </div>
            </div>
            <p class="ns-meta mt-6">{{ __('register.done.privacy_note') }}</p>
        @else
            <div class="bg-crimson text-white p-[clamp(24px,4vw,40px)]">
                <div class="ns-eyebrow !text-white/75 mb-4">{{ __('checkin.states.invalid') }}</div>
                <h1 class="ns-h1 !text-[clamp(28px,4vw,42px)] mb-3">{{ __('checkin.not_recognised') }}</h1>
                <p class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.6] text-white/85">{{ __('checkin.invalid_detail') }}</p>
            </div>
        @endif

        <div class="mt-8 flex gap-3 flex-wrap">
            <a href="{{ route('home') }}" class="ns-btn ns-btn-ghost">{{ __('site.cta.back_home') }}</a>
        </div>
    </div>
</x-layouts.site>
