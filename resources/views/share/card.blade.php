@php
    /**
     * The picture people post.
     *
     * Three shapes, each the size the destination actually wants, so nothing is
     * cropped on the way in: 1080×1080 for a feed, 1080×1920 for a story, and
     * 1200×630 for the preview LinkedIn and Facebook draw when somebody posts a
     * link. The wide one is landscape and has to lay out sideways — a column
     * that works at 1:1 has nowhere to go at 1.91:1.
     *
     * Deliberately no QR code. This is going on a public feed, and the badge QR
     * is what opens the gate: posted once, it is a free pass for whoever
     * screenshots it first.
     */
    $story = $format === 'story';
    $wide = $format === 'og';
    $rtl = in_array($locale, ['ku', 'ar'], true);

    $width = $wide ? 1200 : 1080;
    $height = match ($format) { 'story' => 1920, 'og' => 630, default => 1080 };
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    @vite(['resources/css/app.css'])
    <style>
        html, body { margin: 0; padding: 0; overflow: hidden; }

        body {
            width: {{ $width }}px;
            height: {{ $height }}px;
            background: #050708;
            color: #fff;
            font-family: var(--ns-body);
            overflow: hidden;
            position: relative;
        }

        /* A wash of the track colour, so a fair card and a conference card are
           told apart at thumbnail size without reading a word. Anchored to the
           bottom outer corner: over the top-left it washed out the white
           wordmark and swallowed the magenta strapline underneath it. */
        .glow {
            position: absolute;
            inset-inline-end: 0;
            bottom: 0;
            width: 78%;
            height: {{ $story ? '34%' : ($wide ? '78%' : '54%') }};
            /*
             * No negative offsets. Off-canvas positioning is invisible in LTR but
             * in RTL the browser counts it as scrollable width, which pushed the
             * whole Kurdish and Arabic card sideways and clipped it — the same
             * trap the skip link fell into. The gradient fades out on its own.
             */
            background: radial-gradient(circle at 72% 82%, {{ $accent }}, transparent 64%);
            opacity: 0.55;
        }

        .edge {
            position: absolute;
            inset-block: 0;
            inset-inline-start: 0;
            width: 20px;
            background: {{ $accent }};
        }

        .sheet {
            position: relative;
            height: 100%;
            padding: {{ $story ? '160px 92px 150px' : ($wide ? '56px 64px' : '86px 84px') }};
            display: flex;
            flex-direction: column;
            justify-content: {{ $story ? 'space-evenly' : 'space-between' }};
        }

        /*
         * align-self matters here. The sheet is a flex column, so a bare image
         * stretches to the full width of it, and with a fixed height that means
         * the wordmark is squashed to a letterbox. Sizing it by width instead of
         * height keeps the 1.65:1 lockup honest at both card sizes.
         */
        .mark {
            display: block;
            align-self: flex-start;
            width: {{ $story ? 380 : ($wide ? 240 : 300) }}px;
            height: auto;
        }

        /*
         * The headline takes the space left between the wordmark and the facts
         * and centres itself in it. With plain space-between the free space all
         * pooled below the headline, which pushed the kicker up against the
         * wordmark until the two read as one block.
         */
        .middle {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* Trimmed so the block below actually reaches the bottom padding:
               once the column overflows its box, space-between stops working
               and the footer floats at a different height in each language —
               and the name drawn onto the finished picture sits at one fixed
               height for all of them. */
            padding-block: {{ $story ? 34 : ($wide ? 8 : 12) }}px;
        }

        .kicker {
            font-size: {{ $story ? 32 : ($wide ? 22 : 27) }}px;
            font-weight: 800;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: {{ $accent }};
            margin-bottom: {{ $story ? 28 : ($wide ? 12 : 18) }}px;
        }

        h1 {
            font-family: var(--ns-display);
            font-size: {{ $story ? 118 : ($wide ? 68 : 100) }}px;
            line-height: 1.02;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin: 0;
        }

        .line {
            font-size: {{ $story ? 44 : ($wide ? 27 : 37) }}px;
            line-height: 1.42;
            color: rgba(255, 255, 255, 0.8);
            margin: {{ $story ? '40px' : ($wide ? '18px' : '34px') }} 0 0;
            max-width: {{ $wide ? '34ch' : '21ch' }};
        }

        .facts {
            display: flex;
            gap: {{ $wide ? 44 : 60 }}px;
            flex-wrap: wrap;
            padding-top: {{ $story ? 42 : ($wide ? 20 : 30) }}px;
            border-top: 2px solid rgba(255, 255, 255, 0.22);
        }

        .fact-label {
            font-size: {{ $wide ? 17 : 22 }}px;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: {{ $wide ? 8 : 12 }}px;
        }

        .fact-value {
            font-family: var(--ns-display);
            font-size: {{ $story ? 40 : ($wide ? 26 : 35) }}px;
            font-weight: 600;
        }

        /*
         * Latin digits, as everywhere else on the site — and isolated, so a date
         * or a handle keeps its own direction inside a right-to-left line. Left
         * to the surrounding paragraph, "28-30" comes out as "30-28" and
         * "@nextstepfair" puts its @ on the wrong end.
         */
        .num,
        .handle {
            direction: ltr;
            unicode-bidi: isolate;
            font-variant-numeric: lining-nums tabular-nums;
        }

        .foot {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-top: {{ $story ? 46 : ($wide ? 16 : 28) }}px;
        }

        .handle { font-size: {{ $story ? 34 : ($wide ? 21 : 29) }}px; font-weight: 700; color: rgba(255, 255, 255, 0.6); }

        /*
         * The top row: our wordmark at one edge, the partnership at the other.
         *
         * Opposite corners rather than side by side, so neither reads as part of
         * the other's lockup — the fair is ours, the marks beside it are whose
         * fair it is with. A row costs no height either way: it is as tall as
         * the wordmark was on its own.
         *
         * The marks used to sit in the bottom corner, which is the part a feed
         * thumbnail crops. The attendee's name has gone there instead, drawn on
         * afterwards by the browser: it is one line, it survives the crop, and
         * it belongs next to the handle.
         */
        .head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: {{ $story ? 34 : ($wide ? 22 : 28) }}px;
        }

        /* The label and the marks, stacked and aligned to the outer edge. */
        .partnership {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: {{ $story ? 14 : ($wide ? 8 : 11) }}px;
        }

        .partnership-label {
            font-size: {{ $story ? 24 : ($wide ? 15 : 20) }}px;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.62);
        }

        .partners {
            display: flex;
            align-items: center;
            gap: {{ $story ? 24 : ($wide ? 14 : 20) }}px;
            background: #fff;
            padding: {{ $story ? '20px 26px' : ($wide ? '12px 16px' : '16px 22px') }};
        }

        /*
         * Sized against the wordmark beside them, not against the body text.
         * Roughly half its height, which is what a mark of a different shape
         * needs to read as its equal rather than as a footnote.
         */
        .partners img { height: {{ $story ? 124 : ($wide ? 66 : 96) }}px; width: auto; display: block; }
        .partners .rule { width: 2px; height: {{ $story ? 96 : ($wide ? 50 : 74) }}px; background: rgba(5, 7, 8, 0.18); }
    </style>
