<?php

namespace App\Providers;

use App\Models\Registration;
use App\Services\Messaging\CloudApiWhatsAppGateway;
use App\Services\Messaging\Contracts\WhatsAppGateway;
use App\Services\Messaging\LogWhatsAppGateway;
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
         * every message in the delivery log without calling Meta, which is what
         * runs until the templates are approved and the credentials arrive.
         */
        $this->app->bind(WhatsAppGateway::class, function () {
            return config('whatsapp.driver') === 'cloud_api'
                ? $this->app->make(CloudApiWhatsAppGateway::class)
                : $this->app->make(LogWhatsAppGateway::class);
        });
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
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
        RateLimiter::for('registration', fn (Request $request) => [
            Limit::perMinute(6)->by($request->ip()),
            Limit::perDay(40)->by($request->ip()),
        ]);

        // The route parameter is the ticket id, so a flood against one ticket is
        // limited separately from a flood from one address.
        RateLimiter::for('otp', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perMinute(6)->by('otp:'.(string) $request->route('registration')),
        ]);

        // Sign-in sends a real WhatsApp message per attempt, and the phone number
        // is the only credential — so throttle the number as well as the address.
        RateLimiter::for('otp-signin', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perHour(15)->by($request->ip()),
            Limit::perMinute(3)->by('signin:'.Registration::phoneHash((string) $request->input('phone'))),
        ]);

        RateLimiter::for('leads', fn (Request $request) => Limit::perMinute(4)->by($request->ip()));

        RateLimiter::for('checkin-scan', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));
    }
}
