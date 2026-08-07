<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :title="__('site.pages.reports.title')" :lead="__('site.pages.reports.lead')">
            <div class="ns-hairgrid sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($reports as $report)
                    <a href="{{ $report->url() ?: '#' }}" class="text-ink hover:text-ink px-[26px] pt-7 pb-[30px] flex flex-col gap-3 border-t-[5px]"
                       style="border-color:{{ $report->accent ?: '#B64698' }}">
                        <div class="ns-eyebrow !text-[10.5px]">{{ $report->t('kind') }} @if($report->year)· <span class="ns-num">{{ $report->year }}</span>@endif</div>
                        <div class="font-[family-name:var(--ns-display)] text-[22px] font-semibold leading-[1.15]">{{ $report->t('name') }}</div>
                        <div class="font-[family-name:var(--ns-body)] text-sm leading-[1.6] text-slate">{{ $report->t('description') }}</div>
                        <div class="mt-auto font-[family-name:var(--ns-body)] text-[13px] font-bold text-magenta">
                            {{ __('site.cta.download') }} · <span class="ns-num">{{ $report->size_label }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </x-ns.page-head>
    </div>
</x-layouts.site>
