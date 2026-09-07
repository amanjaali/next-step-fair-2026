<?php

/*
 * The Zankoline guidance desks.
 *
 * Written to be read by a student in the week the form opens and by a parent
 * who has never filled one in. Every line here can be replaced from the
 * dashboard; what is shipped is the version the site reads with nothing edited.
 */

return [
    'nav' => 'Zankoline guidance',
    'kicker' => 'Free guidance for grade 12',
    'title' => 'Filling in your Zankoline',
    'lead' => 'Zankoline is the form that decides where you study. You fill it in once, in order of preference, against a deadline — and the order you put your choices in matters as much as the choices themselves. Our advisers sit with you while you do it.',

    'what' => [
        'title' => 'What the desk does',
        'lead' => 'Not a lecture. You bring your marks and your questions, and you leave with a list you understand.',
    ],

    'steps' => [
        'one' => [
            'title' => 'We read your average with you',
            'body' => 'Your grade 12 average against last year\'s admission marks for the departments you are aiming at, so you can see which of your choices are realistic, which are a stretch, and which are safe.',
        ],
        'two' => [
            'title' => 'We put your choices in order',
            'body' => 'The single most common mistake is a list ordered by what sounds best rather than by what you would actually accept. We go through yours line by line until the order is the order you mean.',
        ],
        'three' => [
            'title' => 'We check the practical side',
            'body' => 'City, cost of living, distance from home, whether a department teaches in a language you read comfortably, and what the degree leads to. A place you cannot take up is not a place.',
        ],
        'four' => [
            'title' => 'We fill it in with you',
            'body' => 'The form itself, on the day, with somebody beside you — and a copy of what you submitted, so there is no argument later about what you chose.',
        ],
    ],

    'bring' => [
        'title' => 'What to bring',
        'items' => [
            'Your grade 12 results, or your best estimate if they are not out yet',
            'Your national ID or nationality certificate',
            'A phone with your email address working on it',
            'A parent or guardian, if you would like one there',
        ],
    ],

    'where' => [
        'title' => 'Where to find us',
        'lead' => 'Seven places this year, not one. The desks run through the three days of Next Step and stay open in each area through the Zankoline period.',
        'note' => 'Positions on the map are approximate. The address below each name is the one to travel to.',
        'person' => 'Ask for',
        'phone' => 'Phone',
        'address' => 'Address',
        'directions' => 'Directions',
        'soon' => 'Address and phone number to be confirmed.',
        'count' => '{1} 1 centre|[2,*] :count centres',
    ],

    /* The band on the front page, and the card on the opportunities board. */
    'promo' => [
        'kicker' => 'A service we run',
        'title' => 'We help you fill in your Zankoline',
        'body' => 'The form that decides where you study, filled in with an adviser beside you — in seven places this year, and free. Bring your marks and your questions.',
        'cta' => 'See the seven centres',
        'where' => 'Where the desks are',
    ],

    'cta' => [
        'title' => 'Coming to Next Step?',
        'body' => 'The guidance desks are at the fair as well, alongside the universities themselves — so you can ask a department the question and fill the form in on the same afternoon.',
        'button' => 'Register for the fair',
    ],
];
