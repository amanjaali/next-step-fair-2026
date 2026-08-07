<x-layouts.site :title="$title" :navKey="$navKey" track="conference">
    <div class="ns-wrap max-w-[620px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-cobalt"></span>
            <span class="ns-eyebrow !text-cobalt">{{ __('institution.nav.portal') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3">{{ __('institution.signin.title') }}</h1>
        <p class="ns-body max-w-[54ch] mb-8">{{ __('institution.signin.lead') }}</p>

        @if (session('status'))
            <div class="bg-bone-200 p-5 mb-6 font-[family-name:var(--ns-body)] text-[15px]">{{ session('status') }}</div>
        @endif

        <div class="ns-card">
            <form method="POST" action="{{ route('portal.signin.send') }}" novalidate>
                @csrf
                <label class="block mb-5">
                    <span class="ns-label">{{ __('institution.signin.email') }} <span class="ns-req">*</span></span>
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="ns-input">
                    @error('email')<span class="ns-error">{{ $message }}</span>@enderror
                </label>
                <button type="submit" class="ns-btn ns-btn-cobalt">{{ __('institution.signin.submit') }}</button>
            </form>

            <div class="mt-7 pt-5 border-t border-[rgba(5,7,8,0.12)] flex items-center gap-3 flex-wrap">
                <span class="ns-meta">{{ __('institution.signin.no_account') }}</span>
                <a href="{{ route('portal.register') }}" class="font-[family-name:var(--ns-body)] text-sm font-bold text-cobalt">
                    {{ __('institution.signin.register') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts.site>
