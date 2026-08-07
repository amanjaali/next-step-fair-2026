<?php

namespace App\Services;

use App\Models\EventSession;
use App\Models\Registration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * .ics calendar files: one for a registrant's own days, one for the full agenda.
 */
class CalendarService
{
    public function forRegistration(Registration $registration): string
    {
        $days = $registration->isConference() ? [1] : ($registration->dayList() ?: [1, 2, 3]);
        $lines = $this->header();

        foreach ($days as $day) {
            $date = config("nextstep.event.days.$day.date");
            if (! $date) {
                continue;
            }

            $start = Carbon::parse($date.' 10:00', config('nextstep.event.timezone'));
            $end = Carbon::parse($date.' 20:00', config('nextstep.event.timezone'));

            $title = $registration->isConference()
                ? __('notifications.ics.conference_title', [], $registration->locale)
                : __('notifications.ics.fair_title', [], $registration->locale);

            $lines = array_merge($lines, $this->event(
                uid: $registration->ticket_id.'-day'.$day.'@nextstepfair.com',
                start: $start,
                end: $end,
                summary: $title.' — '.__('site.common.day', ['n' => $day], $registration->locale),
                description: __('notifications.ics.description', ['ticket' => $registration->ticket_ref], $registration->locale),
                location: config('nextstep.event.venue.address.en'),
            ));
        }

        return implode("\r\n", array_merge($lines, ['END:VCALENDAR']))."\r\n";
    }

    /** @param Collection<int, EventSession> $sessions */
    public function forSessions(Collection $sessions): string
    {
        $lines = $this->header();

        foreach ($sessions as $session) {
            $lines = array_merge($lines, $this->event(
                uid: 'session-'.$session->id.'@nextstepfair.com',
                start: $session->startsAtDateTime(),
                end: $session->endsAtDateTime(),
                summary: $session->t('title'),
                description: trim($session->t('description').' '.$session->t('who')),
                location: trim($session->hallLabel().', '.config('nextstep.event.venue.name')),
            ));
        }

        return implode("\r\n", array_merge($lines, ['END:VCALENDAR']))."\r\n";
    }

    public function downloadAgenda(Collection $sessions): StreamedResponse
    {
        $body = $this->forSessions($sessions);

        return response()->streamDownload(fn () => print ($body), 'next-step-fair-2026-agenda.ics', [
            'Content-Type' => 'text/calendar; charset=utf-8',
        ]);
    }

    private function header(): array
    {
        return [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Next Step Organization//Next Step Fair 2026//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.config('nextstep.event.name'),
            'X-WR-TIMEZONE:'.config('nextstep.event.timezone'),
        ];
    }

    private function event(string $uid, Carbon $start, Carbon $end, string $summary, string $description, string $location): array
    {
        return [
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$start->clone()->utc()->format('Ymd\THis\Z'),
            'DTEND:'.$end->clone()->utc()->format('Ymd\THis\Z'),
            'SUMMARY:'.$this->escape($summary),
            'DESCRIPTION:'.$this->escape($description),
            'LOCATION:'.$this->escape($location),
            'END:VEVENT',
        ];
    }

    /** RFC 5545 text escaping. */
    private function escape(string $value): string
    {
        return str_replace(
            ['\\', ';', ',', "\n", "\r"],
            ['\\\\', '\;', '\,', '\n', ''],
            trim(strip_tags($value))
        );
    }
}
