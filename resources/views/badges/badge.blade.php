{{-- A6 badge, print-ready, rendered by DomPDF for the PDF and by the headless
     browser (or the GD fallback) for the PNG. Layout is table-based on purpose:
     DomPDF has no flexbox or grid. --}}
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ config("nextstep.locales.$locale.dir", 'ltr') }}">
<head>
    <meta charset="utf-8">
    <title>{{ $registration->full_name }} — {{ $registration->ticket_ref }}</title>
    <style>
        @page { margin: 0; size: A6 portrait; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #ffffff;
            background: {{ $accent }};
        }
        .badge { width: 100%; padding: 9mm 8mm 7mm; }
        .brand { font-size: 13pt; font-weight: bold; line-height: 1.1; letter-spacing: -0.2pt; }
        .edition { font-size: 6.5pt; letter-spacing: 1.6pt; text-transform: uppercase; opacity: 0.78; margin-top: 2mm; }
        .chip {
            background: #ffffff; color: {{ $accent }}; font-size: 7pt; font-weight: bold;
            letter-spacing: 1.2pt; padding: 1.6mm 2.4mm; white-space: nowrap;
        }
        /*
         * The partnership marks, directly under the event name.
         *
         * White ground behind them, always: these are supplied as coloured marks
         * on white, and a ministry's seal knocked onto a magenta badge is not the
         * mark any more. inline-block rather than flex — DomPDF has neither flex
         * nor grid, and this file is the printed badge as well as the picture.
         */
        .partners { background: #ffffff; padding: 2.4mm 3mm; margin-top: 5mm; }
        .partners img { height: 13mm; width: auto; vertical-align: middle; }
        .partners .rule { display: inline-block; width: 0.4mm; height: 10mm; background: rgba(5, 7, 8, 0.18); vertical-align: middle; margin: 0 2.5mm; }

        .name { font-size: {{ mb_strlen($registration->full_name) > 26 ? '15pt' : '19pt' }}; font-weight: bold; line-height: 1.05; margin-top: 6mm; }
        .institution { font-size: 10pt; font-weight: bold; line-height: 1.3; margin-top: 2mm; }
        .position { font-size: 8.5pt; opacity: 0.85; margin-top: 1mm; }
        .meta { font-size: 8pt; opacity: 0.9; margin-top: 2mm; }
        .qr-wrap { background: #ffffff; padding: 3mm; margin-top: 6mm; width: 46mm; }
        .qr-wrap img { display: block; width: 40mm; height: 40mm; }
        .ticket { font-size: 7pt; letter-spacing: 1pt; opacity: 0.85; margin-top: 3mm; }
        .foot { font-size: 6.5pt; opacity: 0.75; margin-top: 2mm; line-height: 1.4; }
        /* A Latin date inside a right-to-left line reads back to front —
           "28-30 September" came out as "September 30-28". Isolating it
           settles the direction of that run without turning the line. */
        .num { direction: ltr; unicode-bidi: isolate; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }
    </style>
</head>
<body>
<div class="badge">
    <table>
        <tr>
            <td>
                <div class="brand">
                    {{ $isConference ? 'Next Step' : 'Next Step Fair' }}<br>
                    {{ $isConference ? 'Conference '.config('nextstep.event.year') : config('nextstep.event.year') }}
                </div>
                <div class="edition">
                    {{ $isConference ? __('site.common.day', ['n' => 1], $locale).' · '.ns_day_date(1) : __('site.common.edition_4', [], $locale) }}
                </div>
            </td>
            <td style="text-align: end; width: 30mm;">
                <span class="chip">{{ $typeChip }}</span>
            </td>
        </tr>
    </table>

    @if ($marks ?? [])
        {{-- A table so the white ground is only as wide as the marks: a bare div
             would run the full width of the badge as a white band. --}}
        <table style="width: auto;"><tr><td class="partners">
            @foreach ($marks as $index => $mark)
                @if ($index > 0)<span class="rule"></span>@endif
                <img src="{{ $mark }}" alt="">
            @endforeach
        </td></tr></table>
    @endif

    <div class="name">{{ $registration->full_name }}</div>

    @if ($isConference)
        {{-- Institution is an explicit requirement on the conference badge. --}}
        <div class="institution">{{ $registration->organization }}</div>
        @if ($registration->position)
            <div class="position">{{ $registration->position }}</div>
        @endif
    @else
        <div class="meta">{{ $registration->city }} · {{ $daysLabel }}</div>
    @endif

    <div class="qr-wrap">
        <img src="{{ $qr }}" alt="QR">
    </div>

    <div class="ticket">TICKET {{ $registration->ticket_ref }}</div>
    <div class="foot">
        <span class="num">{{ ns_event_dates() }}</span> · {{ config('nextstep.event.venue.name') }}, {{ config('nextstep.event.venue.city') }}
    </div>
</div>
</body>
</html>
