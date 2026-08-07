<?php

/**
 * "I'm attending Next Step Fair 2026."
 *
 * The captions are written in the first person, for the person posting — not
 * for the fair. A student is choosing a life, a parent is helping somebody else
 * choose one, and a director general is representing an institution; one
 * generic line would be posted by none of them.
 */
return [
    'title' => 'Tell people you are coming',
    'kicker' => 'Share',
    'lead' => 'A card to post, and the words to go with it. Most of the people who find Next Step hear about it from somebody they know.',

    'card' => [
        'kicker' => "I'm attending",
        'handle' => '@nextstepfair · nextstepfair.com',
        'lines' => [
            'student' => 'Three days of universities, seminars and scholarships. Free entry.',
            'parent' => 'Universities, scholarships and the people who decide — all in one hall.',
            'delegate' => 'Where higher education in the Kurdistan Region is discussed.',
        ],
    ],

    'formats' => [
        'feed' => 'Feed post',
        'feed_note' => 'Square — Instagram and Facebook',
        'story' => 'Story',
        'story_note' => 'Full screen — Instagram and WhatsApp status',
    ],

    'download' => 'Download the picture',
    'copy' => 'Copy the caption',
    'copied' => 'Copied',
    'caption_label' => 'Your caption',

    'targets' => [
        'whatsapp' => 'Share on WhatsApp',
        'facebook' => 'Share on Facebook',
        'linkedin' => 'Share on LinkedIn',
    ],

    'instagram_title' => 'Posting to Instagram',
    'instagram_note' => 'Instagram cannot be posted to from a website. Download the picture, open Instagram, and paste the caption — the story card is the full-screen one.',

    'no_qr_note' => 'Your badge QR is not on these cards, and should not be posted anywhere. It is what opens the gate, and anybody who photographs it can walk in on your ticket.',

    /*
     * Placeholders: :name :dates :venue :city :url :tags
     */
    'landing' => [
        'kicker' => 'Somebody you know is going',
        'title' => "I'm attending Next Step Fair 2026",
        'lines' => [
            'student' => 'Three days at the Cultural Factory in Sulaimani: universities from across the Region and abroad, seminars, workshops, and the National Scholarship Program. Free entry, and open to anybody finishing school.',
            'parent' => 'Three days at the Cultural Factory in Sulaimani. Universities, scholarship routes, and the people who actually make the admissions decisions — in one hall, so a family can ask everything in an afternoon.',
            'delegate' => 'Three days at the Cultural Factory in Sulaimani, opening with a policy conference: ministries, universities and partners on where higher education in the Kurdistan Region goes next.',
        ],
        'cta' => 'Register — it is free',
        'cta_secondary' => 'See the whole programme',
        'what' => [
            'expo' => 'The Expo',
            'expo_note' => 'Thirty-two universities and institutes, three days, free entry.',
            'scholarship' => 'The Scholarship Program',
            'scholarship_note' => 'Forty fully funded degrees, held in regional quotas.',
            'conference' => 'The Conference',
            'conference_note' => 'Day 1: the institutions that shape higher education here, in one room.',
        ],
    ],

    'captions' => [
        'student' => "I'm going to Next Step Fair 2026.\n\nThree days at :venue in :city, :dates — universities from across the Region and abroad, seminars, workshops, and the scholarship programme, all in one place.\n\nIf you are finishing school like me, this is the one place you can ask the questions nobody answers properly online. Entry is free.\n\n:url\n:tags",

        'parent' => "We are going to Next Step Fair 2026.\n\n:dates at :venue, :city. Universities, scholarship routes, and the people who actually make the admissions decisions — in one hall, over three days.\n\nIf you are helping your child work out what comes after school, this is the short way to do it. Free entry, and worth the trip.\n\n:url\n:tags",

        'government' => "I will be attending the Next Step Conference 2026 — :dates at :venue, :city.\n\nHigher education in the Kurdistan Region, discussed by the people responsible for it: ministries, universities and partners on one platform, before the fair opens its doors to thirty thousand students.\n\n:url\n:tags",

        'official' => "Attending Next Step Fair 2026 — :dates, :venue, :city.\n\nThe Day 1 conference brings the institutions that shape higher education in the Region into one room, and the two days after it put them in front of the students who will live with those decisions.\n\n:url\n:tags",

        'private' => "I will be at Next Step Fair 2026 — :dates at :venue, :city.\n\nEvery business in this Region depends on what these graduates can actually do. I am going to hear where higher education here is heading, and to say plainly what our sector needs from it.\n\n:url\n:tags",

        'individual' => "I'm attending Next Step Fair 2026 — :dates at :venue, :city.\n\nThree days on higher education in the Kurdistan Region: universities from home and abroad, seminars, workshops and scholarships. Free entry, and open to anybody.\n\n:url\n:tags",
    ],
];
