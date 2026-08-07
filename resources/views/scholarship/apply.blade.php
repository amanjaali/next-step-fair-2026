@php
    $labels = [
        1 => __('scholarship.apply.steps.profile'),
        2 => __('scholarship.apply.steps.academic'),
        3 => __('scholarship.apply.steps.statement'),
        4 => __('scholarship.apply.steps.review'),
    ];
@endphp

<x-layouts.scholarship :title="$title" :scholarshipNav="$scholarshipNav" :attendee="$attendee" :application="$application">
    <div class="ns-wrap max-w-[860px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('scholarship.apply.kicker', ['cycle' => $cycle]) }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(28px,4vw,44px)] mb-7">{{ $labels[$step] }}</h1>

        {{-- The four steps, always visible. Each is a separate page and a separate
             save, so nothing is lost by closing the browser mid-way. --}}
        <div class="grid gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] sm:grid-cols-4 mb-3">
            @foreach ($labels as $n => $label)
                <a href="{{ route('scholarship.apply.form', ['step' => $n]) }}"
                   @class([
                       'px-4 py-3 block no-underline border-t-4',
                       'bg-ink border-magenta' => $n === $step,
                       'bg-white border-transparent' => $n !== $step,
                   ])>
                    <div @class([
                        'ns-eyebrow !text-[9.5px] mb-1',
                        '!text-white/60' => $n === $step,
                        '!text-muted' => $n !== $step,
                    ])>{{ __('scholarship.apply.step_n', ['n' => $n]) }}</div>
                    <div @class([
                        'font-[family-name:var(--ns-body)] text-[14px] font-bold leading-[1.25]',
                        'text-white' => $n === $step,
                        'text-ink' => $n !== $step,
                    ])>{{ $label }}</div>
                </a>
            @endforeach
        </div>

        <div class="flex justify-between gap-4 flex-wrap mb-8">
            <span class="ns-meta text-[12.5px]">{{ __('scholarship.apply.autosave') }}</span>
            <span class="ns-meta text-[12.5px] ns-num">{{ __('scholarship.apply.complete', ['percent' => $application->completeness()]) }}</span>
        </div>

        @if ($errors->any())
            <div class="ns-card border-t-[6px] !border-t-crimson mb-8" role="alert">
                <ul class="list-none m-0 p-0 flex flex-col gap-2">
                    @foreach ($errors->all() as $error)
                        <li class="font-[family-name:var(--ns-body)] text-[15px] text-crimson">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="ns-card">
            @if ($step === 4)
                {{-- Review and submit. --}}
                <h2 class="font-[family-name:var(--ns-display)] text-[24px] font-semibold mb-2">{{ __('scholarship.apply.review_title') }}</h2>
                <p class="ns-body !text-[15px] text-body-soft mb-7 max-w-[56ch]">{{ __('scholarship.apply.review_lead') }}</p>

                <dl class="m-0 mb-8 border-t border-[rgba(5,7,8,0.14)]">
                    @foreach ([
                        __('scholarship.apply.f.name') => $attendee->full_name,
                        __('scholarship.apply.f.region') => $application->regionName(),
                        __('scholarship.apply.f.district') => $application->district,
                        __('scholarship.apply.f.average') => $application->exam_status === 'pending'
                            ? __('scholarship.apply.pending_results')
                            : $application->exam_average,
                        __('scholarship.apply.f.school') => $application->school_name,
                        __('scholarship.apply.f.first_choice') => trim($application->first_choice_university.' · '.$application->first_choice_department, ' ·'),
                        __('scholarship.apply.f.statement') => $application->statement ? str_word_count($application->statement).' '.__('scholarship.apply.words') : null,
                        __('scholarship.apply.f.proposal') => $application->proposal ? str_word_count($application->proposal).' '.__('scholarship.apply.words') : null,
                    ] as $label => $value)
                        <div class="flex justify-between gap-6 py-[11px] border-b border-[rgba(5,7,8,0.1)] flex-wrap">
                            <dt class="ns-eyebrow !text-[9.5px] pt-[3px]">{{ $label }}</dt>
                            <dd @class([
                                'm-0 font-[family-name:var(--ns-body)] text-[14.5px] text-end',
                                'text-crimson' => blank($value),
                            ])>{{ filled($value) ? $value : __('scholarship.apply.missing') }}</dd>
                        </div>
                    @endforeach
                </dl>

                <form method="POST" action="{{ route('scholarship.apply.submit') }}">
                    @csrf
                    <label class="flex gap-[14px] items-start cursor-pointer mb-7">
                        <input type="checkbox" name="confirm" value="1" class="sr-only">
                        <span class="ns-box mt-[2px]"></span>
                        <span class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.55] max-w-[58ch]">
                            {{ __('scholarship.apply.confirm') }}
                        </span>
                    </label>

                    <button type="submit" class="ns-btn ns-btn-magenta">{{ __('scholarship.apply.submit_final') }}</button>
                    <p class="ns-meta text-[12.5px] mt-4 max-w-[54ch]">{{ __('scholarship.apply.submit_note') }}</p>
                </form>
            @else
                <form method="POST" action="{{ route('scholarship.apply.save') }}">
                    @csrf
                    <input type="hidden" name="step" value="{{ $step }}">

                    @if ($step === 1)
                        {{-- The quota. Decided by where grade 12 was completed, and
                             fixed once the application is in. --}}
                        <h2 class="font-[family-name:var(--ns-display)] text-[22px] font-semibold mb-2">{{ __('scholarship.apply.region_title') }}</h2>
                        <p class="ns-body !text-[14.5px] text-body-soft mb-6 max-w-[56ch]">{{ __('scholarship.apply.region_lead') }}</p>

                        <div class="grid gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] sm:grid-cols-2 lg:grid-cols-4 mb-7">
                            @foreach ($regions as $code => $region)
                                <label class="ns-tile !border-0 bg-white px-4 py-3 block cursor-pointer">
                                    <input type="radio" name="region_code" value="{{ $code }}" class="sr-only"
                                           @checked($application->region_code === $code)>
                                    <span class="ns-tile-title block font-[family-name:var(--ns-body)] text-[14.5px] font-bold mb-[2px]">{{ $region['name'] }}</span>
                                    <span class="ns-meta text-[12px] ns-num">{{ trans_choice('scholarship.apply.seats', $region['seats'], ['count' => $region['seats']]) }}</span>
                                </label>
                            @endforeach
                        </div>

                        <label class="block max-w-[400px]">
                            <span class="ns-label">{{ __('scholarship.apply.f.district') }} <span class="ns-req">*</span></span>
                            <select name="district" class="ns-select">
                                <option value="">{{ __('scholarship.apply.district_placeholder') }}</option>
                                @foreach ($regions as $code => $region)
                                    <optgroup label="{{ $region['name'] }}">
                                        @foreach ($region['districts'] as $district)
                                            <option value="{{ $district }}" @selected($application->district === $district)>{{ $district }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </label>
                    @elseif ($step === 2)
                        <h2 class="font-[family-name:var(--ns-display)] text-[22px] font-semibold mb-2">{{ __('scholarship.apply.academic_title') }}</h2>
                        <p class="ns-body !text-[14.5px] text-body-soft mb-6 max-w-[56ch]">{{ __('scholarship.apply.academic_lead') }}</p>

                        <div class="grid gap-[22px] sm:grid-cols-2">
                            <div>
                                <span class="ns-label">{{ __('scholarship.apply.f.results') }} <span class="ns-req">*</span></span>
                                <div class="flex gap-2">
                                    @foreach (['published', 'pending'] as $option)
                                        <label class="ns-chip flex-1 justify-center">
                                            <input type="radio" name="exam_status" value="{{ $option }}" class="sr-only"
                                                   @checked($application->exam_status === $option)>
                                            <span>{{ __("scholarship.apply.results.$option") }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <label>
                                <span class="ns-label">{{ __('scholarship.apply.f.average') }}</span>
                                <input type="number" name="exam_average" step="0.01" min="0" max="100"
                                       value="{{ old('exam_average', $application->exam_average) }}" class="ns-input ns-num">
                                <span class="ns-hint">{{ __('scholarship.apply.average_note', ['min' => config('scholarship.minimum_average')]) }}</span>
                            </label>

                            <label>
                                <span class="ns-label">{{ __('scholarship.apply.f.stream') }} <span class="ns-req">*</span></span>
                                <select name="stream" class="ns-select">
                                    <option value="">—</option>
                                    @foreach (['scientific', 'literary', 'vocational'] as $option)
                                        <option value="{{ $option }}" @selected($application->stream === $option)>{{ __("register.options.stream.$option") }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                <span class="ns-label">{{ __('scholarship.apply.f.school') }} <span class="ns-req">*</span></span>
                                <input type="text" name="school_name"
                                       value="{{ old('school_name', $application->school_name ?: $attendee->school_name) }}" class="ns-input">
                            </label>
                        </div>

                        <h3 class="font-[family-name:var(--ns-display)] text-[18px] font-semibold mt-9 mb-2">{{ __('scholarship.apply.choices_title') }}</h3>
                        <p class="ns-body !text-[14px] text-body-soft mb-5 max-w-[56ch]">{{ __('scholarship.apply.choices_lead') }}</p>

                        @foreach (['first' => true, 'second' => false] as $slot => $required)
                            <div class="grid gap-[22px] sm:grid-cols-2 mb-5">
                                <label>
                                    <span class="ns-label">
                                        {{ __("scholarship.apply.f.{$slot}_university") }}
                                        @if ($required)<span class="ns-req">*</span>@endif
                                    </span>
                                    <select name="{{ $slot }}_choice_university" class="ns-select">
                                        <option value="">—</option>
                                        @foreach ($universities as $university)
                                            <option value="{{ $university['name'] }}"
                                                    @selected($application->{$slot.'_choice_university'} === $university['name'])>{{ $university['name'] }}</option>
                                        @endforeach
                                    </select>
                                </label>

                                <label>
                                    <span class="ns-label">
                                        {{ __('scholarship.apply.f.department') }}
                                        @if ($required)<span class="ns-req">*</span>@endif
                                    </span>
                                    <select name="{{ $slot }}_choice_department" class="ns-select">
                                        <option value="">—</option>
                                        @foreach ($universities as $university)
                                            <optgroup label="{{ $university['name'] }}">
                                                @foreach ($university['departments'] as $department)
                                                    <option value="{{ $department['name'] }}"
                                                            @selected($application->{$slot.'_choice_department'} === $department['name'])>
                                                        {{ $department['name'] }} · {{ $department['seats'] }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                        @endforeach
                    @elseif ($step === 3)
                        <h2 class="font-[family-name:var(--ns-display)] text-[22px] font-semibold mb-2">{{ __('scholarship.apply.statement_title') }}</h2>
                        <p class="ns-body !text-[14.5px] text-body-soft mb-7 max-w-[56ch]">{{ __('scholarship.apply.statement_lead') }}</p>

                        <label class="block mb-7">
                            <span class="ns-label">{{ __('scholarship.apply.f.statement') }} <span class="ns-req">*</span></span>
                            <span class="ns-hint block mb-2">{{ __('scholarship.apply.statement_prompt') }}</span>
                            <textarea name="statement" rows="10" class="ns-textarea">{{ old('statement', $application->statement) }}</textarea>
                            <span class="ns-hint ns-num">{{ __('scholarship.apply.word_range', config('scholarship.statement_words')) }}</span>
                        </label>

                        <label class="block">
                            <span class="ns-label">{{ __('scholarship.apply.f.proposal') }} <span class="ns-req">*</span></span>
                            <span class="ns-hint block mb-2">{{ __('scholarship.apply.proposal_prompt') }}</span>
                            <textarea name="proposal" rows="12" class="ns-textarea">{{ old('proposal', $application->proposal) }}</textarea>
                            <span class="ns-hint ns-num">{{ __('scholarship.apply.word_range', config('scholarship.proposal_words')) }}</span>
                        </label>
                    @endif

                    <div class="flex justify-between items-center gap-4 mt-9 pt-6 border-t border-[rgba(5,7,8,0.14)] flex-wrap">
                        @if ($step > 1)
                            <a href="{{ route('scholarship.apply.form', ['step' => $step - 1]) }}" class="ns-btn ns-btn-ghost">{{ __('site.cta.back') }}</a>
                        @else
                            <span></span>
                        @endif

                        <button type="submit" class="ns-btn ns-btn-magenta">{{ __('scholarship.apply.save_continue') }}</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-layouts.scholarship>
