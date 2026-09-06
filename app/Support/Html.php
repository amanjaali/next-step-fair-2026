<?php

namespace App\Support;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * An allowlist for editor-written HTML.
 *
 * The dashboard's rich-text fields are printed unescaped — they have to be, or a
 * paragraph would arrive as visible tags. That makes the editor's output a
 * publishing channel into every visitor's browser, and TinyMCE's own toolbar is
 * not a boundary: it has a source-code view, and a stolen editor password is a
 * stolen editor account. So the HTML is filtered on the way out, on the server.
 *
 * Anything not named here is dropped. That includes <script>, every on* handler,
 * and any href or src that is not plainly http, https, mailto or tel — the three
 * shapes an attack takes in practice:
 *
 *     <script>alert(1)</script>
 *     <img src=x onerror=alert(1)>
 *     <a href="javascript:alert(1)">Click</a>
 *
 * Filtering on output rather than on save is deliberate: it also covers the
 * articles already in the database, which a save-time filter never would.
 */
final class Html
{
    /** Tags an editor can produce with the toolbar they are given. */
    private const ALLOWED_TAGS = [
        'p', 'br', 'hr', 'span', 'div',
        'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup', 'mark', 'small',
        'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'dl', 'dt', 'dd',
        'blockquote', 'cite', 'q', 'pre', 'code',
        'a', 'img', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'colgroup', 'col',
        'iframe',
    ];

    /** Attributes allowed on any tag, and the extra ones each tag may carry. */
    private const GLOBAL_ATTRIBUTES = ['id', 'class', 'dir', 'lang', 'title', 'style'];

    private const TAG_ATTRIBUTES = [
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height', 'loading'],
        'iframe' => ['src', 'width', 'height', 'allow', 'allowfullscreen', 'frameborder'],
        'td' => ['colspan', 'rowspan', 'headers'],
        'th' => ['colspan', 'rowspan', 'scope', 'headers'],
        'col' => ['span'],
        'colgroup' => ['span'],
        'ol' => ['start', 'type'],
    ];

    /**
     * Only video embeds, and only from the two services editors are told to
     * paste. An iframe from anywhere else is somebody else's page rendered
     * inside ours, which is how a convincing fake sign-in form gets published.
     */
    private const IFRAME_HOSTS = [
        'www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'youtube-nocookie.com',
        'player.vimeo.com', 'vimeo.com',
    ];

    /** Tags whose text goes with them, rather than being kept. */
    private const DROP_CONTENTS = [
        'script', 'style', 'iframe', 'object', 'embed', 'form',
        'input', 'button', 'textarea', 'select', 'link', 'meta',
    ];

    public static function clean(?string $html): string
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');

        $previous = libxml_use_internal_errors(true);

        // The fragment is wrapped so the parser has a root, and marked UTF-8 so
        // Kurdish and Arabic survive the round trip.
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"?><div id="ns-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            // Unparseable: publish it as text rather than as markup.
            return e($html);
        }

        $root = (new DOMXPath($document))->query('//div[@id="ns-root"]')->item(0);

        if (! $root instanceof DOMElement) {
            return e($html);
        }

        self::cleanChildren($root);

        $out = '';

        foreach ($root->childNodes as $child) {
            $out .= $document->saveHTML($child);
        }

        return $out;
    }

    private static function cleanChildren(DOMNode $node): void
    {
        // Snapshot first: the list is live, and removing while iterating skips.
        $children = iterator_to_array($node->childNodes);

        foreach ($children as $child) {
            self::cleanNode($child);
        }
    }

    private static function cleanNode(DOMNode $node): void
    {
        if ($node instanceof DOMElement) {
            $tag = strtolower($node->nodeName);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                // <script>alert(1)</script> leaves nothing behind; <span>text</span>
                // keeps its text. Dropping the words with the tag would silently
                // delete a paragraph an editor wrote.
                self::unwrap($node, in_array($tag, self::DROP_CONTENTS, true));

                return;
            }

            self::cleanAttributes($node, $tag);
            self::cleanChildren($node);

            return;
        }

        // Comments can carry conditional-comment markup in old browsers.
        if ($node->nodeType === XML_COMMENT_NODE || $node->nodeType === XML_PI_NODE) {
            $node->parentNode?->removeChild($node);
        }
    }

    /** Replaces an element with its children, or removes it outright. */
    private static function unwrap(DOMElement $element, bool $dropContents): void
    {
        $parent = $element->parentNode;

        if (! $parent) {
            return;
        }

        if (! $dropContents) {
            // Filtered in place first, then lifted. Cleaning the parent again
            // afterwards would walk this same element once more and never end.
            self::cleanChildren($element);

            while ($element->firstChild) {
                $child = $element->firstChild;
                $element->removeChild($child);
                $parent->insertBefore($child, $element);
            }
        }

        $parent->removeChild($element);
    }

    private static function cleanAttributes(DOMElement $element, string $tag): void
    {
        $allowed = array_merge(self::GLOBAL_ATTRIBUTES, self::TAG_ATTRIBUTES[$tag] ?? []);

        /** @var array<int, DOMAttr> $attributes */
        $attributes = iterator_to_array($element->attributes);

        foreach ($attributes as $attribute) {
            $name = strtolower($attribute->nodeName);
            $value = $attribute->nodeValue ?? '';

            if (! in_array($name, $allowed, true)) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            if ($name === 'style' && ! self::styleIsSafe($value)) {
                $element->removeAttribute('style');

                continue;
            }

            if (in_array($name, ['href', 'src'], true) && ! self::urlIsSafe($value, $tag)) {
                $element->removeAttribute($attribute->nodeName);
            }
        }

        // An external link opened in a new tab can reach back through
        // window.opener without this.
        if ($tag === 'a' && $element->getAttribute('target') === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }

        if ($tag === 'iframe' && ! $element->hasAttribute('src')) {
            $element->parentNode?->removeChild($element);
        }
    }

    /**
     * javascript:, vbscript: and data: are the ways a URL becomes code. Anything
     * else with a scheme must be http, https, mailto or tel; anything without one
     * is a path on this site, which is fine.
     */
    private static function urlIsSafe(string $url, string $tag): bool
    {
        // Entities, tabs and newlines inside "java&#09;script:" are how filters
        // that only look at the first characters get walked past.
        $normalised = strtolower(trim(html_entity_decode($url, ENT_QUOTES, 'UTF-8')));
        $normalised = preg_replace('/[\x00-\x20]+/', '', $normalised) ?? '';

        foreach (['javascript:', 'vbscript:', 'data:', 'file:'] as $scheme) {
            if (str_starts_with($normalised, $scheme)) {
                return false;
            }
        }

        if ($tag === 'iframe') {
            $host = strtolower((string) parse_url(trim($url), PHP_URL_HOST));

            return in_array($host, self::IFRAME_HOSTS, true);
        }

        if (! preg_match('/^[a-z][a-z0-9+.-]*:/', $normalised)) {
            return true;   // relative, anchor or protocol-relative path
        }

        return (bool) preg_match('/^(https?|mailto|tel):/', $normalised);
    }

    /** Alignment and colour are fine; anything that can fetch or run is not. */
    private static function styleIsSafe(string $style): bool
    {
        $flat = strtolower(preg_replace('/\s+/', '', $style) ?? '');

        foreach (['url(', 'expression', 'javascript:', 'behavior', '@import', 'binding'] as $needle) {
            if (str_contains($flat, $needle)) {
                return false;
            }
        }

        return true;
    }
}
