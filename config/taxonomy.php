<?php

/**
 * The vocabulary both sides of the fair speak.
 *
 * Bands, not exact numbers. A seventeen-year-old cannot state a tuition budget to
 * the dollar and a university cannot promise one, but both can work in ranges —
 * and ranges are what a recruiter plans against and what a report can aggregate.
 *
 * Changing a key here breaks stored data. Add new keys; do not rename old ones.
 */
return [

    'degree_levels' => ['foundation', 'diploma', 'bachelor', 'master', 'phd'],

    'languages' => ['en', 'ku', 'ar', 'tr', 'other'],

    /* Tuition per year, in USD. 'free' covers public places with no fee. */
    'budget_bands' => [
        'free' => [0, 0],
        'under_2k' => [1, 2000],
        '2k_5k' => [2000, 5000],
        '5k_10k' => [5000, 10000],
        '10k_20k' => [10000, 20000],
        'over_20k' => [20000, null],
        'undecided' => [null, null],
    ],

    /* Grade on the Iraqi 100-point scale, which is what a school leaver has. */
    'grade_bands' => [
        'under_60' => [0, 59],
        '60_69' => [60, 69],
        '70_79' => [70, 79],
        '80_89' => [80, 89],
        '90_plus' => [90, 100],
        'not_yet' => [null, null],   // has not sat the exams
    ],

    /* Destinations that actually recruit here. 'undecided' is a real answer. */
    'countries' => [
        'IQ', 'TR', 'JO', 'AE', 'QA', 'SA', 'EG', 'DE', 'GB', 'US', 'CA', 'MY', 'HU', 'PL', 'undecided',
    ],

    'career_goals' => [
        'employment', 'postgraduate', 'family_business', 'own_business',
        'public_sector', 'research', 'abroad', 'undecided',
    ],

    /*
    | Fields grouped into sectors. Kept in config rather than only in the seeder so
    | the matching engine, the forms and the reports all read one list.
    */
    'sectors' => [
        'health' => ['medicine', 'dentistry', 'pharmacy', 'nursing', 'public_health', 'medical_laboratory', 'physiotherapy'],
        'engineering' => ['civil', 'mechanical', 'electrical', 'petroleum', 'architecture', 'chemical', 'industrial', 'mechatronics'],
        'computing' => ['computer_science', 'software_engineering', 'information_technology', 'cybersecurity', 'data_science', 'artificial_intelligence'],
        'business' => ['business_administration', 'accounting', 'finance', 'banking', 'marketing', 'management', 'economics', 'logistics'],
        'law_politics' => ['law', 'political_science', 'international_relations', 'public_administration'],
        'education' => ['primary_education', 'special_education', 'educational_psychology', 'physical_education'],
        'sciences' => ['biology', 'chemistry', 'physics', 'mathematics', 'statistics', 'environmental_science', 'geology'],
        'humanities' => ['english_language', 'kurdish_language', 'arabic_language', 'history', 'sociology', 'psychology', 'philosophy', 'translation'],
        'media_arts' => ['journalism', 'media_communication', 'graphic_design', 'fine_arts', 'music', 'film_production'],
        'agriculture' => ['agricultural_engineering', 'food_science', 'veterinary', 'animal_production', 'horticulture'],
        'tourism' => ['tourism_management', 'hospitality', 'culinary_arts', 'aviation'],
        'vocational' => ['technical_trades', 'nursing_diploma', 'it_support', 'accounting_technician', 'construction_technology'],
    ],

    /*
    | How much each signal counts toward a match, out of 100. Field overlap
    | dominates on purpose: everything else is a filter on a decision that is
    | fundamentally about what someone wants to study.
    */
    'match_weights' => [
        'field' => 45,
        'level' => 15,
        'country' => 15,
        'budget' => 10,
        'language' => 8,
        'grade' => 7,
    ],

    /* Below this a match is noise and is not stored. */
    'match_threshold' => 35,

    /* How much each interaction says about real interest, for lead scoring. */
    'interaction_weights' => [
        'booth_scan' => 40,
        'enquiry' => 30,
        'shortlist' => 20,
        'brochure' => 10,
        'profile_view' => 5,
    ],
];
