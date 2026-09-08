<?php

namespace App\Services;

use App\Models\Registration;
use Illuminate\Support\Collection;

/**
 * "I'm attending Next Step Fair 2026" — the words, the picture and the buttons.
 *
 * The point of this is reach the fair does not have to pay for, and the thing
 * that decides whether anybody actually posts is whether the caption already
 * says what they would have wanted to say. So the caption is written for the
 * person, not for the fair: a grade 12 student is choosing a life, a parent is
 * helping somebody else choose one, a director general is representing an
 * institution. One generic line would be shared by none of them.
 *
 * Nothing here carries the badge QR. That code is the entry credential — posted
 * to a public feed it is a free pass for whoever screenshots it first.
 */
class ShareKit
{
    /** Feed post: square, what Instagram and Facebook want. */
    public const FORMAT_FEED = 'feed';

    /** Story: 9:16, what Instagram and WhatsApp status want. */
    public const FORMAT_STORY = 'story';

    /**
     * The preview LinkedIn and Facebook draw from a posted link: 1200×630.
     *
     * Not offered as a download — nobody posts this one by hand. It is what the
     * platform fetches when it unfurls the URL, and without it a shared link
     * shows the site's logo on a white square, which looks like nothing.
     */
    public const FORMAT_OG = 'og';

    public function __construct(private readonly Registration $registration) {}

    public static function for(Registration $registration): self
    {
        return new self($registration);
    }

    /**
     * Which set of words this person gets.
     *
     * Everything on the fair track that is not a student or a parent is somebody
     * who walked in, and the plainest wording suits them best.
     */
    public function audience(): string
    {
        $type = $this->registration->type;

        $known = [
            Registration::TYPE_STUDENT,
            Registration::TYPE_PARENT,
            Registration::TYPE_GOVERNMENT,
            Registration::TYPE_OFFICIAL,
            Registration::TYPE_PRIVATE,
            Registration::TYPE_INDIVIDUAL,
        ];

        return in_array($type, $known, true) ? $type : Registration::TYPE_INDIVIDUAL;
    }

    /**
     * Which artwork this person gets.
     *
     * Three cards cover six audiences: a student, somebody bringing one, and a
     * delegate. Beyond that the picture stops changing in any way a viewer would
     * notice, and every extra variant is another file to keep in step in three
     * languages.
     */
    public function cardVariant(): string
    {
        return match ($this->audience()) {
            Registration::TYPE_STUDENT => 'student',
            Registration::TYPE_PARENT => 'parent',
            default => 'delegate',
        };
    }

    /** The one line printed under the event name on the card. */
    public function cardLine(): string
    {
        return __("share.card.lines.{$this->cardVariant()}");
    }

    /** The pre-rendered artwork, in the language of the page. */
    public function image(string $format, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return asset("assets/share/{$locale}-{$this->cardVariant()}-{$format}.png");
    }

    /**
     * The caption, ready to paste.
     *
     * In the language of the page they are reading, not the one they registered
     * in. Somebody who has switched the site to English is about to write an
     * English post, and being handed Kurdish text on an English page reads as a
     * fault rather than a kindness. The switcher in the header changes both.
     */
    public function caption(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return trim(__("share.captions.{$this->audience()}", [
            'name' => $this->registration->firstName(),
            'dates' => ns_event_dates(),
            'venue' => config('nextstep.event.venue.name'),
            'city' => config('nextstep.event.venue.city'),
            'url' => route('attending', ['locale' => $locale, 'who' => $this->cardVariant()]),
            'tags' => $this->hashtags($locale),
        ], $locale));
    }

    /**
     * The tag line, ready to paste.
     *
     * A hash is a neutral character, so on a Kurdish or Arabic line the browser
     * hands the leading one to the paragraph's own direction and the tags come
     * out as "NextStepFair #NextStep2026 #Kurdistan#". A left-to-right mark in
     * front settles it. It is invisible, it survives a copy and paste, and the
     * platforms the caption is pasted into leave it alone.
     */
    public function hashtags(?string $locale = null): string
    {
        $tags = collect(config('nextstep.share.hashtags'))
            ->map(fn (string $tag) => '#'.ltrim($tag, '#'))
            ->implode(' ');

        $locale ??= app()->getLocale();

        return in_array($locale, ['ku', 'ar'], true) ? "\u{200E}".$tags : $tags;
    }

