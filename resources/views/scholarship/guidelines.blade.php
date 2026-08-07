<x-layouts.scholarship :title="$title" :scholarshipNav="$scholarshipNav" :attendee="$attendee" :application="$application">
    <div class="ns-wrap max-w-[860px] pt-[clamp(28px,4vw,56px)] pb-[clamp(72px,10vw,140px)]">

        <div class="flex items-center gap-3 mb-3">
            <span class="w-[26px] h-2 bg-magenta"></span>
            <span class="ns-eyebrow !text-magenta">{{ __('scholarship.guidelines.kicker') }}</span>
        </div>

        <h1 class="ns-h1 !text-[clamp(30px,4.2vw,46px)] mb-3">{{ __('scholarship.guidelines.heading') }}</h1>
        <p class="ns-body max-w-[58ch] mb-10">{{ __('scholarship.guidelines.lead') }}</p>

        <h2 class="ns-h2 !text-[clamp(21px,2.4vw,28px)] mb-2">{{ __('scholarship.guidelines.who_title') }}</h2>
        <ul class="list-none m-0 p-0 mb-10 border-t border-[rgba(5,7,8,0.14)]">
            @foreach (__('scholarship.guidelines.who') as $rule)
                <li class="flex gap-4 py-[13px] border-b border-[rgba(5,7,8,0.1)]">
                    <span class="text-teal font-bold shrink-0">✓</span>
                    <span class="font-[family-name:var(--ns-body)] text-[15px] leading-[1.55]">{{ $rule }}</span>
                </li>
            @endforeach
        </ul>

        {{-- Published on purpose: an applicant should know what is being judged and
             what it is worth before writing a word. --}}
        <h2 class="ns-h2 !text-[clamp(21px,2.4vw,28px)] mb-2">{{ __('scholarship.guidelines.scoring_title') }}</h2>
        <p class="ns-body !text-[15px] text-body-soft max-w-[58ch] mb-6">{{ __('scholarship.guidelines.scoring_lead') }}</p>

        <div class="grid gap-5 sm:grid-cols-3 mb-10">
            @foreach ($rubric as $key => $weight)
                <div class="bg-white border-t-4 border-magenta px-5 py-5">
                    <div class="ns-num font-[family-name:var(--ns-display)] text-[30px] font-bold leading-none mb-2">{{ $weight }}%</div>
                    <div class="font-[family-name:var(--ns-body)] text-[15px] font-bold mb-1">{{ __("scholarship.rubric.$key") }}</div>
                    <p class="ns-body !text-[13.5px] text-body-soft">{{ __("scholarship.rubric_notes.$key") }}</p>
                </div>
            @endforeach
        </div>

        <h2 class="ns-h2 !text-[clamp(21px,2.4vw,28px)] mb-2">{{ __('scholarship.guidelines.documents_title') }}</h2>
        <p class="ns-body !text-[15px] text-body-soft max-w-[58ch] mb-6">{{ __('scholarship.guidelines.documents_lead') }}</p>

        <div class="border border-[rgba(5,7,8,0.14)] mb-10">
            @foreach ($documents as $document)
                <div class="flex justify-between gap-6 px-5 py-[13px] border-b border-[rgba(5,7,8,0.1)] last:border-b-0 flex-wrap">
                    <span class="font-[family-name:var(--ns-body)] text-[14.5px] font-medium">{{ __("scholarship.documents.$document.name") }}</span>
                    <span class="ns-meta text-[12.5px]">{{ __("scholarship.documents.$document.note") }}</span>
                </div>
            @endforeach
        </div>

        <h2 class="ns-h2 !text-[clamp(21px,2.4vw,28px)] mb-6">{{ __('scholarship.guidelines.timeline_title') }}</h2>
        <div class="border-s-2 border-[rgba(5,7,8,0.16)] ps-6 flex flex-col gap-6 mb-10">
            @foreach ($timeline as $key => $date)
                <div class="relative">
                    <span class="absolute -start-[31px] top-[6px] w-[10px] h-[10px] bg-magenta"></span>
                    <div class="ns-num ns-eyebrow !text-[10px] mb-1">{{ ns_format_date(\Illuminate\Support\Carbon::parse($date)) }}</div>
                    <div class="font-[family-name:var(--ns-body)] text-[15px] font-bold">{{ __("scholarship.timeline.$key.title") }}</div>
                    <p class="ns-body !text-[13.5px] text-body-soft max-w-[54ch]">{{ __("scholarship.timeline.$key.note") }}</p>
                </div>
            @endforeach
        </div>

        <div class="ns-card border-s-[6px] !border-s-magenta">
            <p class="ns-body !text-[15px] mb-5 max-w-[58ch]">{{ __('scholarship.guidelines.committee_link') }}</p>
            <a href="{{ route('scholarship.committee') }}" class="ns-btn ns-btn-ghost ns-btn-sm">{{ __('scholarship.nav.committee') }}</a>
        </div>
    </div>
</x-layouts.scholarship>
