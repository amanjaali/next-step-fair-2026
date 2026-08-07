<x-layouts.scholarship :title="$title" :scholarshipNav="$scholarshipNav" :attendee="$attendee" :application="$application">
    <div class="ns-wrap max-w-[860px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('scholarship.committee.kicker') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,46px)] mb-3">{{ __('scholarship.committee.heading') }}</h1>
        <p class="ns-body max-w-[58ch] mb-10">{{ __('scholarship.committee.lead') }}</p>

        {{-- Named publicly. A rejected applicant is entitled to know who decided
             and on what, and a panel that can be named is a panel that behaves. --}}
        @foreach (['external' => __('scholarship.committee.external'), 'internal' => __('scholarship.committee.internal')] as $side => $heading)
            <h2 class="ns-h2 !text-[clamp(19px,2.2vw,24px)] mb-1">{{ $heading }}</h2>
            <p class="ns-body !text-[14px] text-body-soft mb-5 max-w-[56ch]">{{ __("scholarship.committee.{$side}_note") }}</p>

            <div class="border border-[rgba(5,7,8,0.14)] mb-10">
                @foreach ($members[$side] ?? [] as $member)
                    <div class="px-5 py-[15px] border-b border-[rgba(5,7,8,0.1)] last:border-b-0">
                        <div class="font-[family-name:var(--ns-body)] text-[15px] font-bold">{{ $member['name'] }}</div>
                        <div class="ns-meta text-[12.5px] mt-[2px]">{{ $member['role'] }}</div>
                    </div>
                @endforeach
            </div>
        @endforeach

        <h2 class="ns-h2 !text-[clamp(19px,2.2vw,24px)] mb-5">{{ __('scholarship.committee.how_title') }}</h2>
        <ul class="list-none m-0 p-0 border-t border-[rgba(5,7,8,0.14)]">
            @foreach (__('scholarship.committee.how') as $rule)
                <li class="py-[13px] border-b border-[rgba(5,7,8,0.1)] font-[family-name:var(--ns-body)] text-[15px] leading-[1.55]">{{ $rule }}</li>
            @endforeach
        </ul>
    </div>
</x-layouts.scholarship>
