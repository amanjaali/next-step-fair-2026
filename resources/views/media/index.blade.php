<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :title="__('site.pages.media.title')" :lead="__('site.pages.media.lead')">

            <div class="flex gap-3 flex-wrap mb-10">
                <a href="{{ route('media.photos') }}" class="ns-btn ns-btn-ink">{{ __('site.pages.media.photos') }}</a>
                <a href="{{ route('media.videos') }}" class="ns-btn ns-btn-ghost">{{ __('site.pages.media.videos') }}</a>
                <a href="{{ route('press') }}" class="ns-btn ns-btn-ghost">{{ __('site.pages.media.press_kit') }}</a>
            </div>

            <h2 class="ns-h2 !text-[clamp(24px,2.6vw,32px)] mb-6">{{ __('site.pages.media.albums') }}</h2>
            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3 mb-14">
                @foreach ($albums as $album)
                    <a href="{{ route('media.album', $album) }}" class="text-ink hover:text-ink block group">
                        <x-ns.frame :label="$album->category" ratio="3/2" class="mb-4" />
                        <div class="font-[family-name:var(--ns-display)] text-[19px] font-semibold leading-tight mb-1 group-hover:text-magenta">{{ $album->t('title') }}</div>
                        <div class="ns-meta ns-num">{{ $album->items_count }} · {{ $album->year }}</div>
                    </a>
                @endforeach
            </div>

            <h2 class="ns-h2 !text-[clamp(24px,2.6vw,32px)] mb-6">{{ __('site.pages.media.videos') }}</h2>
            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($videos as $video)
                    <a href="{{ route('media.videos') }}" class="bg-ink text-white hover:text-white flex flex-col justify-between p-5 aspect-video">
                        <span class="ns-eyebrow !text-white/50">{{ $video->type === 'reel' ? 'Reel' : __('site.pages.media.videos') }} · <span class="ns-num">{{ $video->year }}</span></span>
                        <div>
                            <div class="w-[34px] h-[34px] border-2 border-white flex items-center justify-center mb-3"><span class="text-xs">▶</span></div>
                            <span class="font-[family-name:var(--ns-display)] text-lg font-semibold">{{ $video->t('alt') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </x-ns.page-head>
    </div>
</x-layouts.site>
