@php
    $isStudent = $type === \App\Models\Registration::TYPE_STUDENT;
    // Completing a visitor pass: the name and the verified number are already on
    // file, so the form starts from what is known and the badge never changes.
    $upgrade = $upgrade ?? null;
@endphp

<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[760px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('register.kicker') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(32px,4.6vw,48px)] mb-3">{{ __('register.title') }}</h1>
        <p class="ns-body max-w-[58ch] mb-3">{{ $isStudent ? __('register.lead_student') : __('register.lead_parent') }}</p>
        <p class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.6] text-slate max-w-[58ch] mb-9">
            {!! __('register.cross_link', ['link' => '<a href="'.route('register.conference').'">'.__('register.cross_link_label').'</a>']) !!}
        </p>

        @if ($upgrade)
            <div class="ns-card border-t-[6px] !border-t-magenta mb-9">
                <h2 class="font-[family-name:var(--ns-display)] text-[24px] font-semibold mb-3">{{ __('register.upgrade.title') }}</h2>
                <p class="ns-body mb-2 max-w-[58ch]">{{ __('register.upgrade.body') }}</p>
                <p class="ns-meta text-[13px]">{{ __('register.upgrade.keeping', ['ticket' => $upgrade->ticket_ref]) }}</p>
            </div>
        @endif

        @if (session('duplicate'))
            {{-- One phone number, one badge: offer to resend rather than duplicate. --}}
            <div class="ns-card border-t-[6px] !border-t-magenta mb-9" role="alert">
                <h2 class="font-[family-name:var(--ns-display)] text-[24px] font-semibold mb-3">{{ __('register.duplicate.title') }}</h2>
                <p class="ns-body mb-6 max-w-[58ch]">{{ __('register.duplicate.body') }}</p>
                <div class="flex gap-3 flex-wrap items-center">
                    <form method="POST" action="{{ route('register.duplicate.resend') }}">
                        @csrf
                        <button type="submit" class="ns-btn ns-btn-magenta ns-btn-sm">{{ __('register.duplicate.resend') }}</button>
                    </form>
                    <a href="{{ route('attendee.signin') }}" class="ns-btn ns-btn-ghost ns-btn-sm">{{ __('register.duplicate.signin') }}</a>
                </div>
            </div>
        @endif

        @if (session('status'))
            <div class="bg-bone-200 p-5 mb-9 font-[family-name:var(--ns-body)] text-[15px]">{{ session('status') }}</div>
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

        {{-- Student or parent. The two forms differ enough that switching is a page,
             not a toggle: a parent should never see a password field. --}}
        <div class="grid gap-px bg-[rgba(5,7,8,0.14)] mb-9 sm:grid-cols-2 border border-[rgba(5,7,8,0.14)]">
            @foreach ([\App\Models\Registration::TYPE_STUDENT, \App\Models\Registration::TYPE_PARENT] as $option)
                <a href="{{ route('register.fair', ['type' => $option]) }}"
                   @class([
                       'block px-6 py-[18px] border-s-4',
                       'bg-white border-magenta' => $type === $option,
                       'bg-white/55 border-transparent' => $type !== $option,
                   ])>
                    <div @class([
                        'font-[family-name:var(--ns-display)] text-[19px] font-semibold mb-[3px]',
                        'text-magenta' => $type === $option,
                        'text-ink' => $type !== $option,
                    ])>{{ __("register.types.$option.label") }}</div>
                    <div class="ns-meta text-[12.5px]">{{ __("register.types.$option.note") }}</div>
                </a>
            @endforeach
        </div>

        <div class="ns-card">
            <form method="POST" action="{{ route('register.fair.store') }}" novalidate>
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="locale" value="{{ app()->getLocale() }}">
                <input type="hidden" name="utm_source" value="{{ request('utm_source') }}">
                <input type="hidden" name="utm_medium" value="{{ request('utm_medium') }}">
                <input type="hidden" name="utm_campaign" value="{{ request('utm_campaign') }}">

                {{-- Honeypot: a real person never fills this in. --}}
                <input type="text" name="ns_hp" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

                <div class="grid gap-[22px] sm:grid-cols-2">
                    <label class="sm:col-span-2">
                        <span class="ns-label">{{ __('register.step1.name') }} <span class="ns-req">*</span></span>
                        <input type="text" name="full_name" value="{{ old('full_name', $upgrade?->full_name) }}" autocomplete="name"
                               placeholder="{{ __('register.step1.name_hint') }}" class="ns-input">
                        @error('full_name')<span class="ns-error">{{ $message }}</span>@enderror
                    </label>

                    <label @class(['sm:col-span-2' => ! $isStudent])>
                        <span class="ns-label">{{ __('register.step1.phone') }} <span class="ns-req">*</span></span>
                        @if ($upgrade)
                            {{-- Already verified by the code that issued the pass, and the
                                 number the badge is registered against. Shown, not asked. --}}
                            <input type="hidden" name="phone" value="{{ $upgrade->phone }}">
                            <input type="hidden" name="phone_country" value="{{ $upgrade->phone_country }}">
                            <span class="ns-input flex items-center gap-2 bg-bone-200 text-body-soft ns-num">
                                <span>{{ $upgrade->phone_country }}</span>
                                <span>{{ $upgrade->phone }}</span>
                            </span>
                            <span class="ns-hint">{{ __('register.upgrade.phone_locked') }}</span>
                        @else
                            <span class="flex">
                                <select name="phone_country" class="ns-prefix !w-auto cursor-pointer" aria-label="{{ __('rsvp.step1.country_code') }}">
                                    @foreach (config('nextstep.phone.countries') as $code => $country)
                                        <option value="{{ $code }}" @selected(old('phone_country', config('nextstep.phone.default_country')) === $code)>{{ $code }}</option>
                                    @endforeach
                                </select>
                                <input type="tel" name="phone" value="{{ old('phone') }}" inputmode="numeric" autocomplete="tel"
                                       placeholder="{{ __('register.step1.phone_placeholder') }}" class="ns-input flex-1 min-w-0">
                            </span>
                            <span class="ns-hint">{{ __('register.step1.phone_note') }}</span>
                            @error('phone')<span class="ns-error">{{ $message }}</span>@enderror
                        @endif
                    </label>

                    @if ($isStudent)
                        <label>
                            <span class="ns-label">{{ __('register.step1.dob') }} <span class="ns-req">*</span></span>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $upgrade?->date_of_birth?->format('Y-m-d')) }}"
                                   max="{{ now()->subYears(10)->format('Y-m-d') }}" class="ns-input">
                            @error('date_of_birth')<span class="ns-error">{{ $message }}</span>@enderror
                        </label>
                    @endif

                    <label>
                        <span class="ns-label">{{ __('register.step1.city') }} <span class="ns-req">*</span></span>
                        <select name="city" class="ns-select">
                            <option value="">{{ __('register.step1.city_placeholder') }}</option>
                            @foreach (config('nextstep.cities') as $city)
                                <option value="{{ $city }}" @selected(old('city', $upgrade?->city) === $city)>{{ $city }}</option>
                            @endforeach
                        </select>
                        @error('city')<span class="ns-error">{{ $message }}</span>@enderror
                    </label>

                    @if ($isStudent)
                        <label>
                            <span class="ns-label">{{ __('register.step1.stage') }} <span class="ns-req">*</span></span>
                            <select name="education_stage" class="ns-select">
                                <option value="">{{ __('register.step1.stage_placeholder') }}</option>
                                @foreach (array_keys(config('nextstep.education_stages')) as $stage)
                                    <option value="{{ $stage }}" @selected(old('education_stage', $upgrade?->education_stage) === $stage)>
                                        {{ __("register.options.stage.$stage") }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="ns-hint">{{ __('register.step1.stage_note') }}</span>
                            @error('education_stage')<span class="ns-error">{{ $message }}</span>@enderror
                        </label>

                        <label>
                            <span class="ns-label">{{ __('register.step1.school') }}</span>
                            <input type="text" name="school_name" value="{{ old('school_name', $upgrade?->school_name) }}" class="ns-input">
                            @error('school_name')<span class="ns-error">{{ $message }}</span>@enderror
                        </label>
                    @endif
                </div>

                @if ($isStudent)
                    {{-- The account half of the form. Everything above is the badge;
                         everything here is what lets them come back to it. --}}
                    <div class="mt-9 pt-7 border-t border-[rgba(5,7,8,0.14)]">
                        <div class="ns-eyebrow !text-[10.5px] mb-2">{{ __('register.account.title') }}</div>
                        <p class="ns-body !text-[14.5px] text-body-soft max-w-[56ch] mb-6">{{ __('register.account.lead') }}</p>

                        <div class="grid gap-[22px] sm:grid-cols-2">
                            <label>
                                <span class="ns-label">{{ __('register.step1.email') }} <span class="ns-req">*</span></span>
                                <input type="email" name="email" value="{{ old('email', $upgrade?->email) }}" autocomplete="email"
                                       placeholder="{{ __('register.step1.email_placeholder') }}" class="ns-input">
                                @error('email')<span class="ns-error">{{ $message }}</span>@enderror
                            </label>

                            <label>
                                <span class="ns-label">
                                    {{ __('register.account.password') }}
                                    @unless ($upgrade?->isStudentAccount())<span class="ns-req">*</span>@endunless
                                </span>
                                <input type="password" name="password" autocomplete="new-password"
                                       placeholder="{{ __('register.account.password_hint') }}" class="ns-input">
                                @error('password')<span class="ns-error">{{ $message }}</span>@enderror
                            </label>
                        </div>

                        <ul class="list-none m-0 mt-6 p-0 flex flex-col gap-[7px]">
                            @foreach (__('register.account.benefits') as $benefit)
                                <li class="font-[family-name:var(--ns-body)] text-[14px] text-body-soft flex gap-[10px]">
                                    <span class="text-teal font-bold">✓</span><span>{{ $benefit }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <label class="flex gap-[14px] items-start cursor-pointer mt-8 mb-7">
                    <input type="checkbox" name="consent_terms" value="1" class="sr-only" @checked(old('consent_terms'))>
                    <span class="ns-box mt-[2px]"></span>
                    <span class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.55]">
                        {{ __('register.consents.combined') }}
                        <a href="{{ route('terms') }}" target="_blank">{{ __('site.footer.terms') }}</a> ·
                        <a href="{{ route('privacy') }}" target="_blank">{{ __('site.footer.privacy') }}</a>
                    </span>
                </label>
                @error('consent_terms')<span class="ns-error block mb-5">{{ $message }}</span>@enderror

                <button type="submit" class="ns-btn ns-btn-magenta">{{ __('register.submit') }}</button>

                <p class="ns-meta text-[12.5px] mt-5 max-w-[54ch]">{{ __('register.after_note') }}</p>
            </form>

            @unless ($upgrade)
                <div class="mt-7 pt-5 border-t border-[rgba(5,7,8,0.12)] flex items-center gap-3 flex-wrap">
                    <span class="ns-meta">{{ __('register.quick.switch_back') }}</span>
                    <a href="{{ route('register.quick') }}"
                       class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">
                        {{ __('register.quick.switch_back_link') }}
                    </a>
                </div>
            @endunless
        </div>
    </div>
</x-layouts.site>
