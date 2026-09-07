@props(['centres'])

{{--
    Where the desks are, drawn rather than embedded.

    A third-party map would be a tracking script, a key to keep, a grey panel
    while it loads and a stranger's typography in the middle of the page. This
    is drawn from the same coordinates: every marker is projected into a square
    of ground fitted around the centres themselves, so one added in the
    dashboard with a latitude and longitude lands in the right place with nobody
    positioning anything by hand.

    It is a schematic and says so underneath — no border is claimed and no
    settlement but ours is drawn. The address on each card is what actually gets
    somebody to the door.
--}}
@php
    $hub = collect($centres)->firstWhere('slug', 'sulaymaniyah') ?? ($centres[0] ?? null);
    $placeable = collect($centres)->filter(fn ($c) => isset($c['x'], $c['y']))->values();

    /*
     * Which side of its dot each label sits on.
     *
     * Right by default, left near the right edge — and left as well when the
     * next town along is close enough that a right-hand label would be written
     * across it. Two names on top of each other is the one thing that makes a
     * map of seven places unreadable, and which pair collides depends on the
     * list, which is edited in the dashboard.
     */
    $flipped = [];

    foreach ($placeable as $centre) {
        $crowdedRight = $placeable->contains(
            fn ($other) => $other !== $centre
                && $other['x'] > $centre['x']
                && $other['x'] - $centre['x'] < 22
                && abs($other['y'] - $centre['y']) < 6
        );

        $flipped[$centre['slug'] ?? $centre['label']] = $centre['x'] > 72 || $crowdedRight;
    }
@endphp

<figure class="m-0">
    <div class="relative w-full aspect-square bg-ink overflow-hidden">

        {{-- A soft light under the base, so the panel has a centre of gravity
             and does not read as a black rectangle with dots on it. --}}
        <span aria-hidden="true" class="absolute inset-0"
              style="background:radial-gradient(circle at 55% 52%,rgba(14,155,148,0.22) 0%,transparent 58%)"></span>

        <svg class="absolute inset-0 w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
            {{-- Ground: faint contour rings, drawn from the centre outwards.
                 They are decoration and read as one — the map's information is
                 the seven positions, and nothing else here pretends otherwise. --}}
            @foreach ([16, 27, 38, 49] as $i => $r)
                <circle cx="50" cy="50" r="{{ $r }}"
                        fill="none" stroke="rgba(255,255,255,{{ 0.09 - $i * 0.015 }})"
                        stroke-width="0.3" vector-effect="non-scaling-stroke"/>
            @endforeach

            {{-- A hairline graticule, for the same reason a plan has a grid. --}}
            @foreach ([25, 50, 75] as $line)
                <line x1="{{ $line }}" y1="0" x2="{{ $line }}" y2="100"
                      stroke="rgba(255,255,255,0.06)" stroke-width="0.25" vector-effect="non-scaling-stroke"/>
                <line x1="0" y1="{{ $line }}" x2="100" y2="{{ $line }}"
                      stroke="rgba(255,255,255,0.06)" stroke-width="0.25" vector-effect="non-scaling-stroke"/>
            @endforeach

            {{-- Seven desks, one programme: every centre tied back to the base. --}}
            @if ($hub && isset($hub['x'], $hub['y']))
                @foreach ($placeable as $centre)
                    @continue(($centre['slug'] ?? null) === ($hub['slug'] ?? null))
                    <line x1="{{ $hub['x'] }}" y1="{{ $hub['y'] }}" x2="{{ $centre['x'] }}" y2="{{ $centre['y'] }}"
                          stroke="rgba(255,255,255,0.22)" stroke-width="0.35" stroke-dasharray="1.6 1.6"
                          vector-effect="non-scaling-stroke"/>
                @endforeach
            @endif
        </svg>

        @foreach ($placeable as $i => $centre)
            @php
                $key = $centre['slug'] ?? $i;
                $isHub = ($centre['slug'] ?? null) === ($hub['slug'] ?? null);
                $flip = $flipped[$centre['slug'] ?? $centre['label']] ?? false;
            @endphp

            <button type="button"
                    class="absolute -translate-x-1/2 -translate-y-1/2 flex items-center gap-[8px] bg-transparent border-0 p-0 cursor-pointer {{ $flip ? 'flex-row-reverse' : '' }}"
                    style="left:{{ $centre['x'] }}%;top:{{ $centre['y'] }}%"
                    @mouseenter="active = '{{ $key }}'" @mouseleave="active = null"
                    @focus="active = '{{ $key }}'" @blur="active = null"
                    @click="active = '{{ $key }}'">

                <span class="relative flex items-center justify-center shrink-0 rounded-full transition-all duration-150
                             {{ $isHub ? 'w-[17px] h-[17px]' : 'w-[13px] h-[13px]' }}"
                      :class="active === '{{ $key }}'
                          ? 'bg-magenta ring-4 ring-magenta/25 scale-[1.25]'
                          : '{{ $isHub ? 'bg-teal ring-4 ring-teal/20' : 'bg-white' }}'"></span>

                <span class="font-[family-name:var(--ns-body)] text-[11.5px] font-bold whitespace-nowrap leading-[1.3] transition-colors duration-150"
                      :class="active === '{{ $key }}' ? 'text-magenta' : 'text-white/85'">
                    {{ $centre['label'] }}
                </span>
            </button>
        @endforeach

        {{-- North, so the schematic reads as a map rather than as a diagram. --}}
        <div class="absolute top-4 end-4 flex flex-col items-center gap-[3px] text-white/35">
            <svg width="11" height="13" viewBox="0 0 11 13" fill="none" aria-hidden="true">
                <path d="M5.5 0L11 13 5.5 9.6 0 13 5.5 0z" fill="currentColor"/>
            </svg>
            <span class="ns-eyebrow !text-[8.5px] !text-white/35">N</span>
        </div>
    </div>

    <figcaption class="ns-meta text-[12px] mt-3">{{ ns_zankoline('where.note') }}</figcaption>
</figure>
