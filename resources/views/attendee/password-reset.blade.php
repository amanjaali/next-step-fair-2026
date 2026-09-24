<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="ns-wrap max-w-[620px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('attendee.nav.my_next_step') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3">{{ __('attendee.password.new_title') }}</h1>
        <p class="ns-body max-w-[54ch] mb-8">{{ __('attendee.password.new_lead') }}</p>

        <div class="ns-card">
            <form method="POST" action="{{ route('attendee.password.update') }}" novalidate
                  x-data="{ show: false }">
                @csrf
                <label class="block mb-5">
                    <span class="ns-label">{{ __('attendee.password.new_password') }} <span class="ns-req">*</span></span>
                    <span class="relative block">
                        <input :type="show ? 'text' : 'password'" name="password" autocomplete="new-password" class="ns-input !pe-[64px]">
                        <button type="button" @click="show = ! show"
                                class="absolute inset-y-0 end-0 px-4 font-[family-name:var(--ns-body)] text-[12.5px] font-bold text-body-soft">
                            <span x-text="show ? '{{ __('attendee.password.hide') }}' : '{{ __('attendee.password.show') }}'"></span>
                        </button>
                    </span>
                    @error('password')<span class="ns-error">{{ $message }}</span>@enderror
                </label>

                <label class="block mb-6">
                    <span class="ns-label">{{ __('attendee.password.confirm_password') }} <span class="ns-req">*</span></span>
                    <span class="relative block">
                        <input :type="show ? 'text' : 'password'" name="password_confirmation" autocomplete="new-password" class="ns-input !pe-[64px]">
                        <button type="button" @click="show = ! show"
                                class="absolute inset-y-0 end-0 px-4 font-[family-name:var(--ns-body)] text-[12.5px] font-bold text-body-soft">
                            <span x-text="show ? '{{ __('attendee.password.hide') }}' : '{{ __('attendee.password.show') }}'"></span>
                        </button>
                    </span>
                </label>

                <button type="submit" class="ns-btn ns-btn-magenta">{{ __('attendee.password.submit_reset') }}</button>
            </form>
        </div>
    </div>
</x-layouts.site>
