<x-layouts.scholarship :title="$title" :scholarshipNav="$scholarshipNav" :attendee="$attendee" :application="$application">
    <div class="ns-wrap max-w-[860px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <a href="{{ route('scholarship.universities') }}" class="ns-meta text-[13px] mb-6 inline-block">← {{ __('scholarship.nav.universities') }}</a>

        <div class="ns-eyebrow !text-magenta mb-3">{{ __("scholarship.tiers.{$university['tier']}") }}</div>
        <h1 class="ns-h1 !text-[clamp(28px,3.8vw,42px)] mb-3">{{ $university['name'] }}</h1>
        <p class="ns-body max-w-[58ch] mb-8">{{ __("scholarship.university_about.{$university['slug']}") }}</p>

        <div class="grid gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] sm:grid-cols-4 mb-10">
            @foreach ([
                __('scholarship.university.city') => $university['city'],
                __('scholarship.university.language') => $university['language'],
                __('scholarship.university.founded') => $university['founded'],
                __('scholarship.university.housing') => __("scholarship.housing.{$university['housing']}"),
            ] as $label => $value)
                <div class="bg-white px-5 py-4">
                    <div class="ns-eyebrow !text-[9px] mb-1">{{ $label }}</div>
                    <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold ns-num">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        <h2 class="ns-h2 !text-[clamp(21px,2.4vw,28px)] mb-2">{{ __('scholarship.university.seats_title') }}</h2>
        <p class="ns-body !text-[15px] text-body-soft max-w-[58ch] mb-6">{{ __('scholarship.university.seats_lead') }}</p>

        <div class="border border-[rgba(5,7,8,0.14)]">
            @foreach ($university['departments'] as $department)
                <div class="flex justify-between items-center gap-6 px-5 py-[14px] border-b border-[rgba(5,7,8,0.1)] last:border-b-0">
                    <span class="font-[family-name:var(--ns-body)] text-[15px] font-medium">{{ $department['name'] }}</span>
                    <span class="ns-num font-[family-name:var(--ns-display)] text-[18px] font-bold text-magenta">{{ $department['seats'] }}</span>
                </div>
            @endforeach
        </div>

        <a href="{{ route('scholarship.apply') }}" class="ns-btn ns-btn-magenta mt-9">{{ __('scholarship.home.cta') }}</a>
    </div>
</x-layouts.scholarship>
