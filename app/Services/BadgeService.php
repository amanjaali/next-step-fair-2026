<?php

namespace App\Services;

use App\Models\Registration;
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

        return [
            'registration' => $registration,
            'locale' => $registration->locale,
            'accent' => $registration->accent(),
            'isConference' => $registration->isConference(),
            'qr' => $this->qr->pngDataUri($verifyUrl, 600),
            'verifyUrl' => $verifyUrl,
            'typeChip' => strtoupper($registration->type),
            'daysLabel' => $registration->isConference()
                ? __('site.common.day', ['n' => 1]).' · '.ns_day_date(1)
                : $registration->daysLabel(),
            'marks' => $this->partnerMarks(),
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

    /** A6 PDF, the format printed at the registration desk. */
    public function pdf(Registration $registration): string
    {
        $pdf = Pdf::loadView('badges.badge', $this->payload($registration))
            ->setPaper('a6', 'portrait');

        return $pdf->output();
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
                return Browsershot::html($html)
                    ->windowSize(760, 1080)
                    ->deviceScaleFactor(2)
                    ->setScreenshotType('png')
                    ->screenshot();
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
     * KNOWN LIMIT, and it matters here more than most places: GD draws a string
     * as a run of code points, left to right, with no Arabic shaping and no
     * bidi. A Kurdish or Arabic name therefore comes out unjoined and reversed.
     * Latin names, the ticket, the dates and the QR are all correct, and the QR
     * is what the gate actually reads — but a person whose own name is printed
     * backwards on their badge will not care about that.
     *
     * The fix is not in this method: it is to install spatie/browsershot and a
     * headless Chrome on the server, after which png() renders the same Blade
     * view the printed PDF uses, and the browser does the shaping properly.
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

        $write = function (string $text, int $size, int $x, int $y, bool $strong = false, ?int $colour = null) use ($image, $white, $regular, $bold) {
            if ($regular) {
                imagettftext($image, $size, 0, $x, $y, $colour ?? $white, $strong ? $bold : $regular, $text);
            } else {
                // No TTF available: the bitmap font is ASCII-only, so transliterate.
                imagestring($image, 5, $x, $y - 14, $this->toAscii($text), $colour ?? $white);
            }
        };

        $write(strtoupper(config('nextstep.event.short_name')), 20, 56, 84, true);
        $write($registration->isConference()
            ? 'Conference '.config('nextstep.event.year')
            : (string) config('nextstep.event.year'), 20, 56, 116, true);

        /*
         * The partnership, in the top corner opposite the event name — the same
         * arrangement as the printed badge and the card, so somebody holding
         * one and looking at the other sees the same thing.
         */
        /*
         * English here even on a Kurdish badge. GD draws code points in the
         * order it is given them and does no Arabic shaping or bidi, so a
         * Kurdish label comes out unjoined and back to front. Better one honest
         * English line than a mangled Kurdish one — and see the note on this
         * method about the same problem with names.
         */
        $this->writeRight($image, 'IN PARTNERSHIP WITH', 11, 704, 78, $regular, $white);
        $this->drawMarks($image, 704, 96, 72);

        // Type chip, knocked out of the accent ground.
        $chip = strtoupper($registration->type);
        imagefilledrectangle($image, 56, 214, 56 + (int) (strlen($chip) * 13) + 32, 258, $white);
        $write($chip, 13, 72, 245, true, $ink);

        $write($registration->full_name, mb_strlen($registration->full_name) > 26 ? 24 : 30, 56, 316, true);

        if ($registration->isConference()) {
            $write((string) $registration->organization, 16, 56, 352, true);
            if ($registration->position) {
                $write((string) $registration->position, 14, 56, 380);
            }
        } else {
            $write(trim($registration->city.' · '.$registration->daysLabel(), ' ·'), 14, 56, 352);
        }

        // White quiet zone behind the QR, as scan reliability requires.
        $qrPng = $this->qr->png($this->tickets->verifyUrl($registration), 460);
        $qrImage = imagecreatefromstring($qrPng);
        imagefilledrectangle($image, 56, 404, 596, 944, $white);
        imagecopyresampled($image, $qrImage, 86, 434, 0, 0, 480, 480, imagesx($qrImage), imagesy($qrImage));

        $write('TICKET '.$registration->ticket_ref, 13, 56, 1004, true);
        $write(ns_event_dates().' · '.config('nextstep.event.venue.name').', '.config('nextstep.event.venue.city'), 11, 56, 1034);

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
     * SVG is skipped rather than guessed at: GD cannot read it, and an
     * exception here would take the whole badge down with it. The browser
     * render, which handles SVG properly, is the one that runs where it matters.
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

            if (! is_readable($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'svg') {
                continue;
            }

            $source = @imagecreatefromstring((string) file_get_contents($path));

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
