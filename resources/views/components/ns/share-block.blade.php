@props([
    'registration',
    'heading' => true,
])

@php
    /**
     * "I'm attending Next Step Fair 2026" — the picture, the words, the buttons.
     *
     * Used on both confirmation pages and on the share page under /me, so a
     * conference delegate who has no account still gets the whole thing on the
     * screen where they are most likely to use it: the one that just told them
     * they are in.
     */
    $kit = \App\Services\ShareKit::for($registration);
    $caption = $kit->caption();
@endphp

<div x-data="{ format: 'feed', copied: false }">
    @if ($heading)
        <div class="ns-eyebrow !text-magenta mb-2">{{ __('share.kicker') }}</div>
        <h2 class="ns-h2 !text-[clamp(22px,2.6vw,30px)] mb-2">{{ __('share.title') }}</h2>
        <p class="ns-body !text-[14.5px] max-w-[58ch] mb-7">{{ __('share.lead') }}</p>
    @endif

    <div class="grid gap-8 md:grid-cols-[minmax(240px,320px)_minmax(0,1fr)] items-start">

        {{-- ----------------------------------------------------- the picture -- --}}
        <div>
            {{-- Two shapes. A story cropped out of a square looks like a mistake,
                 and a mistake does not get posted. --}}
            <div class="flex gap-px bg-[rgba(5,7,8,0.14)] border border-[rgba(5,7,8,0.14)] mb-4">
                @foreach (\App\Services\ShareKit::formats() as $option)
                    <button type="button" @click="format = '{{ $option }}'"
                            class="flex-1 px-4 py-3 text-start border-0 cursor-pointer transition-colors"
                            :class="format === '{{ $option }}' ? 'bg-ink text-white' : 'bg-white text-ink'">
                        <span class="block font-[family-name:var(--ns-body)] text-[13.5px] font-bold">
                            {{ __("share.formats.{$option}") }}
                        </span>
                        <span class="block font-[family-name:var(--ns-body)] text-[11px] mt-[2px]"
                              :class="format === '{{ $option }}' ? 'text-white/60' : 'text-slate'">
                            {{ __("share.formats.{$option}_note") }}
                        </span>
                    </button>
                @endforeach
            </div>

            @foreach (\App\Services\ShareKit::formats() as $option)
                @php $slot = \App\Services\ShareKit::nameSlot($option); @endphp

                {{-- The artwork is the same for everybody; the name is what makes
                     it theirs, and it goes on in the browser. Until that runs —
                     and if it never does — this is the plain card and a link
                     straight to it, which is still worth posting. --}}
                <div x-show="format === '{{ $option }}'" x-cloak
                     data-share-figure
                     data-card="{{ $kit->image($option) }}"
                     data-name="{{ $kit->displayName() }}"
                     data-filename="next-step-2026-{{ $option }}.png"
                     data-width="{{ $slot['width'] }}" data-height="{{ $slot['height'] }}"
                     data-x="{{ $slot['x'] }}" data-y="{{ $slot['y'] }}"
                     data-size="{{ $slot['size'] }}" data-max="{{ $slot['max'] }}">

                    <img src="{{ $kit->image($option) }}" alt="{{ __('share.title') }}"
                         class="w-full h-auto block border border-[rgba(5,7,8,0.14)] mb-3">

                    <canvas class="w-full h-auto block border border-[rgba(5,7,8,0.14)] mb-3 hidden"></canvas>

                    <a href="{{ $kit->image($option) }}" download data-share-download
                       class="ns-btn ns-btn-magenta w-full">{{ __('share.download') }}</a>
                </div>
            @endforeach
        </div>

        {{-- ------------------------------------------------------- the words -- --}}
        <div>
            <div class="ns-eyebrow !text-[10.5px] mb-2">{{ __('share.caption_label') }}</div>

            <textarea x-ref="caption" rows="10"
                      class="ns-input !font-[family-name:var(--ns-body)] !text-[14px] !leading-[1.6] w-full resize-y mb-3"
            >{{ $caption }}</textarea>

            <button type="button"
                    @click="navigator.clipboard.writeText($refs.caption.value); copied = true; setTimeout(() => copied = false, 2000)"
                    class="ns-btn ns-btn-ghost ns-btn-sm mb-7">
                <span x-show="! copied">{{ __('share.copy') }}</span>
                <span x-show="copied" x-cloak>{{ __('share.copied') }}</span>
            </button>

            {{-- Wrapping, not a fixed three columns. In a narrow container three
                 equal cells are about seventy pixels each and the labels sit on
                 top of one another. --}}
            <div class="flex flex-wrap gap-3 mb-7">
                @foreach ($kit->targets() as $target)
                    <a href="{{ $target['url'] }}" target="_blank" rel="noopener"
                       class="ns-btn ns-btn-ghost ns-btn-sm flex-1 basis-[190px] whitespace-nowrap">{{ $target['label'] }}</a>
                @endforeach
            </div>

            {{-- Instagram has no share intent from the web. Saying so plainly is
                 more use than a button that opens the wrong screen. --}}
            <div class="border-s-[6px] border-magenta bg-bone-50 px-5 py-4 mb-4">
                <div class="font-[family-name:var(--ns-body)] text-[14px] font-bold mb-1">
                    {{ __('share.instagram_title') }}
                </div>
                <p class="ns-body !text-[13.5px] text-body-soft">{{ __('share.instagram_note') }}</p>
            </div>

            <p class="ns-meta text-[12px] leading-[1.6] max-w-[56ch]">{{ __('share.no_qr_note') }}</p>
        </div>
    </div>
</div>
