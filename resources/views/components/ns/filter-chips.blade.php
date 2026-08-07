@props([
    'options' => [],   // [value => label]
    'active' => 'all',
    'param' => 'filter',
    'ink' => false,
])

{{-- Filters are links, not JavaScript: they work without it, they are
     shareable, and they leave a trail in analytics. --}}
<div class="flex gap-2 flex-wrap">
    @foreach ($options as $value => $label)
        <a href="{{ request()->fullUrlWithQuery([$param => $value === 'all' ? null : $value, 'page' => null]) }}"
           @class(['ns-chip', 'ns-chip-ink' => $ink, 'is-on' => (string) $active === (string) $value])
           @if ((string) $active === (string) $value) aria-current="true" @endif>{{ $label }}</a>
    @endforeach
</div>
