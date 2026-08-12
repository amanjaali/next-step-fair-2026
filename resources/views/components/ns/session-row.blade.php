@props(['session', 'saveable' => true])

<div class="grid gap-7 border-t border-[rgba(5,7,8,0.14)] py-[26px] items-start lg:grid-cols-[130px_minmax(0,1fr)_220px]">
    <div>
        <div class="ns-num font-[family-name:var(--ns-display)] text-[22px] font-semibold">{{ $session->timeLabel() }}</div>
        <div class="ns-meta ns-num mt-1 text-[12.5px]">{{ $session->duration_label }}</div>
    </div>

    <div>
        <div class="flex items-center gap-[9px] mb-[9px] flex-wrap">
            <span class="ns-typechip {{ $session->chipClass() }}">{{ $session->typeLabel() }}</span>
            <span class="ns-meta text-xs">{{ $session->hallLabel() }} · {{ $session->languages }}</span>
        </div>

        <h3 class="font-[family-name:var(--ns-display)] text-2xl font-semibold leading-[1.15] mb-2">
            {{ $session->t('title') }}
        </h3>

        <p class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.6] text-slate max-w-[66ch]">
            {{ $session->t('description') }}
        </p>

        @if ($session->relationLoaded('speakers') && $session->speakers->isNotEmpty())
            <div class="flex flex-wrap gap-x-3 gap-y-1 mt-[10px]">
                @foreach ($session->speakers as $speaker)
                    <a href="{{ route('speakers.show', $speaker) }}"
                       class="font-[family-name:var(--ns-body)] text-[13.5px] font-semibold text-cobalt">
                        {{ $speaker->t('name') }}@if (! $loop->last)<span class="text-slate"> ·</span>@endif
                    </a>
                @endforeach
            </div>
        @elseif ($session->t('who'))
            <div class="font-[family-name:var(--ns-body)] text-[13.5px] font-semibold text-cobalt mt-[10px]">
                {{ $session->t('who') }}
            </div>
        @endif
    </div>

    <div class="flex lg:justify-end">
        @if ($saveable && $session->bookable)
            {{-- The same button whether or not they are signed in. A guest's press is
                 held in the session and applied the moment they finish registering,
                 so nobody has to find the session again afterwards. --}}
            @php($isSaved = in_array($session->id, ns_saved_session_ids(), true))
            <form method="POST" action="{{ route('me.agenda.toggle', $session) }}">
                @csrf
                <button type="submit" @class([
                    'ns-btn ns-btn-sm whitespace-nowrap',
                    'ns-btn-ghost' => ! $isSaved,
                    'ns-btn-magenta' => $isSaved,
                ])>
                    {{ $isSaved ? __('attendee.agenda.saved_label') : __('site.pages.agenda.add_to_agenda') }}
                </button>
            </form>
        @elseif ($session->track === 'conference' && ! $session->bookable)
            {{-- Says why there is no button, rather than leaving a gap that reads
                 as something failing to load. --}}
            <span class="ns-meta text-[12.5px] lg:text-end">{{ __('site.pages.agenda.by_invitation') }}</span>
        @endif
    </div>
</div>
