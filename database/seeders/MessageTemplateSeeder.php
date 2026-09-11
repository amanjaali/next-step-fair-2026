<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

/**
 * One row per template, per channel, per language.
 *
 * The bodies come from lang/{locale}/notifications.php so the copy submitted to
 * Meta for approval and the copy the log driver renders are the same text.
 * `approval_status` stays `pending` until Meta approves the matching template.
 */
class MessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $locales = array_keys(config('nextstep.locales'));

        $whatsapp = [
            'otp' => ['code'],
            'rsvp_confirmed' => ['name'],
            'registration_confirmed_student' => ['name'],
            'registration_confirmed_parent' => ['name'],
            // The visitor pass confirms through the same path, so it needs its own
            // template — without one the message body renders as the lookup key.
            'registration_confirmed_visitor' => ['name', 'ticket'],
            'event_reminder_3days' => [],
            'event_reminder_1day' => [],
            'day_of_directions' => ['name'],
            'session_reminder' => ['title', 'hall'],
            'post_event_thankyou_survey' => ['name', 'link'],
        ];

        foreach ($whatsapp as $key => $variables) {
            foreach ($locales as $locale) {
                MessageTemplate::updateOrCreate(
                    ['key' => $key, 'channel' => 'whatsapp', 'locale' => $locale],
                    [
                        'name' => ucwords(str_replace('_', ' ', $key)),
                        'body' => __("notifications.whatsapp.$key", [], $locale),
                        'meta_template_name' => config("whatsapp.templates.$key"),
                        'variables' => $variables,
                        'approval_status' => 'pending',
                        'active' => true,
                    ]
                );
            }
        }

        $email = [
            'rsvp_confirmed' => ['subject' => 'rsvp_subject', 'body' => 'rsvp_confirmed'],
            'rsvp_pending' => ['subject' => 'rsvp_subject_pending', 'body' => 'rsvp_pending'],
            'reminder_3days' => ['subject' => 'reminder_subject_3days', 'body' => 'rsvp_confirmed'],
            'reminder_1day' => ['subject' => 'reminder_subject_1day', 'body' => 'rsvp_confirmed'],
            'thankyou' => ['subject' => 'thankyou_subject', 'body' => 'rsvp_confirmed'],
            'badge_resent' => ['subject' => 'badge_resent_subject', 'body' => 'rsvp_badge_note'],
        ];

        foreach ($email as $key => $keys) {
            foreach ($locales as $locale) {
                MessageTemplate::updateOrCreate(
                    ['key' => $key, 'channel' => 'email', 'locale' => $locale],
                    [
                        'name' => ucwords(str_replace('_', ' ', $key)),
                        'subject' => __("notifications.email.{$keys['subject']}", [], $locale),
                        'body' => __("notifications.email.{$keys['body']}", [], $locale),
                        'variables' => ['name', 'title', 'organization', 'ticket'],
                        // E-mail needs no third-party approval.
                        'approval_status' => 'approved',
                        'approved_at' => now(),
                        'active' => true,
                    ]
                );
            }
        }
    }
}
