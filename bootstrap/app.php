<?php

use App\Http\Middleware\DropHoneypotSubmissions;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'locale' => SetLocale::class,
        ]);

        // Every public form carries the same hidden decoy field, so the check
        // belongs here rather than repeated in nine validators.
        $middleware->web(append: [DropHoneypotSubmissions::class]);

        // Badge downloads and the scanner are served over HTTPS in production.
        $middleware->trustProxies(at: '*');

        /*
         * Three guarded areas, three front doors: staff scan tickets at /checkin,
         * universities work their leads under /{locale}/portal, and attendees manage
         * their own registration under /{locale}/me. Sending any of the three to
         * another's login is a dead end, so the path decides.
         */
        $middleware->redirectGuestsTo(function (Request $request) {
            return match (true) {
                $request->is('checkin*') => route('checkin.login'),
                $request->is('*/portal*') => route('portal.signin'),
                default => route('attendee.signin'),
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->is('checkin/*') && $request->expectsJson(),
        );
    })->create();
