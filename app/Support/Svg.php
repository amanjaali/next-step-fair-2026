<?php

namespace App\Support;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use Symfony\Component\Process\Process;

/**
 * An allowlist for uploaded SVG.
 *
 * Logos arrive as SVG — it is what a designer sends and what scales properly in
 * a header — and an SVG is not a picture. It is a document: it can carry
 * <script>, on* handlers, an <image> pointing anywhere, a <foreignObject> full
 * of HTML. Served from our own origin, as these are, that is a stored
 * cross-site scripting hole with a partner's logo painted over it.
 *
 * So the file is cleaned when it is uploaded, not when it is shown: it is
 * written to disk by the web server and served by nginx, which will never run
 * PHP over it again.
 *
 * Anything not named here is dropped. What remains is drawing — shapes, paths,
 * gradients, text — which is what a logo actually is.
 */
final class Svg
{
    /*
     * Lowercase, every one of them: element names in SVG are camel-cased —
     * linearGradient, clipPath, feGaussianBlur — and they are compared here
     * after lowercasing. An allowlist written in camel case matches nothing,
     * which silently strips the gradient out of a logo and leaves the shape
     * that referenced it filled with black.
     */
    private const ALLOWED_TAGS = [
        'svg', 'g', 'defs', 'symbol', 'use', 'title', 'desc', 'metadata',
        'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
        'text', 'tspan', 'textpath',
        'lineargradient', 'radialgradient', 'stop', 'pattern',
        'clippath', 'mask', 'filter',
        'fegaussianblur', 'feoffset', 'feblend', 'fecolormatrix', 'femerge',
        'femergenode', 'feflood', 'fecomposite', 'fedropshadow',
        'style',
    ];

    /** Anything starting with "on" is refused outright, so it is not listed. */
    private const ALLOWED_ATTRIBUTES = [
        'id', 'class', 'style', 'transform', 'viewbox', 'xmlns', 'xmlns:xlink',
        'version', 'width', 'height', 'x', 'y', 'x1', 'y1', 'x2', 'y2',
        'cx', 'cy', 'r', 'rx', 'ry', 'd', 'points', 'dx', 'dy', 'rotate',
        'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-width',
        'stroke-linecap', 'stroke-linejoin', 'stroke-dasharray',
        'stroke-dashoffset', 'stroke-opacity', 'stroke-miterlimit',
        'opacity', 'color', 'offset', 'stop-color', 'stop-opacity',
        'gradientunits', 'gradienttransform', 'spreadmethod',
        'patternunits', 'patterncontenttunits', 'clip-path', 'clip-rule',
        'mask', 'filter', 'font-family', 'font-size', 'font-weight',
        'font-style', 'text-anchor', 'letter-spacing', 'dominant-baseline',
        'preserveaspectratio', 'overflow', 'display', 'visibility',
        'stddeviation', 'in', 'in2', 'result', 'mode', 'type', 'values',
        'flood-color', 'flood-opacity', 'href', 'xlink:href',
    ];

    /** Elements whose content goes with them rather than being kept. */
    private const DROP_WITH_CONTENTS = [
        'script', 'foreignobject', 'iframe', 'embed', 'object', 'audio',
        'video', 'animate', 'animatetransform', 'animatemotion', 'set',
        'handler', 'listener', 'image',
    ];

    public static function clean(string $svg): string
    {
        if (trim($svg) === '') {
            return '';
        }

        /*
         * A doctype in an uploaded logo is not a logo feature. It is where an
         * external entity is declared — <!ENTITY xxe SYSTEM "file:///etc/passwd">
         * — and the file it names is pasted into the document as text. The file
         * is refused before the parser sees it rather than cleaned afterwards.
         */
        if (preg_match('/<!DOCTYPE/i', $svg) || preg_match('/<!ENTITY/i', $svg)) {
            return '';
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        /*
         * LIBXML_NONET forbids fetching anything over the network. Note what is
         * NOT passed: LIBXML_NOENT, which despite its name *substitutes*
         * entities, and LIBXML_DTDLOAD, which fetches the declaration that
         * defines them. Passing those two was the whole vulnerability, and a
         * test reads /etc/passwd back out if either returns.
         */
        $loaded = $document->loadXML($svg, LIBXML_NONET);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded || ! $document->documentElement) {
            return '';
        }

        if ($document->doctype || strtolower($document->documentElement->nodeName) !== 'svg') {
            return '';
        }

        self::cleanNode($document->documentElement);

        return (string) $document->saveXML($document->documentElement);
    }

    /** Whether a file on disk is safe to keep, after cleaning it in place. */
    public static function cleanFile(string $absolutePath): bool
    {
        if (! is_file($absolutePath)) {
            return false;
        }

        $cleaned = self::clean((string) file_get_contents($absolutePath));

        if ($cleaned === '') {
            return false;
        }

        return file_put_contents($absolutePath, $cleaned) !== false;
    }

    /**
     * Whether this machine can turn an SVG into a picture at all.
     *
     * It matters because of one artefact: the badge PNG that goes out on
     * WhatsApp is composed by GD, and GD cannot read SVG. Everywhere else — the
     * website, the printed PDF, the share cards — draws SVG properly, so an SVG
     * logo looks perfect right up until somebody opens their badge.
     */
    public static function canRasterise(): bool
    {
        return self::rasteriser() !== null;
    }

