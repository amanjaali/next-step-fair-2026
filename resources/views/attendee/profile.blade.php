<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[1040px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('attendee.profile.title') }}</span>
        </div>

        <div class="flex justify-between items-end gap-6 flex-wrap mb-9">
            <div class="flex items-center gap-5 min-w-0">
                {{-- Their picture if they added one, their initials if not. A grey
                     silhouette says nothing about anybody. --}}
                <div class="w-[72px] h-[72px] shrink-0 border border-[rgba(5,7,8,0.16)] bg-bone-200 overflow-hidden flex items-center justify-center">
                    @if ($registration->photoUrl())
                        <img src="{{ $registration->photoUrl() }}" alt="" class="w-full h-full object-cover">
                    @else
                        <span class="font-[family-name:var(--ns-display)] text-[24px] font-bold text-slate">
                            {{ $registration->initials() }}
                        </span>
                    @endif
                </div>

                <div class="min-w-0">
                    <h1 class="ns-h1 !text-[clamp(28px,4vw,42px)]">
                        {{ __('attendee.profile.welcome', ['name' => $registration->firstName()]) }}
                    </h1>
                    <a href="{{ route('me.edit') }}"
                       class="font-[family-name:var(--ns-body)] text-[13.5px] font-bold text-magenta">
                        {{ __('attendee.edit.edit_link') }}
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('attendee.signout') }}">
                @csrf
                <button type="submit" class="ns-btn ns-btn-ghost ns-btn-sm">{{ __('attendee.nav.sign_out') }}</button>
            </form>
        </div>


        @if (session('status'))
            <div class="bg-bone-200 p-5 mb-6 font-[family-name:var(--ns-body)] text-[15px]">{{ session('status') }}</div>
        @endif

        {{-- A scholarship is the largest thing this site hands anybody, so on
             their own account it comes before everything else. --}}
        @if ($award)
            <x-ns.award-badge :application="$award" class="mb-9" />
        @endif

        {{-- What the committee has decided, waiting where they will see it.

             Unopened updates are marked and lead straight to the application
             they are about; opening one is what marks it read, so a headline
             that scrolled past is still there next time. --}}
        @if ($updates->isNotEmpty())
            <div class="border border-[rgba(5,7,8,0.14)] bg-white mb-9">
                <div class="flex items-baseline justify-between gap-4 flex-wrap px-6 pt-5 pb-3">
                    <div>
                        <h2 class="font-[family-name:var(--ns-display)] text-[19px] font-semibold">{{ __('updates.title') }}</h2>
                        <p class="ns-meta text-[12.5px] mt-[3px]">{{ __('updates.lead') }}</p>
                    </div>
                    @if ($registration->unreadUpdates())
                        <span class="ns-eyebrow !text-[9.5px] !text-white bg-magenta px-[9px] py-[5px]">
                            {{ trans_choice('updates.unread', $registration->unreadUpdates(), ['count' => $registration->unreadUpdates()]) }}
                        </span>
                    @endif
                </div>

                @foreach ($updates as $update)
                    <a href="{{ route('me.updates.open', $update) }}"
                       @class([
                           'flex items-start gap-4 px-6 py-[15px] border-t border-[rgba(5,7,8,0.1)] text-ink hover:bg-bone-50 block',
                           'bg-bone-50' => $update->isUnread(),
                       ])>
                        <span @class([
                            'w-[10px] h-[10px] rounded-full shrink-0 mt-[6px]',
                            'bg-teal' => $update->isUnread() && $update->accent() === 'teal',
                            'bg-magenta' => $update->isUnread() && $update->accent() !== 'teal',
                            'bg-[rgba(5,7,8,0.18)]' => ! $update->isUnread(),
                        ])></span>

                        <span class="min-w-0 flex-1 block">
                            <span @class([
                                'font-[family-name:var(--ns-body)] text-[15px] block',
                                'font-bold' => $update->isUnread(),
                                'font-medium text-body-soft' => ! $update->isUnread(),
                            ])>{{ $update->title() }}</span>

                            <span class="ns-meta text-[12.5px] mt-[3px] block max-w-[62ch]">{{ $update->body() }}</span>

                            <span class="ns-meta text-[11.5px] mt-[5px] block">
                                <span class="ns-num">{{ $update->created_at->format('j M Y') }}</span>
                                @if ($update->isUnread())
                                    <span class="text-magenta font-bold"> · {{ __('updates.new') }}</span>
                                @endif
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif

                {{-- A visitor pass gets in, but has no agenda. Offer the upgrade rather than
             showing an empty agenda they cannot fill. --}}
        @if ($registration->isQuickPass())
            <div class="border-s-[6px] border-[#F2A93B] bg-[#FFF8EC] px-6 py-5 mb-9">
                <div class="font-[family-name:var(--ns-display)] text-[19px] font-semibold mb-1">
                    {{ __('attendee.profile.quick_pass') }}
                </div>
                <p class="ns-body !text-[14.5px] mb-4 max-w-[62ch]">{{ __('attendee.profile.quick_pass_note') }}</p>
                <a href="{{ route('register.fair') }}" class="ns-btn ns-btn-magenta ns-btn-sm">
                    {{ __('attendee.profile.quick_pass_upgrade') }}
                </a>
            </div>
        @endif

        {{-- What the registration is actually worth. A student registered once and
             everything below is already open to them — this says so, rather than
             asking them to fill in another form to find out. --}}
        @if ($registration->type === \App\Models\Registration::TYPE_STUDENT)
            <div class="border-s-[6px] border-magenta bg-white px-6 py-5 mb-9">
                <div class="font-[family-name:var(--ns-display)] text-[19px] font-semibold mb-1">
                    {{ __('attendee.services.title') }}
                </div>
                <p class="ns-body !text-[14.5px] mb-5 max-w-[64ch]">{{ __('attendee.services.lead') }}</p>

                <div class="grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('me.agenda') }}" class="border border-[rgba(5,7,8,0.16)] bg-bone-50 px-4 py-3 block hover:border-magenta">
                        <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold mb-[2px]">{{ __('attendee.services.agenda') }}</div>
                        <div class="ns-meta text-[12px]">{{ __('attendee.services.agenda_note') }}</div>
                    </a>

                    @if ($registration->canApplyForScholarship())
                        <a href="{{ route('scholarship.home') }}" class="border border-[rgba(5,7,8,0.16)] bg-bone-50 px-4 py-3 block hover:border-magenta">
                            <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold mb-[2px]">{{ __('attendee.services.scholarship') }}</div>
                            <div class="ns-meta text-[12px]">{{ __('attendee.services.scholarship_note') }}</div>
                        </a>
                    @endif

                    <a href="{{ route('opportunities') }}" class="border border-[rgba(5,7,8,0.16)] bg-bone-50 px-4 py-3 block hover:border-magenta">
                        <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold mb-[2px]">{{ __('attendee.services.opportunities') }}</div>
                        <div class="ns-meta text-[12px]">{{ __('attendee.services.opportunities_note') }}</div>
                    </a>

                    {{-- Optional, and framed that way. Nobody has to answer these to
                         use anything else. --}}
                    <a href="{{ route('me.interests') }}" class="border border-[rgba(5,7,8,0.16)] bg-bone-50 px-4 py-3 block hover:border-magenta">
                        <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold mb-[2px]">
                            {{ $registration->matches()->count()
                                ? __('attendee.matches.title')
                                : __('attendee.services.matches') }}
                        </div>
                        <div class="ns-meta text-[12px]">{{ __('attendee.services.matches_note') }}</div>
                    </a>
                </div>
            </div>
        @endif

        <div class="flex flex-wrap gap-10 items-start">
            <div class="flex-[1_1_460px] min-w-0">

                {{-- ---------------------------------------------- agenda -- --}}
                <section class="mb-12">
                    <div class="flex items-baseline justify-between gap-4 flex-wrap mb-4">
                        <h2 class="ns-h2 !text-[clamp(22px,2.4vw,28px)]">{{ __('attendee.profile.agenda') }}</h2>
                        @unless ($registration->isQuickPass())
                            <a href="{{ route('me.agenda') }}" class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">
                                {{ __('attendee.profile.edit_agenda') }}
                            </a>
                        @endunless
                    </div>

                    @forelse ($saved as $day => $sessions)
                        <div class="mb-6">
                            <div class="ns-eyebrow !text-[10.5px] mb-3">
                                {{ __('site.common.day', ['n' => $day]) }} · <span class="ns-num">{{ ns_day_date($day) }}</span>
                            </div>
                            @foreach ($sessions as $session)
                                <div class="flex items-start gap-4 border-t border-[rgba(5,7,8,0.14)] py-4">
                                    <span class="ns-num font-[family-name:var(--ns-display)] text-[18px] font-semibold w-[58px] shrink-0">
                                        {{ $session->timeLabel() }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-[family-name:var(--ns-body)] text-[15.5px] font-semibold leading-[1.35]">
                                            {{ $session->t('title') }}
                                        </div>
                                        <div class="ns-meta text-[12.5px] mt-[3px]">{{ $session->hallLabel() }}</div>
                                    </div>
                                    <form method="POST" action="{{ route('me.agenda.toggle', $session) }}">
                                        @csrf
                                        <button type="submit" class="ns-meta !text-crimson hover:underline bg-transparent border-0 cursor-pointer p-0">
                                            {{ __('attendee.agenda.remove') }}
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <p class="ns-body !text-[14.5px] text-body-soft max-w-[58ch]">{{ __('attendee.agenda.empty') }}</p>
                        @unless ($registration->isQuickPass())
                            <a href="{{ route('me.agenda') }}" class="ns-btn ns-btn-ghost ns-btn-sm mt-4">
                                {{ __('attendee.agenda.add') }}
                            </a>
                        @endunless
                    @endforelse
                </section>

                {{-- ------------------------------------------ attendance -- --}}
                <section class="mb-12">
                    <h2 class="ns-h2 !text-[clamp(22px,2.4vw,28px)] mb-4">{{ __('attendee.profile.attendance') }}</h2>
                    @forelse ($checkIns as $checkIn)
                        <div class="border-t border-[rgba(5,7,8,0.14)] py-[14px] font-[family-name:var(--ns-body)] text-[14.5px]">
                            <span class="ns-num">{{ __('attendee.profile.attendance_row', [
                                'day' => $checkIn->day,
                                'time' => $checkIn->checked_in_at?->format('H:i'),
                                'gate' => $checkIn->gate ?: 'A',
                            ]) }}</span>
                        </div>
                    @empty
                        <p class="ns-body !text-[14.5px] text-body-soft">{{ __('attendee.profile.attendance_empty') }}</p>
                    @endforelse
                </section>

                {{-- -------------------------------------------- messages -- --}}
                <section>
                    <h2 class="ns-h2 !text-[clamp(22px,2.4vw,28px)] mb-4">{{ __('attendee.profile.messages') }}</h2>
                    @forelse ($messages as $message)
                        <div class="border-t border-[rgba(5,7,8,0.14)] py-[14px]">
                            <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                <span class="font-[family-name:var(--ns-body)] text-[14.5px] font-semibold">
                                    {{ __('attendee.templates.'.$message->template_key) }}
                                </span>
                                <span class="ns-meta text-[12px] ns-num">
                                    {{ $message->created_at?->format('j M · H:i') }} · {{ $message->status }}
                                </span>
                            </div>
                            <p class="ns-body !text-[13.5px] text-body-soft mt-1 max-w-[62ch]">
                                {{ Str::limit($message->preview, 130) }}
                            </p>
                        </div>
                    @empty
                        <p class="ns-body !text-[14.5px] text-body-soft">{{ __('attendee.profile.messages_empty') }}</p>
                    @endforelse
                </section>
            </div>

            {{-- ------------------------------------------------- badge -- --}}
            <aside class="flex-[1_1_280px] max-w-[340px] min-w-0">
                <div class="ns-card !p-6 mb-5">
                    <div class="ns-eyebrow !text-[10.5px] mb-4">{{ __('attendee.profile.badge') }}</div>

                    @if ($qr)
                        <img src="{{ $qr }}" alt="{{ __('attendee.profile.badge') }}" class="w-full max-w-[210px] mx-auto block mb-4">
                    @endif

                    <dl class="m-0 mb-5">
                        <div class="flex flex-col gap-[3px] py-[9px] border-b border-[rgba(5,7,8,0.1)]">
                            <dt class="ns-eyebrow !text-[9.5px]">{{ __('attendee.profile.ticket') }}</dt>
                            <dd class="m-0 font-[family-name:var(--ns-display)] text-[15px] font-bold ns-num">{{ $registration->ticket_ref }}</dd>
                        </div>
                        <div class="flex flex-col gap-[3px] py-[9px]">
                            <dt class="ns-eyebrow !text-[9.5px]">{{ __('attendee.profile.days') }}</dt>
                            <dd class="m-0 font-[family-name:var(--ns-body)] text-sm text-body">{{ $registration->daysLabel() }}</dd>
                        </div>
                    </dl>

                    <p class="ns-body !text-[13px] text-body-soft mb-4">{{ __('attendee.profile.badge_note') }}</p>

                    @if ($registration->badgeIssued())
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('ticket.png', $registration->ticket_id) }}" class="ns-btn ns-btn-ghost ns-btn-sm w-full">
                                {{ __('attendee.profile.download_badge') }}
                            </a>
                            <a href="{{ route('ticket.ics', $registration->ticket_id) }}" class="ns-btn ns-btn-ghost ns-btn-sm w-full">
                                {{ __('attendee.profile.add_calendar') }}
                            </a>
                        </div>
                    @endif
                </div>

                <div class="ns-card !p-6">
                    <div class="ns-eyebrow !text-[10.5px] mb-3">{{ __('attendee.profile.details') }}</div>
                    <dl class="m-0">
                        @foreach (array_filter([
                            __('attendee.profile.name') => $registration->full_name,
                            __('attendee.profile.phone') => $registration->phone_country.' '.$registration->maskedPhone(),
                            __('attendee.profile.email') => $registration->email,
                            __('attendee.profile.city') => $registration->city,
                        ]) as $label => $value)
                            <div class="flex flex-col gap-[2px] py-[8px] border-b border-[rgba(5,7,8,0.08)]">
                                <dt class="ns-eyebrow !text-[9px]">{{ $label }}</dt>
                                <dd class="m-0 font-[family-name:var(--ns-body)] text-[13.5px] text-body">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <div class="ns-eyebrow !text-[10.5px] mt-5 mb-2">{{ __('attendee.profile.consents') }}</div>
                    <ul class="list-none m-0 p-0 flex flex-col gap-[6px]">
                        @foreach ([
                            'terms' => __('attendee.profile.consent_terms'),
                            'whatsapp' => __('attendee.profile.consent_whatsapp'),
                            'photography' => __('attendee.profile.consent_photography'),
                        ] as $key => $label)
                            <li class="font-[family-name:var(--ns-body)] text-[13px] text-body-soft flex gap-2">
                                <span>{{ ($registration->consents[$key] ?? false) ? '✓' : '—' }}</span>
                                <span>{{ $label }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.site>
