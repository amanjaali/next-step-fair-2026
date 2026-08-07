@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#050708">
    <title>{{ $title ?? __('checkin.title') }} — {{ config('nextstep.event.short_name') }}</title>
    <link rel="manifest" href="{{ route('checkin.manifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/brand/nextstep-transparent-sm.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    @vite(['resources/css/checkin.css', 'resources/js/checkin.js'])
</head>
<body>
{{ $slot }}
<script>
    // Registers the offline shell. Scope is /checkin only: the public site is
    // never served from cache.
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('{{ route('checkin.sw') }}', { scope: '/checkin' });
    }
</script>
</body>
</html>
