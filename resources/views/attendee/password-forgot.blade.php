<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[620px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('attendee.nav.my_next_step') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3">{{ __('attendee.password.forgot_title') }}</h1>
        <p class="ns-body max-w-[54ch] mb-8">{{ __('attendee.password.forgot_lead') }}</p>

        <div class="ns-card">
            <form method="POST" action="{{ route('attendee.password.send') }}" novalidate>
                @csrf
                <label class="block mb-2">
                    <span class="ns-label">{{ __('attendee.password.phone_label') }} <span class="ns-req">*</span></span>
                    <span class="flex gap-2">
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

                <button type="submit" class="ns-btn ns-btn-magenta mt-4">{{ __('attendee.password.submit_request') }}</button>
            </form>

            <div class="mt-7 pt-5 border-t border-[rgba(5,7,8,0.12)]">
                <a href="{{ route('attendee.signin') }}" class="ns-meta">{{ __('site.cta.back') }}</a>
            </div>
        </div>
    </div>
</x-layouts.site>
