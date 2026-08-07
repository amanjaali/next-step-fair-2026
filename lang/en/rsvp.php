<?php

/**
 * Conference RSVP — government and official delegates.
 * Two steps, six required fields, confirmation and badge by e-mail.
 */
return [

    'kicker' => 'Conference · Day 1 only',
    'title' => 'Conference RSVP',
    'lead' => '28 September, Hall B, Cultural Factory. By RSVP for ministries, directorates, diplomatic missions, university leadership, companies and individuals. One short form.',
    'cross_link' => 'Registering as a student or parent instead? :link — the two are separate.',
    'cross_link_label' => 'Use the fair registration form',

    'attending_as' => 'I am attending as',
    'consent' => 'I accept the terms and privacy policy, and consent to my name and organisation appearing on the delegate list.',
    'after_note' => 'Institutional addresses are confirmed straight away. An address from a free mail service is checked by the protocol team first, and the badge follows the approval.',

    'types' => [
        'government' => ['label' => 'Government', 'note' => 'Ministries, directorates and government bodies'],
        'official' => ['label' => 'Official', 'note' => 'Universities, missions, NGOs, media and associations'],
        'private' => ['label' => 'Private sector', 'note' => 'Companies and businesses'],
        'individual' => ['label' => 'Individual', 'note' => 'Attending on your own account'],
    ],

    'steps' => [
        'delegate' => 'Delegate & institution',
        'programme' => 'Programme & protocol',
    ],
    'step_of' => 'Step :current of :total · Confirmation and badge arrive by email',

    'step1' => [
        'email_note' => 'Your confirmation and badge are sent here.',
        'country_code' => 'Country code',
        'heading' => 'Delegate and institution',
        'lead' => 'Name and institution are printed on the badge exactly as entered here.',
        'name' => 'Full name',
        'name_hint' => 'As it should appear on the badge',
        'position' => 'Title or position',
        'organization' => 'Organisation or company',
        'position_hint' => 'e.g. Director General',
        'org_government' => 'Ministry, directorate or government body',
        'org_official' => 'Organization or institution name',
        'org_hint_government' => 'e.g. Ministry of Higher Education and Scientific Research',
        'org_hint_official' => 'e.g. University of Sulaimani',
        'org_note' => 'Printed as the second line of the badge.',
        'department' => 'Department or unit',
        'org_type' => 'Organization type',
        'website' => 'Organization website',
        'linkedin' => 'LinkedIn profile',
        'email' => 'Official email',
        'email_hint' => 'name@institution.gov.krd',
        'email_free' => 'Free-mail address: your RSVP will be reviewed before the badge is issued.',
        'email_institutional' => 'Institutional addresses are approved automatically.',
        'phone' => 'Mobile number',
        'phone_placeholder' => '751 000 0000',
        'city' => 'City',
        'delegation' => 'Delegation size',
    ],

    'step2' => [
        'heading' => 'Programme and protocol',
        'sessions' => 'Sessions you will attend',
        'interpretation' => 'Interpretation required',
        'letter' => 'Official letter of invitation',
        'speaking' => 'Are you speaking or presenting?',
        'speaking_title' => 'Session title',
        'speaking_bio' => 'Short biography',
        'speaking_photo' => 'Headshot',
        'media' => 'Media accreditation needed?',
        'media_outlet' => 'Outlet name',
        'media_id' => 'Press ID',
        'media_crew' => 'Crew size',
        'dietary' => 'Dietary requirements',
        'notes' => 'Notes to the organisers (accessibility, dietary, protocol)',
        'notes_placeholder' => 'Optional',
        'consent' => 'I accept the terms and privacy policy, and consent to my name and institution appearing on the delegate list.',
        'review_note' => 'RSVPs from non-institutional addresses are reviewed by the protocol team before the badge is issued. You will hear back within two working days.',
    ],

    'submit' => 'Submit RSVP',

    'done' => [
        'kicker' => 'RSVP received',
        'title' => 'Your RSVP is confirmed',
        'title_pending' => 'Your RSVP is with the protocol team',
        'lead' => 'A confirmation email with your badge PDF, the Day 1 programme and a calendar invitation has been sent to :email.',
        'lead_pending' => 'The protocol team reviews RSVPs from non-institutional addresses. You will hear back at :email within two working days, and the badge follows on approval.',
        'inbox' => 'Sent to your inbox',
        'contents' => [
            'Formal RSVP confirmation with your name, title and institution',
            'Badge PDF (A6) with the QR, name and institution printed',
            'Day 1 programme summary and interpretation details',
            'Venue address, Gate B entrance instructions and a calendar invitation',
        ],
        'actions' => 'Actions',
        'download_pdf' => 'Download badge — PDF (A6)',
        'calendar' => 'Add to calendar (.ics)',
        'modify' => 'Modify or cancel this RSVP',
        'gate_note' => 'Official delegations enter through Gate B on Salim Street, where protocol staff will meet you from 08:15. Contact the organising team on :email.',
        'day1_programme' => 'Day 1 programme',
    ],

    'duplicate' => [
        'title' => 'This email already has an RSVP',
        'body' => 'We have an RSVP for this address. We can send the confirmation and badge again.',
        'resend' => 'Resend my confirmation',
        'resent' => 'Sent. Check your inbox, including the junk folder.',
    ],

    'manage' => [
        'title' => 'Your RSVP',
        'lead' => 'You can cancel your place here. To change any detail, reply to the confirmation email and the protocol team will update it.',
        'cancel' => 'Cancel my RSVP',
        'cancelled' => 'Your RSVP is cancelled. The badge is no longer valid at the gate.',
    ],

    'options' => [
        'org_type' => [
            'university' => 'University',
            'international' => 'International organization',
            'ngo' => 'NGO',
            'diplomatic' => 'Diplomatic mission',
            'private' => 'Private sector',
            'media' => 'Media',
            'association' => 'Association',
            'other' => 'Other',
        ],
        'delegation' => [
            'self' => 'Self only',
            'plus1' => '+1',
            'plus2' => '+2',
            'plus3' => '+3 or more',
        ],
        'interpretation' => [
            'ku' => 'Kurdish',
            'ar' => 'Arabic',
            'none' => 'None',
        ],
        'letter' => [
            'yes' => 'Yes',
            'no' => 'No',
        ],
        'dietary' => [
            'none' => 'None',
            'vegetarian' => 'Vegetarian',
            'halal' => 'Halal only',
            'other' => 'Other',
        ],
    ],

    'errors' => [
        'name' => 'Enter the name exactly as it should be printed on the badge.',
        'position' => 'Enter your title or position.',
        'organization' => 'Enter the institution printed on your badge.',
        'email' => 'Enter a valid official email address.',
        'phone' => 'Enter a valid mobile number.',
        'city' => 'Choose your city.',
        'sessions' => 'Choose at least one session.',
        'consent' => 'You need to accept the terms and the delegate list consent.',
    ],
];
