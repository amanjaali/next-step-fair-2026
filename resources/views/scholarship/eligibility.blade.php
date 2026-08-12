<x-layouts.scholarship :title="$title" :scholarshipNav="$scholarshipNav" :attendee="$attendee" :application="$application">
    <div class="ns-wrap max-w-[760px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('scholarship.eligibility.kicker') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(28px,4vw,42px)] mb-3">{{ __('scholarship.eligibility.heading') }}</h1>
        <p class="ns-body max-w-[56ch] mb-9">{{ __('scholarship.eligibility.lead') }}</p>

        @if (session('checked'))
            @if ($verdict === 'fail')
                {{-- Closed, and told why now rather than at screening in November. --}}
                <div class="ns-card border-t-[6px] !border-t-crimson mb-9" role="alert">
                    <div class="font-[family-name:var(--ns-display)] text-[22px] font-semibold mb-3">{{ __('scholarship.eligibility.fail_title') }}</div>
                    @foreach ($questions as $question)
                        @if (in_array($answers[$question['id']] ?? null, $question['fail'], true))
                            <p class="ns-body !text-[15px] mb-3 max-w-[58ch]">{{ __("scholarship.eligibility.questions.{$question['id']}.fail") }}</p>
                        @endif
                    @endforeach
                </div>
            @elseif ($verdict === 'warn')
                <div class="ns-card border-t-[6px] !border-t-[#C08A1E] mb-9" role="alert">
                    <div class="font-[family-name:var(--ns-display)] text-[22px] font-semibold mb-3">{{ __('scholarship.eligibility.warn_title') }}</div>
                    @foreach ($questions as $question)
                        @if (in_array($answers[$question['id']] ?? null, $question['warn'], true))
                            <p class="ns-body !text-[15px] mb-3 max-w-[58ch]">{{ __("scholarship.eligibility.questions.{$question['id']}.warn") }}</p>
                        @endif
                    @endforeach
                    <a href="{{ route('scholarship.apply.form') }}" class="ns-btn ns-btn-magenta ns-btn-sm mt-2">{{ __('scholarship.eligibility.continue') }}</a>
                </div>
            @elseif ($verdict === 'pass')
                <div class="ns-card border-t-[6px] !border-t-teal mb-9" role="status">
                    <div class="font-[family-name:var(--ns-display)] text-[22px] font-semibold mb-3">{{ __('scholarship.eligibility.pass_title') }}</div>
                    <p class="ns-body !text-[15px] mb-5 max-w-[58ch]">{{ __('scholarship.eligibility.pass_body') }}</p>
                    <a href="{{ route('scholarship.apply.form') }}" class="ns-btn ns-btn-magenta">{{ __('scholarship.eligibility.continue') }}</a>
                </div>
            @endif
        @endif

        @if ($errors->any())
            <div class="ns-card border-t-[6px] !border-t-crimson mb-9" role="alert">
                <ul class="list-none m-0 p-0 flex flex-col gap-2">
                    @foreach ($errors->all() as $error)
                        <li class="font-[family-name:var(--ns-body)] text-[15px] text-crimson">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- The form counts itself.
             Five questions with no indication of how many are answered, and a
             live-looking button that silently bounces you back to a list of
             errors, is the whole reason this page felt broken. The count is live,
             the unanswered ones are marked, and the button says what is left. --}}
        <form method="POST" action="{{ route('scholarship.eligibility.save') }}"
              x-data="nsEligibility({{ count($questions) }})">
            @csrf

            <div class="flex items-center gap-3 flex-wrap mb-4">
                <div class="h-1.5 flex-1 min-w-[140px] max-w-[280px] bg-[rgba(5,7,8,0.1)] rounded-full overflow-hidden">
                    <div class="h-full bg-magenta transition-[width] duration-300"
                         :style="`width: ${(answered / total) * 100}%`" style="width: 0"></div>
                </div>
                <span class="ns-meta ns-num text-[12.5px]"
                      x-text="`${answered} / ${total} {{ __('scholarship.eligibility.answered') }}`"></span>
            </div>

            <div class="flex flex-col gap-px bg-[rgba(5,7,8,0.12)] border border-[rgba(5,7,8,0.12)] ns-radius overflow-hidden">
                @foreach ($questions as $index => $question)
                    <fieldset class="bg-white px-6 py-6 border-0 m-0" data-question="{{ $question['id'] }}"
                              :class="showMissing && missing.includes('{{ $question['id'] }}') && 'ns-unanswered'">
                        <legend class="contents">
                            <div class="flex gap-3 items-baseline mb-2">
                                <span class="ns-num font-[family-name:var(--mono)] text-[12px] text-muted">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="font-[family-name:var(--ns-display)] text-[18px] font-semibold leading-[1.3]">
                                    {{ __("scholarship.eligibility.questions.{$question['id']}.title") }}
                                </span>
                            </div>
                        </legend>

                        <p class="ns-body !text-[13.5px] text-body-soft mb-4 ps-[30px] max-w-[60ch]">
                            {{ __("scholarship.eligibility.questions.{$question['id']}.note") }}
                        </p>

                        <div class="flex gap-2 flex-wrap ps-[30px]">
                            @foreach ($question['options'] as $option)
                                <label class="ns-chip">
                                    <input type="radio" class="sr-only" @change="recount()"
                                           name="answers[{{ $question['id'] }}]" value="{{ $option }}"
                                           @checked(($answers[$question['id']] ?? null) === $option)>
                                    <span>{{ __("scholarship.eligibility.questions.{$question['id']}.options.$option") }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
            </div>

            <div class="flex items-center gap-4 flex-wrap mt-8">
                <button type="submit" class="ns-btn ns-btn-magenta" :disabled="answered < total"
                        :class="answered < total && 'opacity-45 cursor-not-allowed'">
                    {{ __('scholarship.eligibility.submit') }}
                </button>
                <span class="ns-meta text-[13px]" x-show="answered < total" x-cloak style="display: none"
                      x-text="`{{ __('scholarship.eligibility.remaining') }}`.replace(':count', total - answered)"></span>
            </div>

            <p class="ns-meta text-[12.5px] mt-4 max-w-[54ch]">{{ __('scholarship.eligibility.note') }}</p>
        </form>
    </div>
</x-layouts.scholarship>
