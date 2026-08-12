<x-layouts.scholarship :title="$title" :scholarshipNav="$scholarshipNav" :attendee="$attendee" :application="$application">
    <div class="ns-wrap max-w-[860px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <a href="{{ route('scholarship.home') }}" class="ns-meta text-[13px] mb-6 inline-block">← {{ __('scholarship.nav.home') }}</a>

        <div class="ns-eyebrow !text-magenta mb-3">{{ __("scholarship.region_types.{$region['type']}") }}</div>
        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,46px)] mb-3">{{ $region['name'] }}</h1>
        <p class="ns-body max-w-[58ch] mb-9">
            {{ trans_choice('scholarship.region.lead', $region['seats'], ['count' => $region['seats'], 'region' => $region['name']]) }}
        </p>

        <div class="grid gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] sm:grid-cols-2 mb-10">
            <div class="bg-white px-6 py-5">
                <div class="ns-eyebrow !text-[9.5px] mb-2">{{ __('scholarship.region.seats') }}</div>
                <div class="ns-num font-[family-name:var(--ns-display)] text-[34px] font-bold leading-none text-magenta">{{ $region['seats'] }}</div>
            </div>
            <div class="bg-white px-6 py-5">
                <div class="ns-eyebrow !text-[9.5px] mb-2">{{ __('scholarship.region.districts') }}</div>
                <div class="ns-num font-[family-name:var(--ns-display)] text-[34px] font-bold leading-none">{{ count($region['districts']) }}</div>
            </div>
        </div>

        <h2 class="ns-h2 !text-[clamp(19px,2.2vw,24px)] mb-4">{{ __('scholarship.region.districts_title') }}</h2>
        <p class="ns-body !text-[15px] text-body-soft max-w-[58ch] mb-5">{{ __('scholarship.region.districts_lead') }}</p>

        <div class="flex gap-2 flex-wrap mb-10">
            @foreach ($region['districts'] as $district)
                <span class="border border-[rgba(5,7,8,0.18)] px-3 py-[6px] font-[family-name:var(--ns-body)] text-[13.5px]">{{ $district }}</span>
            @endforeach
        </div>

        <x-ns.scholarship-cta :attendee="$attendee" />
    </div>
</x-layouts.scholarship>
