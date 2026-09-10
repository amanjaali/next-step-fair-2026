<?php

namespace App\Providers;

use App\Models\Registration;
use App\Models\Setting;
use App\Services\Messaging\CloudApiWhatsAppGateway;
use App\Services\Messaging\Contracts\WhatsAppGateway;
use App\Services\Messaging\LogWhatsAppGateway;
use App\Services\Messaging\OtpiqWhatsAppGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
         * The WhatsApp gateway is swapped by config, not by code. `log` records
         * every message in the delivery log without calling anybody, which is
         * what runs until the templates are approved and the keys arrive — and
         * it stays the default, so a missing setting cannot start sending real
         * messages to real people by accident.
         */
        $this->app->bind(WhatsAppGateway::class, function () {
            return match (config('whatsapp.driver')) {
                'otpiq' => $this->app->make(OtpiqWhatsAppGateway::class),
                'cloud_api' => $this->app->make(CloudApiWhatsAppGateway::class),
                default => $this->app->make(LogWhatsAppGateway::class),
            };
        });
    }

    /**
     * Event facts edited in the dashboard, laid over the config at boot.
     *
     * The name, the dates and the venue are cited in more than a hundred places:
     * badges, WhatsApp messages, the calendar file, the structured data Google
     * reads. Overlaying them here rather than reading a setting at each call site
     * means one edit corrects all of them, instead of correcting the home page
     * and leaving the badge stale.
     */
    private function applyEditedEventFacts(): void
    {
        try {
            $edited = Setting::get('event_overrides', []);
        } catch (\Throwable $e) {
            // No settings table yet: a fresh install part-way through migrating.
            return;
        }

        if (! is_array($edited) || $edited === []) {
            return;
        }

        $paths = [
            'name' => 'nextstep.event.name',
            'edition_label' => 'nextstep.event.edition_label',
            'start_date' => 'nextstep.event.start_date',
            'end_date' => 'nextstep.event.end_date',
            'opening_hours' => 'nextstep.event.opening_hours',
            'venue_name' => 'nextstep.event.venue.name',
            'venue_city' => 'nextstep.event.venue.city',
        ];

        foreach ($paths as $key => $path) {
            $value = trim((string) ($edited[$key] ?? ''));

            if ($value !== '') {
                config([$path => $value]);
            }
        }
    }

    public function boot(): void
    {
        $this->applyEditedEventFacts();

        /*
         * Follow the address the site is actually served at, not the environment
         * name — except locally, where `php artisan serve` is plain HTTP only.
         *
         * This used to force https for anything running in production. On a
         * server without a certificate that points every stylesheet and script at
         * port 443, which is closed — so the browser gets nothing and renders the
         * page as raw unstyled HTML while the HTML itself returns 200. Reading
         * APP_URL means the site is https the moment the certificate exists and
         * APP_URL says so, and plain http until then.
         *
         * Local keeps HTTP even when APP_URL is copied from production as https,
         * otherwise every asset request becomes an unsupported SSL handshake
         * against the built-in server.
         */
        if (! $this->app->environment('local') && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        /*
         * Language-neutral pages — ticket verification, badge downloads, the
         * scanner — still render the site chrome, which links into the localised
         * tree. Seeding the default here means route('home') resolves there too;
         * SetLocale overrides it for every request under /{locale}/.
         */
        URL::defaults(['locale' => config('app.locale')]);

        $fallback = config('app.locale');
        View::share('locale', $fallback);
        View::share('localeConfig', config("nextstep.locales.{$fallback}"));
        View::share('dir', config("nextstep.locales.{$fallback}.dir"));
        View::share('isRtl', false);

        $this->configureRateLimiters();
    }

    /**
     * Form endpoints are rate limited per IP and, where we have one, per phone
     * number — a single mobile connection at a school can carry a whole class.
     */
    private function configureRateLimiters(): void
    {
        RateLimiter::for('registration', fn(Request $request) => [
            Limit::perMinute(6)->by($request->ip()),
            Limit::perDay(40)->by($request->ip()),
        ]);

        // The route parameter is the ticket id, so a flood against one ticket is
        // limited separately from a flood from one address.
        RateLimiter::for('otp', fn(Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perMinute(6)->by('otp:' . (string) $request->route('registration')),
        ]);

        // Sign-in sends a real WhatsApp message per attempt, and the phone number
        // is the only credential — so throttle the number as well as the address.
        RateLimiter::for('otp-signin', fn(Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perHour(15)->by($request->ip()),
            Limit::perMinute(3)->by('signin:' . Registration::phoneHash((string) $request->input('phone'))),
        ]);

        RateLimiter::for('leads', fn(Request $request) => Limit::perMinute(4)->by($request->ip()));

        RateLimiter::for('checkin-scan', fn(Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));
    }
}
