<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :title="__('site.pages.media.photos')" :lead="__('site.pages.media.lead')">
            <div class="flex gap-2 flex-wrap mb-9">
                <x-ns.filter-chips param="year" :active="$activeYear" ink
                                   :options="collect(['all' => __('site.common.all_years')])->merge($years->mapWithKeys(fn ($y) => [$y => $y]))->all()" />
                <x-ns.filter-chips param="category" :active="$activeCategory"
                                   :options="collect(['all' => __('site.common.all')])->merge($categories->filter()->mapWithKeys(fn ($c) => [$c => $c]))->all()" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($albums as $album)
                    <a href="{{ route('media.album', $album) }}" class="text-ink hover:text-ink block group">
                        <x-ns.frame :label="$album->category" ratio="3/2" class="mb-4" />
                        <div class="font-[family-name:var(--ns-display)] text-[19px] font-semibold leading-tight mb-1 group-hover:text-magenta">{{ $album->t('title') }}</div>
                        <div class="ns-meta ns-num">{{ $album->items_count }} · {{ __('site.common.day', ['n' => $album->day]) }} · {{ $album->year }}</div>
                    </a>
                @endforeach
            </div>
        </x-ns.page-head>
    </div>
</x-layouts.site>
