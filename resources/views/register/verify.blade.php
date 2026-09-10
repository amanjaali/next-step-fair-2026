<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[720px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('register.kicker') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3">{{ __('register.step4.heading') }}</h1>
        <p class="ns-body max-w-[58ch] mb-8">{{ __('register.step4.lead') }}</p>

        @if (session('status'))
            <div class="bg-bone-200 p-5 mb-6 font-[family-name:var(--ns-body)] text-[15px]">{{ session('status') }}</div>
        @endif

        {{-- Test mode only. Present when APP_DEBUG is on (no OTP is sent). --}}
        @if ($testingCode)
            <div class="border-2 border-dashed border-[#F2A93B] bg-[#FFF8EC] p-5 mb-6" role="status">
                <div class="ns-eyebrow !text-[10.5px] !text-[#8A6100] mb-2">Test mode · no WhatsApp message was sent</div>
                <div class="flex items-baseline gap-3 flex-wrap">
                    <span class="ns-num font-[family-name:var(--ns-display)] text-[30px] font-bold tracking-[0.18em]">{{ $testingCode }}</span>
                    <span class="font-[family-name:var(--ns-body)] text-[13.5px] text-body-soft">
                        Enter this code to continue. It disappears once WhatsApp is connected.
                    </span>
                </div>
            </div>
        @endif

        <div class="ns-card" x-data="nsOtp({{ $cooldown }})">
            <div class="ns-eyebrow !text-[11px] mb-4">{{ __('register.step4.verification') }}</div>

            <form method="POST" action="{{ route('register.fair.verify.submit', $registration->ticket_id) }}"
                  class="flex items-end gap-4 flex-wrap">
                @csrf
                <label>
                    <span class="ns-label">{{ __('register.step4.code') }}</span>
                    <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required
                           maxlength="6" placeholder="000000" @input="onInput($event)"
                           class="ns-input !w-[190px] ns-num !text-[22px] !tracking-[0.24em] !bg-white"
                           @error('code') aria-invalid="true" @enderror>
                </label>
                <button type="submit" class="ns-btn ns-btn-magenta">{{ __('register.submit') }}</button>
            </form>

            @error('code')
                <span class="ns-error">{{ $message }}</span>
            @enderror

            <div class="flex items-center gap-4 flex-wrap mt-6 pt-5 border-t border-[rgba(5,7,8,0.12)]">
                <form method="POST" action="{{ route('register.fair.resend', $registration->ticket_id) }}">
                    @csrf
                    <button type="submit" class="ns-btn ns-btn-ghost ns-btn-sm !text-magenta !border-[rgba(182,70,152,0.5)]"
                            :disabled="cooldown > 0" :class="cooldown > 0 ? 'opacity-50' : ''">
                        <span x-show="cooldown <= 0">{{ __('register.step4.resend') }}</span>
                        <span x-show="cooldown > 0" x-cloak x-text="'{{ __('register.step4.resend_in', ['seconds' => ':s']) }}'.replace(':s', cooldown)"></span>
                    </button>
                </form>
                <span class="ns-meta">{{ __('register.step4.sent_to', ['phone' => $registration->phone_country.' '.$registration->maskedPhone()]) }}</span>
            </div>
        </div>
    </div>
</x-layouts.site>
