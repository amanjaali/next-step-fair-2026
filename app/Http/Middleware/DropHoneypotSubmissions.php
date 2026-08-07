<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Quietly drops form posts that filled in the hidden decoy field.
 *
 * This used to be a validation rule (`size:0`) on a field named `company`, and
 * it did real damage: browsers and password managers autofill anything called
 * "company", so people registering for the fair were stopped with
 * "The company field must be 0 characters" — an error about a field they could
 * not see and had never typed in.
 *
 * Two things fix that. The field is now named `ns_hp`, which nothing autofills,
 * and it is hidden with `display: none` rather than parked off-screen where
 * autofill still reaches it. And a trip is no longer a validation failure: the
 * request goes back the way it came with nothing said. A bot learns nothing
 * about which field gave it away, and in the unlikely event a real person trips
 * it, they see their form again rather than an accusation.
 */
class DropHoneypotSubmissions
{
    public const FIELD = 'ns_hp';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('post') && filled($request->input(self::FIELD))) {
            return back();
        }

        return $next($request);
    }
}
