<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the request locale from the /{locale}/ URL segment.
 *
 * The chosen language is remembered in a year-long cookie so the switcher is
 * persistent across pages and visits, and registered as a URL default so every
 * route() call in a view stays inside the language the visitor is reading.
 */
class SetLocale
{
    public const COOKIE = 'ns_locale';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');
        $supported = array_keys(config('nextstep.locales'));

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);

        $config = config("nextstep.locales.{$locale}");

        /*
         * The locale segment has done its job. Dropping it from the route
         * parameters keeps controller signatures clean: a route like
         * /{locale}/news/{post} passes only the post to the controller.
         */
        $request->route()?->forgetParameter('locale');

        View::share('locale', $locale);
        View::share('localeConfig', $config);
        View::share('dir', $config['dir']);
        View::share('isRtl', $config['dir'] === 'rtl');

        $response = $next($request);

        // Refresh the preference on every page view so it never quietly expires.
        if ($request->cookie(self::COOKIE) !== $locale && method_exists($response, 'withCookie')) {
            $response->withCookie(Cookie::make(self::COOKIE, $locale, 60 * 24 * 365, null, null, null, false));
        }

        return $response;
    }
}
