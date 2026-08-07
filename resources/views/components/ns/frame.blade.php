@props([
    'label' => null,
    'src' => null,
    'alt' => '',
    'ratio' => null,
    'center' => false,
    'height' => null,
])

{{-- Photography placeholder: a grey frame with an uppercase label until the real
     image lands, and the image itself once it does. --}}
<div {{ $attributes->class([
        'ns-frame',
        'ns-frame-center' => $center || ! $src,
    ])->merge([
        'style' => trim(($ratio ? "aspect-ratio:$ratio;" : '').($height ? "height:$height;" : '')),
    ]) }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" decoding="async">
    @elseif ($label)
        <span>{{ $label }}</span>
    @endif
    {{ $slot }}
</div>
