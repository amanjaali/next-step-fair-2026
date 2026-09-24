<?php

/**
 * The Next Step National Scholarship Program.
 *
 * The tone is deliberately plain. A seventeen-year-old deciding whether to spend
 * an evening on an application deserves to be told what is required, what is
 * judged, and what happens if they fall short — in words, not in policy language.
 */
return [

    'name' => 'National Scholarship Program',
    'seats_word' => 'seats',

    'nav' => [
        'home' => 'Overview',
        'about' => 'About',
        'universities' => 'Universities',
        'guidelines' => 'Guidelines',
        'recipients' => 'Recipients',
        'apply' => 'Apply',
        'status' => 'My application',
        'committee' => 'The committee',
    ],

    'region_types' => [
        'province' => 'Province',
        'independent' => 'Independent administration',
    ],

    'tiers' => [
        'founding' => 'Founding donor',
        'donor' => 'Donor',
    ],

    'housing' => [
        'included' => 'Included',
        'contribution' => 'Contribution',
        'none' => 'Not included',
    ],

    'rubric' => [
        'academic' => 'Academic background',
        'feasibility' => 'Project feasibility',
        'interview' => 'Interview',
    ],

    'rubric_notes' => [
        'academic' => 'Exam average, stream and consistency across the years.',
        'feasibility' => 'Whether the proposal could actually start here, with what exists.',
        'interview' => 'Clarity, motivation, and commitment to come back and work.',
    ],

    'documents' => [
        'certificate' => ['name' => 'Grade 12 certificate or provisional result', 'note' => 'PDF or a clear photo'],
        'national_id' => ['name' => 'National ID or civil status card', 'note' => 'Both sides'],
        'residence' => ['name' => 'Residence confirmation', 'note' => 'Issued in your region'],
        'judicial_record' => ['name' => 'Judicial record certificate', 'note' => 'Up to two weeks to issue'],
    ],

    'timeline' => [
        'opens' => ['title' => 'Applications open', 'note' => 'The form opens after the eligibility check.'],
        'closes' => ['title' => 'Deadline', 'note' => 'Nothing is accepted after this date, for any reason.'],
        'screening' => ['title' => 'Screening', 'note' => 'Documents and the 85% baseline are checked before any file is read.'],
        'interviews' => ['title' => 'Interviews', 'note' => 'Shortlisted candidates meet the committee, in person or online.'],
        'decisions' => ['title' => 'Decisions', 'note' => 'Every applicant hears back, including those who were not selected.'],
    ],

    /* ------------------------------------------------------------- home -- */

    /*
     * Shown instead of "Start your application" to anybody signed in who is not
     * a student — a parent, a delegate. The application is student-only, and
     * being told that up front is better than pressing a button and being
     * turned away by the gate.
     */
    'not_for_you' => [
        'title' => 'The application is for students',
        'body' => 'Only a student with a Next Step account can apply — they answer the eligibility questions and write the statement themselves. If you are here for somebody, this is the page to put in front of them.',
        'send' => 'Send this to your student',
        'register' => 'How a student registers',
        'message' => "The Next Step National Scholarship Program is open — funded places at partner universities and institutes across the Kurdistan Region.\n\nYou apply yourself, with a Next Step account. Everything is here:\n:url",
    ],

    'home' => [
        'title' => 'Overview',
        'kicker' => 'Cycle :cycle',
        'heading' => '376 scholarships. 12 universities and institutes.',
        'lead' => 'The National Scholarship Program funds a first undergraduate degree, at a participating university or institute in the Kurdistan Region. Scholarship details vary by university and institute, and include both free (fully funded) and subsidized (partially funded) scholarships. Check what each one offers before you apply.',
        'cta' => 'Start your application',
        'cta_rules' => 'Read the guidelines',

        'quota_title' => 'This cycle\'s confirmed scholarships',
        'quota_lead' => 'Every seat sits with the university that funds it. Here is what each partner has confirmed for this cycle.',
        'quota_note' => 'Seat counts reflect what each university has confirmed for this cycle and can change before applications close.',
        'seats_confirming' => 'Confirming',

        'covers_title' => 'What a scholarship can include',
        'covers' => [
            ['title' => 'Tuition', 'body' => 'At many universities, full tuition for every year of the degree, paid directly to the university. Some offer partial tuition instead — each university\'s page says which.'],
            ['title' => 'A living stipend', 'body' => 'At some universities, paid monthly through the academic year, so a seat does not depend on a second job.'],
            ['title' => 'Books and materials', 'body' => 'At some universities, an annual allowance for the things a course actually requires.'],
            ['title' => 'Housing, where offered', 'body' => 'Some universities include it, some contribute. Each one says which on its page.'],
        ],

        'universities_title' => 'Where the seats are',
        'universities_all' => 'All participating universities',

        'deadline_kicker' => 'Applications close',
        'deadline_note' => 'Date to be announced',
    ],

    'institution_names' => [
        'azmar' => 'Azmar Technical and Vocational Institute',
        'lutka' => 'Lutka Technical and Vocational Institute',
        'nit' => 'National Institute of Technology',
        'komar' => 'Komar University of Science and Technology',
        'qaiwan-intl' => 'Qaiwan International University',
        'bright' => 'Bright Technical and Vocational Institute',
        'kti' => 'Kurdistan Technical Institute',
        'auis' => 'American University of Iraq, Sulaimani',
        'nishtiman' => 'Nishtiman Institute',
        'lebanese-french' => 'Lebanese French University',
        'knowledge' => 'Knowledge University',
        'jihan' => 'Jihan University',
    ],

    /* ------------------------------------------------------------ about -- */

    'about' => [
        'title' => 'About',
        'kicker' => 'About the programme',
        'heading' => 'Why this programme exists',
        'lead' => 'A student with the grades and without the money is a loss the region cannot afford. This is a straightforward attempt to close that gap, one accepted student at a time.',
        'sections' => [
            [
                'title' => 'The problem it addresses',
                'body' => [
                    'Every year students finish grade 12 with an average high enough for medicine, engineering or law, and go to work instead. Not because they were not good enough, but because a private university degree costs more than a family earns.',
                    'Public places are limited and competitive, and the subjects with the strongest demand are the hardest to get into. The gap between what a student could do and what they end up doing is, very often, only money.',
                ],
            ],
            [
                'title' => 'Why regional quotas',
                'body' => [
                    'A single national ranking would send almost every seat to the largest cities, where schools are better resourced and results are higher. That is not a fair contest, and it is not what the region needs.',
                    'Holding seats per region means a student in Byara or Chwarqurna competes against their own province rather than against Sulaymaniyah Centre. Eight seats for each province, two for each independent administration.',
                ],
            ],
            [
                'title' => 'What is expected in return',
                'body' => [
                    'Recipients are asked to finish the degree, to keep the university\'s academic standing, and to work in the Kurdistan Region for at least two years afterwards.',
                    'The proposal each applicant writes is part of this: a problem here, and something they could realistically do about it with the degree they are asking for.',
                ],
            ],
        ],
        'cta_note' => 'Applications for the current cycle are open to students finishing grade 12 in the Kurdistan Region.',
    ],

    /* ------------------------------------------------------- guidelines -- */

    'guidelines' => [
        'title' => 'Guidelines',
        'kicker' => 'The rules, in full',
        'heading' => 'Who can apply, and how it is judged',
        'lead' => 'Everything here is published before applications open and does not change during a cycle.',

        'who_title' => 'Who can apply',
        'who' => [
            'You completed grade 12 at a school inside the Kurdistan Region.',
            'Your national exam average is 85% or above. Results still pending are accepted at application, but the certificate must follow within seven days of publication.',
            'You are beginning your first undergraduate degree in the 2026–2027 academic year.',
            'You do not already hold a full scholarship from another programme. A partial discount or a school award does not disqualify you.',
            'You can provide a national ID, residence confirmation and a judicial record certificate before the deadline.',
        ],

        'scoring_title' => 'How files are scored',
        'scoring_lead' => 'Screening happens first and is pass or fail: without the documents and the 85% baseline, a file is closed before any committee member reads it. Everything that survives screening is scored out of 100.',

        'documents_title' => 'Documents',
        'documents_lead' => 'All four are required at screening. Request the judicial record certificate early — it can take up to two weeks, and a file missing it at the deadline is closed unread.',

        'timeline_title' => 'Dates for this cycle',

        'committee_link' => 'The committee is named publicly, along with what each member scores and when they must recuse themselves.',
    ],

    /* ------------------------------------------------------- committee -- */

    'committee' => [
        'title' => 'The committee',
        'kicker' => 'Who decides',
        'heading' => 'The selection committee',
        'lead' => 'Named publicly, because an applicant who is turned down is entitled to know who decided and on what basis.',
        'external' => 'External members',
        'external_note' => 'Academics from universities across the region. They score the files. They are not employed by Next Step.',
        'internal' => 'Next Step members',
        'internal_note' => 'They run the process — screening, logistics, compliance — and do not score files.',
        'how_title' => 'How decisions are made',
        'how' => [
            'Each surviving file is scored independently by at least two external members before any discussion.',
            'A member with a family, teaching or financial connection to an applicant recuses themselves from that file, and the recusal is recorded.',
            'Regional quotas are filled from the ranked list within each region. A high score in one region cannot take a seat from another.',
            'The chair votes only to break a tie.',
            'Every applicant is told the outcome, and an applicant who is not selected can ask for the reason in writing.',
        ],
    ],

    /* ------------------------------------------------------ recipients -- */

    'recipients' => [
        'title' => 'Recipients',
        'kicker' => 'Recipients',
        'heading' => 'The students the programme has funded',
        'lead' => 'Published after each cycle closes, with the region and the subject for every seat awarded.',
        'empty_title' => 'The first cycle is still open',
        'empty_body' => 'Recipients for :cycle will be published here once decisions are made and every applicant has been told the outcome. Until then this page stays empty rather than showing names that do not exist yet.',
        'quota_title' => 'Where this cycle\'s seats are',
        'quota_lead' => '376 in total, across 12 universities and institutes.',
    ],

    /* ---------------------------------------------------- universities -- */

    'universities' => [
        'title' => 'Universities',
        'kicker' => 'Participating universities',
        'heading' => 'Where the seats are, and in what',
        'lead' => 'Each university pledges seats to named departments. Check that the subject you want is funded somewhere before you apply — the number beside a department is the seats it holds this cycle.',
    ],

    'university' => [
        'city' => 'City',
        'language' => 'Teaching language',
        'founded' => 'Founded',
        'housing' => 'Housing',
        'seats_title' => 'Seats pledged this cycle',
        'seats_lead' => 'Seats are held per department, not pooled. A seat in Pharmacy cannot be moved to Law.',
    ],

    /* ----------------------------------------------------------- region -- */

    'region' => [
        'lead' => '{1} One seat is held for :region this cycle.|[2,*] :count seats are held for :region this cycle. You compete against other applicants from this region only.',
        'seats' => 'Seats this cycle',
        'districts' => 'Districts',
        'districts_title' => 'Districts in this quota',
        'districts_lead' => 'Pick the district where you sat your grade 12 exams. It is what places you in this quota.',
    ],

    /* ------------------------------------------------------ eligibility -- */

    'eligibility' => [
        'title' => 'Eligibility check',
        'kicker' => 'Before you start',
        'heading' => 'Two questions, about a minute',
        'lead' => 'This runs before the application opens so nobody spends an evening writing a statement and a proposal only to be closed at screening for something we could have told them now.',
        'submit' => 'Continue',
        'answered' => 'answered',
        'remaining' => ':count still to answer',
        'gate_blocked' => 'The eligibility check opens once you have a Next Step student account. Here is what is still needed.',
        'continue' => 'Open the application',
        'note' => 'Your answers are saved and can be changed until you submit the application.',

        'pass_title' => 'You are eligible to apply',
        'pass_body' => 'Nothing in your answers stops you. The application is four steps and saves as you go, so you can stop and come back.',
        'warn_title' => 'You can apply, with something to sort out',
        'fail_title' => 'You cannot apply this cycle',

        'errors' => [
            'answer_all' => 'Answer both questions.',
        ],

        'questions' => [
            'year' => [
                'title' => 'Will you begin your first undergraduate degree in the 2026–2027 academic year?',
                'note' => 'Asked so the committee has this on file — it does not decide whether you can apply.',
                'options' => ['y' => 'Yes', 'n' => 'No'],
            ],
            'funded' => [
                'title' => 'Do you already hold a full scholarship from another programme?',
                'note' => 'A partial discount or a school award does not disqualify you.',
                'options' => ['n' => 'No', 'p' => 'Partial funding only', 'y' => 'Yes, full'],
                'fail' => 'A student already holding full funding cannot take a second full award. Declining the other programme in writing makes you eligible to apply.',
            ],
        ],
    ],

    /* ------------------------------------------------------------ apply -- */

    'apply' => [
        'title' => 'Apply',
        'kicker' => 'Application · cycle :cycle',
        'gate_heading' => 'Three things, then the form',
        'gate_lead' => 'All three are shown here with where you stand, so you are not sent between pages discovering one requirement at a time.',

        'gate1_title' => 'A Next Step account',
        'gate1_body' => 'One account for the expo, the panels, the seminars and this. Your progress saves against it.',
        'gate1_cta' => 'Register as a student',

        'gate2_title' => 'The eligibility check',
        'gate2_body' => 'Two questions on your start date and other funding. About a minute.',
        'gate2_cta' => 'Start the check',
        'gate2_review' => 'Review your answers',

        'gate3_title' => 'The application',
        'gate3_body' => 'Four steps: your region, your grade 12 record and choices, your statement and proposal, then review.',
        'gate3_cta' => 'Open the application',

        'state_done' => 'Done',
        'state_required' => 'Required',
        'state_locked' => 'Locked',
        'state_passed' => 'Passed',
        'state_open' => 'Open',
        'state_submitted' => 'Submitted',

        'have_account' => 'Already registered as a student? Sign in and the application will know who you are.',

        'not_eligible' => [
            'stage' => [
                'title' => 'This cycle is for students finishing school',
                'body' => 'The award funds a first undergraduate degree from year one, so it is open to students in grade 12 and those who have just finished. Your account\'s education stage does not currently match that. If it is wrong, update it from your account page.',
                'action' => 'Update my education stage',
            ],
            'unconfirmed' => [
                'title' => 'Your registration is not confirmed yet',
                'body' => 'The scholarship opens once your Next Step registration is confirmed. This is usually quick — check back shortly, or speak to the registration desk if it has been longer than a day.',
            ],
        ],

        'ready_title' => 'What to have ready',
        'ready_lead' => 'Two of these take time to obtain. Start them before you start writing.',
        'ready' => [
            ['what' => 'Grade 12 certificate or provisional result', 'note' => 'PDF or photo'],
            ['what' => 'National ID or civil status card', 'note' => 'Both sides'],
            ['what' => 'Residence confirmation', 'note' => 'Issued in your region'],
            ['what' => 'Judicial record certificate', 'note' => 'Up to two weeks'],
            ['what' => 'Personal statement', 'note' => '50–600 words'],
            ['what' => 'Problem-solving proposal', 'note' => '50–800 words'],
        ],

        'steps' => [
            'profile' => 'Your region',
            'academic' => 'Record and choices',
            'statement' => 'Statement and proposal',
            'review' => 'Review and submit',
        ],
        'step_n' => 'Step :n',
        'autosave' => 'Saved as you go — you can close this and come back',
        'complete' => ':percent% complete',
        'save_continue' => 'Save and continue',

        'region_title' => 'The quota you compete in',
        'region_lead' => 'This must be the region where you completed grade 12. It sets which quota you compete in and cannot be changed after you submit.',
        'district_placeholder' => 'Choose your district',
        'seats' => '{1} 1 seat|[2,*] :count seats',

        'academic_title' => 'Your grade 12 record',
        'academic_lead' => 'If your results are still pending, say so — you can still apply, and upload the certificate within seven days of publication.',
        'results' => ['published' => 'Published', 'pending' => 'Still pending'],

        'choices_title' => 'Where you want to study',
        'choices_lead' => 'A first choice is required. A second is optional but useful, because seats are held per department and the one you want may already be taken in your region.',
        'requirements_title' => 'Before you choose this one',
        'requirements_none' => 'Nothing has been added for this university yet.',
        'requirements_ack' => 'I have read all of the above and understand it applies to this choice.',
        'choice_form_hint' => 'Photograph or scan the form the department gave you and attach it here. JPG, PNG or PDF, up to 8 MB.',
        'choice_form_uploaded' => 'A form is already attached. Choosing a file replaces it.',

        'external_form_title' => 'This university also has its own application form',
        'external_form_lead' => ':university asks applicants to also complete their own form, separate from this one. Open it, complete it there, then confirm below.',
        'external_form_cta' => "Open :university's form",
        'external_form_ack' => "I have completed :university's own application form.",

        'statement_title' => 'Your statement and proposal',
        'statement_lead' => 'These are what the committee actually reads. Write them yourself, in your own words.',
        'statement_prompt' => 'Who you are, what you want to study, and why it matters to you.',
        'proposal_prompt' => 'A problem in the Kurdistan Region, and something you could realistically do about it with this degree.',
        'word_range' => ':min–:max words',

        'review_title' => 'Check it before it goes',
        'review_lead' => 'Nothing can be changed after you submit. Read it once more.',
        'pending_results' => 'Results pending',
        'missing' => 'Not filled in',
        'words' => 'words',

        'confirm' => 'I confirm the information here is true, and I understand that a false statement withdraws the award at any stage, including after it is granted.',
        'submit_final' => 'Submit my application',
        'submit_note' => 'You will get a confirmation on WhatsApp and by email, and can follow the status from My application.',

        'errors' => [
            'region' => 'Choose the region where you completed grade 12.',
            'district' => 'Choose your district.',
            'average' => 'Enter your exam average, or say that results are still pending.',
            'statement' => 'Write your personal statement.',
            'statement_short' => 'The personal statement should be at least 50 words.',
            'statement_long' => 'The personal statement should be at most 600 words.',
            'proposal' => 'Write your proposal.',
            'proposal_short' => 'The proposal should be at least 50 words.',
            'proposal_long' => 'The proposal should be at most 800 words.',
            'incomplete' => 'Some parts of the application are still empty. Go back through the four steps before submitting.',
            'confirm' => 'Confirm that the information is true before submitting.',
            'first_choice_ack' => 'Read the requirements for your first choice, then check the box to confirm.',
            'second_choice_ack' => 'Read the requirements for your second choice, then check the box to confirm.',
            'third_choice_ack' => 'Read the requirements for your third choice, then check the box to confirm.',
            'fourth_choice_ack' => 'Read the requirements for your fourth choice, then check the box to confirm.',
            'fifth_choice_ack' => 'Read the requirements for your fifth choice, then check the box to confirm.',
            'external_form_ack' => "Open the university's own application form and complete it, then check the box to confirm.",
            'choice_form' => 'Attach the filled form for this choice.',
            'choice_form_type' => 'The form must be a JPG, PNG, WEBP or PDF file.',
            'choice_form_size' => 'The form must be smaller than 8 MB.',
            'duplicate_choice' => 'You have already listed this department as another choice. Pick a different one, or a different university.',
        ],

        'f' => [
            'name' => 'Name',
            'region' => 'Region',
            'district' => 'District',
            'results' => 'Exam results',
            'average' => 'Exam average',
            'stream' => 'Stream',
            'school' => 'School',
            'first_university' => 'First choice university',
            'second_university' => 'Second choice university',
            'third_university' => 'Third choice university',
            'fourth_university' => 'Fourth choice university',
            'fifth_university' => 'Fifth choice university',
            'department' => 'Department',
            'choice_form' => 'Filled form from the department',
            'first_choice' => 'First choice',
            'second_choice' => 'Second choice',
            'third_choice' => 'Third choice',
            'fourth_choice' => 'Fourth choice',
            'fifth_choice' => 'Fifth choice',
            'statement' => 'Personal statement',
            'proposal' => 'Problem-solving proposal',
        ],

        'add_choice' => '+ Add another choice',
        'remove_choice' => 'Remove',
    ],

    /* ----------------------------------------------------------- status -- */

    'status' => [
        'title' => 'My application',
        'kicker' => 'Cycle :cycle',
        'heading' => 'Your application, :name',
        'just_submitted' => 'Your application is in. You will hear from us at every stage, including if you are not selected.',
        'not_submitted' => 'Your application is started but not submitted. Nothing is read until you submit it.',
        'continue' => 'Continue the application',
        'summary' => 'What you submitted',
        'seats_here' => 'Seats in your region',
        'submitted_on' => 'Submitted',
        'scoring' => 'How it will be scored',
        'scoring_note' => 'Screening is pass or fail. Everything past it is scored out of 100 on these three.',

        'stages' => [
            'submitted' => ['title' => 'Submitted', 'note' => 'We have your application and the documents attached to it.'],
            'screening' => ['title' => 'Screening', 'note' => 'Documents and the 85% baseline are checked. No file is read before this passes.'],
            'shortlisted' => ['title' => 'Shortlisted', 'note' => 'Scored by two external committee members and put forward for interview.'],
            'interview' => ['title' => 'Interview', 'note' => 'A conversation with the committee, in person or online.'],
            'decided' => ['title' => 'Decision', 'note' => 'Every applicant is told the outcome, selected or not.'],
        ],

        /*
         * The result itself, said plainly.
         *
         * A tracker that moves a marker to "Decision" and stops has told the
         * applicant that somebody knows the answer and they do not. Whichever
         * way it went, it is written here.
         */
        'outcome' => [
            'decided_on' => 'Decided',
            'awarded' => [
                'kicker' => 'The result',
                'title' => 'You have been awarded a scholarship',
                'body' => 'The committee has selected you for the National Scholarship Program, cycle :cycle. Congratulations — this is a fully funded place.',
                'next' => 'The scholarship office will contact you on the number and the email on your account to confirm your place and the paperwork. Keep both up to date until you hear from them.',
            ],
            'reserve' => [
                'kicker' => 'The result',
                'title' => 'You are on the reserve list',
                'body' => 'Your application was strong enough to be held in reserve for cycle :cycle. If a place is released, reserves are offered it in order.',
                'next' => 'Nothing is required from you now. If your place comes up, the scholarship office will contact you on the number and the email on your account.',
            ],
            'declined' => [
                'kicker' => 'The result',
                'title' => 'You were not selected this time',
                'body' => 'Your application for cycle :cycle was not selected. There are far more applications than funded places, and being turned down here says nothing about what you can do next.',
                'next' => 'The scholarship page lists the other routes open to you — university offers, partner scholarships and the opportunities board — and you can apply again in the next cycle.',
            ],
        ],

        /* The mark on their account once they hold a scholarship. */
        'award' => [
            'eyebrow' => 'National Scholarship Program',
            'title' => 'Scholarship awarded',
            'note' => 'Cycle :cycle',
            'link' => 'See my application',
        ],
    ],
];
