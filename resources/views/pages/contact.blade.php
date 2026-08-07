<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :title="__('site.pages.contact.title')" :lead="__('site.pages.contact.lead')">
            <div class="grid gap-12 lg:grid-cols-[1fr_1.1fr] items-start">
                <div>
                    <div class="flex flex-col gap-5 mb-8">
                        @foreach ([
                            __('site.nav.contact') => config('nextstep.contact.general'),
                            __('site.common.press') => config('nextstep.contact.media'),
                            __('site.nav.conference') => config('nextstep.contact.protocol'),
                            __('site.pages.sponsors.kicker') => config('nextstep.contact.partnerships'),
                        ] as $label => $email)
                            <div class="border-b border-[rgba(5,7,8,0.12)] pb-4">
                                <div class="ns-eyebrow !text-[10.5px] mb-[6px]">{{ $label }}</div>
                                <a href="mailto:{{ $email }}" class="font-[family-name:var(--ns-body)] text-[15.5px] font-bold text-ink hover:text-magenta">{{ $email }}</a>
                            </div>
                        @endforeach
                        <div>
                            <div class="ns-eyebrow !text-[10.5px] mb-[6px]">{{ __('site.common.venue') }}</div>
                            @foreach (config('nextstep.event.venue.address') as $code => $address)
                                <div dir="{{ config("nextstep.locales.$code.dir") }}" class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.6] mb-2">{{ $address }}</div>
                            @endforeach
                            <span class="ns-num font-[family-name:var(--ns-body)] text-[15px]">{{ config('nextstep.contact.phone') }}</span>
                        </div>
                    </div>

                    <a href="{{ config('nextstep.event.venue.map_url') }}" target="_blank" rel="noopener"
                       class="ns-frame ns-frame-center min-h-[280px] relative w-full">
                        <span>{{ __('site.home.venue_map') }}</span>
                        <span class="absolute left-1/2 top-1/2 w-[14px] h-[14px] bg-magenta"></span>
                    </a>
                </div>

                <div class="ns-card">
                    <h2 class="font-[family-name:var(--ns-display)] text-[26px] font-semibold mb-6">{{ __('site.pages.contact.form_title') }}</h2>

                    @if (session('status'))
                        <div class="bg-bone-200 p-5 mb-6 font-[family-name:var(--ns-body)] text-[15px]">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" class="flex flex-col gap-5">
                        @csrf
                        <input type="text" name="ns_hp" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

                        <div class="grid gap-5 sm:grid-cols-2">
                            <label>
                                <span class="ns-label">{{ __('register.step1.name') }} <span class="ns-req">*</span></span>
                                <input type="text" name="name" required value="{{ old('name') }}" class="ns-input"
                                       @error('name') aria-invalid="true" @enderror>
                                @error('name')<span class="ns-error">{{ $message }}</span>@enderror
                            </label>
                            <label>
                                <span class="ns-label">{{ __('site.common.email_address') }} <span class="ns-req">*</span></span>
                                <input type="email" name="email" required value="{{ old('email') }}" class="ns-input"
                                       @error('email') aria-invalid="true" @enderror>
                                @error('email')<span class="ns-error">{{ $message }}</span>@enderror
                            </label>
                            <label>
                                <span class="ns-label">{{ __('register.step1.phone') }}</span>
                                <input type="tel" name="phone" value="{{ old('phone') }}" class="ns-input">
                            </label>
                            <label>
                                <span class="ns-label">{{ __('site.common.institution') }}</span>
                                <input type="text" name="organization" value="{{ old('organization') }}" class="ns-input">
                            </label>
                        </div>

                        <label>
                            <span class="ns-label">{{ __('rsvp.step2.notes') }} <span class="ns-req">*</span></span>
                            {{-- Rich text is deliberately not offered on public forms: plain text only. --}}
                            <textarea name="message" rows="6" required class="ns-textarea" @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
                            @error('message')<span class="ns-error">{{ $message }}</span>@enderror
                        </label>

                        <button type="submit" class="ns-btn ns-btn-ink self-start">{{ __('site.cta.send') }}</button>
                    </form>
                </div>
            </div>
        </x-ns.page-head>
    </div>
</x-layouts.site>
