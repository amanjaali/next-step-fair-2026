@props([
    'kicker' => null,
    'title' => null,
    'lead' => null,
    'breadcrumb' => null,   // [['label' => ..., 'url' => ...], ...]
])

<div class="ns-wrap pt-[clamp(28px,4vw,56px)]">
    @if ($breadcrumb)
        <nav class="ns-meta flex gap-2 items-center flex-wrap mb-8" aria-label="Breadcrumb">
            @foreach ($breadcrumb as $crumb)
                @if (! empty($crumb['url']))
                    <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                @else
                    <span>{{ $crumb['label'] }}</span>
                @endif
                @if (! $loop->last)<span aria-hidden="true">/</span>@endif
            @endforeach
        </nav>
    @endif

    <div class="grid gap-14 lg:grid-cols-[1.15fr_1fr] items-end mb-10">
        <div>
            @if ($kicker)
                <span class="ns-eyebrow">{{ $kicker }}</span>
            @endif
            <h1 class="ns-h1 {{ $kicker ? 'mt-5' : '' }}">{{ $title }}</h1>
        </div>
        @if ($lead)
            <p class="ns-body">{{ $lead }}</p>
        @endif
    </div>

    {{ $slot }}
</div>
