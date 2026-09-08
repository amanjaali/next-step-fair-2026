<?php

/**
 * Fair registration — student and parent.
 *
 * Option arrays are keyed by the value stored in the database, so a translation
 * change never rewrites historic answers or breaks an admin filter.
 */
return [

    'kicker' => 'Fair registration',
    'title' => 'Register for the Fair',
    'lead_student' => 'Free, and about a minute. This is also your Next Step account — one registration for the expo, the panels, the seminars, the workshops, Zankoline and the scholarship.',
    'lead_parent' => 'Free, and about thirty seconds. Your QR badge arrives on WhatsApp and gets you in on all three days.',
    'cross_link' => 'Attending the Day 1 conference as a government or official delegate? :link — the two are separate.',
    'cross_link_label' => 'Use the Conference RSVP form',

    'account' => [
        'title' => 'Your Next Step account',
        'lead' => 'You register once. After that, everything Next Step runs knows who you are.',
        'password' => 'Password',
        'password_hint' => 'At least 8 characters',
        'benefits' => [
            'Apply for the National Scholarship Program without filling this in again',
            'Book panels, seminars, workshops and Zankoline sessions',
            'Keep your QR badge, your agenda and your applications in one place',
        ],
    ],

    'after_note' => 'That is everything. Your badge is issued the moment you press the button, and we send it to your WhatsApp with the QR on it — so check the number before you submit.',

    'types' => [
        'student' => ['label' => 'Student', 'note' => 'Creates your Next Step account'],
        'parent' => ['label' => 'Parent', 'note' => 'Just a badge for the expo'],
    ],

    'steps' => [
        'personal' => 'Personal',
        'academic' => 'Academic',
        'family' => 'Family',
        'attendance' => 'Attendance',
        'confirm' => 'Confirm',
    ],
    'step_of' => 'Step :current of :total · Progress is saved on this device',
    'saved_note' => 'Saved automatically',
    'saved_note_final' => 'You can edit this later from your WhatsApp link',

    'step1' => [
        'stage' => 'Where are you now?',
        'stage_placeholder' => 'Choose one',
        'stage_note' => 'Grade 12 and recent graduates can also apply for the scholarship.',
        'school' => 'School or university name',
        'email_placeholder' => 'you@example.com',
        'heading' => 'Personal details',
        'name' => 'Full name',
        'name_hint' => 'As it should appear on your badge',
        'dob' => 'Date of birth',
        'phone' => 'Phone number',
        'phone_placeholder' => '770 000 0000',
        'phone_note' => 'We send your badge here on WhatsApp.',
        'email' => 'Email address',
        'email_placeholder' => 'name@example.com',
        'city' => 'City',
        'city_placeholder' => 'Select your city',
        'pref_lang' => 'Preferred language',
        'gender' => 'Gender',
    ],

    'step2_student' => [
        'heading' => 'Academic details',
        'status' => 'Current status',
        'school' => 'School name',
        'school_hint' => 'Start typing to search',
        'stream' => 'Stream',
        'fields' => 'Intended field of study',
        'abroad' => 'Interested in studying abroad?',
    ],

    'step2_parent' => [
        'heading' => 'Family details',
        'relationship' => 'Relationship to student',
        'child_grade' => "Child's current grade",
        'child_grade_hint' => 'e.g. 12th grade',
        'children' => 'Number of children attending',
        'topics' => 'Topics of interest',
    ],

    'step3' => [
        'heading' => 'Attendance',
        'days' => 'Which day(s) will you attend?',
        'reasons' => 'Why are you attending?',
        'seminars' => 'Seminars and workshops you want to attend',
        'hear' => 'How did you hear about Next Step?',
    ],

    'step4' => [
        'heading' => 'Confirm and verify',
        'lead' => 'We send a 6-digit code to your WhatsApp number to check it is reachable before we issue your badge.',
        'verification' => 'WhatsApp verification',
        'code' => '6-digit code',
        'resend' => 'Resend code',
        'resend_in' => 'Resend in :seconds s',
        'sent_to' => 'Sent to :phone',
        'wrong_code' => 'That code does not match. Check the message and try again.',
        'expired' => 'That code has expired. Send a new one.',
        'sent' => 'A new code is on its way.',
    ],

    'consents' => [
        'combined' => 'I accept the terms and privacy policy, and agree to receive my badge and event messages on WhatsApp.',
        'terms' => 'I accept the terms and privacy policy.',
        'whatsapp' => 'I agree to receive WhatsApp updates about the event. This is how your badge is delivered.',
        'photography' => 'I consent to appear in event photography. (Optional)',
    ],

    'submit' => 'Verify and get my badge',
    'continue' => 'Continue',

    'done' => [
        'kicker' => 'Registered',
        'title' => "You're registered, :name",
        'lead' => 'Your badge is on its way to WhatsApp. Save the message and show the QR at the entrance. Screenshots work too.',
        'next_steps' => 'Next steps',
        'download_png' => 'Download badge — PNG',
        'download_pdf' => 'Download badge — PDF (A6)',
        'calendar' => 'Add to calendar (.ics)',
        'delivery' => 'Delivery',
        'delivered_to' => 'WhatsApp to :phone — :status',
        'reminder_scheduled' => 'Reminder scheduled :date',
        'directions_scheduled' => 'Day-of directions scheduled :date',
        'privacy_note' => 'The QR contains a signed ticket ID only — no phone number, no name. If you lose it, staff can find you by name or phone at registration.',
        'ticket' => 'TICKET :id',
    ],

    'duplicate' => [
        'title' => 'This phone number is already registered',
        'body' => 'A badge for the fair already exists on this number, so we have not made a second one. We can send that badge to your WhatsApp again, or you can sign in to open it here.',
        'resend' => 'Resend my QR badge',
        'resent' => 'Sent. Check your WhatsApp messages.',
        'signin' => 'Sign in to my badge',
    ],

    'upgrade' => [
        'title' => 'Finish your registration',
        'body' => 'You already have a visitor pass. Fill in the rest and it becomes a full registration — a personal agenda, session reminders and university matches — on the same badge.',
        'keeping' => 'Your QR badge and ticket :ticket stay exactly as they are.',
        'phone_locked' => 'Already verified. This is the number your badge is registered to.',
        'done' => 'Your registration is complete. Same badge, same ticket number — your agenda and matches are now open.',
    ],

    'options' => [
        'stage' => [
            'grade12' => 'Grade 12',
            'graduate' => 'Finished school',
            'university' => 'At university',
            'other' => 'Something else',
        ],
        'gender' => [
            'male' => 'Male',
            'female' => 'Female',
            'undisclosed' => 'Prefer not to say',
        ],
        'pref_lang' => [
            'ku' => 'Kurdish',
            'ar' => 'Arabic',
            'en' => 'English',
        ],
        'status' => [
            'grade12' => '12th grade student',
            'graduate' => 'Recent graduate',
            'university' => 'University student',
            'other' => 'Other',
        ],
        'stream' => [
            'scientific' => 'Scientific',
            'literary' => 'Literary',
            'vocational' => 'Vocational',
            'other' => 'Other',
        ],
        'fields' => [
            'medicine' => 'Medicine',
            'engineering' => 'Engineering',
            'it_ai' => 'IT & AI',
            'business' => 'Business',
            'law' => 'Law',
            'arts_design' => 'Arts & Design',
            'education' => 'Education',
            'agriculture' => 'Agriculture',
            'vocational' => 'Vocational/Technical',
            'undecided' => 'Undecided',
        ],
        'abroad' => [
            'yes' => 'Yes',
            'no' => 'No',
            'maybe' => 'Maybe',
        ],
        'relationship' => [
            'father' => 'Father',
            'mother' => 'Mother',
            'guardian' => 'Guardian',
            'other' => 'Other',
        ],
        'children' => [
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4plus' => '4 or more',
        ],
        'reasons_student' => [
            'zankoline' => 'Complete Zankoline application',
            'meet_universities' => 'Meet universities and institutes',
            'workshops' => 'Join workshops and panels',
            'offers' => 'Learn about offers and discounts',
            'networking' => 'Networking and opportunities',
            'other' => 'Other',
        ],
        'reasons_parent' => [
            'support_child' => 'To support my child',
            'meet_universities' => 'Meet universities and institutes',
            'panels' => 'Panels and discussions',
            'zankoline' => 'Zankoline consultation',
            'offers' => 'Learn about offers and discounts',
            'other' => 'Other',
        ],
        'topics' => [
            'scholarships' => 'Scholarships & financial aid',
            'zankoline' => 'Zankoline system',
            'abroad' => 'Studying abroad',
            'careers' => 'Career prospects',
            'vocational' => 'Vocational pathways',
        ],
        'hear' => [
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'tiktok' => 'TikTok',
            'school' => 'School',
            'friend' => 'Friend or family',
            'tv_radio' => 'TV or radio',
            'other' => 'Other',
        ],
    ],

    'errors' => [
        'email' => 'Enter your email address.',
        'email_taken' => 'An account already uses this email address. Sign in instead, or use another address.',
        'password' => 'Choose a password of at least 8 characters.',
        'stage' => 'Tell us where you are now.',
        'name' => 'Enter your full name as it should appear on the badge.',
        'dob' => 'Enter your date of birth.',
        'phone' => 'Enter a valid mobile number, for example 770 000 0000.',
        'city' => 'Choose your city.',
        'pref_lang' => 'Choose the language for your badge and messages.',
        'status' => 'Choose your current status.',
        'relationship' => 'Choose your relationship to the student.',
        'days' => 'Choose at least one day.',
        'reasons' => 'Choose at least one reason.',
        'terms' => 'You need to accept the terms and privacy policy.',
        'whatsapp' => 'WhatsApp consent is required — it is how your badge is delivered.',
        'otp' => 'Enter the 6-digit code from WhatsApp.',
    ],

    /* The visitor pass: two fields, no account. */
    'quick' => [
        'title' => 'Just attending',
        'lead' => 'Name and number, nothing else. You get a QR badge on WhatsApp that gets you in on all three days.',
        'name' => 'Your name',
        'name_hint' => 'As it should appear on the badge.',
        'phone' => 'Mobile number',
        'phone_hint' => 'We send your badge here on WhatsApp.',
        'consent' => 'I agree to the terms and the privacy policy, and to receive my badge on WhatsApp.',
        'submit' => 'Get my pass',
        'switch_back' => 'Just want to attend, without an account?',
        'switch_back_link' => 'Get a visitor pass instead',
        'switch' => 'Want a personal agenda and session reminders?',
        'switch_link' => 'Do the full registration instead',
    ],
];
