<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schedule;

/*
 * Scheduled work. Point cron at:
 *   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
 */

// WhatsApp reminders, timed off the event dates in config/nextstep.php.
$start = Carbon::parse(config('nextstep.event.start_date'));

Schedule::command('nextstep:reminders three-days')
    ->dailyAt('10:00')
    ->timezone(config('nextstep.event.timezone'))
    ->when(fn () => now(config('nextstep.event.timezone'))->isSameDay($start->clone()->subDays(3)));

Schedule::command('nextstep:reminders one-day')
    ->dailyAt('17:00')
    ->timezone(config('nextstep.event.timezone'))
    ->when(fn () => now(config('nextstep.event.timezone'))->isSameDay($start->clone()->subDay()));

Schedule::command('nextstep:reminders day-of')
    ->dailyAt('08:00')
    ->timezone(config('nextstep.event.timezone'));

// Personal agenda reminders, during the event only.
Schedule::command('nextstep:session-reminders')
    ->everyFiveMinutes()
    ->timezone(config('nextstep.event.timezone'))
    ->between('08:00', '21:00');

// SEO and housekeeping.
Schedule::command('nextstep:sitemap')->dailyAt('03:00');
Schedule::command('queue:prune-batches --hours=48')->daily();
Schedule::command('model:prune')->daily();