</head>
<body>
    <div class="glow"></div>
    <div class="edge"></div>

    <div class="sheet">
        <div class="head">
            <img class="mark" src="{{ ns_brand('logo_light', 'assets/brand/nextstep-white-sm.png') }}" alt="">

            @if ($marks = ns_partner_marks())
                <div class="partnership">
                    <div class="partnership-label">{{ __('site.common.in_partnership') }}</div>

                    <div class="partners">
                        @foreach ($marks as $index => $mark)
                            @if ($index > 0)<span class="rule"></span>@endif
                            <img src="{{ $mark['src'] }}" alt="">
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="middle">
            <div class="kicker">{{ __('share.card.kicker') }}</div>
            <h1>{{ config('nextstep.event.name') }}</h1>
            <p class="line">{{ $line }}</p>
        </div>

        <div>
            <div class="facts">
                <div>
                    <div class="fact-label">{{ __('site.common.dates') }}</div>
                    <div class="fact-value num">{{ ns_event_dates() }}</div>
                </div>
                <div>
                    <div class="fact-label">{{ __('site.common.venue') }}</div>
                    <div class="fact-value">{{ config('nextstep.event.venue.name') }}</div>
                </div>
            </div>

            <div class="foot">
                <div class="handle">{{ __('share.card.handle') }}</div>
            </div>
        </div>
    </div>
</body>
</html>
