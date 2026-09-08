<?php

/**
 * Event-wide facts and platform settings for Next Step Fair 2026.
 *
 * Content that editors change (news, speakers, sponsors, sessions...) lives in the
 * database. This file holds the things that are structural: dates, tracks, locales,
 * delivery channels and the security parameters for the ticket QR codes.
 */
return [

    'event' => [
        'name' => 'Next Step Fair 2026',
        'short_name' => 'Next Step Fair',
        'organisation' => 'Next Step Organization',
        'edition' => 4,
        'edition_label' => '4th Edition',
        'year' => 2026,
        'start_date' => '2026-09-28',
        'end_date' => '2026-09-30',
        'opening_hours' => '10:00–20:00',
        'timezone' => 'Asia/Baghdad',

        // Day 1 carries the conference track; all three days are fair days.
        'days' => [
            1 => ['date' => '2026-09-28', 'conference' => true],
            2 => ['date' => '2026-09-29', 'conference' => false],
            3 => ['date' => '2026-09-30', 'conference' => false],
        ],

        'venue' => [
            'name' => 'Cultural Factory',
            'city' => 'Sulaimani',
            'address' => [
                'en' => 'Cultural Factory, Salim Street, Sulaimani 46001, Kurdistan Region, Iraq',
                'ku' => 'کارگەی کولتوری، شەقامی سالم، سلێمانی 46001، هەرێمی کوردستان، عێراق',
                'ar' => 'مصنع الثقافة، شارع سالم، السليمانية 46001، إقليم كوردستان، العراق',
            ],
            'map_url' => 'https://maps.google.com/?q=Cultural+Factory+Sulaimani',
            'latitude' => 35.5608,
            'longitude' => 45.4347,
        ],
    ],

    'contact' => [
        'general' => 'info@nextstepfair.com',
        'media' => 'media@nextstepfair.com',
        'protocol' => 'protocol@nextstepfair.com',
        'partnerships' => 'partnerships@nextstepfair.com',
        'privacy' => 'privacy@nextstepfair.com',
        'phone' => '+964 770 000 0000',
        'media_phone' => '+964 771 000 0000',
    ],

    'social' => [
        'instagram' => 'https://instagram.com/nextstepfair',
        'facebook' => 'https://facebook.com/nextstepfair',
        'tiktok' => 'https://tiktok.com/@nextstepfair',
        'youtube' => 'https://youtube.com/@nextstepfair',
        'linkedin' => 'https://linkedin.com/company/nextstepfair',
    ],

    /*
    |---------------------------------------------------------------------------
    | Sharing
    |---------------------------------------------------------------------------
    |
    | What a person posts after they register. Keep the tag list short — three
    | is read, ten is scrolled past — and keep the first one the same every year
    | so the archive stays searchable.
    |
    */

    'share' => [
        'hashtags' => ['NextStepFair', 'NextStep2026', 'Kurdistan'],
    ],

    'links' => [
        'scholarships' => 'https://scholarship.nextstepfair.com',
        'act4sdgs' => 'https://act4sdgs.org/profile/click_iraq',
    ],

    /*
    |---------------------------------------------------------------------------
    | Languages
    |---------------------------------------------------------------------------
    | The site is a real three-language mirror. `dir` drives the layout flip;
    | numerals stay Latin in every language, which is why no numeral system is
    | configured here.
    */
    'locales' => [
        'en' => [
            'label' => 'English',
            'native' => 'English',
            'code' => 'EN',
            'dir' => 'ltr',
            'html_lang' => 'en',
            'display_font' => "'Space Grotesk'",
            'body_font' => 'Manrope',
        ],
        'ku' => [
            'label' => 'Kurdish Sorani',
            'native' => 'کوردی',
            'code' => 'KU',
            'dir' => 'rtl',
            'html_lang' => 'ckb',
            'display_font' => "'Noto Kufi Arabic'",
            'body_font' => "'Noto Sans Arabic'",
        ],
        'ar' => [
            'label' => 'Arabic',
            'native' => 'العربية',
            'code' => 'AR',
            'dir' => 'rtl',
            'html_lang' => 'ar',
            'display_font' => "'Noto Kufi Arabic'",
            'body_font' => "'Noto Sans Arabic'",
        ],
    ],

    /*
    |---------------------------------------------------------------------------
    | Registration tracks
    |---------------------------------------------------------------------------
    | Two flows, one table, one discriminator. The accent colour is the wayfinding
    | system: magenta is the fair, cobalt is the conference, everywhere.
    */
    'tracks' => [
        'fair' => [
            'types' => ['student', 'parent'],
            'accent' => '#B64698',
            'channel' => 'whatsapp',
            'auto_confirm' => true,
            'duplicate_key' => 'phone',
        ],
        'conference' => [
            'types' => ['government', 'official'],
            'accent' => '#2C4BE0',
            'channel' => 'email',
            // Institutional addresses are auto-approved; free-mail goes to protocol.
            'auto_confirm' => false,
            'duplicate_key' => 'email',
        ],
    ],

    // Free-mail domains that push a conference RSVP into manual protocol review.
    'free_mail_domains' => ['gmail', 'yahoo', 'hotmail', 'outlook', 'icloud', 'proton', 'yandex', 'aol'],

    /*
    |---------------------------------------------------------------------------
    | Ticket QR codes
    |---------------------------------------------------------------------------
    | The QR never carries personal data. It encodes {verify_url}/{ticketId}?sig=
    | where sig is an HMAC-SHA256 of the ticket id under a server-side secret, so a
    | leaked badge image resolves to nothing without the server.
    */
    'qr' => [
        'secret' => env('TICKET_QR_SECRET', env('APP_KEY')),
        'signature_length' => 32,
        'size' => 640,
        'margin' => 0,
        'error_correction' => 'medium',
    ],

    'badge' => [
        'page_size' => 'A6',
        'download_link_ttl' => 60 * 24 * 7, // minutes a signed badge URL stays valid
    ],

    /*
    |---------------------------------------------------------------------------
    | Registration
    |---------------------------------------------------------------------------
    |
    | Whether a fair registration has to answer a WhatsApp code before its badge
    | is issued.
    |
    | Off, which is how the fair runs: the form is the whole thing. The badge is
    | made and sent to the number given, and nobody is held at a code screen —
    | which is where registrations were being abandoned, on a school computer or
    | a borrowed phone.
    |
    | The cost is that the number is taken on trust. A mistyped digit sends
    | somebody's badge to a stranger and cannot be recovered by that person, and
    | a number can be entered by someone who does not hold it. Turn this on for
    | a cycle where that matters; nothing else has to change.
    */
    'registration' => [
        'verify_phone' => (bool) env('REGISTRATION_VERIFY_PHONE', false),
    ],

    'cities' => [
        'Sulaimani', 'Erbil', 'Duhok', 'Halabja', 'Ranya', 'Chamchamal', 'Kalar', 'Koya',
        'Shaqlawa', 'Zakho', 'Dukan', 'Penjwen', 'Qaladze', 'Darbandikhan', 'Sayed Sadiq',
        'Sharbazher', 'Sharazoor', 'Qaradagh', 'Biara', 'Khurmal', 'Sirwan', 'Mergasur',
        'Choman', 'Rawandz', 'Banaslawa', 'Amedi', 'Semel', 'Bardarash', 'Akre',
    ],

    /*
    |---------------------------------------------------------------------------
    | Where a student is in their education
    |---------------------------------------------------------------------------
    |
    | The one academic question the expo asks. Grade 12 and recent graduates are
    | the group the scholarship is for, so this is also the first gate the
    | application checks.
    */
    'education_stages' => [
        'grade12' => 'grade12',
        'graduate' => 'graduate',
        'university' => 'university',
        'other' => 'other',
    ],

    'phone' => [
        'default_country' => '+964',
        'countries' => ['+964' => 'Iraq', '+90' => 'Türkiye', '+98' => 'Iran', '+963' => 'Syria', '+44' => 'UK', '+1' => 'US'],
    ],

    'analytics' => [
        'ga4' => env('GA4_MEASUREMENT_ID'),
        'meta_pixel' => env('META_PIXEL_ID'),
        'tiktok_pixel' => env('TIKTOK_PIXEL_ID'),
    ],

    'turnstile' => [
        'enabled' => env('TURNSTILE_ENABLED', false),
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    'retention' => [
        'registrations_months' => 24,
        'checkin_logs_months' => 12,
    ],
];
