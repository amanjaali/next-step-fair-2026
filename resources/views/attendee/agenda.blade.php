<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('attendee.profile.title') }}</span>
        </div>

        <div class="flex justify-between items-end gap-6 flex-wrap mb-4">
            <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)]">{{ __('attendee.agenda.title') }}</h1>
            <a href="{{ route('me') }}" class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">
                {{ __('attendee.profile.title') }}
            </a>
        </div>

        <p class="ns-body max-w-[62ch] mb-4">{{ __('attendee.agenda.lead') }}</p>
        <p class="ns-meta mb-9">{{ trans_choice('attendee.agenda.count', count($saved), ['count' => count($saved)]) }}</p>

        @if (session('status'))
            <div class="bg-bone-200 p-5 mb-8 font-[family-name:var(--ns-body)] text-[15px]">{{ session('status') }}</div>
        @endif

        @foreach ($days as $day => $sessions)
            <section class="mb-12">
                <div class="flex items-baseline gap-3 mb-2">
                    <h2 class="ns-h2 !text-[clamp(22px,2.4vw,30px)]">{{ __('site.common.day', ['n' => $day]) }}</h2>
                    <span class="ns-meta ns-num">{{ ns_day_date($day) }}</span>
                </div>

                @foreach ($sessions as $session)
                    @php($isSaved = in_array($session->id, $saved, true))
                    <div @class([
                        'grid gap-6 border-t border-[rgba(5,7,8,0.14)] py-[22px] items-start lg:grid-cols-[110px_minmax(0,1fr)_190px]',
                        'bg-white/70' => $isSaved,
                    ])>
                        <div class="ns-num font-[family-name:var(--ns-display)] text-[20px] font-semibold">
                            {{ $session->timeLabel() }}
                        </div>

                        <div class="min-w-0">
                            <div class="flex items-center gap-[9px] mb-2 flex-wrap">
                                <span class="ns-typechip {{ $session->chipClass() }}">{{ $session->typeLabel() }}</span>
                                <span class="ns-meta text-xs">{{ $session->hallLabel() }}</span>
                            </div>
                            <h3 class="font-[family-name:var(--ns-display)] text-[21px] font-semibold leading-[1.2] mb-1">
                                {{ $session->t('title') }}
                            </h3>
                            <p class="font-[family-name:var(--ns-body)] text-[14px] leading-[1.55] text-slate max-w-[64ch]">
                                {{ $session->t('description') }}
                            </p>
                        </div>

                        <div class="flex lg:justify-end">
                            <form method="POST" action="{{ route('me.agenda.toggle', $session) }}">
                                @csrf
                                <button type="submit" @class([
                                    'ns-btn ns-btn-sm whitespace-nowrap',
                                    'ns-btn-magenta' => ! $isSaved,
                                    'ns-btn-ghost' => $isSaved,
                                ])>
                                    {{ $isSaved ? __('attendee.agenda.remove') : __('attendee.agenda.add') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </section>
        @endforeach
    </div>
</x-layouts.site>
