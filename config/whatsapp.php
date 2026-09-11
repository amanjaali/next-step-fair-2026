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
     * OTPIQ credentials and badge flags live in otpiq_settings (OtpiqSettingsSeeder).
     * These defaults apply only when the table is missing or not yet seeded.
     */
    'otpiq' => [
        'base_url' => 'https://api.otpiq.com/api',
        'api_key' => null,
        'account_id' => null,
        'phone_id' => null,
        'webhook_secret' => null,
        'timeout' => 20,
        'send_header_image' => false,
        'send_button_link' => false,
        'public_url' => 'https://www.nextstepfair.com',
        'local_header_image' => null,
        'verify_ssl' => true,
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
    | OTPIQ templates — one entry per logical key × locale.
    |
    |   name          Exact name in the OTPIQ dashboard (sent as templateName).
    |   id            OTPIQ template id (paste from the dashboard into .env).
    |   body          Named slots to send, in {{1}}, {{2}}, … order.
    |   header_image  True only if that locale's Meta template has an IMAGE header.
    |
    | RSVP is live. Student / parent names are ready; fill their ids when approved.
    */
    'otpiq_templates' => [

        // Dashboard ids live in otpiq_templates (OtpiqTemplateSeeder). These
        // entries are the fallback when the table is empty or not migrated yet.
        'rsvp_confirmed' => [
            'en' => [
                'name' => 'rsvp_confirmed_en_2026',
                'body' => ['name'],
                'header_image' => true,
            ],
            'ku' => [
                'name' => 'rsvp_confirmed_ku_2026',
                'body' => ['name', 'ticket'],
                'header_image' => false,
            ],
            'ar' => [
                'name' => 'rsvp_confirmed_ar_2026',
                'body' => ['name'],
                'header_image' => false,
            ],
        ],

        'registration_confirmed_student' => [
            'en' => [
                'name' => 'registration_confirmed_student_en_2026',
                'body' => ['name', 'days', 'ticket'],
                'header_image' => true,
            ],
            'ku' => [
                'name' => 'registration_confirmed_student_ku_2026',
                'body' => ['name', 'days', 'ticket'],
                'header_image' => true,
            ],
            'ar' => [
                'name' => 'registration_confirmed_student_ar_2026',
                'body' => ['name', 'days', 'ticket'],
                'header_image' => true,
            ],
        ],

        'registration_confirmed_parent' => [
            'en' => [
                'name' => 'registration_confirmed_parent_en_2026',
                'body' => ['name', 'days', 'ticket'],
                'header_image' => true,
            ],
            'ku' => [
                'name' => 'registration_confirmed_parent_ku_2026',
                'body' => ['name', 'days', 'ticket'],
                'header_image' => true,
            ],
            'ar' => [
                'name' => 'registration_confirmed_parent_ar_2026',
                'body' => ['name', 'days', 'ticket'],
                'header_image' => true,
            ],
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
