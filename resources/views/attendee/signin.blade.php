<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[620px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('attendee.nav.my_next_step') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3">{{ __('attendee.signin.title') }}</h1>
        <p class="ns-body max-w-[54ch] mb-8">{{ __('attendee.signin.lead') }}</p>

        @if (session('status'))
            <div class="bg-bone-200 p-5 mb-6 font-[family-name:var(--ns-body)] text-[15px]">{{ session('status') }}</div>
        @endif

        {{-- Students: the Next Step ID they created when they registered. --}}
        <div class="ns-card mb-6">
            <form method="POST" action="{{ route('attendee.signin.password') }}" novalidate>
                @csrf
                <label class="block mb-5">
                    <span class="ns-label">{{ __('attendee.signin.email') }} <span class="ns-req">*</span></span>
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="ns-input">
                    @error('email')<span class="ns-error">{{ $message }}</span>@enderror
                </label>

                <label class="block mb-6">
                    <span class="ns-label">{{ __('register.account.password') }} <span class="ns-req">*</span></span>
                    <input type="password" name="password" autocomplete="current-password" class="ns-input">
                    @error('password')<span class="ns-error">{{ $message }}</span>@enderror
                </label>

                <button type="submit" class="ns-btn ns-btn-magenta">{{ __('attendee.signin.submit') }}</button>
            </form>

            <div class="mt-7 pt-5 border-t border-[rgba(5,7,8,0.12)] flex items-center gap-3 flex-wrap">
                <span class="ns-meta">{{ __('attendee.signin.no_account') }}</span>
                <a href="{{ route('register.fair') }}"
                   class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">
                    {{ __('attendee.signin.register_instead') }}
                </a>
            </div>
        </div>

        {{-- Everyone else. A parent or a visitor has no account and does not need
             one — they have lost a WhatsApp message, and that is all.
             This used to be a self-service "send me a code" form, but nothing
             behind it ever delivered a code — a dead end dressed up as a fix.
             Sent to a person instead, until that delivery actually exists. --}}
        <details class="ns-card !py-5">
            <summary class="font-[family-name:var(--ns-body)] text-[15px] font-bold cursor-pointer">
                {{ __('attendee.signin.badge_title') }}
            </summary>

            <p class="ns-body !text-[14.5px] text-body-soft mt-4 mb-5 max-w-[52ch]">{{ __('attendee.signin.badge_lead') }}</p>

            <a href="{{ route('contact') }}" class="ns-btn ns-btn-ghost ns-btn-sm">{{ __('site.nav.contact') }}</a>
        </details>
    </div>
</x-layouts.site>
