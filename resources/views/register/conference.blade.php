@php
    // An individual comes on their own account, so there is no organisation to name.
    $needsOrganization = $type !== \App\Models\Registration::TYPE_INDIVIDUAL;
@endphp

<x-layouts.site :title="$title" :navKey="$navKey" track="conference">
    <div class="ns-wrap max-w-[760px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-cobalt"></span>
            <span class="ns-eyebrow !text-cobalt">{{ __('rsvp.kicker') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(32px,4.6vw,48px)] mb-3">{{ __('rsvp.title') }}</h1>
        <p class="ns-body max-w-[58ch] mb-3">{{ __('rsvp.lead') }}</p>
        <p class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.6] text-slate max-w-[58ch] mb-9">
            {!! __('rsvp.cross_link', ['link' => '<a href="'.route('register.fair').'">'.__('rsvp.cross_link_label').'</a>']) !!}
        </p>

        @if (session('status'))
            <div class="bg-bone-200 p-5 mb-8 font-[family-name:var(--ns-body)] text-[15px]">{{ session('status') }}</div>
        @endif

        @if (session('duplicate'))
            <div class="ns-card border-t-[6px] !border-t-cobalt mb-9" role="alert">
                <h2 class="font-[family-name:var(--ns-display)] text-[24px] font-semibold mb-3">{{ __('rsvp.duplicate.title') }}</h2>
                <p class="ns-body max-w-[56ch]">{{ __('rsvp.duplicate.body') }}</p>
            </div>
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

        {{-- Four audiences, one form. The choice only changes whether we ask for an
             organisation, so it sits inline rather than sending you to a new page. --}}
        <div class="mb-8">
            <span class="ns-label">{{ __('rsvp.attending_as') }} <span class="ns-req">*</span></span>
            <div class="grid gap-px bg-[rgba(5,7,8,0.14)] sm:grid-cols-2 border border-[rgba(5,7,8,0.14)]">
                @foreach (\App\Models\Registration::conferenceTypes() as $option)
                    <a href="{{ route('register.conference', ['type' => $option]) }}"
                       @class([
                           'block px-5 py-4 border-s-4',
                           'bg-white border-cobalt' => $type === $option,
                           'bg-white/55 border-transparent' => $type !== $option,
                       ])>
                        <div @class([
                            'font-[family-name:var(--ns-display)] text-[17px] font-semibold mb-[3px]',
                            'text-cobalt' => $type === $option,
                            'text-ink' => $type !== $option,
                        ])>{{ __("rsvp.types.$option.label") }}</div>
                        <div class="ns-meta text-[12.5px]">{{ __("rsvp.types.$option.note") }}</div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="ns-card">
            <form method="POST" action="{{ route('register.conference.store') }}" novalidate>
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="locale" value="{{ app()->getLocale() }}">

                {{-- Honeypot: a real person never fills this in. --}}
                <input type="text" name="ns_hp" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

                <div class="grid gap-[22px] sm:grid-cols-2">
                    <label class="sm:col-span-2">
                        <span class="ns-label">{{ __('rsvp.step1.name') }} <span class="ns-req">*</span></span>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" autocomplete="name" class="ns-input">
                        @error('full_name')<span class="ns-error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="ns-label">{{ __('rsvp.step1.position') }} <span class="ns-req">*</span></span>
                        <input type="text" name="position" value="{{ old('position') }}" autocomplete="organization-title"
                               placeholder="{{ __('rsvp.step1.position_hint') }}" class="ns-input">
                        @error('position')<span class="ns-error">{{ $message }}</span>@enderror
                    </label>

                    @if ($needsOrganization)
                        <label>
                            <span class="ns-label">{{ __('rsvp.step1.organization') }} <span class="ns-req">*</span></span>
                            <input type="text" name="organization" value="{{ old('organization') }}" autocomplete="organization" class="ns-input">
                            @error('organization')<span class="ns-error">{{ $message }}</span>@enderror
                        </label>
                    @endif

                    <label @class(['sm:col-span-2' => ! $needsOrganization])>
                        <span class="ns-label">{{ __('rsvp.step1.email') }} <span class="ns-req">*</span></span>
                        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="ns-input">
                        <span class="ns-hint">{{ __('rsvp.step1.email_note') }}</span>
                        @error('email')<span class="ns-error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="ns-label">{{ __('rsvp.step1.phone') }} <span class="ns-req">*</span></span>
                        <span class="flex">
                            <select name="phone_country" class="ns-prefix !w-auto cursor-pointer" aria-label="{{ __('rsvp.step1.country_code') }}">
                                @foreach (config('nextstep.phone.countries') as $code => $country)
                                    <option value="{{ $code }}" @selected(old('phone_country', config('nextstep.phone.default_country')) === $code)>{{ $code }}</option>
                                @endforeach
                            </select>
                            <input type="tel" name="phone" value="{{ old('phone') }}" inputmode="numeric" autocomplete="tel"
                                   placeholder="{{ __('register.step1.phone_placeholder') }}" class="ns-input flex-1 min-w-0">
                        </span>
                        @error('phone')<span class="ns-error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="ns-label">{{ __('rsvp.step1.city') }} <span class="ns-req">*</span></span>
                        <select name="city" class="ns-select">
                            <option value="">{{ __('register.step1.city_placeholder') }}</option>
                            @foreach (config('nextstep.cities') as $city)
                                <option value="{{ $city }}" @selected(old('city') === $city)>{{ $city }}</option>
                            @endforeach
                        </select>
                        @error('city')<span class="ns-error">{{ $message }}</span>@enderror
                    </label>
                </div>

                <label class="flex gap-[14px] items-start cursor-pointer mt-7 mb-7">
                    <input type="checkbox" name="consent_terms" value="1" class="sr-only" @checked(old('consent_terms'))>
                    <span class="ns-box mt-[2px]"></span>
                    <span class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.55]">
                        {{ __('rsvp.consent') }}
                        <a href="{{ route('terms') }}" target="_blank">{{ __('site.footer.terms') }}</a> ·
                        <a href="{{ route('privacy') }}" target="_blank">{{ __('site.footer.privacy') }}</a>
                    </span>
                </label>
                @error('consent_terms')<span class="ns-error block mb-5">{{ $message }}</span>@enderror

                <button type="submit" class="ns-btn ns-btn-cobalt">{{ __('rsvp.submit') }}</button>

                <p class="ns-meta text-[12.5px] mt-5 max-w-[54ch]">{{ __('rsvp.after_note') }}</p>
            </form>
        </div>
    </div>
</x-layouts.site>
