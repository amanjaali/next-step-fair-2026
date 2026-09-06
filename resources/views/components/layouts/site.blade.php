@props([
    'title' => null,
    'description' => null,
    'navKey' => null,
    'track' => null,
    'ogType' => 'website',
    'ogImage' => null,
])
<!DOCTYPE html>
<html lang="{{ $localeConfig['html_lang'] }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#050708">

    <title>{{ $title ?? config('nextstep.event.name') }}</title>
    <meta name="description" content="{{ $description ?? __('site.seo.default_description') }}">

    {{-- One canonical per language, with hreflang across all three plus x-default. --}}
    <link rel="canonical" href="{{ url()->current() }}">
    @foreach (array_keys(config('nextstep.locales')) as $alt)
        <link rel="alternate" hreflang="{{ config("nextstep.locales.$alt.html_lang") }}"
              href="{{ ns_alternate_url($alt) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ ns_alternate_url('en') }}">

    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ config('nextstep.event.name') }}">
    <meta property="og:title" content="{{ $title ?? config('nextstep.event.name') }}">
    <meta property="og:description" content="{{ $description ?? __('site.seo.default_description') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ $localeConfig['html_lang'] }}">
    {{-- 1200×630 is the shape LinkedIn and Facebook draw in a feed, and both
         want the dimensions declared: without them the first crawl often shows
         no picture at all, and the first crawl is the one that gets cached. --}}
    <meta property="og:image" content="{{ $ogImage ?? url(ns_brand('share_default', 'assets/share/'.app()->getLocale().'-student-og.png')) }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $title ?? config('nextstep.event.name') }}">

    {{-- LinkedIn reads Open Graph and ignores the Twitter tags; X reads these.
         Repeating the values is what makes the same link look right in both. --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? config('nextstep.event.name') }}">
    <meta name="twitter:description" content="{{ $description ?? __('site.seo.default_description') }}">
    <meta name="twitter:image" content="{{ $ogImage ?? url(ns_brand('share_default', 'assets/share/'.app()->getLocale().'-student-og.png')) }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ ns_brand('logo_dark', 'assets/brand/nextstep-transparent-sm.png') }}">

    @stack('schema')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.analytics')
</head>
<body @if($track) data-track="{{ $track }}" @endif>

<a href="#main" class="ns-skip">{{ __('site.nav.skip') }}</a>

@include('partials.header', ['navKey' => $navKey])

<main id="main">
    {{ $slot }}
</main>

@include('partials.footer')

    @stack('scripts')
</body>
</html>
