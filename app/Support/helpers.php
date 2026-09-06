<?php

use App\Models\Setting;
use App\Support\Html;
use Illuminate\Support\Carbon;

if (! function_exists('ns_alternate_url')) {
    /**
     * The current URL rewritten into another language, for hreflang tags and the
     * language switcher. Query strings are preserved.
     */
    function ns_alternate_url(string $locale): string
    {
        $supported = array_keys(config('nextstep.locales'));
        $segments = request()->segments();

        if (isset($segments[0]) && in_array($segments[0], $supported, true)) {
            $segments[0] = $locale;
        } else {
            array_unshift($segments, $locale);
        }

        $url = url(implode('/', $segments));

        return ($query = request()->getQueryString()) ? $url.'?'.$query : $url;
    }
}

if (! function_exists('ns_saved_session_ids')) {
    /**
     * Session ids the signed-in attendee has saved, resolved once per request.
     *
     * The agenda renders forty session rows; asking the relation per row would be
     * forty queries for one answer.
     */
    function ns_saved_session_ids(): array
    {
        static $ids = null;

        if ($ids === null) {
            $attendee = auth('attendee')->user();
            $ids = $attendee ? $attendee->savedSessions()->pluck('event_sessions.id')->all() : [];
        }

        return $ids;
    }
}

if (! function_exists('ns_month')) {
    /**
     * Month name in the current language, from lang/{locale}/site.php.
     *
     * Not Carbon's: its `ku` locale is Kurmanji written in Latin script, which is
     * unreadable beside Sorani body copy, and its `ar` locale returns the Gulf
     * names (سبتمبر) where Iraq writes أيلول.
     */
    function ns_month(int $month): string
    {
        return __('site.months.'.$month);
    }
}

if (! function_exists('ns_format_date')) {
    /** "28 September 2026" in English, and the local month name in KU and AR. */
    function ns_format_date(Carbon $date, bool $withYear = true): string
    {
        return $date->format('j').' '.ns_month((int) $date->format('n')).($withYear ? ' '.$date->format('Y') : '');
    }
}

if (! function_exists('ns_event_dates')) {
    /**
     * "28–30 September 2026" — an en dash, and Latin numerals in every language.
     */
    function ns_event_dates(): string
    {
        $start = Carbon::parse(config('nextstep.event.start_date'));
        $end = Carbon::parse(config('nextstep.event.end_date'));

        return $start->format('j').'–'.ns_format_date($end);
    }
}

if (! function_exists('ns_days_until')) {
    /**
     * Days until the fair opens, floored at zero once it has started.
     */
    function ns_days_until(): int
    {
        $start = Carbon::parse(config('nextstep.event.start_date'), config('nextstep.event.timezone'))->startOfDay();

        return max(0, (int) ceil(Carbon::now(config('nextstep.event.timezone'))->startOfDay()->diffInDays($start, false)));
    }
}

if (! function_exists('ns_track_accent')) {
    /**
     * The wayfinding colour for a track: magenta for the fair, cobalt for the
     * conference. Used on badges, chips and section rules.
     */
    function ns_track_accent(?string $track): string
    {
        return $track === 'conference'
            ? config('nextstep.tracks.conference.accent')
            : config('nextstep.tracks.fair.accent');
    }
}

if (! function_exists('ns_day_date')) {
    /**
     * Formatted date of an event day number, e.g. 1 => "28 September".
     */
    function ns_day_date(int $day, string $format = 'j F'): string
    {
        $date = config("nextstep.event.days.$day.date");

        if (! $date) {
            return '';
        }

        return ns_format_date(Carbon::parse($date), str_contains($format, 'Y'));
    }
}

