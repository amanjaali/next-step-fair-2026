<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[620px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('attendee.nav.my_next_step') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3">{{ __('attendee.signin.code_title') }}</h1>
        <p class="ns-body max-w-[54ch] mb-8">
            {{-- Deliberately non-committal: the previous screen never confirms whether
                 the number is registered, so this cannot be used to fish for one. --}}
            {{ $phone ? __('attendee.signin.code_lead', ['phone' => $phone]) : __('attendee.signin.code_lead_generic') }}
        </p>

        @if ($testingCode)
            <div class="border-2 border-dashed border-[#F2A93B] bg-[#FFF8EC] p-5 mb-6" role="status">
                <div class="ns-eyebrow !text-[10.5px] !text-[#8A6100] mb-2">Test mode · no WhatsApp message was sent</div>
                <span class="ns-num font-[family-name:var(--ns-display)] text-[30px] font-bold tracking-[0.18em]">{{ $testingCode }}</span>
            </div>
        @endif

        <div class="ns-card">
            <form method="POST" action="{{ route('attendee.signin.verify') }}" class="flex items-end gap-4 flex-wrap">
                @csrf
                <label>
                    <span class="ns-label">{{ __('register.step4.code') }}</span>
                    <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required
                           maxlength="6" placeholder="000000"
                           class="ns-input !w-[190px] ns-num !text-[22px] !tracking-[0.24em] !bg-white">
                </label>
                <button type="submit" class="ns-btn ns-btn-magenta">{{ __('attendee.signin.title') }}</button>
            </form>

            @error('code')<span class="ns-error">{{ $message }}</span>@enderror

            <div class="mt-6 pt-5 border-t border-[rgba(5,7,8,0.12)]">
                <a href="{{ route('attendee.signin') }}" class="ns-meta">{{ __('site.cta.back') }}</a>
            </div>
        </div>
    </div>
</x-layouts.site>
