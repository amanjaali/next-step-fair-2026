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
        ];
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
     * headless browser on the box. It draws with the DejaVu face DomPDF ships,
     * so accented Latin and Kurdish/Arabic characters survive.
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

        // Type chip, knocked out of the accent ground.
        $chip = strtoupper($registration->type);
        imagefilledrectangle($image, 56, 156, 56 + (int) (strlen($chip) * 13) + 32, 200, $white);
        $write($chip, 13, 72, 187, true, $ink);

        $write($registration->full_name, mb_strlen($registration->full_name) > 26 ? 24 : 30, 56, 268, true);

        if ($registration->isConference()) {
            $write((string) $registration->organization, 16, 56, 306, true);
            if ($registration->position) {
                $write((string) $registration->position, 14, 56, 336);
            }
        } else {
            $write(trim($registration->city.' · '.$registration->daysLabel(), ' ·'), 14, 56, 306);
        }

        // White quiet zone behind the QR, as scan reliability requires.
        $qrPng = $this->qr->png($this->tickets->verifyUrl($registration), 460);
        $qrImage = imagecreatefromstring($qrPng);
        imagefilledrectangle($image, 56, 396, 616, 956, $white);
        imagecopyresampled($image, $qrImage, 86, 426, 0, 0, 500, 500, imagesx($qrImage), imagesy($qrImage));

        $write('TICKET '.$registration->ticket_ref, 13, 56, 1004, true);
        $write(ns_event_dates().' · '.config('nextstep.event.venue.name').', '.config('nextstep.event.venue.city'), 11, 56, 1034);

        ob_start();
        imagepng($image);
        $bytes = (string) ob_get_clean();

        imagedestroy($qrImage);
        imagedestroy($image);

        return $bytes;
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
