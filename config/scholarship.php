<?php

/**
 * The Next Step National Scholarship Program.
 *
 * Forty seats, held in regional quotas so that a student from Halabja competes
 * against Halabja and not against Sulaimani. The quota a student falls into is
 * decided by where they completed grade 12, which is why it cannot be changed
 * after submission.
 *
 * The rules live here rather than in the database because they are the programme:
 * changing a seat count on a region or an eligibility question is a decision the
 * committee takes between cycles, not an edit somebody makes on a Tuesday.
 *
 * Participating universities and their department seat pledges are the exception:
 * those are edited from the dashboard (scholarship_universities).
 */
return [

    'cycle' => '2026–2027',

    'seats' => 40,

    /*
    |---------------------------------------------------------------------------
    | Regional quotas
    |---------------------------------------------------------------------------
    |
    | Four provinces at eight seats each, four independent administrations at two.
    | Districts are listed so an applicant picks the one they sat their exams in.
    */
    'regions' => [
        'HLB' => ['name' => 'Halabja', 'type' => 'province', 'seats' => 8,
            'districts' => ['Halabja Centre', 'Khurmal', 'Sirwan', 'Byara']],
        'SLM' => ['name' => 'Sulaymaniyah', 'type' => 'province', 'seats' => 8,
            'districts' => ['Sulaymaniyah Centre', 'Chamchamal', 'Qaradagh', 'Sharazoor', 'Said Sadiq', 'Penjwen', 'Dukan']],
        'ERB' => ['name' => 'Erbil', 'type' => 'province', 'seats' => 8,
            'districts' => ['Erbil Centre', 'Koya', 'Shaqlawa', 'Makhmour', 'Dashti Hawler']],
        'DHK' => ['name' => 'Duhok', 'type' => 'province', 'seats' => 8,
            'districts' => ['Duhok Centre', 'Semel', 'Amedi', 'Bardarash', 'Shekhan']],
        'GRM' => ['name' => 'Garmian', 'type' => 'independent', 'seats' => 2,
            'districts' => ['Kalar', 'Kifri', 'Darbandikhan', 'Bawanur']],
        'RPR' => ['name' => 'Raparin', 'type' => 'independent', 'seats' => 2,
            'districts' => ['Ranya', 'Qaladze', 'Hero', 'Chwarqurna']],
        'ZKH' => ['name' => 'Zakho', 'type' => 'independent', 'seats' => 2,
            'districts' => ['Zakho Centre', 'Batifa', 'Derkar', 'Rizgari']],
        'SOR' => ['name' => 'Soran', 'type' => 'independent', 'seats' => 2,
            'districts' => ['Soran Centre', 'Rawandz', 'Choman', 'Mergasur']],
    ],

    /*
    |---------------------------------------------------------------------------
    | Confirmed university and institute scholarships
    |---------------------------------------------------------------------------
    |
    | The headline count on the scholarship homepage — separate from the 40-seat
    | regional quota above, and separate from the dashboard-managed university
    | catalogue used on the application form. This is the confirmed pledge total
    | for the current cycle, updated by hand each time a partner confirms or
    | revises its count. Names are translated per locale under
    | scholarship.institution_names.
    */
    'university_scholarships' => [
        'total' => 376,
        'count' => 12,
        'institutions' => [
            ['slug' => 'azmar', 'seats' => 123],
            ['slug' => 'lutka', 'seats' => 120],
            ['slug' => 'nit', 'seats' => 100],
            ['slug' => 'komar', 'seats' => 15],
            ['slug' => 'qaiwan-intl', 'seats' => 5],
            ['slug' => 'bright', 'seats' => 4],
            ['slug' => 'kti', 'seats' => 4],
            ['slug' => 'auis', 'seats' => 2],
            ['slug' => 'nishtiman', 'seats' => 2],
            ['slug' => 'lebanese-french', 'seats' => 1],
            ['slug' => 'knowledge', 'seats' => 0],
            ['slug' => 'jihan', 'seats' => 0],
        ],
    ],

    /*
    |---------------------------------------------------------------------------
    | The eligibility check
    |---------------------------------------------------------------------------
    |
    | Two questions, about a minute. It runs before the application opens so
    | that nobody spends an evening on a statement and a proposal only to be
    | closed at screening for something they could have been told in advance.
    |
    | `fail` ends it. `warn` lets them through with something to fix. `year` is
    | asked for the record only — no answer to it closes the application.
    */
    'eligibility' => [
        [
            'id' => 'year',
            'options' => ['y', 'n'],
            'fail' => [],
            'warn' => [],
        ],
        [
            'id' => 'funded',
            'options' => ['n', 'p', 'y'],
            'fail' => ['y'],
            'warn' => [],
        ],
    ],

    /* The baseline the committee screens against, before anybody reads a file. */
    'minimum_average' => 85,

    /*
    |---------------------------------------------------------------------------
    | Scoring
    |---------------------------------------------------------------------------
    |
    | Published deliberately: an applicant should be able to see what is being
    | judged and what it is worth before they write a word.
    */
    'rubric' => [
        'academic' => 40,
        'feasibility' => 35,
        'interview' => 25,
    ],

    /* What screening needs on file. Nothing is read without all of it. */
    'documents' => ['certificate', 'national_id', 'residence', 'judicial_record'],

    'statement_words' => ['min' => 50, 'max' => 600],

    'proposal_words' => ['min' => 50, 'max' => 800],

    /*
    |---------------------------------------------------------------------------
    | The committee
    |---------------------------------------------------------------------------
    |
    | Published so that a rejected applicant can see who decided, and on what.
    | Next Step staff run the process; the academics score it.
    */
    'committee' => [
        ['name' => 'Avin Qadir', 'role' => 'Organizer and Co-Founder, Next Step', 'side' => 'internal'],
        ['name' => 'Rebwar Hama Salih', 'role' => 'Programme Director, Next Step', 'side' => 'internal'],
        ['name' => 'Nazand Kareem Bakr', 'role' => 'Compliance and Registration Lead, Next Step', 'side' => 'internal'],
        ['name' => 'Dr. Shahen Aziz', 'role' => 'Professor of Computer Science, University of Sulaimani', 'side' => 'external'],
        ['name' => 'Dr. Trifa Muhammad Amin', 'role' => 'Dean of Medicine, University of Duhok', 'side' => 'external'],
        ['name' => 'Dr. Karwan Jalal Rashid', 'role' => 'Professor of Engineering, Salahaddin University', 'side' => 'external'],
        ['name' => 'Dr. Shukria Omar Tahir', 'role' => 'Head of Education Research, Koya University', 'side' => 'external'],
    ],

    /* Fixed dates for the cycle, shown on the guidelines page. */
    'timeline' => [
        'opens' => '2026-09-01',
        'closes' => '2026-11-15',
        'screening' => '2026-11-30',
        'interviews' => '2027-01-15',
        'decisions' => '2027-02-15',
    ],
];
