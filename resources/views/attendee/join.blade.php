<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[760px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('attendee.agenda.title') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3">{{ __('attendee.join.title') }}</h1>
        <p class="ns-body max-w-[58ch] mb-6">{{ __('attendee.join.lead') }}</p>

        {{-- The session they pressed on is held in the browser session and attached
             the moment they finish either route below, so the click is never lost. --}}
        @if ($pendingSession)
            <div class="border-s-[6px] border-magenta bg-white px-6 py-5 mb-9">
                <div class="ns-eyebrow !text-[10.5px] mb-2">{{ __('attendee.join.pending', ['session' => '']) }}</div>
                <div class="font-[family-name:var(--ns-display)] text-[21px] font-semibold leading-[1.2]">
                    {{ $pendingSession->t('title') }}
                </div>
                <div class="ns-meta ns-num mt-1">
                    {{ __('site.common.day', ['n' => $pendingSession->day]) }} ·
                    {{ $pendingSession->timeLabel() }} · {{ $pendingSession->hallLabel() }}
                </div>
            </div>
        @endif

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="ns-card flex flex-col">
                <h2 class="font-[family-name:var(--ns-display)] text-[22px] font-semibold mb-2">
                    {{ __('attendee.join.register') }}
                </h2>
                <p class="ns-body !text-[14.5px] mb-6 flex-1">{{ __('attendee.join.register_note') }}</p>
                <a href="{{ route('register.fair') }}" class="ns-btn ns-btn-magenta w-full">
                    {{ __('site.cta.register_fair') }}
                </a>
            </div>

            <div class="ns-card flex flex-col">
                <h2 class="font-[family-name:var(--ns-display)] text-[22px] font-semibold mb-2">
                    {{ __('attendee.join.signin') }}
                </h2>
                <p class="ns-body !text-[14.5px] mb-6 flex-1">{{ __('attendee.join.signin_note') }}</p>
                <a href="{{ route('attendee.signin') }}" class="ns-btn ns-btn-ghost w-full">
                    {{ __('attendee.nav.sign_in') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts.site>
