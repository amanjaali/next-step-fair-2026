@php
    $isStudent = $registration->type === \App\Models\Registration::TYPE_STUDENT;
    $isDelegate = $registration->isConference();
@endphp

<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[760px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <a href="{{ route('me') }}" class="ns-meta text-[13px] mb-6 inline-block">← {{ __('attendee.profile.title') }}</a>

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('attendee.edit.kicker') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3">{{ __('attendee.edit.title') }}</h1>
        <p class="ns-body max-w-[58ch] mb-10">{{ __('attendee.edit.lead') }}</p>

        @if ($errors->any())
            <div class="border-t-4 border-crimson bg-white p-6 mb-8">
                <ul class="list-none m-0 p-0 flex flex-col gap-2">
                    @foreach ($errors->all() as $error)
                        <li class="font-[family-name:var(--ns-body)] text-[14.5px] text-crimson">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('me.update') }}" enctype="multipart/form-data"
              x-data="{ preview: null, removing: false }">
            @csrf

            {{-- ------------------------------------------------------ photo -- --}}
            <section class="mb-10">
                <div class="ns-eyebrow !text-[10.5px] mb-4">{{ __('attendee.edit.photo') }}</div>

                <div class="flex items-center gap-6 flex-wrap">
                    <div class="w-[104px] h-[104px] shrink-0 border border-[rgba(5,7,8,0.16)] bg-bone-200 overflow-hidden flex items-center justify-center">
                        <template x-if="preview && ! removing">
                            <img :src="preview" alt="" class="w-full h-full object-cover">
                        </template>

                        <template x-if="! preview">
                            <div class="w-full h-full flex items-center justify-center"
                                 :class="removing ? '' : ''">
                                @if ($registration->photoUrl())
                                    <img src="{{ $registration->photoUrl() }}" alt=""
                                         class="w-full h-full object-cover" x-show="! removing">
                                    <span class="font-[family-name:var(--ns-display)] text-[30px] font-bold text-slate"
                                          x-show="removing" x-cloak>{{ $registration->initials() }}</span>
                                @else
                                    <span class="font-[family-name:var(--ns-display)] text-[30px] font-bold text-slate">
                                        {{ $registration->initials() }}
                                    </span>
                                @endif
                            </div>
                        </template>
                    </div>

                    <div class="min-w-0">
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                               @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null; removing = false"
                               class="font-[family-name:var(--ns-body)] text-[14px] mb-2 block">
                        <p class="ns-meta text-[12px] max-w-[42ch]">{{ __('attendee.edit.photo_note') }}</p>

                        @if ($registration->photoUrl())
                            <label class="flex items-center gap-2 mt-3 cursor-pointer">
                                <input type="checkbox" name="remove_photo" value="1" x-model="removing">
                                <span class="font-[family-name:var(--ns-body)] text-[13.5px]">{{ __('attendee.edit.photo_remove') }}</span>
                            </label>
                        @endif
                    </div>
                </div>
            </section>

            {{-- ------------------------------------------------------ about -- --}}
            <section class="mb-10">
                <div class="ns-eyebrow !text-[10.5px] mb-4">{{ __('attendee.edit.about') }}</div>

                <div class="grid gap-[22px] sm:grid-cols-2">
                    <label class="sm:col-span-2">
                        <span class="ns-label">{{ __('attendee.profile.name') }} <span class="ns-req">*</span></span>
                        <input type="text" name="full_name" value="{{ old('full_name', $registration->full_name) }}"
                               autocomplete="name" class="ns-input">
                        @error('full_name')<span class="ns-error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="ns-label">{{ __('attendee.profile.city') }} <span class="ns-req">*</span></span>
                        <select name="city" class="ns-input">
                            @foreach ($cities as $city)
                                <option value="{{ $city }}" @selected(old('city', $registration->city) === $city)>{{ $city }}</option>
                            @endforeach
                        </select>
                        @error('city')<span class="ns-error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="ns-label">{{ __('attendee.edit.locale') }} <span class="ns-req">*</span></span>
                        <select name="locale" class="ns-input">
                            @foreach ($locales as $code => $config)
                                <option value="{{ $code }}" @selected(old('locale', $registration->locale) === $code)>{{ $config['native'] ?? $code }}</option>
                            @endforeach
                        </select>
                        <span class="ns-hint">{{ __('attendee.edit.locale_note') }}</span>
                    </label>

                    {{-- The number is the badge and the account. Changing it from a
                         signed-in session would move somebody's badge to another
                         handset, so it is shown and not edited. --}}
                    <label class="sm:col-span-2">
                        <span class="ns-label">{{ __('attendee.profile.phone') }}</span>
                        <input type="text" value="{{ $registration->phone_country }} {{ $registration->maskedPhone() }}"
                               class="ns-input !bg-bone-200 !text-slate" disabled>
                        <span class="ns-hint">{{ __('attendee.edit.phone_note') }}</span>
                    </label>
                </div>
            </section>

            {{-- --------------------------------------------------- account -- --}}
            <section class="mb-10">
                <div class="ns-eyebrow !text-[10.5px] mb-4">{{ __('attendee.edit.account') }}</div>

                <div class="grid gap-[22px] sm:grid-cols-2">
                    <label class="sm:col-span-2">
                        <span class="ns-label">
                            {{ __('attendee.profile.email') }}
                            @if ($isStudent)<span class="ns-req">*</span>@endif
                        </span>
                        <input type="email" name="email" value="{{ old('email', $registration->email) }}"
                               autocomplete="email" class="ns-input">
                        @error('email')<span class="ns-error">{{ $message }}</span>@enderror
                    </label>

                    @if ($isStudent)
                        <label class="sm:col-span-2">
                            <span class="ns-label">{{ __('attendee.edit.password') }}</span>
                            <input type="password" name="password" autocomplete="new-password" class="ns-input">
                            <span class="ns-hint">{{ __('attendee.edit.password_note') }}</span>
                            @error('password')<span class="ns-error">{{ $message }}</span>@enderror
                        </label>
                    @endif
                </div>
            </section>

            {{-- ------------------------------------------------ study / work -- --}}
            @if ($isStudent)
                <section class="mb-10">
                    <div class="ns-eyebrow !text-[10.5px] mb-4">{{ __('attendee.edit.study') }}</div>

                    <div class="grid gap-[22px] sm:grid-cols-2">
                        <label>
                            <span class="ns-label">{{ __('register.step1.stage') }}</span>
                            <select name="education_stage" class="ns-input">
                                <option value="">{{ __('register.step1.stage_placeholder') }}</option>
                                @foreach (array_keys($stages) as $key)
                                    <option value="{{ $key }}" @selected(old('education_stage', $registration->education_stage) === $key)>
                                        {{ __("register.options.stage.$key") }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="ns-hint">{{ __('register.step1.stage_note') }}</span>
                        </label>

                        <label>
                            <span class="ns-label">{{ __('register.step1.school') }}</span>
                            <input type="text" name="school_name" value="{{ old('school_name', $registration->school_name) }}"
                                   class="ns-input">
                        </label>
                    </div>
                </section>
            @endif

            @if ($isDelegate)
                <section class="mb-10">
                    <div class="ns-eyebrow !text-[10.5px] mb-4">{{ __('attendee.edit.work') }}</div>

                    <div class="grid gap-[22px] sm:grid-cols-2">
                        <label>
                            <span class="ns-label">{{ __('rsvp.step1.position') }}</span>
                            <input type="text" name="position" value="{{ old('position', $registration->position) }}" class="ns-input">
                        </label>

                        <label>
                            <span class="ns-label">{{ __('rsvp.step1.organization') }}</span>
                            <input type="text" name="organization" value="{{ old('organization', $registration->organization) }}" class="ns-input">
                        </label>
                    </div>
                </section>
            @endif

            <input type="text" name="ns_hp" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

            <div class="flex gap-3 flex-wrap pt-2">
                <button type="submit" class="ns-btn ns-btn-lg ns-btn-magenta">{{ __('attendee.edit.save') }}</button>
                <a href="{{ route('me') }}" class="ns-btn ns-btn-lg ns-btn-ghost">{{ __('attendee.edit.cancel') }}</a>
            </div>
        </form>
    </div>
</x-layouts.site>
