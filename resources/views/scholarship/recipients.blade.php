<x-layouts.scholarship :title="$title" :scholarshipNav="$scholarshipNav" :attendee="$attendee" :application="$application">
    <div class="ns-wrap max-w-[860px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('scholarship.recipients.kicker') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,46px)] mb-3">{{ __('scholarship.recipients.heading') }}</h1>
        <p class="ns-body max-w-[58ch] mb-10">{{ __('scholarship.recipients.lead') }}</p>

        {{-- No names until there are real ones. Inventing a cohort to fill a page
             is the one thing a scholarship page must not do. --}}
        <div class="ns-card border-s-[6px] !border-s-magenta mb-10">
            <div class="font-[family-name:var(--ns-display)] text-[20px] font-semibold mb-2">{{ __('scholarship.recipients.empty_title') }}</div>
            <p class="ns-body !text-[15px] max-w-[58ch]">{{ __('scholarship.recipients.empty_body') }}</p>
        </div>

        <h2 class="ns-h2 !text-[clamp(19px,2.2vw,24px)] mb-2">{{ __('scholarship.recipients.quota_title') }}</h2>
        <p class="ns-body !text-[15px] text-body-soft max-w-[58ch] mb-6">{{ __('scholarship.recipients.quota_lead') }}</p>

        <div class="grid gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($regions as $code => $region)
                <div class="bg-white px-5 py-4">
                    <div class="flex items-baseline justify-between gap-3">
                        <span class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold">{{ $region['name'] }}</span>
                        <span class="ns-num font-[family-name:var(--ns-display)] text-[18px] font-bold text-magenta">{{ $region['seats'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.scholarship>
