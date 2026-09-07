<?php

namespace App\Observers;

use App\Models\AttendeeNotification;
use App\Models\ScholarshipApplication;

/**
 * Every committee change, told to the student it is about.
 *
 * Watched on the model rather than wired into the dashboard screens, because
 * there are four ways an application moves — the review form, the "move on"
 * button, the bulk screening action, and a command — and a student who hears
 * about three of them has an account that cannot be trusted.
 */
class ScholarshipApplicationObserver
{
    public function updated(ScholarshipApplication $application): void
    {
        if (! $application->registration_id) {
            return;
        }

        /*
         * The decision comes first and on its own. When a committee sets the
         * stage and the outcome in one save — which is the ordinary way of
         * recording it — "a decision has been made" and "you have been awarded"
         * are the same event, and sending both would make the second read as a
         * second, separate result.
         */
        if ($application->wasChanged('decision') && filled($application->decision)) {
            $this->tell($application, 'scholarship.decision', [
                'decision' => $application->decision,
            ]);

            return;
        }

        if (! $application->wasChanged('status')) {
            return;
        }

        /*
         * Submitting is the student's own action, and they are already looking
         * at the page that says so. A draft is not an application yet.
         */
        if (in_array($application->status, [
            ScholarshipApplication::STATUS_DRAFT,
            ScholarshipApplication::STATUS_SUBMITTED,
        ], true)) {
            return;
        }

        $this->tell($application, 'scholarship.stage', [
            'stage' => $application->status,
        ]);
    }

    /** @param array<string, string> $data */
    private function tell(ScholarshipApplication $application, string $type, array $data): void
    {
        AttendeeNotification::create([
            'registration_id' => $application->registration_id,
            'type' => $type,
            'data' => $data + ['cycle' => $application->cycle],
            // Straight to the application itself. An update that lands the
            // student on a general page, to find the change themselves, is the
            // reason people write to ask what it meant.
            'route' => 'scholarship.status',
        ]);
    }
}
