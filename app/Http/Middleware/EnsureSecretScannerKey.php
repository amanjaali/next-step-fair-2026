<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the shared scanner URL behind SECRET_ROUTE_SCANNER_KEY.
 *
 * Wrong or missing keys 404 rather than 403 so the route is not advertised.
 * An empty configured key disables the route entirely.
 */
class EnsureSecretScannerKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('nextstep.scanner.secret_key');
        $provided = (string) $request->route('key');

        if ($expected === '' || $provided === '' || ! hash_equals($expected, $provided)) {
            abort(404);
        }

        return $next($request);
    }
}
