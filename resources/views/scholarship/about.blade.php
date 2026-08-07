<x-layouts.scholarship :title="$title" :scholarshipNav="$scholarshipNav" :attendee="$attendee" :application="$application">
    <div class="ns-wrap max-w-[760px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('scholarship.about.kicker') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,46px)] mb-3">{{ __('scholarship.about.heading') }}</h1>
        <p class="ns-body !text-[clamp(16px,1.6vw,18.5px)] max-w-[58ch] mb-10">{{ __('scholarship.about.lead') }}</p>

        @foreach (__('scholarship.about.sections') as $section)
            <h2 class="ns-h2 !text-[clamp(20px,2.3vw,26px)] mb-3">{{ $section['title'] }}</h2>
            @foreach ($section['body'] as $paragraph)
                <p class="ns-body max-w-[62ch] mb-4">{{ $paragraph }}</p>
            @endforeach
            <div class="h-8"></div>
        @endforeach

        <div class="ns-card border-s-[6px] !border-s-magenta">
            <p class="ns-body !text-[15px] mb-5 max-w-[56ch]">{{ __('scholarship.about.cta_note') }}</p>
            <a href="{{ route('scholarship.apply') }}" class="ns-btn ns-btn-magenta ns-btn-sm">{{ __('scholarship.home.cta') }}</a>
        </div>
    </div>
</x-layouts.scholarship>
