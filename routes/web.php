<?php

use App\Http\Controllers\BadgeLinkController;
use App\Http\Controllers\CaptchaController;
use App\Http\Controllers\Checkin\CheckinController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Registration\RegistrationDeskController;
use App\Http\Controllers\OtpiqWebhookController;
use App\Http\Controllers\QrCampaignController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

$locales = implode('|', array_keys(config('nextstep.locales')));

/*
|--------------------------------------------------------------------------
| Language-neutral routes
|--------------------------------------------------------------------------
| Ticket verification, badge downloads, the staff scanner and campaign QR
| short links sit outside the /{locale}/ tree: they are opened from a printed
| QR or an e-mail, where the language comes from the ticket, not the URL.
*/
Route::get('/', [LocaleController::class, 'root'])->name('root');
Route::get('lang/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

/*
 * The registration captcha picture.
 *
 * Language-neutral because the code in it is letters and digits, and outside
 * the locale tree so that switching language does not invalidate the challenge
 * somebody is halfway through typing. Throttled: it draws an image and writes a
 * session key, and neither should be free to ask for a thousand times a minute.
 */
Route::get('captcha.png', [CaptchaController::class, 'show'])
    ->middleware('throttle:40,1')
    ->name('captcha');

Route::get('verify/{ticket}', [TicketController::class, 'verify'])->name('ticket.verify');
Route::get('ticket/{ticket}/badge.png', [TicketController::class, 'png'])->name('ticket.png');
Route::get('ticket/{ticket}/badge.pdf', [TicketController::class, 'pdf'])->name('ticket.pdf');
Route::get('ticket/{ticket}/calendar.ics', [TicketController::class, 'ics'])->name('ticket.ics');
Route::get('ticket/{ticket}/qr.svg', [TicketController::class, 'qr'])->name('ticket.qr');

// Self-service RSVP management from the signed link in the confirmation e-mail.
Route::get('rsvp/{ticket}/manage', [TicketController::class, 'manage'])
    ->name('rsvp.manage')->middleware('signed');
Route::post('rsvp/{ticket}/cancel', [TicketController::class, 'cancel'])
    ->name('rsvp.cancel')->middleware('signed');

// Campaign QR codes: /q/{code} redirects to the target and records the scan.
Route::get('q/{code}', [QrCampaignController::class, 'redirect'])->name('qr.redirect');

/*
 * The badge link sent on WhatsApp. Short, because a URL button is approved with
 * a fixed address and given only the tail of one at send time.
 */
Route::get('b/{ticket}', BadgeLinkController::class)
    ->middleware('throttle:60,1')
    ->name('badge.link');

// Delivery-status callbacks from the WhatsApp Cloud API.
Route::match(['get', 'post'], 'webhooks/whatsapp', WhatsAppWebhookController::class)
    ->name('webhooks.whatsapp')->withoutMiddleware([ValidateCsrfToken::class]);

// The same, from OTPIQ, who report on their own sms id rather than Meta's.
Route::post('webhooks/otpiq', OtpiqWebhookController::class)
    ->name('webhooks.otpiq')->withoutMiddleware([ValidateCsrfToken::class]);

/*
|--------------------------------------------------------------------------
| Staff check-in PWA
|--------------------------------------------------------------------------
*/
Route::prefix('checkin')->name('checkin.')->group(function () {
    Route::get('login', [CheckinController::class, 'showLogin'])->name('login');
    Route::post('login', [CheckinController::class, 'login'])->name('login.attempt');

    Route::middleware(['auth', 'can:scan-tickets'])->group(function () {
        Route::get('/', [CheckinController::class, 'index'])->name('index');
        Route::post('scan', [CheckinController::class, 'scan'])->name('scan');
        Route::get('search', [CheckinController::class, 'search'])->name('search');
        Route::post('manual/{registration}', [CheckinController::class, 'manual'])->name('manual');
        Route::get('manifest.json', [CheckinController::class, 'manifest'])->name('manifest');
        Route::get('offline-manifest', [CheckinController::class, 'offlineManifest'])->name('offline');
        Route::post('sync', [CheckinController::class, 'sync'])->name('sync');
        Route::post('logout', [CheckinController::class, 'logout'])->name('logout');
    });
});
Route::get('checkin/sw.js', [CheckinController::class, 'serviceWorker'])->name('checkin.sw');

/*
|--------------------------------------------------------------------------
| Registration desk (volunteer walk-in registration)
|--------------------------------------------------------------------------
| A focused, walk-in-friendly screen for registration-desk volunteers: browse
| the pre-registered list, create walk-in accounts, edit a record, and cancel
| one within an hour of creating it. Deliberately not Filament — the desk
| moves fast during rush hours and doesn't need the full admin surface.
*/
Route::prefix('registration')->name('registration.')->group(function () {
    Route::get('login', [RegistrationDeskController::class, 'showLogin'])->name('login');
    Route::post('login', [RegistrationDeskController::class, 'login'])->name('login.attempt');

    Route::middleware(['auth', 'can:access-registration-desk'])->group(function () {
        Route::get('/', [RegistrationDeskController::class, 'index'])->name('index');
        Route::get('search', [RegistrationDeskController::class, 'search'])->name('search');
        Route::get('{registration}', [RegistrationDeskController::class, 'show'])->name('show');
        Route::post('/', [RegistrationDeskController::class, 'store'])->name('store');
        Route::put('{registration}', [RegistrationDeskController::class, 'update'])->name('update');
        Route::delete('{registration}', [RegistrationDeskController::class, 'destroy'])->name('destroy');
        Route::post('logout', [RegistrationDeskController::class, 'logout'])->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| Secret key-gated scanner
|--------------------------------------------------------------------------
| Same gate check-in UI as /checkin, opened via /s/{SECRET_ROUTE_SCANNER_KEY}
| on devices that should not hold a staff login. Wrong keys 404.
*/
Route::prefix('s/{key}')
    ->where(['key' => '[A-Za-z0-9_-]+'])
    ->middleware('scanner.key')
    ->name('scanner.')
    ->group(function () {
        Route::get('/', [CheckinController::class, 'index'])->name('index');
        Route::post('scan', [CheckinController::class, 'scan'])->name('scan');
        Route::get('search', [CheckinController::class, 'search'])->name('search');
        Route::post('manual/{registration}', [CheckinController::class, 'manual'])->name('manual');
        Route::get('offline-manifest', [CheckinController::class, 'offlineManifest'])->name('offline');
        Route::post('sync', [CheckinController::class, 'sync'])->name('sync');
    });

/*
|--------------------------------------------------------------------------
| The public site, one tree per language
|--------------------------------------------------------------------------
*/
Route::prefix('{locale}')
    ->whereIn('locale', array_keys(config('nextstep.locales')))
    ->middleware(SetLocale::class)
    ->group(base_path('routes/site.php'));

/*
| Last resort: a URL with no language prefix — /privacy, /agenda, /register/fair —
| is redirected into the visitor's language rather than 404ing. Must stay last;
| Route::fallback only fires when nothing else matched.
*/
Route::fallback([LocaleController::class, 'fallback']);
