<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="pb-[clamp(72px,10vw,140px)]"
         x-data="nsLightbox({{ $album->items->map(fn ($i) => ['alt' => $i->t('alt'), 'caption' => $i->t('caption'), 'src' => $i->path ? asset('storage/'.$i->path) : null])->toJson() }})">
        <x-ns.page-head :title="$album->t('title')" :lead="$album->t('description')"
                        :breadcrumb="[
                            ['label' => __('site.pages.media.title'), 'url' => route('media')],
                            ['label' => __('site.pages.media.photos'), 'url' => route('media.photos')],
                            ['label' => $album->t('title')],
                        ]">

            {{-- Masonry-ish grid; every image carries alt text. --}}
            <div class="columns-2 lg:columns-3 gap-4 [&>*]:mb-4">
                @foreach ($album->items as $index => $item)
                    <button type="button" @click="show({{ $index }})" class="block w-full border-0 p-0 cursor-pointer bg-transparent text-start">
                        <x-ns.frame :src="$item->path ? asset('storage/'.$item->path) : null" :alt="$item->t('alt')"
                                    :label="$album->category" :ratio="$index % 3 === 0 ? '3/4' : '4/3'" />
                        <span class="sr-only">{{ $item->t('alt') }}</span>
                    </button>
                @endforeach
            </div>
        </x-ns.page-head>

        {{-- Lightbox with keyboard navigation. --}}
        <div x-show="open" x-cloak @keydown.escape.window="close()" @keydown.arrow-right.window="next()"
             @keydown.arrow-left.window="prev()" class="fixed inset-0 z-100 bg-ink/95 flex flex-col p-6"
             role="dialog" aria-modal="true">
            <div class="flex justify-between items-center text-white mb-4">
                <span class="ns-eyebrow !text-white/60" x-text="(index + 1) + ' / ' + items.length"></span>
                <button type="button" @click="close()" class="ns-btn ns-btn-ghost-light ns-btn-sm">{{ __('site.nav.close') }}</button>
            </div>
            <div class="flex-1 flex items-center justify-center min-h-0">
                <template x-if="current.src">
                    <img :src="current.src" :alt="current.alt" class="max-h-full max-w-full object-contain">
                </template>
                <template x-if="!current.src">
                    <div class="ns-frame ns-frame-center w-full max-w-3xl aspect-[4/3]"><span x-text="current.alt"></span></div>
                </template>
            </div>
            <div class="flex justify-between items-center gap-4 text-white mt-4">
                <button type="button" @click="prev()" class="ns-btn ns-btn-ghost-light ns-btn-sm">←</button>
                <span class="font-[family-name:var(--ns-body)] text-sm text-white/80 text-center" x-text="current.caption"></span>
                <button type="button" @click="next()" class="ns-btn ns-btn-ghost-light ns-btn-sm">→</button>
            </div>
        </div>
    </div>
</x-layouts.site>
