<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :title="__('site.pages.media.videos')" :lead="__('site.pages.media.lead')">
            <x-ns.filter-chips param="year" :active="$activeYear" ink
                               :options="collect(['all' => __('site.common.all_years')])->merge($years->mapWithKeys(fn ($y) => [$y => $y]))->all()" />

            <div class="grid gap-6 lg:grid-cols-2 mt-9 mb-14">
                @foreach ($videos as $video)
                    <div>
                        <div class="aspect-video bg-ink">
                            @if ($video->embedUrl())
                                <iframe src="{{ $video->embedUrl() }}" title="{{ $video->t('alt') }}" loading="lazy"
                                        class="w-full h-full" allowfullscreen
                                        allow="accelerometer; clipboard-write; encrypted-media; picture-in-picture"></iframe>
                            @endif
                        </div>
                        <div class="font-[family-name:var(--ns-body)] text-base font-bold mt-3">{{ $video->t('alt') }}</div>
                        <div class="ns-meta ns-num">{{ $video->year }}</div>
                    </div>
                @endforeach
            </div>

            @if ($reels->isNotEmpty())
                <h2 class="ns-h2 !text-[clamp(24px,2.6vw,32px)] mb-6">Reels</h2>
                <div class="grid gap-5 grid-cols-2 lg:grid-cols-4">
                    @foreach ($reels as $reel)
                        <div>
                            <div class="aspect-[9/16] bg-ink">
                                @if ($reel->embedUrl())
                                    <iframe src="{{ $reel->embedUrl() }}" title="{{ $reel->t('alt') }}" loading="lazy"
                                            class="w-full h-full" allowfullscreen></iframe>
                                @endif
                            </div>
                            <div class="font-[family-name:var(--ns-body)] text-sm font-semibold mt-2">{{ $reel->t('alt') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ns.page-head>
    </div>
</x-layouts.site>
