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
         * Master switches. Header image is also gated per template in
         * otpiq_header_image — only locales whose Meta template has an IMAGE
         * header may receive imageUrl, or OTPIQ rejects the whole send.
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
    | Template names must match what Meta approved, exactly. Each one needs an
    | approved variant per language (en / ku / ar) before it can be sent — see
    | docs/whatsapp-templates.md for the submission process and lead times.
    |
    | These are the logical keys the app uses. OTPIQ holds a separate template
    | *name* per language (see otpiq_names below); Cloud API keeps one name and
    | picks the language code instead.
    */
    'templates' => [
        'otp' => 'next_step_otp',
        'registration_confirmed_student' => 'registration_confirmed_student',
        'registration_confirmed_parent' => 'registration_confirmed_parent',
        'registration_confirmed_visitor' => 'registration_confirmed_visitor',
        // The conference track: a delegate is approved by the protocol team first.
        'rsvp_confirmed' => 'rsvp_confirmed',
        'event_reminder_3days' => 'event_reminder_3days',
        'event_reminder_1day' => 'event_reminder_1day',
        'day_of_directions' => 'day_of_directions',
        'session_reminder' => 'session_reminder_15min',
        'post_event_thankyou_survey' => 'post_event_thankyou_survey',
    ],

    /*
    | OTPIQ template names as they appear in the dashboard. Conference RSVP sends
    | rsvp_confirmed_{locale} on submit.
    */
    'otpiq_names' => [
        'rsvp_confirmed' => [
            'en' => 'rsvp_confirmed_en',
            'ku' => 'rsvp_confirmed_ku',
            'ar' => 'rsvp_confirmed_ar',
        ],
    ],

    /*
    | Body placeholders actually approved in OTPIQ, in order. Extra values the
    | app still knows must not be sent — Meta rejects a mismatched parameter
    | count. Ticket still travels on the URL button when OTPIQ_SEND_BUTTON_LINK
    | is on (and in the Kurdish body as {{2}}).
    */
    'otpiq_body' => [
        'rsvp_confirmed' => [
            'en' => ['name'],
            'ku' => ['name', 'ticket'],
            'ar' => ['name'],
        ],
    ],

    /*
    | Locales whose OTPIQ template was approved with an IMAGE header. Only the
    | English rsvp_confirmed variant has an IMAGE header today; ku/ar still get
    | the badge via the URL button.
    */
    'otpiq_header_image' => [
        'rsvp_confirmed' => [
            'en' => true,
            'ku' => false,
            'ar' => false,
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
