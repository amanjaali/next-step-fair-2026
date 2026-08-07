<x-layouts.scholarship :title="$title" :scholarshipNav="$scholarshipNav" :attendee="$attendee" :application="$application">
    <div class="ns-wrap max-w-[1080px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('scholarship.universities.kicker') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,46px)] mb-3">{{ __('scholarship.universities.heading') }}</h1>
        <p class="ns-body max-w-[60ch] mb-10">{{ __('scholarship.universities.lead') }}</p>

        <div class="grid gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] md:grid-cols-2">
            @foreach ($universities as $university)
                <a href="{{ route('scholarship.university', ['slug' => $university['slug']]) }}"
                   class="bg-white px-6 py-6 block no-underline hover:bg-bone-50">
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div class="ns-eyebrow !text-[9.5px] !text-magenta">{{ __("scholarship.tiers.{$university['tier']}") }}</div>
                        <div class="ns-num font-[family-name:var(--ns-display)] text-[24px] font-bold text-ink leading-none">
                            {{ collect($university['departments'])->sum('seats') }}
                        </div>
                    </div>

                    <div class="font-[family-name:var(--ns-display)] text-[19px] font-semibold text-ink mb-2 leading-[1.25]">{{ $university['name'] }}</div>
                    <div class="ns-meta text-[12.5px] mb-4">{{ $university['city'] }} · {{ $university['language'] }}</div>

                    <div class="flex gap-[6px] flex-wrap">
                        @foreach ($university['departments'] as $department)
                            <span class="border border-[rgba(5,7,8,0.18)] px-[9px] py-[3px] font-[family-name:var(--ns-body)] text-[12px]">
                                {{ $department['name'] }} <span class="ns-num text-muted">{{ $department['seats'] }}</span>
                            </span>
                        @endforeach
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-layouts.scholarship>
