<?php

namespace App\Services;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;

/**
 * QR generation, on our own server.
 *
 * Nothing here calls an external QR service: badges have to be issuable when the
 * venue's connection is unreliable, and a ticket token must never leave the box.
 */
class QrCodeService
{
    /** Raw PNG bytes, sized for print at A6 and legible on a dim phone screen. */
    public function png(string $data, ?int $size = null): string
    {
        return (new PngWriter)->write($this->code($data, $size))->getString();
    }

    /** Inline-able data URI, for the badge HTML and the confirmation e-mail. */
    public function pngDataUri(string $data, ?int $size = null): string
    {
        return (new PngWriter)->write($this->code($data, $size))->getDataUri();
    }

    public function svg(string $data, ?int $size = null): string
    {
        return (new SvgWriter)->write($this->code($data, $size))->getString();
    }

    private function code(string $data, ?int $size = null): QrCode
    {
        return new QrCode(
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: $this->errorCorrection(),
            size: $size ?? (int) config('nextstep.qr.size'),
            margin: (int) config('nextstep.qr.margin'),
            foregroundColor: new Color(5, 7, 8),        // Next Ink, not pure black
            backgroundColor: new Color(255, 255, 255),
        );
    }

    private function errorCorrection(): ErrorCorrectionLevel
    {
        return match (config('nextstep.qr.error_correction')) {
            'low' => ErrorCorrectionLevel::Low,
            'quartile' => ErrorCorrectionLevel::Quartile,
            'high' => ErrorCorrectionLevel::High,
            default => ErrorCorrectionLevel::Medium,
        };
    }
}
