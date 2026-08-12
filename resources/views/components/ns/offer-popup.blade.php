@props(['popup' => null])

@if ($popup)
    @php
        /**
         * The offer popup, shown once on the home page.
         *
         * Once per version, not once ever: the dismiss key carries the popup's
         * updated_at, so editing it in the dashboard brings it back for everybody
         * and leaving it alone keeps it closed. That is what makes a popup the
         * team rewrites most days worth having.
         *
         * It decides whether to open in the browser, from localStorage, so it is
         * unaffected by page caching — and it never renders on the server as
         * "open", so nobody sees it flash before the script has decided.
         */
        $key = $popup->dismissKey();
    @endphp

    <div x-data="nsOfferPopup('{{ $key }}')" x-cloak @keydown.escape.window="close()">
        {{-- Hidden three ways over: the inline style holds before the stylesheet
             has parsed, [x-cloak] holds until Alpine boots, and x-show takes
             over after that. The inline one is what Alpine itself would set, and
             it removes it on the way open. --}}
        <div x-show="open"
             x-cloak
             style="display: none"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-[100] bg-[rgba(5,7,8,0.72)] flex items-start sm:items-center justify-center p-4 overflow-y-auto"
             @click.self="close()"
             role="dialog" aria-modal="true" aria-labelledby="ns-popup-title">

            <div class="bg-bone w-full max-w-[600px] my-auto border-t-[6px] border-magenta relative rounded-[var(--ns-radius-lg)] overflow-hidden"
                 x-transition:enter="transition ease-out duration-200 delay-75"
                 x-transition:enter-start="opacity-0 translate-y-3"
                 x-transition:enter-end="opacity-100 translate-y-0">

                <button type="button" @click="close()" aria-label="{{ __('popup.close') }}"
                        class="absolute top-3 end-3 w-9 h-9 flex items-center justify-center bg-transparent border-0 cursor-pointer text-slate hover:text-ink text-[22px] leading-none">
                    &times;
                </button>

                <div class="px-[clamp(22px,4vw,38px)] pt-[clamp(26px,4vw,38px)] pb-4">
                    <div class="ns-eyebrow !text-magenta mb-3">{{ __('popup.kicker') }}</div>

                    <h2 id="ns-popup-title" class="ns-h2 !text-[clamp(22px,3.4vw,30px)] leading-[1.15] mb-3">
                        {{ $popup->t('title') }}
                    </h2>

                    @if ($popup->t('intro'))
                        <p class="ns-body !text-[14.5px] text-body-soft max-w-[52ch]">{{ $popup->t('intro') }}</p>
                    @endif
                </div>

                {{-- Several offers, one interruption. Scrolls inside the box rather
                     than growing past the bottom of the screen. --}}
                <div class="px-[clamp(22px,4vw,38px)] max-h-[52vh] overflow-y-auto">
                    <div class="grid gap-px bg-[rgba(5,7,8,0.1)] border border-[rgba(5,7,8,0.1)] ns-radius overflow-hidden">
                        @foreach ($popup->items as $item)
                            <div class="bg-white px-5 py-[18px]">
                                <div class="flex items-start justify-between gap-3 mb-1">
                                    <div class="font-[family-name:var(--ns-display)] text-[17px] font-semibold leading-[1.25] text-ink">
                                        {{ $item->t('title') }}
                                    </div>
                                    @if ($item->badge)
                                        <span class="ns-eyebrow !text-[9px] !text-magenta whitespace-nowrap shrink-0 mt-1">{{ $item->badge }}</span>
                                    @endif
                                </div>

                                @if ($item->t('body'))
                                    <p class="ns-body !text-[14px] text-body-soft mb-3">{{ $item->t('body') }}</p>
                                @endif

                                @if ($item->action_url)
                                    <a href="{{ route('popup.go', $item) }}"
                                       class="font-[family-name:var(--ns-body)] text-[13.5px] font-bold text-magenta">
                                        {{ $item->actionLabel() }} →
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="px-[clamp(22px,4vw,38px)] py-[clamp(20px,3vw,28px)] flex gap-3 flex-wrap items-center">
                    <a href="{{ route('opportunities') }}" class="ns-btn ns-btn-magenta">{{ __('popup.see_all') }}</a>
                    <button type="button" @click="close()" class="ns-btn ns-btn-ghost">{{ $popup->dismissLabel() }}</button>
                </div>
            </div>
        </div>
    </div>
@endif
