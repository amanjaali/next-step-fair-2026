<?php

/**
 * Copy for outbound WhatsApp messages and transactional e-mail.
 *
 * WhatsApp template bodies must match what Meta approved. These strings are the
 * source used for the template submissions and for the `log` driver preview, and
 * the variable order here matches the {{1}}, {{2}}... order in the submissions.
 */
return [

    'whatsapp' => [
        'otp' => 'Your Next Step Fair verification code is :code. It expires in 10 minutes. Do not share it with anyone.',

        'registration_confirmed_student' => "Hello :name, your registration for Next Step Fair 2026 is confirmed.\n\n28–30 September 2026, Cultural Factory, Sulaimani. Your days: :days. Entry is free.\n\nYour badge is attached. Save this message — show the QR at the entrance.\nTicket: :ticket",

        'registration_confirmed_parent' => "Hello :name, your registration for Next Step Fair 2026 is confirmed.\n\n28–30 September 2026, Cultural Factory, Sulaimani. Your days: :days. Entry is free for you and your child.\n\nYour badge is attached. Save this message — show the QR at the entrance.\nTicket: :ticket",

        // The visitor pass is valid for the whole run, so it names the dates
        // rather than a chosen subset of days.
        'registration_confirmed_visitor' => "Hello :name, your visitor pass for Next Step Fair 2026 is ready.\n\n28–30 September 2026, Cultural Factory, Sulaimani. Valid all three days. Entry is free.\n\nYour badge is attached. Save this message — show the QR at the entrance.\nTicket: :ticket",

        'rsvp_confirmed' => "Hello :name, your place at the Next Step Conference 2026 is confirmed.\n\n28 September 2026, Cultural Factory, Sulaimani. Doors 09:00, the opening session begins at 10:00.\n\nYour badge is attached. Save this message — show the QR at the delegate entrance.\nTicket: :ticket",

        'event_reminder_3days' => 'Next Step Fair 2026 opens in three days, on 28 September at the Cultural Factory in Sulaimani. Doors 10:00. Bring your QR badge and your grades.',

        'event_reminder_1day' => 'Next Step Fair 2026 opens tomorrow at 10:00, Cultural Factory, Sulaimani. Show the QR in this chat at Gate A. Free parking behind Hall C.',

        'day_of_directions' => 'Good morning :name. Next Step Fair is open today from 10:00 to 20:00 at the Cultural Factory, Salim Street, Sulaimani. Gate A is step-free. Your QR badge is in this chat.',

        'session_reminder' => ':title starts in 15 minutes in :hall. Seats are allocated on arrival.',

        'post_event_thankyou_survey' => 'Thank you for coming to Next Step Fair 2026, :name. Two minutes on what you found useful helps us plan 2027: :link',
    ],

    'email' => [
        'rsvp_subject' => 'Your RSVP for the Next Step Conference 2026 is confirmed',
        'rsvp_subject_pending' => 'Your RSVP for the Next Step Conference 2026 has been received',
        'rsvp_greeting' => 'Dear :title :name,',
        'rsvp_confirmed' => 'Your attendance at the Next Step Conference 2026 is confirmed. The conference takes place on Monday 28 September 2026 in Hall B of the Cultural Factory, Sulaimani, from 09:00 to 18:00.',
        'rsvp_pending' => 'Your RSVP for the Next Step Conference 2026 has been received and is with the protocol team for review. We will confirm within two working days, and your badge will follow with that confirmation.',
        'rsvp_badge_note' => 'Your delegate badge is attached as a PDF and carries your name, your institution and the QR code checked at the gate. The QR is also shown below in case you prefer to display it from this email.',
        'rsvp_programme' => 'Day 1 programme',
        'rsvp_venue' => 'Venue and access',
        'rsvp_venue_body' => 'Cultural Factory, Salim Street, Sulaimani. Official delegations enter through Gate B, where protocol staff will meet you from 08:15. Parking for delegations is reserved behind Hall B.',
        'rsvp_interpretation' => 'Simultaneous interpretation is provided in Kurdish, Arabic and English in every conference session.',
        'rsvp_calendar' => 'A calendar invitation (.ics) is attached.',
        'rsvp_modify' => 'Modify or cancel your RSVP',
        'rsvp_contact' => 'For anything else, the organising team is on :email.',
        'rsvp_signoff' => 'Next Step Organization',
        'rsvp_ticket' => 'Ticket reference',

        'reminder_subject_3days' => 'Next Step Conference 2026 — three days to go',
        'reminder_subject_1day' => 'Next Step Conference 2026 — tomorrow, Hall B',
        'thankyou_subject' => 'Thank you — Next Step Conference 2026',

        'badge_resent_subject' => 'Your Next Step badge',

        'lead_subject' => 'New enquiry from the Next Step website',
        'newsletter_welcome_subject' => 'You are on the Next Step list',
    ],

    'ics' => [
        'fair_title' => 'Next Step Fair 2026',
        'conference_title' => 'Next Step Conference 2026 — Day 1',
        'description' => 'Cultural Factory, Salim Street, Sulaimani. Show your QR badge at the entrance. Ticket :ticket.',
    ],
];