    /**
     * An SVG as PNG bytes, at the given height, or null if this machine has no
     * way to do it faithfully.
     *
     * Faithfully is the point. A partner's logo drawn approximately is worse
     * than a partner's logo left out — so this uses a real renderer or nothing,
     * rather than a best-effort PHP one that would quietly reshape a ministry's
     * seal.
     *
     * The result is cached beside the source: badges are generated one per
     * registrant, and a fair issues thousands.
     */
    public static function rasterise(string $absolutePath, int $height): ?string
    {
        if (! is_readable($absolutePath)) {
            return null;
        }

        $cache = dirname($absolutePath).'/.raster';
        $key = $cache.'/'.hash('xxh128', $absolutePath.filemtime($absolutePath)).'-'.$height.'.png';

        if (is_readable($key)) {
            return (string) file_get_contents($key);
        }

        $png = self::render($absolutePath, $height);

        if ($png === null) {
            return null;
        }

        if (! is_dir($cache)) {
            @mkdir($cache, 0o755, true);
        }

        @file_put_contents($key, $png);

        return $png;
    }

    /** Imagick first, then the usual command-line converters. */
    private static function render(string $path, int $height): ?string
    {
        $tool = self::rasteriser();

        if ($tool === 'imagick') {
            try {
                $image = new \Imagick;
                $image->setBackgroundColor(new \ImagickPixel('transparent'));
                $image->readImage($path);
                $image->setImageFormat('png32');
                $image->resizeImage(0, $height * 2, \Imagick::FILTER_LANCZOS, 1);

                return $image->getImageBlob();
            } catch (\Throwable) {
                return null;
            }
        }

        $target = tempnam(sys_get_temp_dir(), 'mark').'.png';

        $command = match ($tool) {
            'rsvg-convert' => ['rsvg-convert', '-h', (string) ($height * 2), '-o', $target, $path],
            'inkscape' => ['inkscape', $path, '--export-type=png', '--export-height='.($height * 2), '--export-filename='.$target],
            default => null,
        };

        if ($command === null) {
            return null;
        }

        $process = new Process($command);
        $process->setTimeout(20);
        $process->run();

        $png = $process->isSuccessful() && is_readable($target) ? (string) file_get_contents($target) : null;

        @unlink($target);

        return $png ?: null;
    }

    /** @return 'imagick'|'rsvg-convert'|'inkscape'|null */
    private static function rasteriser(): ?string
    {
        static $found;

        if ($found !== null) {
            return $found ?: null;
        }

        if (extension_loaded('imagick')) {
            return $found = 'imagick';
        }

        foreach (['rsvg-convert', 'inkscape'] as $binary) {
            $which = new Process(['which', $binary]);
            $which->run();

            if ($which->isSuccessful() && trim($which->getOutput()) !== '') {
                return $found = $binary;
            }
        }

        $found = false;

        return null;
    }

    private static function cleanNode(DOMNode $node): void
    {
        // Snapshot: the child list is live, and removing while iterating skips.
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->nodeName);

                // Dangerous: the element and everything inside it goes.
                if (in_array($tag, self::DROP_WITH_CONTENTS, true)) {
                    $child->parentNode?->removeChild($child);

                    continue;
                }

                /*
                 * Merely unrecognised: the element goes and its children are
                 * lifted into its place. <a xlink:href="javascript:…"> wrapping
                 * the word in a logo is the case that matters — dropping the
                 * subtree would take the lettering with it, and a filter that
                 * quietly erases half a logo is one nobody trusts again.
                 */
                if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                    self::cleanNode($child);
                    self::unwrap($child);

                    continue;
                }

                self::cleanAttributes($child);
                self::cleanNode($child);

                continue;
            }

            // Comments can carry conditional markup; processing instructions can
            // carry a stylesheet reference.
            if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $child->parentNode?->removeChild($child);
            }
        }

        if ($node instanceof DOMElement) {
            self::cleanAttributes($node);
        }
    }

    /**
     * Replaces an element with its own children.
     *
     * The children are cleaned before this is called, never after: lifting them
     * first and cleaning the parent again would walk the same nodes a second
     * time and never finish.
     */
    private static function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent) {
            return;
        }

        while ($element->firstChild) {
            $child = $element->firstChild;
            $element->removeChild($child);
            $parent->insertBefore($child, $element);
        }

        $parent->removeChild($element);
    }

    private static function cleanAttributes(DOMElement $element): void
    {
        /** @var array<int, DOMAttr> $attributes */
        $attributes = iterator_to_array($element->attributes);

        foreach ($attributes as $attribute) {
            $name = strtolower($attribute->nodeName);
            $value = (string) $attribute->nodeValue;

            if (str_starts_with($name, 'on') || ! in_array($name, self::ALLOWED_ATTRIBUTES, true)) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            // A reference may only point inside this document: #gradient-2, not
            // somebody else's server and not a javascript: URL.
            if (in_array($name, ['href', 'xlink:href'], true) && ! str_starts_with(trim($value), '#')) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            if (! self::valueIsSafe($value)) {
                $element->removeAttribute($attribute->nodeName);
            }
        }
    }

    /** url(), scripts and imports have no place in a logo's own attributes. */
    private static function valueIsSafe(string $value): bool
    {
        $flat = strtolower(preg_replace('/\s+/', '', $value) ?? '');

        foreach (['javascript:', 'data:text/html', 'vbscript:', 'expression(', '@import', 'behavior:'] as $needle) {
            if (str_contains($flat, $needle)) {
                return false;
            }
        }

        // url(#clip) is how a clip path is referenced and is fine; url() at
        // anything else fetches from somewhere.
        if (str_contains($flat, 'url(') && ! preg_match('/url\(["\']?#/', $flat)) {
            return false;
        }

        return true;
    }
}
