<?php

namespace App\Services;

use App\Models\Registration;
use App\Support\Svg;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

/**
 * Badge artwork, generated server-side.
 *
 * The PDF is A6 and print-ready. The PNG is rendered from the same Blade view so
 * the two never drift; where Browsershot is unavailable (no headless Chrome on
 * the box) the PNG falls back to a composed GD render of the same fields.
 */
class BadgeService
{
    public function __construct(
        private readonly TicketService $tickets,
        private readonly QrCodeService $qr,
    ) {}

    /** Everything the badge view needs, in the registrant's own language. */
    public function payload(Registration $registration): array
    {
        $verifyUrl = $this->tickets->verifyUrl($registration);

        $locale = $registration->locale;

        return [
            'registration' => $registration,
            'locale' => $locale,
            'accent' => $registration->accent(),
            'isConference' => $registration->isConference(),
            'qr' => $this->qr->pngDataUri($verifyUrl, 600),
            'verifyUrl' => $verifyUrl,
            'typeChip' => strtoupper($registration->type),
            'daysLabel' => $registration->isConference()
                ? __('site.common.day', ['n' => 1]).' · '.ns_day_date(1)
                : $registration->daysLabel(),
            'marks' => $this->partnerMarks(),
            'fontCss' => $this->fontCss($locale),
            'bodyFont' => config("nextstep.locales.$locale.body_font", 'DejaVu Sans'),
            'displayFont' => config("nextstep.locales.$locale.display_font", 'DejaVu Sans'),
        ];
    }

    /**
     * The partnership marks, as data URIs.
     *
     * DomPDF fetches an image by path, not by URL, and the badge is also
     * rendered by a headless browser and, on a box without one, composed by GD
     * — three renderers with three ideas of where "/storage/brand/ksa.svg"
     * points. Reading the bytes once here means all three draw the same badge,
     * and none of them has to reach out to the network to do it.
     *
     * @return list<string>
     */
    private function partnerMarks(): array
    {
        $uris = [];

        foreach (ns_partner_marks() as $mark) {
            $path = public_path(ltrim($mark['src'], '/'));

            if (! is_readable($path)) {
                continue;
            }

            $type = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
                'svg' => 'image/svg+xml',
                'jpg', 'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                default => 'image/png',
            };

            $uris[] = 'data:'.$type.';base64,'.base64_encode((string) file_get_contents($path));
        }

