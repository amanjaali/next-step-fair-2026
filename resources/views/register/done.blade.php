@php session()->flash('conversion', $conversion); @endphp
<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[1080px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('register.done.kicker') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.4vw,46px)] mb-3">
            {{ __('register.done.title', ['name' => $registration->firstName()]) }}
        </h1>
        <p class="ns-body max-w-[60ch] mb-9">{{ __('register.done.lead') }}</p>

        @if (session('status'))
            <div class="bg-bone-200 p-5 mb-9 font-[family-name:var(--ns-body)] text-[15px] max-w-[62ch]">{{ session('status') }}</div>
        @endif

        <div class="grid gap-8 lg:grid-cols-[1fr_1.05fr] items-start">

            {{-- The badge as the registrant sees it, magenta for the fair track. --}}
            <div class="bg-magenta text-white px-8 pt-[34px] pb-[30px]">
                {{-- The same arrangement as the badge that is printed and sent:
                     the event on one side, the partnership on the other, and the
                     type below. Somebody holding one and looking at this should
                     see the same badge. --}}
                <div class="flex justify-between items-start gap-4 mb-5">
                    <div>
                        <div class="font-[family-name:var(--ns-display)] text-[21px] font-bold leading-[1.1]">
                            Next Step Fair<br>{{ config('nextstep.event.year') }}
                        </div>
                        <div class="ns-eyebrow !text-white/75 !text-[10px] mt-[7px]">{{ __('site.common.edition_4') }}</div>
                    </div>

                    <x-ns.partnership />
                </div>

                <div class="mb-[22px]">
                    <span class="bg-white text-magenta font-[family-name:var(--ns-display)] text-[10.5px] font-bold tracking-[0.18em] px-[10px] py-[6px]">
                        {{ $registration->typeChip() }}
                    </span>
                </div>

                <div class="font-[family-name:var(--ns-display)] text-[clamp(22px,3vw,30px)] font-bold leading-[1.05] tracking-[-0.02em] mb-[7px]">
                    {{ $registration->full_name }}
                </div>
                <div class="font-[family-name:var(--ns-body)] text-sm text-white/85 mb-6">
                    {{ $registration->city }} · {{ $registration->daysLabel() }}
                </div>

                <div class="bg-white p-4 inline-block">
                    <img src="{{ $qrUrl }}" alt="QR" width="180" height="180" class="block w-[180px] h-[180px]">
                </div>

                <div class="font-[family-name:var(--ns-display)] text-[11px] tracking-[0.12em] text-white/75 mt-[14px]">
                    {{-- Only the reference is Latin. Isolating the whole line instead put
                         the Arabic word for "ticket" on the wrong end of it. --}}
                    {!! __('register.done.ticket', ['id' => '<span class="ns-num">'.e($registration->ticket_ref).'</span>']) !!}
                </div>
            </div>

            <div>
                <div class="ns-card mb-5">
                    <div class="ns-eyebrow !text-[11px] mb-4">{{ __('register.done.next_steps') }}</div>
                    <div class="flex flex-col gap-3">
                        <a href="{{ route('ticket.png', $registration->ticket_id) }}" class="ns-btn ns-btn-ink !justify-start">{{ __('register.done.download_png') }}</a>
                        <a href="{{ route('ticket.pdf', $registration->ticket_id) }}" class="ns-btn ns-btn-ghost !justify-start">{{ __('register.done.download_pdf') }}</a>
                        <a href="{{ route('ticket.ics', $registration->ticket_id) }}" class="ns-btn ns-btn-ghost !justify-start">{{ __('register.done.calendar') }}</a>
                    </div>
                </div>

                <div class="ns-card mb-5">
                    <div class="ns-eyebrow !text-[11px] mb-4">{{ __('register.done.delivery') }}</div>
                    <div class="flex flex-col gap-3">
                        @foreach ($registration->messages()->latest()->take(3)->get() as $message)
                            <div class="flex gap-3 items-center">
                                <span class="w-[9px] h-[9px] shrink-0 {{ $message->isFailed() ? 'bg-crimson' : 'bg-magenta' }}"></span>
                                <span class="font-[family-name:var(--ns-body)] text-sm">
                                    {{ __('register.done.delivered_to', [
                                        'phone' => $registration->phone_country.' '.$registration->maskedPhone(),
                                        'status' => $message->status,
                                    ]) }}
                                </span>
                            </div>
                        @endforeach
                        <div class="flex gap-3 items-center">
                            <span class="w-[9px] h-[9px] bg-cobalt shrink-0"></span>
                            <span class="font-[family-name:var(--ns-body)] text-sm">
                                {{ __('register.done.reminder_scheduled', ['date' => ns_format_date(\Illuminate\Support\Carbon::parse(config('nextstep.event.start_date'))->subDays(3), false)]) }}
                            </span>
                        </div>
                        <div class="flex gap-3 items-center">
                            <span class="w-[9px] h-[9px] bg-cobalt shrink-0"></span>
                            <span class="font-[family-name:var(--ns-body)] text-sm">
                                {{ __('register.done.directions_scheduled', ['date' => ns_day_date(1).', 08:00']) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-bone-200 px-[26px] py-6 font-[family-name:var(--ns-body)] text-sm leading-[1.65] text-body-soft">
                    {{ __('register.done.privacy_note') }}
                </div>

                <div class="mt-6 flex gap-[14px] flex-wrap">
                    <a href="{{ route('agenda') }}" class="ns-btn ns-btn-ghost !text-magenta !border-[rgba(182,70,152,0.5)]">{{ __('site.cta.build_agenda') }}</a>
                    <a href="{{ route('home') }}" class="ns-btn ns-btn-ghost">{{ __('site.cta.back_home') }}</a>
                </div>
            </div>
        </div>

        <div class="mt-14 pt-12 border-t border-[rgba(5,7,8,0.14)]">
        </div>
    </div>
</x-layouts.site>
