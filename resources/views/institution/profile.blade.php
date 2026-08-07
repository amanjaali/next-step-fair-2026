<x-layouts.site :title="$title" :navKey="$navKey" track="conference">
    <div class="ns-wrap max-w-[980px] pt-[clamp(24px,3vw,40px)] pb-[clamp(72px,10vw,140px)]">
        @include('institution.partials.nav', ['current' => 'profile'])

        <h1 class="ns-h1 !text-[clamp(28px,3.6vw,40px)] mb-3">{{ __('institution.profile.title') }}</h1>
        <p class="ns-body max-w-[64ch] mb-9">{{ __('institution.profile.lead') }}</p>

        @if ($errors->any())
            <div class="ns-card border-t-[6px] !border-t-crimson mb-8" role="alert">
                <ul class="list-none m-0 p-0 flex flex-col gap-2">
                    @foreach ($errors->all() as $error)
                        <li class="font-[family-name:var(--ns-body)] text-[15px] text-crimson">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('portal.profile.save') }}" novalidate>
            @csrf

            {{-- Programmes first: it is the only section without which nothing works. --}}
            <div class="ns-card mb-6">
                <span class="ns-label">{{ __('institution.profile.fields') }} <span class="ns-req">*</span></span>
                <span class="ns-hint mb-5 block">{{ __('institution.profile.fields_hint') }}</span>

                @foreach ($sectors as $sector)
                    <details class="border-t border-[rgba(5,7,8,0.12)] py-3" @if($sector->fields->pluck('id')->intersect(array_keys($selected->all()))->isNotEmpty()) open @endif>
                        <summary class="cursor-pointer font-[family-name:var(--ns-body)] text-[15px] font-semibold py-1">
                            {{ $sector->t('name') }}
                        </summary>
                        <div class="pt-3 flex flex-col gap-2">
                            @foreach ($sector->fields as $field)
                                @php($levels = $selected[$field->id] ?? [])
                                <div class="flex items-center gap-3 flex-wrap py-1">
                                    <span class="font-[family-name:var(--ns-body)] text-[14px] w-[220px] shrink-0">{{ $field->t('name') }}</span>
                                    <div class="flex gap-2 flex-wrap">
                                        @foreach (config('taxonomy.degree_levels') as $level)
                                            <label class="ns-chip !py-[5px] !px-[10px] !text-[12.5px]">
                                                <input type="checkbox" name="fields[{{ $field->id }}][]" value="{{ $level }}"
                                                       class="sr-only" @checked(in_array($level, $levels, true))>
                                                <span>{{ __("taxonomy.levels.$level") }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>

            <div class="ns-card mb-6">
                <div class="ns-eyebrow !text-[11px] mb-5">{{ __('institution.profile.about') }}</div>

                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach (array_keys(config('nextstep.locales')) as $code)
                        <label class="sm:col-span-2">
                            <span class="ns-label">{{ __('institution.profile.description') }} · {{ strtoupper($code) }}</span>
                            <textarea name="description[{{ $code }}]" rows="3" class="ns-textarea"
                                      dir="{{ config("nextstep.locales.$code.dir") }}">{{ old("description.$code", $organization->getTranslation('description', $code, false)) }}</textarea>
                        </label>
                    @endforeach

                    <label>
                        <span class="ns-label">{{ __('institution.profile.website') }}</span>
                        <input type="url" name="website" value="{{ old('website', $organization->website) }}" class="ns-input" placeholder="https://">
                    </label>
                    <label>
                        <span class="ns-label">{{ __('institution.profile.city') }}</span>
                        <input type="text" name="city" value="{{ old('city', $organization->city) }}" class="ns-input">
                    </label>
                </div>

                <div class="mt-6">
                    <span class="ns-label">{{ __('institution.profile.countries') }}</span>
                    <div class="flex gap-2 flex-wrap">
                        @foreach (config('taxonomy.countries') as $code)
                            @continue($code === 'undecided')
                            <label class="ns-chip">
                                <input type="checkbox" name="campus_countries[]" value="{{ $code }}" class="sr-only"
                                       @checked(in_array($code, old('campus_countries', $organization->campus_countries ?? [$organization->country])))>
                                <span>{{ __("taxonomy.countries.$code") }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 mt-6">
                    <div>
                        <span class="ns-label">{{ __('institution.profile.levels') }}</span>
                        <div class="flex gap-2 flex-wrap">
                            @foreach (config('taxonomy.degree_levels') as $level)
                                <label class="ns-chip">
                                    <input type="checkbox" name="degree_levels[]" value="{{ $level }}" class="sr-only"
                                           @checked(in_array($level, old('degree_levels', $organization->degree_levels ?? [])))>
                                    <span>{{ __("taxonomy.levels.$level") }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <span class="ns-label">{{ __('institution.profile.languages') }}</span>
                        <div class="flex gap-2 flex-wrap">
                            @foreach (config('taxonomy.languages') as $code)
                                <label class="ns-chip">
                                    <input type="checkbox" name="languages[]" value="{{ $code }}" class="sr-only"
                                           @checked(in_array($code, old('languages', $organization->languages ?? [])))>
                                    <span>{{ __("taxonomy.languages.$code") }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="ns-card mb-6">
                <div class="ns-eyebrow !text-[11px] mb-5">{{ __('institution.profile.tuition') }}</div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <label>
                        <span class="ns-label">{{ __('institution.profile.tuition_min') }}</span>
                        <input type="number" name="tuition_min" value="{{ old('tuition_min', $organization->tuition_min) }}" class="ns-input ns-num" min="0">
                    </label>
                    <label>
                        <span class="ns-label">{{ __('institution.profile.tuition_max') }}</span>
                        <input type="number" name="tuition_max" value="{{ old('tuition_max', $organization->tuition_max) }}" class="ns-input ns-num" min="0">
                    </label>
                    <label>
                        <span class="ns-label">{{ __('institution.profile.min_grade') }}</span>
                        <select name="min_grade_band" class="ns-select">
                            <option value="">—</option>
                            @foreach (array_keys(config('taxonomy.grade_bands')) as $band)
                                @continue($band === 'not_yet')
                                <option value="{{ $band }}" @selected(old('min_grade_band', $organization->min_grade_band) === $band)>
                                    {{ __("taxonomy.grades.$band") }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="ns-label">{{ __('institution.profile.capacity') }}</span>
                        <input type="number" name="intake_capacity" value="{{ old('intake_capacity', $organization->intake_capacity) }}" class="ns-input ns-num" min="0">
                    </label>
                    <label>
                        <span class="ns-label">{{ __('institution.profile.deadline') }}</span>
                        <input type="date" name="application_deadline" class="ns-input ns-num"
                               value="{{ old('application_deadline', $organization->application_deadline?->format('Y-m-d')) }}">
                    </label>
                    <label>
                        <span class="ns-label">{{ __('institution.profile.target') }}</span>
                        <input type="number" name="recruitment_target" class="ns-input ns-num" min="0"
                               value="{{ old('recruitment_target', $organization->recruitment_goals['target_students'] ?? null) }}">
                    </label>
                </div>

                <label class="flex gap-[14px] items-start cursor-pointer mt-6">
                    <input type="checkbox" name="offers_scholarships" value="1" class="sr-only"
                           @checked(old('offers_scholarships', $organization->offers_scholarships))>
                    <span class="ns-box mt-[2px]"></span>
                    <span class="font-[family-name:var(--ns-body)] text-[14.5px]">{{ __('institution.profile.scholarships') }}</span>
                </label>
            </div>

            <div class="ns-card mb-8">
                <div class="ns-eyebrow !text-[11px] mb-5">{{ __('institution.profile.contact') }}</div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <label>
                        <span class="ns-label">{{ __('institution.profile.contact_email') }}</span>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $organization->contact_email) }}" class="ns-input">
                    </label>
                    <label>
                        <span class="ns-label">{{ __('institution.profile.contact_phone') }}</span>
                        <input type="tel" name="contact_phone" value="{{ old('contact_phone', $organization->contact_phone) }}" class="ns-input ns-num">
                    </label>
                </div>
            </div>

            <button type="submit" class="ns-btn ns-btn-cobalt ns-btn-lg">{{ __('institution.profile.save') }}</button>
        </form>
    </div>
</x-layouts.site>
