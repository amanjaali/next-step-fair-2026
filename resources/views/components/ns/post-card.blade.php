@props(['post', 'showReadTime' => true])

@php
    $route = $post->type === 'blog' ? route('blog.show', $post) : route('news.show', $post);
@endphp

<a href="{{ $route }}" class="text-ink hover:text-ink flex flex-col group">
    <x-ns.frame :label="$post->cover_placeholder" :src="$post->coverUrl()"
                :alt="$post->t('title')" ratio="3/2" class="mb-[18px]">
        <span class="absolute inset-y-0 start-0 w-2" style="background:{{ $post->accent() }}"></span>
    </x-ns.frame>

    <div class="flex items-center gap-[10px] mb-[11px] flex-wrap">
        @if ($post->category)
            <span class="ns-typechip border"
                  style="color:{{ $post->accent() }};border-color:{{ $post->accentBorder() }}">
                {{ $post->category->t('name') }}
            </span>
        @endif
        <span class="ns-meta ns-num">{{ $post->published_at ? ns_format_date($post->published_at) : '' }}</span>
    </div>

    <h3 class="font-[family-name:var(--ns-display)] text-[23px] font-semibold leading-[1.15] mb-[10px] text-pretty group-hover:underline">
        {{ $post->t('title') }}
    </h3>

    <p class="font-[family-name:var(--ns-body)] text-[14.5px] leading-[1.65] text-slate mb-[14px]">
        {{ $post->t('excerpt') }}
    </p>

    @if ($showReadTime)
        <span class="mt-auto font-[family-name:var(--ns-body)] text-[13px] font-bold text-magenta">
            {{ __('site.common.min_read', ['n' => $post->readingTime()]) }}
        </span>
    @endif
</a>
