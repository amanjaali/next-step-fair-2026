@props([
    'title' => null,
    'scholarshipNav' => null,
    'attendee' => null,
    'application' => null,
])

@php
    // The programme carries its own sub-navigation. It is a different thing from
    // the expo with its own rules and its own deadlines, and mixing the two menus
    // is what makes a site feel like it has no shape.
    $items = [
        ['key' => 'home', 'label' => __('scholarship.nav.home'), 'route' => 'scholarship.home'],
        ['key' => 'about', 'label' => __('scholarship.nav.about'), 'route' => 'scholarship.about'],
        ['key' => 'universities', 'label' => __('scholarship.nav.universities'), 'route' => 'scholarship.universities'],
        ['key' => 'guidelines', 'label' => __('scholarship.nav.guidelines'), 'route' => 'scholarship.guidelines'],
        ['key' => 'recipients', 'label' => __('scholarship.nav.recipients'), 'route' => 'scholarship.recipients'],
        ['key' => 'apply', 'label' => __('scholarship.nav.apply'), 'route' => 'scholarship.apply'],
    ];

    $hasApplication = $application !== null;
@endphp

<x-layouts.site :title="$title" navKey="scholarship">
    {{-- Programme bar. Dark, so it reads as its own place inside the site rather
         than another row of the main menu. --}}
    <div class="bg-ink text-white">
        <div class="ns-wrap flex items-center justify-between gap-6 flex-wrap py-[10px]">
            <a href="{{ route('scholarship.home') }}" class="flex items-baseline gap-3 no-underline">
                <span class="font-[family-name:var(--ns-display)] text-[15px] font-bold text-white">{{ __('scholarship.name') }}</span>
                <span class="ns-num font-[family-name:var(--ns-body)] text-[12px] text-white/60">{{ config('scholarship.cycle') }}</span>
            </a>

            <nav class="flex items-center gap-[clamp(12px,1.6vw,26px)] flex-wrap" aria-label="{{ __('scholarship.name') }}">
                @foreach ($items as $item)
                    <a href="{{ route($item['route']) }}"
                       @class([
                           'font-[family-name:var(--ns-body)] text-[13.5px] font-medium py-1 whitespace-nowrap border-b-2 no-underline',
                           'text-white border-magenta' => $scholarshipNav === $item['key'],
                           'text-white/65 border-transparent hover:text-white' => $scholarshipNav !== $item['key'],
                       ])
                       @if($scholarshipNav === $item['key']) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach

                @if ($hasApplication)
                    <a href="{{ route('scholarship.status') }}"
                       @class([
                           'font-[family-name:var(--ns-body)] text-[13.5px] font-medium py-1 whitespace-nowrap border-b-2 no-underline',
                           'text-white border-magenta' => $scholarshipNav === 'status',
                           'text-white/65 border-transparent hover:text-white' => $scholarshipNav !== 'status',
                       ])>{{ __('scholarship.nav.status') }}</a>
                @endif
            </nav>
        </div>
    </div>

    {{ $slot }}
</x-layouts.site>
