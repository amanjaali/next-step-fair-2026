<x-layouts.site :title="$title" :navKey="$navKey" track="conference">
    <div class="ns-wrap max-w-[620px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3">{{ __('institution.signin.code_title') }}</h1>
        <p class="ns-body max-w-[54ch] mb-8">{{ __('institution.signin.code_lead', ['email' => $email]) }}</p>

        @if ($testingCode)
            <div class="border-2 border-dashed border-[#F2A93B] bg-[#FFF8EC] p-5 mb-6" role="status">
                <div class="ns-eyebrow !text-[10.5px] !text-[#8A6100] mb-2">Test mode · no e-mail was sent</div>
                <span class="ns-num font-[family-name:var(--ns-display)] text-[30px] font-bold tracking-[0.18em]">{{ $testingCode }}</span>
            </div>
        @endif

        <div class="ns-card">
            <form method="POST" action="{{ route('portal.signin.verify') }}" class="flex items-end gap-4 flex-wrap">
                @csrf
                <label>
                    <span class="ns-label">{{ __('register.step4.code') }}</span>
                    <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required maxlength="6"
                           placeholder="000000" class="ns-input !w-[190px] ns-num !text-[22px] !tracking-[0.24em] !bg-white">
                </label>
                <button type="submit" class="ns-btn ns-btn-cobalt">{{ __('institution.signin.title') }}</button>
            </form>
            @error('code')<span class="ns-error">{{ $message }}</span>@enderror
        </div>
    </div>
</x-layouts.site>