    /**
     * The link that goes in the post.
     *
     * Tagged, so the team can see what sharing is actually worth next to the
     * paid channels — and never the ticket URL, which is personal.
     */
    public function url(?string $locale = null): string
    {
        return route('attending', array_filter([
            'who' => $this->cardVariant(),
            'locale' => $locale ?: app()->getLocale(),
            'utm_source' => 'attendee_share',
            'utm_medium' => 'social',
            'utm_campaign' => 'next_step_'.config('nextstep.event.year'),
        ]));
    }

    /**
     * Where the buttons go.
     *
     * Facebook, LinkedIn and WhatsApp take a link and pre-fill the post.
     * Instagram has no such thing — you cannot open a composer from the web —
     * so it is handled by downloading the picture and posting it, which is what
     * the page tells people to do rather than pretending a button will work.
     *
     * @return Collection<int, array{key: string, label: string, url: string}>
     */
    public function targets(?string $locale = null): Collection
    {
        $url = $this->url($locale);
        $caption = $this->caption($locale);

        return collect([
            [
                'key' => 'whatsapp',
                'label' => __('share.targets.whatsapp'),
                'url' => 'https://wa.me/?text='.rawurlencode($caption),
            ],
            [
                'key' => 'facebook',
                'label' => __('share.targets.facebook'),
                'url' => 'https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($url),
            ],
            [
                'key' => 'linkedin',
                'label' => __('share.targets.linkedin'),
                'url' => 'https://www.linkedin.com/sharing/share-offsite/?url='.rawurlencode($url),
            ],
        ]);
    }

    /**
     * The shapes a person is offered to post.
     *
     * The link preview is not among them: it is fetched by the platform, never
     * chosen by anybody.
     *
     * @return list<string>
     */
    public static function formats(): array
    {
        return [self::FORMAT_FEED, self::FORMAT_STORY];
    }

    /**
     * Where the person's name is drawn on the finished card, in image pixels.
     *
     * The bottom corner opposite the handle. It used to be the top corner, until
     * the partnership marks came up to the top row at the size a strategic
     * partner is due and took that space; down here the name sits on the line
     * with @nextstepfair, which is where a signature belongs anyway.
     *
     * `x` is the inset from the outer edge and `y` the baseline, so the same two
     * numbers work in Kurdish and Arabic, where the card is mirrored.
     *
     * Absolute rather than tied to the layout, so the browser drawing it and the
     * card behind it cannot drift apart — and one definition, read by both the
     * page and the script.
     *
     * The name goes on in the browser rather than being baked in. It has to:
     * there are as many names as there are people, and a Kurdish or Arabic one
     * needs contextual shaping and bidi that a browser does correctly and PHP's
     * image libraries do not. It also means nothing personal is ever written to
     * disk on the server.
     *
     * @return array{width: int, height: int, x: int, y: int, size: int, max: int}
     */
    public static function nameSlot(string $format): array
    {
        return match ($format) {
            // The baselines are the handle's own, measured in the browser, and
            // the widths are what is left on that line beside it.
            self::FORMAT_STORY => ['width' => 1080, 'height' => 1920, 'x' => 92, 'y' => 1757, 'size' => 34, 'max' => 320],
            self::FORMAT_OG => ['width' => 1200, 'height' => 630, 'x' => 64, 'y' => 568, 'size' => 24, 'max' => 380],
            default => ['width' => 1080, 'height' => 1080, 'x' => 84, 'y' => 981, 'size' => 30, 'max' => 400],
        };
    }

    /** What is drawn there: their name, and nothing that identifies the ticket. */
    public function displayName(): string
    {
        return trim((string) $this->registration->full_name);
    }

    /** Every shape that gets rendered, including the one nobody picks. */
    public static function renderedFormats(): array
    {
        return [self::FORMAT_FEED, self::FORMAT_STORY, self::FORMAT_OG];
    }

    /**
     * Where a shared link goes.
     *
     * Not the home page. A link to the home page unfurls as the generic site
     * card, and lands somebody who was told "I'm attending" on a page that says
     * nothing about that. This one carries the same artwork in the preview and
     * opens with the reason they clicked.
     */
    public function landingUrl(?string $locale = null): string
    {
        return route('attending', array_filter([
            'locale' => $locale ?: app()->getLocale(),
            'who' => $this->cardVariant(),
            'utm_source' => 'attendee_share',
            'utm_medium' => 'social',
            'utm_campaign' => 'next_step_'.config('nextstep.event.year'),
        ]));
    }
}
