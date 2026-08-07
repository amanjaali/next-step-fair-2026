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
 * changing a seat count or an eligibility question is a decision the committee
 * takes between cycles, not an edit somebody makes on a Tuesday.
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
    | The eligibility check
    |---------------------------------------------------------------------------
    |
    | Five questions, about a minute. It runs before the application opens so that
    | nobody spends an evening on a statement and a proposal only to be closed at
    | screening for something they could have been told in advance.
    |
    | `fail` ends it. `warn` lets them through with something to fix.
    */
    'eligibility' => [
        [
            'id' => 'grade12',
            'options' => ['y', 'n'],
            'fail' => ['n'],
            'warn' => [],
        ],
        [
            'id' => 'average',
            'options' => ['y', 'p', 'n'],
            'fail' => ['n'],
            'warn' => ['p'],
        ],
        [
            'id' => 'year',
            'options' => ['y', 'n'],
            'fail' => ['n'],
            'warn' => [],
        ],
        [
            'id' => 'funded',
            'options' => ['n', 'p', 'y'],
            'fail' => ['y'],
            'warn' => [],
        ],
        [
            'id' => 'docs',
            'options' => ['y', 'p', 'n'],
            'fail' => ['n'],
            'warn' => ['p'],
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

    'statement_words' => ['min' => 400, 'max' => 600],

    'proposal_words' => ['min' => 500, 'max' => 800],

    /*
    |---------------------------------------------------------------------------
    | Participating universities
    |---------------------------------------------------------------------------
    |
    | Seats are pledged per department, so a student knows before applying whether
    | the subject they want is actually funded anywhere.
    */
    'universities' => [
        [
            'slug' => 'auis', 'name' => 'American University of Iraq, Sulaimani',
            'city' => 'Sulaimani', 'language' => 'English', 'tier' => 'founding',
            'founded' => 2007, 'students' => 1600, 'housing' => 'contribution',
            'departments' => [
                ['name' => 'Computer Science', 'seats' => 2],
                ['name' => 'Business Administration', 'seats' => 2],
                ['name' => 'International Studies', 'seats' => 1],
                ['name' => 'Engineering', 'seats' => 1],
            ],
        ],
        [
            'slug' => 'ukh', 'name' => 'University of Kurdistan Hewlêr',
            'city' => 'Erbil', 'language' => 'English', 'tier' => 'founding',
            'founded' => 2006, 'students' => 1300, 'housing' => 'none',
            'departments' => [
                ['name' => 'Medicine', 'seats' => 2],
                ['name' => 'Computer Science & AI', 'seats' => 2],
                ['name' => 'Natural Resources', 'seats' => 1],
                ['name' => 'Law', 'seats' => 1],
            ],
        ],
        [
            'slug' => 'komar', 'name' => 'Komar University of Science and Technology',
            'city' => 'Sulaimani', 'language' => 'English', 'tier' => 'donor',
            'founded' => 2013, 'students' => 2100, 'housing' => 'contribution',
            'departments' => [
                ['name' => 'Pharmacy', 'seats' => 2],
                ['name' => 'Dentistry', 'seats' => 1],
                ['name' => 'Architecture', 'seats' => 1],
                ['name' => 'Computer Engineering', 'seats' => 1],
            ],
        ],
        [
            'slug' => 'tishk', 'name' => 'Tishk International University',
            'city' => 'Erbil', 'language' => 'English', 'tier' => 'donor',
            'founded' => 2008, 'students' => 5400, 'housing' => 'none',
            'departments' => [
                ['name' => 'Medicine', 'seats' => 2],
                ['name' => 'Civil Engineering', 'seats' => 1],
                ['name' => 'Education', 'seats' => 1],
                ['name' => 'Business', 'seats' => 1],
            ],
        ],
        [
            'slug' => 'uhd', 'name' => 'University of Human Development',
            'city' => 'Sulaimani', 'language' => 'English & Kurdish', 'tier' => 'donor',
            'founded' => 2008, 'students' => 4800, 'housing' => 'none',
            'departments' => [
                ['name' => 'Law', 'seats' => 2],
                ['name' => 'Accounting', 'seats' => 1],
                ['name' => 'English Language', 'seats' => 1],
                ['name' => 'Computer Science', 'seats' => 1],
            ],
        ],
        [
            'slug' => 'lfu', 'name' => 'Lebanese French University',
            'city' => 'Erbil', 'language' => 'English', 'tier' => 'donor',
            'founded' => 2007, 'students' => 3600, 'housing' => 'contribution',
            'departments' => [
                ['name' => 'Dentistry', 'seats' => 2],
                ['name' => 'Medical Laboratory', 'seats' => 1],
                ['name' => 'Interior Design', 'seats' => 1],
                ['name' => 'IT', 'seats' => 1],
            ],
        ],
        [
            'slug' => 'qiu', 'name' => 'Qaiwan International University',
            'city' => 'Sulaimani', 'language' => 'English', 'tier' => 'donor',
            'founded' => 2018, 'students' => 1200, 'housing' => 'included',
            'departments' => [
                ['name' => 'Mechanical Engineering', 'seats' => 1],
                ['name' => 'Software Engineering', 'seats' => 2],
                ['name' => 'Business', 'seats' => 1],
            ],
        ],
        [
            'slug' => 'cue', 'name' => 'Catholic University in Erbil',
            'city' => 'Erbil', 'language' => 'English', 'tier' => 'donor',
            'founded' => 2015, 'students' => 900, 'housing' => 'included',
            'departments' => [
                ['name' => 'Medicine', 'seats' => 1],
                ['name' => 'Pharmacy', 'seats' => 1],
                ['name' => 'International Relations', 'seats' => 1],
                ['name' => 'Accounting', 'seats' => 1],
            ],
        ],
    ],

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
