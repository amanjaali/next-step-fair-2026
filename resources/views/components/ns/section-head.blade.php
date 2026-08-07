@props([
    'eyebrow' => null,
    'title' => null,
    'note' => null,
    'href' => null,
    'linkLabel' => null,
    'rule' => false,
])

<div class="mb-9">
    @if ($eyebrow)
        <div class="flex items-baseline gap-5 {{ $title ? 'mb-4' : '' }}">
            <span class="ns-eyebrow">{{ $eyebrow }}</span>
            @if ($rule || ! $title)
                <span class="ns-rule"></span>
            @endif
        </div>
    @endif

    @if ($title || $note || $href)
        <div class="flex items-baseline justify-between gap-6 flex-wrap">
            @if ($title)
                <h2 class="ns-h2">{{ $title }}</h2>
            @endif

            @if ($href)
                <a href="{{ $href }}" class="ns-btn ns-btn-ghost ns-btn-sm">{{ $linkLabel }}</a>
            @elseif ($note)
                <span class="ns-meta">{{ $note }}</span>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
