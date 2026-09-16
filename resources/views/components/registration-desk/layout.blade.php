@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#050708">
    <title>{{ $title ?? __('registration_desk.title') }} — {{ config('nextstep.event.short_name') }}</title>
    <link rel="apple-touch-icon" href="{{ asset('assets/brand/nextstep-transparent-sm.png') }}">
    @vite(['resources/css/registration-desk.css', 'resources/js/registration-desk.js'])
</head>
<body>
{{ $slot }}
</body>
</html>