if (! function_exists('ns_reading_time')) {
    /**
     * Reading-time estimate at 200 words a minute, minimum one minute.
     */
    function ns_reading_time(?string $html): int
    {
        $words = str_word_count(strip_tags((string) $html));

        // Arabic-script text does not tokenise with str_word_count; fall back to
        // whitespace splitting so Kurdish and Arabic posts get a sane estimate.
        if ($words === 0) {
            $words = count(preg_split('/\s+/u', trim(strip_tags((string) $html))) ?: []);
        }

        return max(1, (int) ceil($words / 200));
    }
}

if (! function_exists('ns_mask_phone')) {
    /**
     * "770 ••• 2288" — enough for the registrant to recognise their own number
     * without printing it in full on a shared screen.
     */
    function ns_mask_phone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (strlen($digits) < 6) {
            return $digits;
        }

        return substr($digits, 0, 3).' ••• '.substr($digits, -4);
    }
}

if (! function_exists('ns_uploaded')) {
    /** A path stored on the public disk, as a URL that works on any hostname. */
    function ns_uploaded(?string $path): ?string
    {
        return filled($path) ? '/storage/'.ltrim($path, '/') : null;
    }
}

if (! function_exists('ns_rich')) {
    /**
     * Editor-written HTML, filtered before it is printed unescaped.
     *
     * Every `{!! !!}` that prints content from the dashboard goes through this.
     * See App\Support\Html for what survives and why.
     */
    function ns_rich(?string $html): string
    {
        return Html::clean($html);
    }
}

if (! function_exists('ns_home')) {
    /**
     * A piece of home page copy, as edited in the dashboard.
     *
     * Falls through to the translation file when the team has not overridden it,
     * so the page reads exactly as shipped until somebody changes something —
     * and a language they have not filled in still shows the original rather
     * than an empty heading.
     */
    function ns_home(string $key, array $replace = []): string
    {
        $content = Setting::get('home_content', []);
        $value = trim((string) ($content[$key][app()->getLocale()] ?? ''));

        if ($value === '') {
            return __('site.home.'.$key, $replace);
        }

        foreach ($replace as $token => $with) {
            $value = str_replace([':'.$token, ':'.ucfirst($token)], (string) $with, $value);
        }

        return $value;
    }
}

if (! function_exists('ns_home_counter')) {
    /** A figure on the home page counters bar, editable in the dashboard. */
    function ns_home_counter(string $key, int $default): int
    {
        $counters = Setting::get('counters', []);

        return (int) ($counters[$key] ?? $default);
    }
}

if (! function_exists('ns_image')) {
    /**
     * A photograph chosen in the dashboard, by slot.
     *
     * Returns null when nothing has been uploaded, which is what tells a view to
     * keep the grey frame or the black hero it was designed with — a missing
     * photograph is a considered default here, not a broken image.
     */
    function ns_image(string $slot): ?string
    {
        $images = Setting::get('site_images', []);
        $path = trim((string) ($images[$slot] ?? ''));

        return $path === '' ? null : '/storage/'.ltrim($path, '/');
    }
}

if (! function_exists('ns_cta')) {
    /**
     * A button label, as edited in the dashboard.
     *
     * Same rule as the rest of the home page content: empty means "as written".
     */
    function ns_cta(string $key, string $fallback): string
    {
        $content = Setting::get('home_content', []);
        $value = trim((string) ($content[$key][app()->getLocale()] ?? ''));

        return $value !== '' ? $value : __($fallback);
    }
}

if (! function_exists('ns_brand')) {
    /**
     * A logo or brand mark: the one uploaded in the dashboard, or the one the
     * site ships with.
     *
     * These sit in the header and the footer of every page, so a wrong or
     * missing file here is visible everywhere at once. Uploading a replacement
     * must never be able to leave a blank space: an empty slot falls straight
     * back to the file committed under public/assets.
     */
    function ns_brand(string $slot, string $shipped): string
    {
        $uploaded = trim((string) (Setting::get('brand_images', [])[$slot] ?? ''));

        return $uploaded !== '' ? '/storage/'.ltrim($uploaded, '/') : '/'.ltrim($shipped, '/');
    }
}
