<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[860px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('attendee.profile.title') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3">{{ __('attendee.interests.title') }}</h1>
        <p class="ns-body max-w-[62ch] mb-9">{{ __('attendee.interests.lead') }}</p>

        @if ($errors->any())
            <div class="ns-card border-t-[6px] !border-t-crimson mb-8" role="alert">
                <ul class="list-none m-0 p-0 flex flex-col gap-2">
                    @foreach ($errors->all() as $error)
                        <li class="font-[family-name:var(--ns-body)] text-[15px] text-crimson">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('me.interests.save') }}" novalidate>
            @csrf

            {{-- Fields, grouped by sector. Order of ticking is order of preference,
                 which the matcher weights — so the list is not alphabetical noise. --}}
            <div class="ns-card mb-6">
                <span class="ns-label">{{ __('attendee.interests.fields') }} <span class="ns-req">*</span></span>
                <span class="ns-hint mb-4 block">{{ __('attendee.interests.fields_hint') }}</span>

                @foreach ($sectors as $sector)
                    <div class="mb-5">
                        <div class="ns-eyebrow !text-[10px] mb-2">{{ $sector->t('name') }}</div>
                        <div class="flex gap-2 flex-wrap">
                            @foreach ($sector->fields as $field)
                                <label class="ns-chip">
                                    <input type="checkbox" name="fields[]" value="{{ $field->id }}" class="sr-only"
                                           @checked(in_array($field->id, old('fields', $chosen)))>
                                    <span>{{ $field->t('name') }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="ns-card mb-6">
                <div class="mb-6">
                    <span class="ns-label">{{ __('attendee.interests.level') }} <span class="ns-req">*</span></span>
                    <div class="flex gap-2 flex-wrap">
                        @foreach (config('taxonomy.degree_levels') as $level)
                            <label class="ns-chip">
                                <input type="radio" name="degree_level" value="{{ $level }}" class="sr-only"
                                       @checked(old('degree_level', $registration->degree_level) === $level)>
                                <span>{{ __("taxonomy.levels.$level") }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-6">
                    <span class="ns-label">{{ __('attendee.interests.countries') }}</span>
                    <span class="ns-hint mb-2 block">{{ __('attendee.interests.countries_hint') }}</span>
                    <div class="flex gap-2 flex-wrap">
                        @foreach (config('taxonomy.countries') as $code)
                            <label class="ns-chip">
                                <input type="checkbox" name="preferred_countries[]" value="{{ $code }}" class="sr-only"
                                       @checked(in_array($code, old('preferred_countries', $registration->preferred_countries ?? [])))>
                                <span>{{ __("taxonomy.countries.$code") }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <label>
                        <span class="ns-label">{{ __('attendee.interests.language') }}</span>
                        <select name="language_preference" class="ns-select">
                            <option value="">—</option>
                            @foreach (config('taxonomy.languages') as $code)
                                <option value="{{ $code }}" @selected(old('language_preference', $registration->language_preference) === $code)>
                                    {{ __("taxonomy.languages.$code") }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span class="ns-label">{{ __('attendee.interests.grade') }}</span>
                        <select name="grade_band" class="ns-select">
                            <option value="">—</option>
                            @foreach (array_keys(config('taxonomy.grade_bands')) as $band)
                                <option value="{{ $band }}" @selected(old('grade_band', $registration->grade_band) === $band)>
                                    {{ __("taxonomy.grades.$band") }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="sm:col-span-2">
                        <span class="ns-label">{{ __('attendee.interests.budget') }}</span>
                        <select name="budget_band" class="ns-select">
                            <option value="">—</option>
                            @foreach (array_keys(config('taxonomy.budget_bands')) as $band)
                                <option value="{{ $band }}" @selected(old('budget_band', $registration->budget_band) === $band)>
                                    {{ __("taxonomy.budgets.$band") }}
                                </option>
                            @endforeach
                        </select>
                        <span class="ns-hint">{{ __('attendee.interests.budget_hint') }}</span>
                    </label>

                    <label>
                        <span class="ns-label">{{ __('attendee.interests.start') }}</span>
                        <select name="start_year" class="ns-select ns-num">
                            <option value="">—</option>
                            @for ($y = (int) config('nextstep.event.year'); $y <= (int) config('nextstep.event.year') + 4; $y++)
                                <option value="{{ $y }}" @selected((int) old('start_year', $registration->start_year) === $y)>{{ $y }}</option>
                            @endfor
                        </select>
                    </label>

                    <label>
                        <span class="ns-label">{{ __('attendee.interests.career') }}</span>
                        <select name="career_goal" class="ns-select">
                            <option value="">—</option>
                            @foreach (config('taxonomy.career_goals') as $goal)
                                <option value="{{ $goal }}" @selected(old('career_goal', $registration->career_goal) === $goal)>
                                    {{ __("taxonomy.careers.$goal") }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>

            {{-- Consent is a separate, deliberate act. Matching works without it;
                 this decides only whether an institution may reach the student. --}}
            <div class="ns-card mb-8">
                <label class="flex gap-[14px] items-start cursor-pointer">
                    <input type="checkbox" name="share_with_institutions" value="1" class="sr-only"
                           @checked(old('share_with_institutions', $registration->share_with_institutions))>
                    <span class="ns-box mt-[2px]"></span>
                    <span>
                        <span class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.55] block">
                            {{ __('attendee.interests.share') }}
                        </span>
                        <span class="ns-hint">{{ __('attendee.interests.share_hint') }}</span>
                    </span>
                </label>
            </div>

            <button type="submit" class="ns-btn ns-btn-magenta ns-btn-lg">{{ __('attendee.interests.save') }}</button>
        </form>
    </div>
</x-layouts.site>
