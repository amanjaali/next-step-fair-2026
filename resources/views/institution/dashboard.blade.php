<x-layouts.site :title="$title" :navKey="$navKey" track="conference">
    <div class="ns-wrap pt-[clamp(24px,3vw,40px)] pb-[clamp(72px,10vw,140px)]">
        @include('institution.partials.nav', ['current' => 'dashboard'])

        <h1 class="ns-h1 !text-[clamp(28px,3.6vw,40px)] mb-2">{{ $organization->t('name') }}</h1>
        <p class="ns-meta mb-9">{{ __('institution.dashboard.welcome', ['name' => auth('institution')->user()->firstName()]) }}</p>

        @if (session('status'))
            <div class="bg-bone-200 p-5 mb-8 font-[family-name:var(--ns-body)] text-[15px]">{{ session('status') }}</div>
        @endif

        @if ($organization->claim_status === 'pending')
            <div class="border-s-[6px] border-[#F2A93B] bg-[#FFF8EC] px-6 py-5 mb-8">
                <div class="font-[family-name:var(--ns-display)] text-[18px] font-semibold mb-1">
                    {{ __('institution.dashboard.pending') }}
                </div>
                <p class="ns-body !text-[14.5px] max-w-[64ch]">{{ __('institution.dashboard.pending_note') }}</p>
            </div>
        @endif

        {{-- The nudge that matters: an institution with no programmes listed cannot
             be recommended to anybody, so it is the first thing they are told. --}}
        @if ($completeness < 100)
            <div class="border-s-[6px] border-cobalt bg-white px-6 py-5 mb-9">
                <div class="font-[family-name:var(--ns-display)] text-[18px] font-semibold mb-1">
                    {{ __('institution.dashboard.complete_profile', ['percent' => $completeness]) }}
                </div>
                <p class="ns-body !text-[14.5px] mb-4 max-w-[64ch]">{{ __('institution.dashboard.complete_note') }}</p>
                <a href="{{ route('portal.profile') }}" class="ns-btn ns-btn-cobalt ns-btn-sm">
                    {{ __('institution.dashboard.complete_cta') }}
                </a>
            </div>
        @endif

        <div class="grid gap-px bg-[rgba(5,7,8,0.14)] sm:grid-cols-2 lg:grid-cols-4 mb-11">
            @foreach ([
                'matches' => $stats['matches'],
                'strong' => $stats['strong'],
                'scans' => $stats['scans'],
                'follow_up' => $stats['follow_up'],
            ] as $key => $value)
                <div class="bg-bone px-6 py-[26px]">
                    <div class="ns-num font-[family-name:var(--ns-display)] text-[34px] font-bold leading-none mb-2">{{ $value }}</div>
                    <div class="ns-eyebrow !text-[10px]">{{ __("institution.dashboard.$key") }}</div>
                </div>
            @endforeach
        </div>

        <div class="flex flex-wrap gap-12 items-start">
            <section class="flex-[1_1_420px] min-w-0">
                <h2 class="ns-h2 !text-[clamp(20px,2.2vw,26px)] mb-1">{{ __('institution.dashboard.demand') }}</h2>
                <p class="ns-meta mb-5">{{ __('institution.dashboard.demand_note') }}</p>

                @forelse ($topFields as $row)
                    @php($max = $topFields->max('students') ?: 1)
                    <div class="mb-3">
                        <div class="flex justify-between gap-4 mb-1">
                            <span class="font-[family-name:var(--ns-body)] text-[14.5px]">{{ $row['field'] }}</span>
                            <span class="ns-num ns-meta text-[13px]">{{ $row['students'] }}</span>
                        </div>
                        <div class="h-2 bg-[rgba(5,7,8,0.08)]">
                            <div class="h-2 bg-cobalt" style="width: {{ round($row['students'] / $max * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="ns-body !text-[14.5px] text-body-soft">{{ __('institution.dashboard.nothing_yet') }}</p>
                @endforelse
            </section>

            <section class="flex-[1_1_320px] min-w-0">
                <h2 class="ns-h2 !text-[clamp(20px,2.2vw,26px)] mb-5">{{ __('institution.dashboard.recent') }}</h2>
                @forelse ($recentScans as $scan)
                    <div class="border-t border-[rgba(5,7,8,0.14)] py-3 flex justify-between gap-4">
                        <span class="font-[family-name:var(--ns-body)] text-[14.5px] font-semibold">
                            {{ $scan->registration?->full_name }}
                        </span>
                        <span class="ns-meta text-[12.5px] ns-num">{{ $scan->occurred_at?->format('j M · H:i') }}</span>
                    </div>
                @empty
                    <p class="ns-body !text-[14.5px] text-body-soft">{{ __('institution.dashboard.nothing_yet') }}</p>
                @endforelse
            </section>
        </div>
    </div>
</x-layouts.site>