        return $uris;
    }

    /**
     * A6 PDF, the format printed at the registration desk.
     *
     * DomPDF — like GD — has no Arabic text shaping: it draws whatever
     * codepoints the view produces, one isolated letter at a time, whether or
     * not `dir="rtl"` is set. Without pre-shaping, a Kurdish or Arabic name
     * prints as disconnected letters — confirmed by rendering this exact view
     * through DomPDF with and without shaping and comparing the two rasters.
     * ar-php's glyph joiner exists for precisely this: renderers, GD and
     * DomPDF alike, that only draw isolated forms.
     *
     * So every Arabic-script run in the rendered HTML is shaped in place
     * before DomPDF ever sees it. This only touches text nodes — a run is
     * "Arabic-block characters, optionally with single spaces between Arabic
     * words", which cannot appear inside a tag or attribute.
     */
    public function pdf(Registration $registration): string
    {
        $html = view('badges.badge', $this->payload($registration))->render();
        $html = $this->shapeArabicScriptRuns($html);

        return Pdf::loadHTML($html)->setPaper('a6', 'portrait')->output();
    }

    /**
     * Shape every Arabic-script run in already-rendered HTML, for renderers
     * (DomPDF, GD) that cannot join Arabic letters themselves.
     *
     * Deliberately operates on the rendered string rather than on individual
     * model fields: the badge draws localised strings from several places —
     * the name, the institution, the day label, translated UI text — and a
     * regex over the Arabic Unicode ranges catches all of them in one pass
     * without a shaping call at every call site that might print one.
     */
    private function shapeArabicScriptRuns(string $html): string
    {
        $arabicChar = '[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]';

        return preg_replace_callback(
            "/{$arabicChar}+(?:[ \x{00A0}]{$arabicChar}+)*/u",
            fn (array $m) => $this->shapeForGd($m[0]),
            $html
        ) ?? $html;
    }

    /**
     * PNG for WhatsApp delivery and the "download badge" button.
     * Browsershot gives a pixel-accurate render; GD is the fallback.
     */
    public function png(Registration $registration): string
    {
        $html = view('badges.badge', $this->payload($registration) + ['forPng' => true])->render();

        // Browsershot is optional: install spatie/browsershot plus a headless
        // Chrome to get a pixel-accurate render of the same Blade view.
        if (class_exists(Browsershot::class)) {
            try {
                $shot = Browsershot::html($html)
                    ->windowSize(760, 1080)
                    ->deviceScaleFactor(2)
                    ->setScreenshotType('png')
                    ->noSandbox()
                    ->dismissDialogs()
                    ->setDelay(150);

                if ($node = config('nextstep.badge.node_binary')) {
                    $shot->setNodeBinary($node);
                }

                if ($npm = config('nextstep.badge.npm_binary')) {
                    $shot->setNpmBinary($npm);
                }

                return $shot->screenshot();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $this->fallbackPng($registration);
    }

    /** Stores both artefacts and records them against the registration. */
    public function generate(Registration $registration): Registration
    {
        $disk = Storage::disk(config('filesystems.default'));
        $base = 'badges/'.$registration->ticket_id;

        $disk->put($base.'.pdf', $this->pdf($registration));
        $disk->put($base.'.png', $this->png($registration));

        $registration->forceFill([
            'badge_pdf_path' => $base.'.pdf',
            'badge_png_path' => $base.'.png',
            'badge_generated_at' => now(),
        ])->save();

        return $registration;
    }

    /**
     * GD composition: brand ground, name, institution, type chip and the QR.
     *
     * Deliberately plain — it exists so a badge is always issuable, even with no
     * headless browser on the box.
     *
     * KNOWN LIMIT on the GD path: Latin labels, the ticket, the dates and the QR
     * are all correct, and the QR is what the gate actually reads. Arabic-script
     * names are shaped with ar-php before drawing; for pixel-perfect parity with
     * the printed PDF, install spatie/browsershot and puppeteer so png() renders
     * the same Blade view in headless Chrome.
     */
    private function fallbackPng(Registration $registration): string
    {
        $width = 760;
        $height = 1080;
        $image = imagecreatetruecolor($width, $height);

        [$r, $g, $b] = sscanf($registration->accent(), '#%02x%02x%02x');
        $accent = imagecolorallocate($image, $r, $g, $b);
        $white = imagecolorallocate($image, 255, 255, 255);
        $ink = imagecolorallocate($image, 5, 7, 8);
        imagefilledrectangle($image, 0, 0, $width, $height, $accent);

        $regular = $this->fontPath('DejaVuSans.ttf');
        $bold = $this->fontPath('DejaVuSans-Bold.ttf');
        $shapeForGd = fn (string $text): string => $this->shapeForGd($text);

        // Flat colour blocks first. GD + FreeType stop joining Arabic glyphs after
        // imagecopyresampled, so every line of text is drawn before marks and QR.
        $chip = strtoupper($registration->type);
        imagefilledrectangle($image, 56, 214, 56 + (int) (strlen($chip) * 13) + 32, 258, $white);
        imagefilledrectangle($image, 56, 404, 596, 944, $white);

        $write = function (string $text, int $size, int $x, int $y, bool $strong = false, ?int $colour = null) use ($image, $white, $regular, $bold, $shapeForGd) {
            $text = $shapeForGd($text);

            if ($regular) {
                imagettftext($image, $size, 0, $x, $y, $colour ?? $white, $strong ? $bold : $regular, $text);
            } else {
                // No TTF available: the bitmap font is ASCII-only, so transliterate.
                imagestring($image, 5, $x, $y - 14, preg_replace('/[^\x20-\x7E]/', '-', $text), $colour ?? $white);
            }
        };

        $write(strtoupper(config('nextstep.event.short_name')), 20, 56, 84, true);
        $write($registration->isConference()
            ? 'Conference '.config('nextstep.event.year')
            : (string) config('nextstep.event.year'), 20, 56, 116, true);

        // Arabic-script lines must be drawn before writeRight(): a right-aligned
        // Latin pass through imagettftext breaks subsequent shaped Kurdish glyphs.
        $write($registration->full_name, mb_strlen($registration->full_name) > 26 ? 24 : 30, 56, 316, true);

        if ($registration->isConference()) {
            $write((string) $registration->organization, 16, 56, 352, true);
            if ($registration->position) {
                $write((string) $registration->position, 14, 56, 380);
            }
        } else {
            $write(trim($registration->city.' · '.$registration->daysLabel(), ' ·'), 14, 56, 352);
        }

        $this->writeRight($image, 'IN PARTNERSHIP WITH', 11, 704, 78, $regular, $white);
        $write($chip, 13, 72, 245, true, $ink);
        $write('TICKET '.$registration->ticket_ref, 13, 56, 1004, true);
        $write(ns_event_dates().' · '.config('nextstep.event.venue.name').', '.config('nextstep.event.venue.city'), 11, 56, 1034);

        $this->drawMarks($image, 704, 96, 72);

        $qrPng = $this->qr->png($this->tickets->verifyUrl($registration), 460);
        $qrImage = imagecreatefromstring($qrPng);
        imagecopyresampled($image, $qrImage, 86, 434, 0, 0, 480, 480, imagesx($qrImage), imagesy($qrImage));

        ob_start();
        imagepng($image);
        $bytes = (string) ob_get_clean();

        imagedestroy($qrImage);
        imagedestroy($image);

        return $bytes;
    }

    /**
     * One line of text ending at a given x, rather than starting from one.
     *
     * GD draws from the left and knows nothing about alignment, so the width has
     * to be measured first. Without a TTF face there is nothing to measure, and
     * the bitmap font is a fixed 10px per character — near enough for a label.
     *
     * @param  \GdImage  $canvas
     */
    private function writeRight($canvas, string $text, int $size, int $rightEdge, int $baseline, ?string $font, int $colour): void
    {
        if (! $font) {
            imagestring($canvas, 3, $rightEdge - strlen($this->toAscii($text)) * 8, $baseline - 12, $this->toAscii($text), $colour);

            return;
        }

        $box = imagettfbbox($size, 0, $font, $text);
        $textWidth = (int) abs($box[2] - $box[0]);

        imagettftext($canvas, $size, 0, $rightEdge - $textWidth, $baseline, $colour, $font, $text);
    }

    /**
     * The partnership marks on the GD badge, on their own white ground.
     *
     * This path runs whenever the box has no headless browser, which on a small
     * server is most of the time — so the marks belong here as much as in the
     * Blade view, or the ministry appears on the badge only where Chrome is
     * installed.
     *
     * An SVG is rendered to a picture first — see Svg::rasterise — because that
     * is the format a logo actually arrives in.
     *
     * @param  \GdImage  $canvas
     */
    private function drawMarks($canvas, int $rightEdge, int $y, int $height): void
    {
        $pad = 8;
        $gap = 16;
        $drawn = [];

        foreach (ns_partner_marks() as $mark) {
            $path = public_path(ltrim($mark['src'], '/'));

            if (! is_readable($path)) {
                continue;
            }

            /*
             * GD cannot read SVG, and a logo uploaded as one is the normal case
             * — it is what a designer sends and what the rest of the site
             * prefers. So it is rendered to a picture first, by Imagick or by
             * whichever converter the machine has, and the result cached.
             *
             * If the machine has none, the mark is left out rather than drawn
             * badly, and the brand images screen says so at the moment of
             * upload instead of letting it fail quietly here.
             */
            $bytes = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'svg'
                ? Svg::rasterise($path, $height)
                : (string) file_get_contents($path);

            if (! $bytes) {
                continue;
            }

            $source = @imagecreatefromstring($bytes);

            if ($source === false) {
                continue;
            }

            $drawn[] = $source;
        }

        if (! $drawn) {
            return;
        }

        $widths = array_map(
            fn ($source) => (int) round(imagesx($source) * ($height / imagesy($source))),
            $drawn
        );

        $panel = array_sum($widths) + $gap * (count($drawn) - 1) + $pad * 2;
        $x = $rightEdge - $panel;

        imagefilledrectangle(
            $canvas, $x, $y, $rightEdge, $y + $height + $pad * 2,
            imagecolorallocate($canvas, 255, 255, 255)
        );

        $cursor = $x + $pad;

        foreach ($drawn as $index => $source) {
            imagecopyresampled(
                $canvas, $source,
                $cursor, $y + $pad, 0, 0,
                $widths[$index], $height,
                imagesx($source), imagesy($source)
            );

            $cursor += $widths[$index] + $gap;
            imagedestroy($source);
        }
    }

    /**
     * Arabic-script text for GD: join glyphs and reorder for left-to-right renderers.
     *
     * GD has no bidi and no shaping, so Kurdish and Arabic names otherwise appear as
     * a row of disconnected letters — exactly what shows up on WhatsApp badges when
     * headless Chrome is not installed.
     */
    public function shapeForGd(string $text): string
    {
        if (! preg_match('/\p{Arabic}/u', $text)) {
            return $text;
        }

        try {
            return (new Arabic)->utf8Glyphs($text);
        } catch (\Throwable $e) {
            report($e);

            return $text;
        }
    }

    /** @font-face rules for the badge PNG renderer (Browsershot). DomPDF keeps DejaVu. */
    private function fontCss(string $locale): string
    {
        if (! in_array($locale, ['ku', 'ar'], true)) {
            return '';
        }

        $rules = [];

        foreach ([
            "'Noto Sans Arabic'" => [
                400 => 'noto-sans-arabic-400.woff2',
                700 => 'noto-sans-arabic-700.woff2',
            ],
            "'Noto Kufi Arabic'" => [
                700 => 'noto-kufi-arabic-700.woff2',
            ],
        ] as $family => $weights) {
            foreach ($weights as $weight => $file) {
                $path = resource_path('fonts/badge/'.$file);

                if (! is_readable($path)) {
                    continue;
                }

                $data = base64_encode((string) file_get_contents($path));

                $rules[] = "@font-face{font-family:{$family};font-style:normal;font-weight:{$weight};src:url(data:font/woff2;base64,{$data}) format('woff2');}";
            }
        }

        return implode('', $rules);
    }

    /** DomPDF ships DejaVu; fall back to the system copy, then to no TTF at all. */
    private function fontPath(string $file): ?string
    {
        foreach ([
            base_path('vendor/dompdf/dompdf/lib/fonts/'.$file),
            '/usr/share/fonts/truetype/dejavu/'.$file,
        ] as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    private function toAscii(string $text): string
    {
        return (string) preg_replace('/[^\x20-\x7E]/', '-', str_replace(['–', '·', '—'], ['-', '-', '-'], $text));
    }
}
