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
            {{-- Most of what partners bring is aimed at students. A parent or a
                 delegate should be told that plainly, and given the one thing
                 they can usefully do, rather than left wondering why the board
                 looks thin. --}}
            @if ($attendee->type !== \App\Models\Registration::TYPE_STUDENT)
                @php
                    $pass = __('opportunities.for_students.message', ['url' => route('opportunities')]);
                @endphp
                <div class="border-s-[6px] border-magenta bg-white px-6 py-5 mb-9">
                    <div class="font-[family-name:var(--ns-display)] text-[18px] font-semibold mb-1">
                        {{ __('opportunities.for_students.title') }}
                    </div>
                    <p class="ns-body !text-[14.5px] text-body-soft max-w-[62ch] mb-4">
                        {{ __('opportunities.for_students.body') }}
                    </p>
                    <a href="https://wa.me/?text={{ rawurlencode($pass) }}" target="_blank" rel="noopener"
                       class="ns-btn ns-btn-magenta ns-btn-sm">{{ __('opportunities.for_students.send') }}</a>
                </div>
            @endif

            @if ($closingSoon->isNotEmpty())
                <div class="ns-urgent px-6 py-5 mb-9">
                    <div class="flex items-center gap-2.5 mb-3">
                        <span class="ns-urgent-dot" aria-hidden="true"></span>
                        <span class="ns-eyebrow !text-[10px] !text-white">{{ __('opportunities.closing_title') }}</span>
                    </div>
                    <ul class="list-none m-0 p-0 flex flex-col gap-3">
                        @foreach ($closingSoon as $opportunity)
                            <li class="font-[family-name:var(--ns-body)] text-[15px] flex flex-wrap items-center gap-x-3 gap-y-1.5">
                                <a href="{{ route('opportunities.show', $opportunity->slug) }}" class="font-bold">{{ $opportunity->t('title') }}</a>
                                <span class="ns-urgent-count ns-num text-[12px]">
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
