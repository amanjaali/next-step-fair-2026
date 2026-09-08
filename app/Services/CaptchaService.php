<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

/**
 * The code a person copies off a picture when they register.
 *
 * It replaced the WhatsApp code, and it answers a different question. A WhatsApp
 * code asked "is this your number?" and held everybody at a screen waiting for a
 * message. This asks only "is there a person here?", which is the part worth
 * asking of a public form that issues a badge.
 *
 * Drawn here rather than fetched from Google. reCAPTCHA would mean a request to
 * a third party on every registration, from a country where that request is not
 * always fast, and a record at that third party of everyone who registered. This
 * costs a session key and a hundred lines.
 *
 * What it is not: proof of a phone number, and not proof against somebody who
 * writes a script and reads the picture. It stops the ordinary flood, which is
 * what it is for.
 */
class CaptchaService
{
    /** No 0/O, 1/I/L or 5/S: a person reading them aloud must not be wrong. */
    private const ALPHABET = 'ABCDEFGHJKMNPQRTUVWXY2346789';

    private const LENGTH = 5;

    private const KEY = 'captcha';

    private const TTL_MINUTES = 20;

    /** A new code, remembered as a hash so the session never carries the answer. */
    public function issue(): string
    {
        $code = '';

        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        Session::put(self::KEY, [
            'hash' => Hash::make(strtoupper($code)),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES)->timestamp,
        ]);

        return $code;
    }

    /**
     * Whether what was typed matches, case and spacing forgiven.
     *
     * Single use: right or wrong, the challenge is spent. Otherwise one solved
     * code could be posted a thousand times, which is the flood this exists to
     * stop.
     */
    public function check(?string $answer): bool
    {
        $challenge = Session::get(self::KEY);
        Session::forget(self::KEY);

        if (! is_array($challenge) || blank($answer)) {
            return false;
        }

        if (($challenge['expires_at'] ?? 0) < now()->timestamp) {
            return false;
        }

        $typed = strtoupper(preg_replace('/\s+/', '', $answer) ?? '');

        return Hash::check($typed, (string) ($challenge['hash'] ?? ''));
    }

    /**
     * The picture itself.
     *
     * Deliberately mild: characters jittered and rotated a little, a few lines
     * and specks through them. A captcha that a person cannot read is a
     * registration that does not happen, and the people filling this in are
     * seventeen and on a phone.
     */
    public function png(): string
    {
        $code = $this->issue();

        $width = 220;
        $height = 70;

        $image = imagecreatetruecolor($width, $height);

        $background = imagecolorallocate($image, 245, 243, 240);   // the site's bone
        $ink = imagecolorallocate($image, 5, 7, 8);
        $magenta = imagecolorallocate($image, 182, 70, 152);
        $faint = imagecolorallocate($image, 205, 200, 195);

        imagefilledrectangle($image, 0, 0, $width, $height, $background);

        // Interference first, so it sits under the lettering and never over it.
        for ($i = 0; $i < 5; $i++) {
            imageline(
                $image,
                random_int(0, $width), random_int(0, $height),
                random_int(0, $width), random_int(0, $height),
                $faint
            );
        }

        for ($i = 0; $i < 140; $i++) {
            imagesetpixel($image, random_int(0, $width), random_int(0, $height), $faint);
        }

        $step = (int) (($width - 40) / self::LENGTH);

        foreach (str_split($code) as $i => $character) {
            // Each glyph is drawn large on its own tile, turned a few degrees and
            // dropped in — the built-in font has no size of its own worth using.
            $tile = imagecreatetruecolor(20, 24);
            imagefilledrectangle($tile, 0, 0, 20, 24, $background);
            imagestring($tile, 5, 4, 3, $character, $i % 2 === 0 ? $ink : $magenta);

            $scaled = imagescale($tile, 44, 54);
            $turned = imagerotate($scaled, random_int(-18, 18), $background);

            imagecopy(
                $image, $turned,
                20 + $i * $step, random_int(2, 12),
                0, 0,
                imagesx($turned), imagesy($turned)
            );

            imagedestroy($tile);
            imagedestroy($scaled);
            imagedestroy($turned);
        }

        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();

        imagedestroy($image);

        return $png;
    }
}
