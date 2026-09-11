<?php

/**
 * WhatsApp delivery.
 *
 * Three drivers:
 *
 *   log        writes the fully rendered message to the delivery log and calls
 *              nobody. This is what runs until an account is live, and it is
 *              how the whole flow is checked end to end without spending a
 *              message or needing a public address.
 *   otpiq      OTPIQ, who hold the WhatsApp Business account for Iraq and
 *              Kurdistan and pass our sends to Meta. Templates are built and
 *              approved in their dashboard — see docs/whatsapp-otpiq.md.
 *   cloud_api  Meta directly, for an account we hold ourselves.
 *
 * Whichever is set, everything above the gateway is the same: the queue, the
 * retries, the delivery log and the admin's resend button.
 */
return [

    'driver' => env('WHATSAPP_DRIVER', 'log'),

    /*
     * OTPIQ connection — all values from .env. Secrets never go in the database.
     * After changing .env: php artisan config:clear && php artisan queue:restart
     */
    'otpiq' => [
        'base_url' => env('OTPIQ_BASE_URL', 'https://api.otpiq.com/api'),
        'api_key' => env('OTPIQ_API_KEY'),
        'account_id' => env('OTPIQ_WHATSAPP_ACCOUNT_ID'),
        'phone_id' => env('OTPIQ_WHATSAPP_PHONE_ID'),
        'webhook_secret' => env('OTPIQ_WEBHOOK_SECRET'),
        'timeout' => 20,
        'send_header_image' => (bool) env('OTPIQ_SEND_HEADER_IMAGE', false),
        'send_button_link' => (bool) env('OTPIQ_SEND_BUTTON_LINK', false),
        'public_url' => env('OTPIQ_PUBLIC_URL', 'https://www.nextstepfair.com'),
        'local_header_image' => env('OTPIQ_LOCAL_HEADER_IMAGE'),
        'verify_ssl' => filter_var(env('OTPIQ_VERIFY_SSL', true), FILTER_VALIDATE_BOOL),
    ],

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
    | Logical template keys used by the app / Cloud API. OTPIQ uses a separate
    | name (+ id) per language — see otpiq_templates below.
    */
    'templates' => [
        'otp' => 'next_step_otp',
        'registration_confirmed_student' => 'registration_confirmed_student',
        'registration_confirmed_parent' => 'registration_confirmed_parent',
        'registration_confirmed_visitor' => 'registration_confirmed_visitor',
        'rsvp_confirmed' => 'rsvp_confirmed',
        'event_reminder_3days' => 'event_reminder_3days',
        'event_reminder_1day' => 'event_reminder_1day',
        'day_of_directions' => 'day_of_directions',
        'session_reminder' => 'session_reminder_15min',
        'post_event_thankyou_survey' => 'post_event_thankyou_survey',
    ],

    /*
    | OTPIQ send shape — one entry per logical key × locale.
    |
    |   body    Named slots to send, in {{1}}, {{2}}, … order — must match OTPIQ.
    |   header  Include templateParameters.header.imageUrl when sending a badge.
    |   button  Include the URL-button tail (templateParameters.buttons).
    |
    | Defaults here apply when a row has no override in otpiq_templates (Filament).
    */
    'otpiq_templates' => [
        'rsvp_confirmed' => [
            'en' => ['body' => ['name'], 'header' => true, 'button' => true],
            'ku' => ['body' => ['name', 'ticket'], 'header' => true, 'button' => true],
            'ar' => ['body' => ['name'], 'header' => true, 'button' => true],
        ],

        'registration_confirmed_student' => [
            'en' => ['body' => ['name', 'days', 'ticket'], 'header' => true, 'button' => true],
            'ku' => ['body' => ['name', 'days', 'ticket'], 'header' => true, 'button' => true],
            'ar' => ['body' => ['name', 'days', 'ticket'], 'header' => true, 'button' => true],
        ],

        'registration_confirmed_parent' => [
            'en' => ['body' => ['name', 'days', 'ticket'], 'header' => true, 'button' => true],
            'ku' => ['body' => ['name', 'days', 'ticket'], 'header' => true, 'button' => true],
            'ar' => ['body' => ['name', 'days', 'ticket'], 'header' => true, 'button' => true],
        ],
    ],

    // Meta language codes for template selection (Cloud API).
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
