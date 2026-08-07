<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LocaleController extends Controller
{
    /**
     * Bare "/" — send the visitor to the language they last chose, or the best
     * match from their browser, before falling back to English.
     */
    public function root(Request $request): RedirectResponse
    {
        return redirect()->to('/'.$this->resolve($request));
    }

    /**
     * Switch language while staying on the same page.
     *
     * The switcher posts the current path so /en/agenda?day=2 becomes
     * /ku/agenda?day=2 rather than dumping the visitor back on the home page.
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $supported = array_keys(config('nextstep.locales'));
        abort_unless(in_array($locale, $supported, true), 404);

        $path = ltrim((string) $request->input('to', '/'), '/');
        $segments = explode('/', $path);

        if (in_array($segments[0] ?? '', $supported, true)) {
            array_shift($segments);
        }

        $target = '/'.trim($locale.'/'.implode('/', $segments), '/');

        if ($query = $request->input('query')) {
            $target .= '?'.ltrim($query, '?');
        }

        return redirect()->to($target)
            ->withCookie(cookie(SetLocale::COOKIE, $locale, 60 * 24 * 365));
    }

    /**
     * Anything that missed every route: try it again inside a language prefix.
     *
     * /privacy, /conference, /register/fair are all URLs people type, print on a
     * poster, or paste from a message with the prefix stripped. Sending those to a
     * 404 is a self-inflicted wound, so we resolve the visitor's language and
     * redirect permanently — but only when the prefixed path is a real route, so a
     * genuine typo still gets an honest 404 instead of a redirect loop.
     */
    public function fallback(Request $request): RedirectResponse
    {
        $path = trim($request->path(), '/');
        $supported = array_keys(config('nextstep.locales'));

        abort_if($path === '', 404);
        abort_if(in_array(explode('/', $path)[0], $supported, true), 404);

        $locale = $this->resolve($request);
        $target = $locale.'/'.$path;

        try {
            $matched = app('router')->getRoutes()->match(Request::create('/'.$target, 'GET'));
        } catch (HttpException $e) {
            abort(404);
        }

        // This very route matches everything, so a match on it means nothing real
        // was found — otherwise every typo would redirect instead of 404ing.
        abort_if($matched->isFallback, 404);

        return redirect()->to('/'.$target.($request->getQueryString() ? '?'.$request->getQueryString() : ''), 301);
    }

    /**
     * A first visit always lands in English.
     *
     * Only an explicit choice moves it: picking KU or AR in the switcher sets the
     * cookie, and every later visit honours that. Browser Accept-Language is
     * deliberately ignored — a phone sold in the region often reports Arabic
     * regardless of what its owner reads, so sniffing it sent first-time visitors
     * to a language they had not asked for.
     */
    private function resolve(Request $request): string
    {
        $supported = array_keys(config('nextstep.locales'));
        $cookie = $request->cookie(SetLocale::COOKIE);

        return is_string($cookie) && in_array($cookie, $supported, true)
            ? $cookie
            : config('app.locale', 'en');
    }
}
