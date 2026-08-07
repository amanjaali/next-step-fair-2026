<?php

/**
 * Meta WhatsApp Business Cloud API.
 *
 * The `log` driver writes messages to the delivery log without calling Meta, which
 * is what runs until the production credentials and approved templates land. Switch
 * WHATSAPP_DRIVER=cloud_api once the templates below are approved.
 */
return [

    'driver' => env('WHATSAPP_DRIVER', 'log'),

    'cloud_api' => [
        'base_url' => env('WHATSAPP_BASE_URL', 'https://graph.facebook.com/v21.0'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'token' => env('WHATSAPP_TOKEN'),
        'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
        'timeout' => 20,
    ],

    // Retry schedule for the outbound queue, in seconds.
    'retry_backoff' => [60, 300, 1800],
    'max_attempts' => 4,

    /*
    | Template names must match what Meta approved, exactly. Each one needs an
    | approved variant per language (en / ku / ar) before it can be sent — see
    | docs/whatsapp-templates.md for the submission process and lead times.
    */
    'templates' => [
        'otp' => 'next_step_otp',
        'registration_confirmed_student' => 'registration_confirmed_student',
        'registration_confirmed_parent' => 'registration_confirmed_parent',
        'registration_confirmed_visitor' => 'registration_confirmed_visitor',
        'event_reminder_3days' => 'event_reminder_3days',
        'event_reminder_1day' => 'event_reminder_1day',
        'day_of_directions' => 'day_of_directions',
        'session_reminder' => 'session_reminder_15min',
        'post_event_thankyou_survey' => 'post_event_thankyou_survey',
    ],

    // Meta language codes for template selection.
    'language_codes' => [
        'en' => 'en',
        'ku' => 'ku',
        'ar' => 'ar',
    ],

    'otp' => [
        'length' => 6,
        'ttl_minutes' => 10,
        'max_attempts' => 5,
        'resend_cooldown_seconds' => 60,
        // Fallback channel when WhatsApp delivery fails.
        'sms_fallback' => env('OTP_SMS_FALLBACK', false),
    ],
];
