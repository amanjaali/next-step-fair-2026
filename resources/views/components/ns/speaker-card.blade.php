@props(['speaker', 'dark' => false, 'showSessions' => true])

{{-- Square headshot with a uniform treatment, so mixed-quality photos still
     read as one set. The track bar is the wayfinding colour. --}}
<div @class([
    'flex flex-col',
    'bg-white border border-[rgba(5,7,8,0.12)]' => ! $dark,
])>
    <a href="{{ route('speakers.show', $speaker) }}" class="block">
        <x-ns.frame :src="$speaker->photoUrl()" :alt="$speaker->t('name')" ratio="1/1" center
                    :label="__('site.pages.speakers.title')"
                    :class="$dark ? '!bg-ink-700 !text-white/30' : ''" />
    </a>

    <div @class(['flex flex-col', 'p-[22px] pb-6' => ! $dark, 'pt-[14px]' => $dark])>
        <div class="flex items-center gap-2 mb-[10px]">
            <span class="w-4 h-[5px] shrink-0" style="background:{{ $speaker->accent() }}"></span>
            <span @class(['ns-eyebrow !text-[10px] !tracking-[0.18em]', '!text-white/55' => $dark])>
                {{ $speaker->speaker_type }}
            </span>
            @if ($speaker->country !== 'IQ')
                <span @class(['ns-meta ms-auto text-[11px]', '!text-white/55' => $dark])>{{ $speaker->country }}</span>
            @endif
        </div>

        <a href="{{ route('speakers.show', $speaker) }}"
           @class([
               'font-[family-name:var(--ns-display)] text-[19px] font-semibold leading-[1.15] mb-[7px] block',
               'text-ink hover:text-magenta' => ! $dark,
               'text-white hover:text-white' => $dark,
           ])>{{ $speaker->t('name') }}</a>

        <div @class(['font-[family-name:var(--ns-body)] text-[13.5px] leading-[1.5]', 'text-body-soft' => ! $dark, 'text-white/62' => $dark])>
            {{ $speaker->t('role') }}
        </div>
        <div @class(['font-[family-name:var(--ns-body)] text-[13.5px] leading-[1.5] text-slate', '!text-white/62' => $dark])>
            {{ $speaker->t('organization') }}
        </div>

        @if ($showSessions && ! $dark && $speaker->relationLoaded('sessions') && $speaker->sessions->isNotEmpty())
            <div class="flex gap-[6px] flex-wrap mt-[14px]">
                @foreach ($speaker->sessions->take(2) as $session)
                    <a href="{{ route('agenda', ['day' => $session->day]) }}"
                       class="font-[family-name:var(--ns-body)] text-[11.5px] font-semibold text-ink border border-[rgba(5,7,8,0.2)] px-[9px] py-[6px] hover:border-magenta">
                        {{ __('site.common.day', ['n' => $session->day]) }} · <span class="ns-num">{{ $session->timeLabel() }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
