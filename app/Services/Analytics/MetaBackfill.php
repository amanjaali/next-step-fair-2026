<?php

namespace App\Services\Analytics;

use App\Models\Registration;
use App\Models\ScholarshipApplication;
use Illuminate\Support\Carbon;

/**
 * Sends the Conversions API copy of recent events that happened before the
 * token was set. Meta accepts events up to 7 days old; the fixed event IDs
 * mean anything it already has (from the Pixel, or an earlier run) is not
 * counted twice.
 */
class MetaBackfill
{
    public function __construct(private readonly MetaConversionsClient $client) {}

    /** @return array{ok: bool, submissions: int, registrations: int, received: int, error: ?string} */
    public function run(): array
    {
        // A little inside Meta's 7-day window, so nothing is rejected as too old.
        $since = now()->subDays(7)->addMinutes(15);

        $submissions = ScholarshipApplication::query()
            ->with('registration')
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', $since)
            ->get()
            ->filter(fn (ScholarshipApplication $a) => $a->registration)
            ->map(fn (ScholarshipApplication $a) => MetaEvents::event(
                'SubmitApplication',
                'app_'.$a->id,
                $a->submitted_at->getTimestamp(),
                route('scholarship.apply.form', ['locale' => $a->registration->locale ?: 'en']),
                $this->userData($a->registration),
                ['track' => 'scholarship'],
            ));

        $registrations = Registration::query()
            ->where('track', Registration::TRACK_FAIR)
            // Only people who registered online, as the live event counts: not desk
            // walk-ins or gate visitor passes.
            ->where('is_walk_in', false)
            ->whereNull('created_by')
            ->where('type', '!=', Registration::TYPE_VISITOR)
            ->whereNotNull('confirmed_at')
            ->where('confirmed_at', '>=', $since)
            ->get()
            ->filter(fn (Registration $r) => $r->badgeIssued())
            ->map(fn (Registration $r) => MetaEvents::event(
                'CompleteRegistration',
                'reg_'.$r->id,
                Carbon::parse($r->confirmed_at)->getTimestamp(),
                route('register.fair', ['locale' => $r->locale ?: 'en']),
                $this->userData($r),
                ['track' => 'fair', 'type' => $r->type, 'value' => 0, 'currency' => 'IQD'],
            ));

        $events = $submissions->concat($registrations)->values()->all();

        if ($events === []) {
            return ['ok' => true, 'submissions' => 0, 'registrations' => 0, 'received' => 0, 'error' => null];
        }

        $result = $this->client->send($events);

        return [
            'ok' => $result['ok'],
            'submissions' => $submissions->count(),
            'registrations' => $registrations->count(),
            'received' => $result['received'],
            'error' => $result['error'],
        ];
    }

    /** @return array<string, mixed> */
    private function userData(Registration $registration): array
    {
        // The IP and browser saved when they registered: the closest we have.
        return MetaEvents::userData($registration, $registration->ip_address, $registration->user_agent);
    }
}
