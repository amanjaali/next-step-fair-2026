<x-layouts.scholarship :title="$title" :scholarshipNav="$scholarshipNav" :attendee="$attendee" :application="$application">

    {{-- The claim, and the number that makes it real. --}}
    <section class="bg-ink text-white">
        <div class="ns-wrap max-w-[1080px] pt-[clamp(44px,7vw,96px)] pb-[clamp(40px,6vw,80px)]">
            <div class="flex items-center gap-3 mb-5">
                <span class="w-[26px] h-2 bg-magenta"></span>
                <span class="ns-eyebrow !text-magenta">{{ __('scholarship.home.kicker', ['cycle' => $cycle]) }}</span>
            </div>

            <h1 class="ns-h1 !text-white !text-[clamp(34px,5.6vw,62px)] mb-6 max-w-[18ch]">{{ __('scholarship.home.heading') }}</h1>
            <p class="font-[family-name:var(--ns-body)] text-[clamp(16px,1.7vw,19px)] leading-[1.6] text-white/75 max-w-[56ch] mb-9">
                {{ __('scholarship.home.lead') }}
            </p>

            {{-- items-start, not items-center: for a parent the first item is a
                 panel rather than a button, and centring floats the guidelines
                 link against the middle of it. --}}
            <div class="flex gap-3 flex-wrap items-start">
                <x-ns.scholarship-cta :attendee="$attendee" variant="dark" />
                <a href="{{ route('scholarship.guidelines') }}" class="ns-btn ns-btn-ghost !text-white !border-white/40 hover:!bg-white/10">
                    {{ __('scholarship.home.cta_rules') }}
                </a>
            </div>
        </div>
    </section>

    {{-- Forty seats, and where they go. The quota is the whole point of the
         programme, so it is the first thing after the headline. --}}
    <section class="ns-wrap max-w-[1080px] py-[clamp(40px,6vw,80px)]">
        <div class="flex items-baseline justify-between gap-4 flex-wrap mb-2">
            <h2 class="ns-h2 !text-[clamp(24px,3vw,34px)]">{{ __('scholarship.home.quota_title') }}</h2>
            <span class="ns-num font-[family-name:var(--ns-display)] text-[38px] font-bold leading-none text-magenta">
                {{ config('scholarship.seats') }}
            </span>
        </div>
        <p class="ns-body max-w-[62ch] mb-8">{{ __('scholarship.home.quota_lead') }}</p>

        <div class="grid gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($regions as $code => $region)
                <a href="{{ route('scholarship.region', ['code' => strtolower($code)]) }}"
                   class="bg-white px-5 py-[18px] block no-underline hover:bg-bone-50">
                    <div class="flex items-baseline justify-between gap-3">
                        <span class="font-[family-name:var(--ns-display)] text-[17px] font-semibold text-ink">{{ $region['name'] }}</span>
                        <span class="ns-num font-[family-name:var(--ns-display)] text-[20px] font-bold text-magenta">{{ $region['seats'] }}</span>
                    </div>
                    <div class="ns-meta text-[12px] mt-[3px]">
                        {{ __("scholarship.region_types.{$region['type']}") }} · {{ trans_choice('scholarship.home.districts', count($region['districts']), ['count' => count($region['districts'])]) }}
                    </div>
                </a>
            @endforeach
        </div>

        <p class="ns-meta text-[13px] mt-4 max-w-[62ch]">{{ __('scholarship.home.quota_note') }}</p>
    </section>

    {{-- What the award actually covers. Vague generosity helps nobody decide. --}}
    <section class="bg-bone-200">
        <div class="ns-wrap max-w-[1080px] py-[clamp(40px,6vw,80px)]">
            <h2 class="ns-h2 !text-[clamp(24px,3vw,34px)] mb-8">{{ __('scholarship.home.covers_title') }}</h2>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (__('scholarship.home.covers') as $item)
                    <div class="bg-white border-t-4 border-magenta px-5 py-6">
                        <div class="font-[family-name:var(--ns-display)] text-[18px] font-semibold mb-2">{{ $item['title'] }}</div>
                        <p class="ns-body !text-[14px] text-body-soft">{{ $item['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Where the seats are. A student should be able to see, before applying,
         whether the subject they want is funded anywhere at all. --}}
    <section class="ns-wrap max-w-[1080px] py-[clamp(40px,6vw,80px)]">
        <div class="flex items-baseline justify-between gap-4 flex-wrap mb-8">
            <h2 class="ns-h2 !text-[clamp(24px,3vw,34px)]">{{ __('scholarship.home.universities_title') }}</h2>
            <a href="{{ route('scholarship.universities') }}" class="font-[family-name:var(--ns-body)] text-sm font-bold text-magenta">
                {{ __('scholarship.home.universities_all') }}
            </a>
        </div>

        <div class="grid gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] sm:grid-cols-2">
            @foreach (array_slice($universities, 0, 4) as $university)
                <a href="{{ route('scholarship.university', ['slug' => $university['slug']]) }}"
                   class="bg-white px-6 py-5 block no-underline hover:bg-bone-50">
                    <div class="ns-eyebrow !text-[9.5px] !text-magenta mb-2">{{ __("scholarship.tiers.{$university['tier']}") }}</div>
                    <div class="font-[family-name:var(--ns-display)] text-[18px] font-semibold text-ink mb-1 leading-[1.25]">{{ $university['name'] }}</div>
                    <div class="ns-meta text-[12.5px]">
                        {{ $university['city'] }} · <span class="ns-num">{{ collect($university['departments'])->sum('seats') }}</span> {{ __('scholarship.seats_word') }}
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- The last word is the deadline, because that is the thing people miss. --}}
    <section class="bg-ink text-white">
        <div class="ns-wrap max-w-[1080px] py-[clamp(36px,5vw,64px)] flex items-center justify-between gap-8 flex-wrap">
            <div>
                <div class="ns-eyebrow !text-magenta mb-2">{{ __('scholarship.home.deadline_kicker') }}</div>
                <div class="font-[family-name:var(--ns-display)] text-[clamp(22px,3vw,32px)] font-semibold ns-num">
                    {{ ns_format_date(\Illuminate\Support\Carbon::parse(config('scholarship.timeline.closes'))) }}
                </div>
            </div>
            <x-ns.scholarship-cta :attendee="$attendee" variant="dark" />
        </div>
    </section>

</x-layouts.scholarship>
