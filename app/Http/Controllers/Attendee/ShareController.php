<?php

namespace App\Http\Controllers\Attendee;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Services\ShareKit;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * What somebody posts after they have registered.
 *
 * Word of mouth is how most people find this fair, and the thing that decides
 * whether anybody actually posts is whether the caption already says what they
 * would have wanted to say. So this hands them both halves — the picture and
 * the words — and gets out of the way.
 */
class ShareController extends Controller
{
    public function show(): View
    {
        $registration = $this->attendee();
        $kit = ShareKit::for($registration);

        return view('attendee.share', [
            'navKey' => null,
            'title' => __('share.title').' — '.config('nextstep.event.name'),
            'registration' => $registration,
            'kit' => $kit,
            'caption' => $kit->caption(),
            'targets' => $kit->targets(),
        ]);
    }

    /**
     * The artwork itself, as a web page.
     *
     * The finished PNGs under `public/assets/share` are screenshots of this, so
     * this route is where a card is designed and checked. It is also the honest
     * fallback: if the images have not been generated for a build, this still
     * renders and can be screenshotted.
     */
    public function card(string $variant, string $format): View
    {
        if (! in_array($variant, ['student', 'parent', 'delegate'], true)) {
            throw new NotFoundHttpException;
        }

        if (! in_array($format, ShareKit::renderedFormats(), true)) {
            throw new NotFoundHttpException;
        }

        return view('share.card', [
            'variant' => $variant,
            'format' => $format,
            'locale' => app()->getLocale(),
            'line' => __("share.card.lines.{$variant}"),
            // The conference is cobalt, the fair magenta — the two are told
            // apart at thumbnail size by colour alone.
            'accent' => $variant === 'delegate' ? '#2c4be0' : '#b64698',
        ]);
    }

    /**
     * Where a shared link lands.
     *
     * Open to anybody — the whole point is that it is opened by people who have
     * never been here. It carries no ticket and names nobody: what is shared is
     * that somebody is going, not who.
     */
    public function attending(string $who = 'student'): View
    {
        if (! in_array($who, ['student', 'parent', 'delegate'], true)) {
            throw new NotFoundHttpException;
        }

        $locale = app()->getLocale();

        return view('share.attending', [
            'who' => $who,
            'title' => __('share.landing.title'),
            'description' => __("share.landing.lines.{$who}"),
            // 1200×630, which is what LinkedIn and Facebook draw in a feed. The
            // square card would be cropped to a strip.
            'ogImage' => asset("assets/share/{$locale}-{$who}-og.png"),
            'card' => asset("assets/share/{$locale}-{$who}-feed.png"),
        ]);
    }

    private function attendee(): Registration
    {
        /** @var Registration $registration */
        $registration = Auth::guard('attendee')->user();

        return $registration;
    }
}
