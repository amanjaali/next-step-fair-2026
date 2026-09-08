<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[620px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('register.kicker') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3">{{ __('register.quick.title') }}</h1>
        <p class="ns-body max-w-[54ch] mb-8">{{ __('register.quick.lead') }}</p>

        @if (session('status'))
            <div class="bg-bone-200 p-5 mb-8 font-[family-name:var(--ns-body)] text-[15px]">{{ session('status') }}</div>
        @endif

        @if (session('duplicate'))
            {{-- The number is already on the list. No second pass is made; the one
                 that exists can be sent again instead. --}}
            <div class="ns-card border-t-[6px] !border-t-magenta mb-8" role="alert">
                <h2 class="font-[family-name:var(--ns-display)] text-[24px] font-semibold mb-3">{{ __('register.duplicate.title') }}</h2>
                <p class="ns-body mb-6 max-w-[56ch]">{{ __('register.duplicate.body') }}</p>
                <div class="flex gap-3 flex-wrap items-center">
                    <form method="POST" action="{{ route('register.duplicate.resend') }}">
                        @csrf
                        <button type="submit" class="ns-btn ns-btn-magenta ns-btn-sm">{{ __('register.duplicate.resend') }}</button>
                    </form>
                    <a href="{{ route('attendee.signin') }}" class="ns-btn ns-btn-ghost ns-btn-sm">{{ __('register.duplicate.signin') }}</a>
                </div>
            </div>
        @endif

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
            <form method="POST" action="{{ route('register.quick.store') }}" novalidate>
                @csrf

                {{-- Honeypot: a real person never fills this in. --}}
                <input type="text" name="ns_hp" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

                <label class="block mb-5">
                    <span class="ns-label">{{ __('register.quick.name') }} <span class="ns-req">*</span></span>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" autocomplete="name" class="ns-input">
                    <span class="ns-hint">{{ __('register.quick.name_hint') }}</span>
                    @error('full_name')<span class="ns-error">{{ $message }}</span>@enderror
                </label>

                <label class="block mb-5">
                    <span class="ns-label">{{ __('register.quick.phone') }} <span class="ns-req">*</span></span>
                    <div class="flex gap-2">
                        <select name="phone_country" class="ns-select !w-[110px] ns-num">
                            @foreach (config('nextstep.phone.countries') as $code => $label)
                                <option value="{{ $code }}" @selected(old('phone_country', config('nextstep.phone.default_country')) === $code)>{{ $code }}</option>
                            @endforeach
                        </select>
                        <input type="tel" name="phone" value="{{ old('phone') }}" inputmode="numeric"
                               autocomplete="tel" placeholder="770 000 0000" class="ns-input ns-num flex-1">
                    </div>
                    <span class="ns-hint">{{ __('register.quick.phone_hint') }}</span>
                    @error('phone')<span class="ns-error">{{ $message }}</span>@enderror
                </label>

                <label class="flex gap-[14px] items-start cursor-pointer mb-7">
                    <input type="checkbox" name="consent_terms" value="1" class="sr-only">
                    <span class="ns-box mt-[2px]"></span>
                    <span class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.55]">
                        {{ __('register.quick.consent') }}
                        <a href="{{ route('terms') }}" target="_blank">{{ __('site.footer.terms') }}</a> ·
                        <a href="{{ route('privacy') }}" target="_blank">{{ __('site.footer.privacy') }}</a>
                    </span>
                </label>
                @error('consent_terms')<span class="ns-error">{{ $message }}</span>@enderror

                <button type="submit" class="ns-btn ns-btn-magenta">{{ __('register.quick.submit') }}</button>
            </form>

            <div class="mt-7 pt-5 border-t border-[rgba(5,7,8,0.12)] flex items-center gap-3 flex-wrap">
                <span class="ns-meta">{{ __('register.quick.switch') }}</span>
                <a href="{{ route('register.fair') }}"
                   class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">
                    {{ __('register.quick.switch_link') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts.site>
