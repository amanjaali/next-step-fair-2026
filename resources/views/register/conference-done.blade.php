@php session()->flash('conversion', $conversion); @endphp
<x-layouts.site :title="$title" :navKey="$navKey" track="conference">
    <div class="ns-wrap max-w-[1080px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-cobalt"></span>
            <span class="ns-eyebrow !text-cobalt">{{ __('rsvp.done.kicker') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.4vw,46px)] mb-3">
            {{ $pending ? __('rsvp.done.title_pending') : __('rsvp.done.title') }}
        </h1>
        <p class="ns-body max-w-[62ch] mb-9">
            {{ $pending
                ? __('rsvp.done.lead_pending', ['email' => $registration->email])
                : __('rsvp.done.lead', ['email' => $registration->email]) }}
        </p>

        <div class="grid gap-8 lg:grid-cols-[1fr_1.05fr] items-start">

            {{-- Cobalt badge, carrying the institution as the second line. --}}
            <div class="bg-cobalt text-white px-8 pt-[34px] pb-[30px] {{ $pending ? 'opacity-60' : '' }}">
                <div class="flex justify-between items-start gap-4 mb-[26px]">
                    <div>
                        <div class="font-[family-name:var(--ns-display)] text-[20px] font-bold leading-[1.1]">
                            Next Step<br>Conference {{ config('nextstep.event.year') }}
                        </div>
                        <div class="ns-eyebrow !text-white/75 !text-[10px] mt-[7px]">
                            {{ __('site.common.day', ['n' => 1]) }} · {{ ns_day_date(1) }}
                        </div>
                    </div>
                    <span class="bg-white text-cobalt font-[family-name:var(--ns-display)] text-[10.5px] font-bold tracking-[0.18em] px-[10px] py-[6px]">
                        {{ $registration->typeChip() }}
                    </span>
                </div>

                <div class="font-[family-name:var(--ns-display)] text-[clamp(21px,2.8vw,28px)] font-bold leading-[1.05] tracking-[-0.02em] mb-[7px]">
                    {{ $registration->full_name }}
                </div>
                <div class="font-[family-name:var(--ns-body)] text-[15px] font-bold leading-[1.4] mb-[3px]">{{ $registration->organization }}</div>
                <div class="font-[family-name:var(--ns-body)] text-[13.5px] text-white/82 mb-[22px]">{{ $registration->position }}</div>

                @if ($pending)
                    <div class="bg-white/15 p-5 font-[family-name:var(--ns-body)] text-sm leading-[1.6]">
                        {{ __('rsvp.step2.review_note') }}
                    </div>
                @else
                    <div class="bg-white p-4 inline-block">
                        <img src="{{ $qrUrl }}" alt="QR" width="170" height="170" class="block w-[170px] h-[170px]">
                    </div>
                    <div class="ns-num font-[family-name:var(--ns-display)] text-[11px] tracking-[0.12em] text-white/80 mt-[14px]">
                        {{ __('register.done.ticket', ['id' => $registration->ticket_ref]) }}
                    </div>
                @endif
            </div>

            <div>
                <div class="ns-card mb-5">
                    <div class="ns-eyebrow !text-[11px] mb-4">{{ __('rsvp.done.inbox') }}</div>
                    <div class="flex flex-col gap-3">
                        @foreach (__('rsvp.done.contents') as $line)
                            <div class="flex gap-3 items-start">
                                <span class="w-[9px] h-[9px] bg-cobalt mt-[6px] shrink-0"></span>
                                <span class="font-[family-name:var(--ns-body)] text-sm leading-[1.5]">{{ $line }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                @unless ($pending)
                    <div class="ns-card mb-5">
                        <div class="ns-eyebrow !text-[11px] mb-4">{{ __('rsvp.done.actions') }}</div>
                        <div class="flex flex-col gap-3">
                            <a href="{{ route('ticket.pdf', $registration->ticket_id) }}" class="ns-btn ns-btn-ink !justify-start">{{ __('rsvp.done.download_pdf') }}</a>
                            <a href="{{ route('ticket.ics', $registration->ticket_id) }}" class="ns-btn ns-btn-ghost !justify-start">{{ __('rsvp.done.calendar') }}</a>
                            <a href="{{ \Illuminate\Support\Facades\URL::signedRoute('rsvp.manage', ['ticket' => $registration->ticket_id]) }}"
                               class="ns-btn ns-btn-ghost !justify-start">{{ __('rsvp.done.modify') }}</a>
                        </div>
                    </div>
                @endunless

                <div class="bg-bone-200 px-[26px] py-6 font-[family-name:var(--ns-body)] text-sm leading-[1.65] text-body-soft">
                    {{ __('rsvp.done.gate_note', ['email' => config('nextstep.contact.protocol')]) }}
                </div>

                <div class="mt-6 flex gap-[14px] flex-wrap">
                    <a href="{{ route('agenda', ['day' => 1]) }}" class="ns-btn ns-btn-ghost !text-cobalt !border-[rgba(44,75,224,0.5)]">{{ __('rsvp.done.day1_programme') }}</a>
                    <a href="{{ route('home') }}" class="ns-btn ns-btn-ghost">{{ __('site.cta.back_home') }}</a>
                </div>

            </div>
        </div>

        <div class="mt-14 pt-12 border-t border-[rgba(5,7,8,0.14)]">
            <x-ns.share-block :registration="$registration" />
        </div>
    </div>
</x-layouts.site>
