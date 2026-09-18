{{-- A6 badge, print-ready, rendered by DomPDF for the PDF and by the headless
     browser (or the GD fallback) for the PNG. Layout is table-based on purpose:
     DomPDF has no flexbox or grid. --}}
@php
    $textDir = $scriptDir ?? config("nextstep.locales.$locale.dir", 'ltr');
    // Headless-Chrome PNGs must keep the physical badge layout (brand left,
    // partners right) even when the name is Kurdish. dir="rtl" on <html>
    // mirrors the whole page and squeezes everything into a corner of the
    // oversized viewport Browsershot used to capture — the "tiny badge" bug.
    // Arabic-script lines still get dir="rtl" on their own elements so
    // HarfBuzz joins glyphs; DomPDF keeps the page-level dir it always had.
    $pageDir = ($forPng ?? false) ? 'ltr' : $textDir;
@endphp
<!DOCTYPE html>
<html lang="{{ $htmlLang ?? config("nextstep.locales.$locale.html_lang", $locale) }}" dir="{{ $pageDir }}">
<head>
    <meta charset="utf-8">
    <title>{{ $registration->full_name }} — {{ $registration->ticket_ref }}</title>
    <style>
        @page { margin: 0; size: A6 portrait; }
        * { box-sizing: border-box; }
        {!! $fontCss ?? '' !!}
        body {
            margin: 0;
            font-family: {!! $bodyFont ?? 'DejaVu Sans' !!}, DejaVu Sans, sans-serif;
            color: #ffffff;
            background: {{ $accent }};
        }
        @if ($forPng ?? false)
        html, body {
            width: 105mm;
            height: 148mm;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .badge {
            width: 105mm;
            height: 148mm;
            min-height: 148mm;
        }
        .name[dir="rtl"],
        .meta[dir="rtl"],
        .institution[dir="rtl"],
        .position[dir="rtl"],
        .edition[dir="rtl"] {
            direction: rtl;
            unicode-bidi: isolate;
            width: fit-content;
            max-width: 100%;
        }
        @endif
        .badge { width: 100%; padding: 9mm 8mm 7mm; }
        .brand {
            font-family: {!! $displayFont ?? 'DejaVu Sans' !!}, DejaVu Sans, sans-serif;
            font-size: 13pt; font-weight: bold; line-height: 1.1; letter-spacing: -0.2pt;
        }
        .edition { font-size: 6.5pt; opacity: 0.78; margin-top: 2mm; }
        html[dir="ltr"] .edition { letter-spacing: 1.6pt; text-transform: uppercase; }
        .chip {
            background: #ffffff; color: {{ $accent }}; font-size: 7pt; font-weight: bold;
            letter-spacing: 1.2pt; padding: 1.6mm 2.4mm; white-space: nowrap;
        }
        /*
         * The partnership, in the top corner opposite the event name.
         *
         * White ground behind the marks, always: these are supplied as coloured
         * marks on white, and a ministry's seal knocked onto a magenta badge is
         * not the mark any more. inline-block rather than flex — DomPDF has
         * neither flex nor grid, and this file is the printed badge as well as
         * the picture sent on WhatsApp.
         */
        .partners { background: #ffffff; padding: 2.4mm 3mm; }
        .partners img { height: 13mm; width: auto; vertical-align: middle; }
        .partners .rule { display: inline-block; width: 0.4mm; height: 10mm; background: rgba(5, 7, 8, 0.18); vertical-align: middle; margin: 0 2.5mm; }
        /* letter-spacing breaks Arabic-script joining — keep tracking on Latin only */
        .partnership-label { font-size: 5.5pt; opacity: 0.8; margin-bottom: 1.4mm; }
        html[dir="ltr"] .partnership-label { letter-spacing: 1.2pt; text-transform: uppercase; }

        .name {
            font-family: {!! $displayFont ?? 'DejaVu Sans' !!}, DejaVu Sans, sans-serif;
            font-size: {{ mb_strlen($registration->full_name) > 26 ? '15pt' : '19pt' }};
            font-weight: bold; line-height: 1.05; margin-top: 5mm;
        }
        .institution {
            font-family: {!! $bodyFont ?? 'DejaVu Sans' !!}, DejaVu Sans, sans-serif;
            font-size: 10pt; font-weight: bold; line-height: 1.3; margin-top: 2mm;
        }
        .position {
            font-family: {!! $bodyFont ?? 'DejaVu Sans' !!}, DejaVu Sans, sans-serif;
            font-size: 8.5pt; opacity: 0.85; margin-top: 1mm;
        }
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
                <div class="edition" @if ($textDir === 'rtl') dir="rtl" @endif>
                    {{ $isConference ? __('site.common.day', ['n' => 1], $locale).' · '.ns_day_date(1) : __('site.common.edition_4', [], $locale) }}
                </div>
            </td>

            {{-- The partnership, in the outer top corner. A nested table so the
                 white ground is only as wide as the marks: a bare div would run
                 the width of the badge as a white band. --}}
            <td style="text-align: end;">
                @if ($marks ?? [])
                    <div class="partnership-label">{{ __('site.common.in_partnership', [], $locale) }}</div>
                    <table style="width: auto; margin-inline-start: auto;"><tr><td class="partners">
                        @foreach ($marks as $index => $mark)
                            @if ($index > 0)<span class="rule"></span>@endif
                            <img src="{{ $mark }}" alt="">
                        @endforeach
                    </td></tr></table>
                @endif
            </td>
        </tr>
    </table>

    <div style="margin-top: 5mm;"><span class="chip">{{ $typeChip }}</span></div>

    <div class="name" @if ($textDir === 'rtl') dir="rtl" @endif>{{ $registration->full_name }}</div>

    @if ($isConference)
        {{-- Institution is an explicit requirement on the conference badge. --}}
        <div class="institution" @if ($textDir === 'rtl') dir="rtl" @endif>{{ $registration->organization }}</div>
        @if ($registration->position)
            <div class="position" @if ($textDir === 'rtl') dir="rtl" @endif>{{ $registration->position }}</div>
        @endif
    @else
        <div class="meta" @if ($textDir === 'rtl') dir="rtl" @endif>{{ $registration->city }} · {{ $daysLabel }}</div>
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
