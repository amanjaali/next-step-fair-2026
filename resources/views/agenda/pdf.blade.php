<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('nextstep.event.name') }} — {{ __('site.pages.agenda.title') }}</title>
    <style>
        @page { margin: 18mm 14mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #050708; font-size: 10pt; }
        h1 { font-size: 22pt; margin: 0 0 4pt; letter-spacing: -0.5pt; }
        .meta { color: #4A4B4D; font-size: 9pt; margin-bottom: 16pt; }
        h2 { font-size: 13pt; margin: 18pt 0 6pt; border-bottom: 1pt solid #050708; padding-bottom: 3pt; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 6pt 4pt; border-bottom: 0.5pt solid #dcd8d4; }
        .time { width: 60pt; font-weight: bold; }
        .type { width: 70pt; color: #4A4B4D; font-size: 8.5pt; text-transform: uppercase; letter-spacing: 0.5pt; }
        .title { font-weight: bold; }
        .who { color: #4A4B4D; font-size: 9pt; }
    </style>
</head>
<body>
    <h1>{{ config('nextstep.event.name') }}</h1>
    <div class="meta">
        {{ __('site.pages.agenda.title') }} · {{ ns_event_dates() }} ·
        {{ config('nextstep.event.venue.name') }}, {{ config('nextstep.event.venue.city') }}
    </div>

    @foreach ($sessionsByDay as $day => $sessions)
        <h2>{{ __('site.common.day', ['n' => $day]) }} — {{ ns_day_date($day) }}</h2>
        <table>
            @foreach ($sessions as $session)
                <tr>
                    <td class="time">{{ $session->timeLabel() }}</td>
                    <td class="type">{{ $session->type }}</td>
                    <td>
                        <div class="title">{{ $session->t('title') }}</div>
                        <div class="who">{{ $session->hallLabel() }} · {{ $session->languages }} · {{ $session->t('who') }}</div>
                    </td>
                </tr>
            @endforeach
        </table>
    @endforeach
</body>
</html>
