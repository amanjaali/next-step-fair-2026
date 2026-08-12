<x-layouts.scholarship :title="$title" :scholarshipNav="$scholarshipNav" :attendee="$attendee" :application="$application">
    <div class="ns-wrap max-w-[880px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('scholarship.apply.kicker', ['cycle' => $cycle]) }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,46px)] mb-3">{{ __('scholarship.apply.gate_heading') }}</h1>
        <p class="ns-body max-w-[58ch] mb-6">{{ __('scholarship.apply.gate_lead') }}</p>

        {{-- Sent here from a page they could not open yet. Says so, rather than
             leaving them wondering why the link went somewhere else. --}}
        @if (session('gate_blocked'))
            <div class="border-s-[5px] border-magenta bg-white ns-radius px-5 py-4 mb-8 max-w-[62ch]" role="status">
                <p class="ns-body !text-[14.5px] !m-0">{{ __('scholarship.eligibility.gate_blocked') }}</p>
            </div>
        @endif

        <div class="mb-10"></div>

        @php
            // Three gates, shown together with their state. Discovering them one at
            // a time — sign up, then a check, then a form — is how an application
            // gets abandoned halfway.
            $steps = [
                [
                    'n' => 1,
                    'title' => __('scholarship.apply.gate1_title'),
                    'body' => __('scholarship.apply.gate1_body'),
                    'done' => $hasAccount,
                    'cta' => $hasAccount ? null : __('scholarship.apply.gate1_cta'),
                    'href' => route('register.fair', ['type' => 'student']),
                    'state' => $hasAccount ? __('scholarship.apply.state_done') : __('scholarship.apply.state_required'),
                ],
                [
                    'n' => 2,
                    'title' => __('scholarship.apply.gate2_title'),
                    'body' => __('scholarship.apply.gate2_body'),
                    'done' => $checkPassed,
                    'cta' => $isEligibleStudent ? ($checkPassed ? __('scholarship.apply.gate2_review') : __('scholarship.apply.gate2_cta')) : null,
                    'href' => route('scholarship.eligibility'),
                    'state' => $checkPassed
                        ? __('scholarship.apply.state_passed')
                        : ($isEligibleStudent ? __('scholarship.apply.state_required') : __('scholarship.apply.state_locked')),
                ],
                [
                    'n' => 3,
                    'title' => __('scholarship.apply.gate3_title'),
                    'body' => __('scholarship.apply.gate3_body'),
                    'done' => $application?->isSubmitted() ?? false,
                    'cta' => $checkPassed ? __('scholarship.apply.gate3_cta') : null,
                    'href' => $application?->isSubmitted() ? route('scholarship.status') : route('scholarship.apply.form'),
                    'state' => $application?->isSubmitted()
                        ? __('scholarship.apply.state_submitted')
                        : ($checkPassed ? __('scholarship.apply.state_open') : __('scholarship.apply.state_locked')),
                ],
            ];
        @endphp

        <div class="grid gap-5 md:grid-cols-3 mb-12">
            @foreach ($steps as $step)
                <div @class([
                    'border-t-4 px-6 py-6 flex flex-col',
                    'bg-white border-teal' => $step['done'],
                    'bg-white border-magenta' => ! $step['done'] && $step['cta'],
                    'bg-bone-50 border-[#C9C4BF]' => ! $step['done'] && ! $step['cta'],
                ])>
                    <div class="flex items-center gap-3 mb-3">
                        <span @class([
                            'w-7 h-7 flex items-center justify-center font-[family-name:var(--ns-display)] text-[13px] font-bold text-white ns-num',
                            'bg-teal' => $step['done'],
                            'bg-magenta' => ! $step['done'] && $step['cta'],
                            'bg-[#C9C4BF]' => ! $step['done'] && ! $step['cta'],
                        ])>{{ $step['done'] ? '✓' : $step['n'] }}</span>
                        <span @class([
                            'ns-eyebrow !text-[10px]',
                            '!text-teal' => $step['done'],
                            '!text-magenta' => ! $step['done'] && $step['cta'],
                            '!text-muted' => ! $step['done'] && ! $step['cta'],
                        ])>{{ $step['state'] }}</span>
                    </div>

                    <div class="font-[family-name:var(--ns-display)] text-[19px] font-semibold mb-2">{{ $step['title'] }}</div>
                    <p class="ns-body !text-[14px] text-body-soft mb-5 flex-1">{{ $step['body'] }}</p>

                    @if ($step['cta'])
                        <a href="{{ $step['href'] }}" @class([
                            'ns-btn ns-btn-sm w-full',
                            'ns-btn-magenta' => ! $step['done'],
                            'ns-btn-ghost' => $step['done'],
                        ])>{{ $step['cta'] }}</a>
                    @endif
                </div>
            @endforeach
        </div>

        @unless ($hasAccount)
            <div class="ns-card border-s-[6px] !border-s-magenta mb-12">
                <p class="ns-body !text-[15px] mb-4 max-w-[58ch]">{{ __('scholarship.apply.have_account') }}</p>
                <a href="{{ route('attendee.signin') }}" class="ns-btn ns-btn-ghost ns-btn-sm">{{ __('attendee.signin.title') }}</a>
            </div>
        @endunless

        @if ($hasAccount && ! $isEligibleStudent)
            {{-- A registered student who is not finishing school. Say why rather than
                 leaving a locked card with no explanation. --}}
            <div class="ns-card border-s-[6px] !border-s-[#C08A1E] mb-12">
                <div class="font-[family-name:var(--ns-display)] text-[18px] font-semibold mb-2">{{ __('scholarship.apply.not_eligible_title') }}</div>
                <p class="ns-body !text-[15px] max-w-[58ch]">{{ __('scholarship.apply.not_eligible_body') }}</p>
            </div>
        @endif

        {{-- What to have ready. Two of these take a fortnight to obtain, which is
             the single most useful thing to know before starting. --}}
        <h2 class="ns-h2 !text-[clamp(21px,2.4vw,27px)] mb-2">{{ __('scholarship.apply.ready_title') }}</h2>
        <p class="ns-body !text-[15px] text-body-soft max-w-[58ch] mb-6">{{ __('scholarship.apply.ready_lead') }}</p>

        <div class="border border-[rgba(5,7,8,0.14)]">
            @foreach (__('scholarship.apply.ready') as $item)
                <div class="flex justify-between gap-6 px-5 py-[13px] border-b border-[rgba(5,7,8,0.1)] last:border-b-0 flex-wrap">
                    <span class="font-[family-name:var(--ns-body)] text-[14.5px] font-medium">{{ $item['what'] }}</span>
                    <span class="ns-meta text-[12.5px]">{{ $item['note'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.scholarship>
