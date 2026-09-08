<?php

namespace App\Http\Controllers;

use App\Services\CaptchaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** Draws the registration captcha, and never caches it. */
class CaptchaController extends Controller
{
    public function show(Request $request, CaptchaService $captcha): Response
    {
        /*
         * This is a subresource, not a page somebody visited, and the session must
         * not remember it as one.
         *
         * StartSession writes the current URL into the session as "where they came
         * from" on its way out, after this method has run. The browser fetches the
         * picture last when the form loads, so that URL would be the picture — and
         * every `back()` from then on, including the redirect that carries the
         * "wrong code" message, would send the visitor to a PNG instead of the
         * form they were filling in. Saying plainly that this was a background
         * fetch is what the check for it is there to catch.
         */
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        return response($captcha->png(), 200, [
            'Content-Type' => 'image/png',
            // A cached captcha is one code for everybody, which is the one thing
            // it must never be.
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
