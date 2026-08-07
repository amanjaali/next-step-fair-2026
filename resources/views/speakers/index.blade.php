<x-layouts.site :title="$title" :navKey="$navKey">

    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :title="__('site.pages.speakers.title')"
                        :lead="__('site.pages.speakers.lead', ['count' => $total ?: $speakers->count()])">

            <div class="flex gap-2 flex-wrap mb-9">
                <x-ns.filter-chips param="track" :active="$activeTrack" :options="[
                    'all' => __('site.common.all'),
                    'conference' => __('site.nav.conference'),
                    'fair' => __('site.nav.expo'),
                ]" />
                <x-ns.filter-chips param="type" :active="$activeType" :options="[
                    'all' => __('site.common.all'),
                    'panelist' => 'Panelist',
                    'moderator' => 'Moderator',
                    'international' => 'International',
                ]" />
            </div>

            @if ($fallbackYear)
                {{-- 2026 is still being announced: show the previous year rather than an empty page. --}}
                <div class="ns-card mb-9 max-w-[70ch]">
                    <p class="ns-body">{{ __('site.pages.speakers.to_be_announced') }}</p>
                </div>
            @endif

            <div class="grid gap-7 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($speakers as $speaker)
                    <x-ns.speaker-card :speaker="$speaker" />
                @endforeach
            </div>
        </x-ns.page-head>
    </div>

</x-layouts.site>
