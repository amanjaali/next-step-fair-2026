<x-layouts.site :title="$title" :navKey="$navKey">

    {{-- One Alpine scope over the map and the cards, so hovering either end
         lights the other. It is the whole point of drawing the map ourselves:
         a marker that means nothing until you find its address again is a
         picture, not a map. --}}
    <div x-data="{ active: null }">

        <div class="pb-[clamp(72px,10vw,140px)]">
            <x-ns.page-head :kicker="ns_zankoline('kicker')"
                            :title="ns_zankoline('title')"
                            :lead="ns_zankoline('lead')">

                {{-- ------------------------------------------------ what the desk does -- --}}
                <section class="mb-[clamp(48px,6vw,76px)]">
                    <div class="flex items-baseline gap-5 mb-3 flex-wrap">
                        <span class="ns-eyebrow">{{ ns_zankoline('what.title') }}</span>
                        <span class="ns-rule"></span>
                    </div>
                    <p class="ns-body max-w-[64ch] mb-9">{{ ns_zankoline('what.lead') }}</p>

                    <div class="ns-hairgrid sm:grid-cols-2 xl:grid-cols-4">
                        @foreach (['one', 'two', 'three', 'four'] as $i => $step)
                            <div class="p-[clamp(20px,2.4vw,28px)]">
                                <div class="ns-num text-[28px] font-bold text-magenta leading-none mb-4">
                                    {{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}
                                </div>
                                <h3 class="font-[family-name:var(--ns-display)] text-[18px] font-semibold leading-[1.2] mb-[10px]">
                                    {{ ns_zankoline("steps.$step.title") }}
                                </h3>
                                <p class="ns-body !text-[14.5px] leading-[1.65]">{{ ns_zankoline("steps.$step.body") }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- ------------------------------------------------------ the map -------- --}}
                <section class="mb-[clamp(48px,6vw,76px)]">
                    <div class="flex items-baseline justify-between gap-5 mb-3 flex-wrap">
                        <div class="flex items-baseline gap-5 flex-wrap">
                            <span class="ns-eyebrow">{{ ns_zankoline('where.title') }}</span>
                            <span class="ns-rule"></span>
                        </div>
                        <span class="ns-meta text-[12.5px]">
                            {{ trans_choice('zankoline.where.count', count($centres), ['count' => count($centres)]) }}
                        </span>
                    </div>

                    <p class="ns-body max-w-[64ch] mb-8">{{ ns_zankoline('where.lead') }}</p>

                    {{-- Map and addresses side by side, and cross-lit: a marker
                         you have to match to a list further down the page is a
                         picture, not a map. --}}
                    <div class="grid gap-8 lg:gap-10 lg:grid-cols-[minmax(300px,0.95fr)_1.05fr] items-start">
                        <div class="lg:sticky lg:top-[92px]">
                            <x-ns.centre-map :centres="$centres" />
                        </div>

                    <div class="ns-hairgrid sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                        @foreach ($centres as $i => $centre)
                            @php $key = $centre['slug'] ?? $i; @endphp
                            <div class="p-[clamp(18px,2vw,24px)] transition-colors duration-150"
                                 :class="active === '{{ $key }}' && 'bg-bone-200'"
                                 @mouseenter="active = '{{ $key }}'" @mouseleave="active = null">

                                <div class="flex items-center gap-[10px] mb-4">
                                    <span class="w-[11px] h-[11px] rounded-full shrink-0"
                                          :class="active === '{{ $key }}' ? 'bg-magenta' : 'bg-cobalt'"></span>
                                    <h3 class="font-[family-name:var(--ns-display)] text-[20px] font-semibold leading-[1.15]">
                                        {{ $centre['label'] }}
                                    </h3>
                                </div>

                                @if ($centre['address'] === '' && $centre['phone'] === '' && $centre['person'] === '')
                                    <p class="ns-meta text-[13px]">{{ ns_zankoline('where.soon') }}</p>
                                @else
                                    <dl class="m-0 border-t border-[rgba(5,7,8,0.12)]">
                                        @if ($centre['address'] !== '')
                                            <div class="py-[10px] border-b border-[rgba(5,7,8,0.08)]">
                                                <dt class="ns-eyebrow !text-[9px] mb-[3px]">{{ ns_zankoline('where.address') }}</dt>
                                                <dd class="m-0 font-[family-name:var(--ns-body)] text-[14px] leading-[1.5]">{{ $centre['address'] }}</dd>
                                            </div>
                                        @endif

                                        @if ($centre['person'] !== '')
                                            <div class="py-[10px] border-b border-[rgba(5,7,8,0.08)]">
                                                <dt class="ns-eyebrow !text-[9px] mb-[3px]">{{ ns_zankoline('where.person') }}</dt>
                                                <dd class="m-0 font-[family-name:var(--ns-body)] text-[14px] font-bold">{{ $centre['person'] }}</dd>
                                            </div>
                                        @endif

                                        @if ($centre['phone'] !== '')
                                            <div class="py-[10px]">
                                                <dt class="ns-eyebrow !text-[9px] mb-[3px]">{{ ns_zankoline('where.phone') }}</dt>
                                                <dd class="m-0">
                                                    {{-- A number on a phone is a number to press. --}}
                                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $centre['phone']) }}"
                                                       class="ns-num font-[family-name:var(--ns-body)] text-[14.5px] font-bold text-magenta" dir="ltr">
                                                        {{ $centre['phone'] }}
                                                    </a>
                                                </dd>
                                            </div>
                                        @endif
                                    </dl>
                                @endif

                                @if ($centre['maps_url'] !== '')
                                    <a href="{{ $centre['maps_url'] }}" target="_blank" rel="noopener"
                                       class="ns-btn ns-btn-ghost ns-btn-sm mt-5">{{ ns_zankoline('where.directions') }}</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    </div>
                </section>

                {{-- ---------------------------------------------------- what to bring ---- --}}
                <section class="mb-[clamp(48px,6vw,76px)]">
                    <div class="flex items-baseline gap-5 mb-6 flex-wrap">
                        <span class="ns-eyebrow">{{ ns_zankoline('bring.title') }}</span>
                        <span class="ns-rule"></span>
                    </div>

                    <ul class="m-0 p-0 list-none grid gap-x-10 sm:grid-cols-2 border-t border-[rgba(5,7,8,0.14)]">
                        @foreach (__('zankoline.bring.items') as $item)
                            <li class="flex items-start gap-4 py-[14px] border-b border-[rgba(5,7,8,0.1)]">
                                <span class="w-[7px] h-[7px] bg-magenta shrink-0 mt-[7px]"></span>
                                <span class="ns-body !text-[15px]">{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>

                {{-- ------------------------------------------------------- the ask ------- --}}
                <section class="bg-ink text-white p-[clamp(26px,3.5vw,44px)] flex items-center justify-between gap-8 flex-wrap">
                    <div class="min-w-0 max-w-[58ch]">
                        <h2 class="font-[family-name:var(--ns-display)] text-[clamp(21px,2.5vw,28px)] font-semibold leading-[1.15] mb-3">
                            {{ ns_zankoline('cta.title') }}
                        </h2>
                        <p class="ns-body !text-[15px] !text-white/70">{{ ns_zankoline('cta.body') }}</p>
                    </div>
                    <a href="{{ route('register.fair') }}" class="ns-btn ns-btn-magenta shrink-0">
                        {{ ns_zankoline('cta.button') }}
                    </a>
                </section>
            </x-ns.page-head>
        </div>
    </div>
</x-layouts.site>
