<x-layouts.scholarship :title="$title" :scholarshipNav="$scholarshipNav" :attendee="$attendee" :application="$application">
    <div class="ns-wrap max-w-[860px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('scholarship.status.kicker', ['cycle' => $application->cycle]) }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(28px,4vw,44px)] mb-3">
            {{ __('scholarship.status.heading', ['name' => $attendee->firstName()]) }}
        </h1>

        @if (session('status'))
            <div class="bg-bone-200 p-5 mb-8 font-[family-name:var(--ns-body)] text-[15px] max-w-[62ch]">{{ session('status') }}</div>
        @endif

        @unless ($application->isSubmitted())
            <div class="ns-card border-s-[6px] !border-s-magenta mb-9">
                <p class="ns-body mb-5 max-w-[58ch]">{{ __('scholarship.status.not_submitted') }}</p>
                <a href="{{ route('scholarship.apply.form') }}" class="ns-btn ns-btn-magenta ns-btn-sm">{{ __('scholarship.status.continue') }}</a>
            </div>
        @endunless

        {{-- Where it is, in a queue that only moves forwards. --}}
        <div class="border border-[rgba(5,7,8,0.14)] bg-white mb-9">
            @foreach ($application->timeline() as $stage)
                <div class="flex items-start gap-4 px-6 py-[15px] border-b border-[rgba(5,7,8,0.1)] last:border-b-0">
                    <span @class([
                        'w-[22px] h-[22px] shrink-0 mt-[2px] flex items-center justify-center text-[11px] font-bold text-white',
                        'bg-teal' => $stage['state'] === 'done',
                        'bg-magenta' => $stage['state'] === 'now',
                        'bg-[#C9C4BF]' => $stage['state'] === 'todo',
                    ])>{{ $stage['state'] === 'done' ? '✓' : '·' }}</span>

                    <div class="min-w-0">
                        <div @class([
                            'font-[family-name:var(--ns-body)] text-[15px] font-bold',
                            'text-muted' => $stage['state'] === 'todo',
                        ])>{{ __("scholarship.status.stages.{$stage['key']}.title") }}</div>
                        <div class="ns-meta text-[12.5px] mt-[2px] max-w-[58ch]">{{ __("scholarship.status.stages.{$stage['key']}.note") }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-8 md:grid-cols-2">
            <div>
                <h2 class="ns-h2 !text-[clamp(19px,2.2vw,24px)] mb-4">{{ __('scholarship.status.summary') }}</h2>
                <dl class="m-0 border-t border-[rgba(5,7,8,0.14)]">
                    @foreach (array_filter([
                        __('scholarship.apply.f.region') => $application->regionName(),
                        __('scholarship.apply.f.district') => $application->district,
                        __('scholarship.status.seats_here') => $application->region_code ? $application->seatsInRegion() : null,
                        __('scholarship.apply.f.first_choice') => trim($application->first_choice_university.' · '.$application->first_choice_department, ' ·') ?: null,
                        __('scholarship.status.submitted_on') => $application->submitted_at?->format('j M Y'),
                    ]) as $label => $value)
                        <div class="flex justify-between gap-5 py-[11px] border-b border-[rgba(5,7,8,0.1)] flex-wrap">
                            <dt class="ns-eyebrow !text-[9.5px] pt-[3px]">{{ $label }}</dt>
                            <dd class="m-0 font-[family-name:var(--ns-body)] text-[14px] text-end">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div>
                <h2 class="ns-h2 !text-[clamp(19px,2.2vw,24px)] mb-4">{{ __('scholarship.status.scoring') }}</h2>
                <p class="ns-body !text-[14px] text-body-soft mb-5 max-w-[46ch]">{{ __('scholarship.status.scoring_note') }}</p>

                <div class="flex flex-col gap-3">
                    @foreach ($rubric as $key => $weight)
                        <div>
                            <div class="flex justify-between items-baseline gap-3 mb-[5px]">
                                <span class="font-[family-name:var(--ns-body)] text-[13.5px] font-medium">{{ __("scholarship.rubric.$key") }}</span>
                                <span class="ns-num ns-meta text-[12px]">{{ $weight }}%</span>
                            </div>
                            <div class="h-[6px] bg-[rgba(5,7,8,0.1)]">
                                <div class="h-full bg-magenta" style="width: {{ $weight }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.scholarship>
