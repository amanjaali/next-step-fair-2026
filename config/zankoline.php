<?php

/**
 * The Zankoline guidance desks.
 *
 * Zankoline is the electronic form every grade 12 graduate fills in to choose
 * their university and department, in order of preference. It is filled in once,
 * against a deadline, and a badly ordered list is the difference between a place
 * and a year lost — which is why a desk with somebody who has seen a thousand of
 * them is worth more than any leaflet.
 *
 * 2026 is the first year the desks travel: seven places rather than Sulaimani
 * alone. What is here is the shipped list and where each one sits on the map.
 * The address, the phone number and the person responsible are filled in from
 * the dashboard, because they change between now and September and nobody
 * should need a developer to correct a phone number.
 */

return [

    /*
    |---------------------------------------------------------------------------
    | The map
    |---------------------------------------------------------------------------
    |
    | The corners the schematic map is drawn between. Every marker is placed by
    | projecting its coordinates into this box, so a centre added in the
    | dashboard with a latitude and longitude lands in the right place without
    | anybody positioning it by hand.
    */
    'map' => [
        'lat_min' => 34.35,
        'lat_max' => 37.00,
        'lng_min' => 43.60,
        'lng_max' => 46.35,
    ],

    /*
    |---------------------------------------------------------------------------
    | The centres
    |---------------------------------------------------------------------------
    |
    | Coordinates are the centre of each town, which is close enough for a map
    | read at this size; the address field is what actually gets somebody to the
    | door, and the maps link is what their phone follows.
    */
    'centres' => [
        [
            'slug' => 'sulaymaniyah',
            'name' => ['en' => 'Sulaymaniyah', 'ku' => 'سلێمانی', 'ar' => 'السليمانية'],
            'lat' => 35.561, 'lng' => 45.437,
        ],
        [
            'slug' => 'erbil',
            'name' => ['en' => 'Erbil', 'ku' => 'هەولێر', 'ar' => 'أربيل'],
            'lat' => 36.191, 'lng' => 44.009,
        ],
        [
            'slug' => 'halabja',
            'name' => ['en' => 'Halabja', 'ku' => 'هەڵەبجە', 'ar' => 'حلبجة'],
            'lat' => 35.177, 'lng' => 45.986,
        ],
        [
            'slug' => 'chamchamal',
            'name' => ['en' => 'Chamchamal', 'ku' => 'چەمچەماڵ', 'ar' => 'جمجمال'],
            'lat' => 35.530, 'lng' => 44.833,
        ],
        [
            'slug' => 'garmian',
            'name' => ['en' => 'Garmian', 'ku' => 'گەرمیان', 'ar' => 'كرميان'],
            'lat' => 34.630, 'lng' => 45.322,
        ],
        [
            'slug' => 'raparin',
            'name' => ['en' => 'Raparin', 'ku' => 'ڕاپەڕین', 'ar' => 'رابرين'],
            'lat' => 36.253, 'lng' => 44.881,
        ],
        [
            'slug' => 'soran',
            'name' => ['en' => 'Soran', 'ku' => 'سۆران', 'ar' => 'سوران'],
            'lat' => 36.652, 'lng' => 44.541,
        ],
    ],
];
