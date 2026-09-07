<?php

/*
 * Updates shown in a student's own account.
 *
 * Written as the news itself, not as a notice that news exists. "The committee
 * updated your application" makes somebody click to find out whether they got
 * it; the headline should already have told them.
 */

return [
    'title' => 'Updates',
    'lead' => 'What the scholarship committee has decided about your application.',
    'empty' => 'Nothing yet. Anything the committee decides will appear here.',
    'new' => 'New',
    'open' => 'Open',
    'unread' => '{1} 1 new update|[2,*] :count new updates',

    'types' => [
        'scholarship' => [
            'decision' => [
                'awarded' => [
                    'title' => 'You have been awarded a scholarship',
                    'body' => 'The committee has selected you for the National Scholarship Program, cycle :cycle. Open your application to read what happens next.',
                ],
                'reserve' => [
                    'title' => 'You are on the scholarship reserve list',
                    'body' => 'Your application for cycle :cycle is held in reserve. Open it to read what that means.',
                ],
                'declined' => [
                    'title' => 'A decision has been made on your scholarship application',
                    'body' => 'Your application for cycle :cycle was not selected this time. Open it to read the result and what else is open to you.',
                ],
            ],
            'stage' => [
                'title' => 'Your application has moved to :stage',
                'body' => 'The committee has moved your cycle :cycle application on a stage. Open it to see where it stands.',
            ],
        ],
    ],
];
