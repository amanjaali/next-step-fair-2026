<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :title="__('site.pages.archive.title')" :lead="__('site.pages.archive.lead')">
            <div class="grid gap-7 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($editions as $edition)
                    <a href="{{ route('archive.show', $edition->year) }}"
                       class="text-ink hover:text-ink border border-[rgba(5,7,8,0.14)] block group">
                        <x-ns.frame :label="$edition->year.' cover'" ratio="16/9" center />
                        <div class="p-[26px]">
                            <div class="ns-stat ns-num text-[34px] mb-3 group-hover:text-magenta">{{ $edition->year }}</div>
                            <div class="font-[family-name:var(--ns-body)] text-[14.5px] font-bold mb-2">{{ $edition->t('edition_label') }} · {{ $edition->t('dates_label') }}</div>
                            <div class="ns-meta text-[13.5px] leading-[1.5]">{{ $edition->t('headline') }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </x-ns.page-head>
    </div>
</x-layouts.site>
