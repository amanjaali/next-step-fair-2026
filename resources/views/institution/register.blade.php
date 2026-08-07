<x-layouts.site :title="$title" :navKey="$navKey" track="conference">
    <div class="ns-wrap max-w-[720px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-cobalt"></span>
            <span class="ns-eyebrow !text-cobalt">{{ __('institution.nav.portal') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,44px)] mb-3">{{ __('institution.register.title') }}</h1>
        <p class="ns-body max-w-[60ch] mb-9">{{ __('institution.register.lead') }}</p>

        @if ($errors->any())
            <div class="ns-card border-t-[6px] !border-t-crimson mb-8" role="alert">
                <ul class="list-none m-0 p-0 flex flex-col gap-2">
                    @foreach ($errors->all() as $error)
                        <li class="font-[family-name:var(--ns-body)] text-[15px] text-crimson">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('portal.register.store') }}" novalidate
              x-data="{ existing: '{{ old('organization_id') }}' }">
            @csrf
            <input type="text" name="ns_hp" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

            <div class="ns-card mb-6">
                <div class="ns-eyebrow !text-[11px] mb-5">{{ __('institution.register.your_details') }}</div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <label>
                        <span class="ns-label">{{ __('institution.register.name') }} <span class="ns-req">*</span></span>
                        <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" class="ns-input">
                    </label>
                    <label>
                        <span class="ns-label">{{ __('institution.register.job_title') }}</span>
                        <input type="text" name="job_title" value="{{ old('job_title') }}" class="ns-input">
                    </label>
                    <label class="sm:col-span-2">
                        <span class="ns-label">{{ __('institution.register.email') }} <span class="ns-req">*</span></span>
                        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="ns-input">
                        <span class="ns-hint">{{ __('institution.register.email_hint') }}</span>
                    </label>
                    <label>
                        <span class="ns-label">{{ __('institution.register.phone') }}</span>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="ns-input ns-num">
                    </label>
                </div>
            </div>

            <div class="ns-card mb-6">
                <div class="ns-eyebrow !text-[11px] mb-2">{{ __('institution.register.institution') }}</div>
                <p class="ns-hint mb-5">{{ __('institution.register.existing_hint') }}</p>

                <label class="block mb-5">
                    <span class="ns-label">{{ __('institution.register.choose') }}</span>
                    <select name="organization_id" class="ns-select" x-model="existing">
                        <option value="">{{ __('institution.register.not_listed') }}</option>
                        @foreach ($claimable as $organization)
                            <option value="{{ $organization->id }}">{{ $organization->t('name') }}</option>
                        @endforeach
                    </select>
                </label>

                {{-- Only asked for when they are not claiming an existing entry. --}}
                <div x-show="! existing" x-cloak class="grid gap-5 sm:grid-cols-2">
                    <label class="sm:col-span-2">
                        <span class="ns-label">{{ __('institution.register.institution_name') }}</span>
                        <input type="text" name="institution_name" value="{{ old('institution_name') }}" class="ns-input">
                    </label>
                    <label>
                        <span class="ns-label">{{ __('institution.register.kind') }}</span>
                        <select name="kind" class="ns-select">
                            <option value="university">{{ __('taxonomy.kinds.university') }}</option>
                            <option value="institute">{{ __('taxonomy.kinds.institute') }}</option>
                        </select>
                    </label>
                    <label>
                        <span class="ns-label">{{ __('institution.register.country') }}</span>
                        <select name="country" class="ns-select">
                            @foreach (config('taxonomy.countries') as $code)
                                @continue($code === 'undecided')
                                <option value="{{ $code }}" @selected(old('country', 'IQ') === $code)>{{ __("taxonomy.countries.$code") }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>

            <label class="flex gap-[14px] items-start cursor-pointer mb-8">
                <input type="checkbox" name="consent_terms" value="1" class="sr-only">
                <span class="ns-box mt-[2px]"></span>
                <span class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.55]">
                    {{ __('institution.register.consent') }}
                    <a href="{{ route('terms') }}" target="_blank">{{ __('site.footer.terms') }}</a>
                </span>
            </label>

            <button type="submit" class="ns-btn ns-btn-cobalt ns-btn-lg">{{ __('institution.register.submit') }}</button>
        </form>
    </div>
</x-layouts.site>
