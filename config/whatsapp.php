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

    'otpiq' => [
        'base_url' => env('OTPIQ_BASE_URL', 'https://api.otpiq.com/api'),
        'api_key' => env('OTPIQ_API_KEY'),
        // Both come from the WhatsApp account in the OTPIQ dashboard, not from Meta.
        'account_id' => env('OTPIQ_WHATSAPP_ACCOUNT_ID'),
        'phone_id' => env('OTPIQ_WHATSAPP_PHONE_ID'),
        // Shared with OTPIQ so a delivery report can be told from a stranger's post.
        'webhook_secret' => env('OTPIQ_WEBHOOK_SECRET'),
        'timeout' => 20,
        /*
         * Master switches. Header image is also gated per locale in
         * otpiq_templates.*.header_image — only templates approved with an
         * IMAGE header may receive imageUrl, or OTPIQ rejects the whole send.
         *
         * public_url: HTTPS origin OTPIQ/Meta use to fetch ticket/{id}/badge.png.
         * Must be publicly reachable (production domain or ngrok when local).
         */
        'send_header_image' => (bool) env('OTPIQ_SEND_HEADER_IMAGE', false),
        'send_button_link' => (bool) env('OTPIQ_SEND_BUTTON_LINK', false),
        'public_url' => env('OTPIQ_PUBLIC_URL'),
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

        'rsvp_confirmed' => [
            'en' => [
                'name' => 'rsvp_confirmed_en_2026',
                'id' => env('OTPIQ_TEMPLATE_RSVP_CONFIRMED_EN_ID'),
                'body' => ['name'],
                'header_image' => true,
            ],
            'ku' => [
                'name' => 'rsvp_confirmed_ku_2026',
                'id' => env('OTPIQ_TEMPLATE_RSVP_CONFIRMED_KU_ID'),
                'body' => ['name', 'ticket'],
                'header_image' => false,
            ],
            'ar' => [
                'name' => 'rsvp_confirmed_ar_2026',
                'id' => env('OTPIQ_TEMPLATE_RSVP_CONFIRMED_AR_ID'),
                'body' => ['name'],
                'header_image' => false,
            ],
        ],

        'registration_confirmed_student' => [
            'en' => [
                'name' => 'registration_confirmed_student_en_2026',
                'id' => env('OTPIQ_TEMPLATE_REGISTRATION_CONFIRMED_STUDENT_EN_ID'),
                'body' => ['name', 'days', 'ticket'],
                'header_image' => false,
            ],
            'ku' => [
                'name' => 'registration_confirmed_student_ku_2026',
                'id' => env('OTPIQ_TEMPLATE_REGISTRATION_CONFIRMED_STUDENT_KU_ID'),
                'body' => ['name', 'days', 'ticket'],
                'header_image' => false,
            ],
            'ar' => [
                'name' => 'registration_confirmed_student_ar_2026',
                'id' => env('OTPIQ_TEMPLATE_REGISTRATION_CONFIRMED_STUDENT_AR_ID'),
                'body' => ['name', 'days', 'ticket'],
                'header_image' => false,
            ],
        ],

        'registration_confirmed_parent' => [
            'en' => [
                'name' => 'registration_confirmed_parent_en_2026',
                'id' => env('OTPIQ_TEMPLATE_REGISTRATION_CONFIRMED_PARENT_EN_ID'),
                'body' => ['name', 'days', 'ticket'],
                'header_image' => false,
            ],
            'ku' => [
                'name' => 'registration_confirmed_parent_ku_2026',
                'id' => env('OTPIQ_TEMPLATE_REGISTRATION_CONFIRMED_PARENT_KU_ID'),
                'body' => ['name', 'days', 'ticket'],
                'header_image' => false,
            ],
            'ar' => [
                'name' => 'registration_confirmed_parent_ar_2026',
                'id' => env('OTPIQ_TEMPLATE_REGISTRATION_CONFIRMED_PARENT_AR_ID'),
                'body' => ['name', 'days', 'ticket'],
                'header_image' => false,
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
