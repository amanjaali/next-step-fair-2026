<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\Attendee\InterestsController;
use App\Http\Controllers\Attendee\ProfileController;
use App\Http\Controllers\Attendee\QuickPassController;
use App\Http\Controllers\Attendee\ShareController;
use App\Http\Controllers\Attendee\SignInController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Institution\PortalAuthController;
use App\Http\Controllers\Institution\PortalController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OfferPopupController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Registration\ConferenceRsvpController;
use App\Http\Controllers\Registration\FairRegistrationController;
use App\Http\Controllers\Scholarship\ApplicationController;
use App\Http\Controllers\Scholarship\ScholarshipController;
use App\Http\Controllers\SpeakerController;
use Illuminate\Support\Facades\Route;

/*
| Every route in this file is mounted under /{locale}/ and carries the site
| chrome. Route names are language-neutral: route('agenda') resolves to the
| current locale through the URL default set in SetLocale.
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

/* --------------------------------------------------------------- about ---- */
Route::get('about', [PageController::class, 'about'])->name('about');
Route::get('fair', [PageController::class, 'fair'])->name('fair');
Route::get('conference', [PageController::class, 'conference'])->name('conference');
// Guidance on the form that decides where a school-leaver studies, and the
// seven places the desks sit this year.
Route::get('zankoline', [PageController::class, 'zankoline'])->name('zankoline');
Route::get('sdg', [PageController::class, 'sdg'])->name('sdg');
Route::get('reports', [PageController::class, 'reports'])->name('reports');
Route::get('scholarships', [PageController::class, 'scholarships'])->name('scholarships');

/* ------------------------------------------------------------ opportunities ---- */
/*
| What partners bring to Next Step: ministry scholarships, university offers,
| places on programmes. The board is the concrete answer to "why register?", so
| it is shown to people who have, and everyone else gets the one sentence that
| says what they are missing.
*/
Route::get('opportunities', [OpportunityController::class, 'index'])->name('opportunities');
Route::get('opportunities/{slug}', [OpportunityController::class, 'show'])->name('opportunities.show');
Route::get('opportunities/{slug}/go', [OpportunityController::class, 'go'])->name('opportunities.go');

/* --------------------------------------------------- scholarship program ---- */
/*
| The National Scholarship Program is its own thing with its own pages, but it
| is not a separate website: it sits in the main menu and it reads the same Next
| Step ID, so a student who registered for the expo applies without signing up
| for anything a second time.
*/
Route::prefix('scholarship')->name('scholarship.')->group(function () {
    Route::get('/', [ScholarshipController::class, 'home'])->name('home');
    Route::get('about', [ScholarshipController::class, 'about'])->name('about');
    Route::get('guidelines', [ScholarshipController::class, 'guidelines'])->name('guidelines');
    Route::get('committee', [ScholarshipController::class, 'committee'])->name('committee');
    Route::get('recipients', [ScholarshipController::class, 'recipients'])->name('recipients');
    Route::get('universities', [ScholarshipController::class, 'universities'])->name('universities');
    Route::get('universities/{slug}', [ScholarshipController::class, 'university'])->name('university');
    Route::get('regions/{code}', [ScholarshipController::class, 'region'])->name('region');

    // The gate is open to anyone: it is how a visitor finds out what applying
    // needs. Everything past it wants a verified student account.
    Route::get('apply', [ApplicationController::class, 'gate'])->name('apply');

    Route::get('apply/eligibility', [ApplicationController::class, 'eligibility'])->name('eligibility');
    Route::post('apply/eligibility', [ApplicationController::class, 'saveEligibility'])->name('eligibility.save');
    Route::get('apply/form', [ApplicationController::class, 'form'])->name('apply.form');
    Route::post('apply/form', [ApplicationController::class, 'save'])->name('apply.save');
    Route::post('apply/submit', [ApplicationController::class, 'submit'])->name('apply.submit');
    Route::get('my-application', [ApplicationController::class, 'status'])->name('status');
});
Route::get('privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('terms', [PageController::class, 'terms'])->name('terms');
Route::get('press-kit', [PageController::class, 'pressKit'])->name('press');

/* -------------------------------------------------------- registration ---- */
Route::prefix('register')->name('register.')->group(function () {
    Route::get('/', [FairRegistrationController::class, 'hub'])->name('hub');

    Route::get('fair', [FairRegistrationController::class, 'create'])->name('fair');
    Route::post('fair', [FairRegistrationController::class, 'store'])
        ->middleware('throttle:registration')->name('fair.store');
    Route::get('fair/verify/{registration}', [FairRegistrationController::class, 'showVerify'])
        ->name('fair.verify');
    Route::post('fair/verify/{registration}', [FairRegistrationController::class, 'verify'])
        ->middleware('throttle:otp')->name('fair.verify.submit');
    Route::post('fair/resend/{registration}', [FairRegistrationController::class, 'resend'])
        ->middleware('throttle:otp')->name('fair.resend');
    Route::get('fair/done/{registration}', [FairRegistrationController::class, 'done'])
        ->name('fair.done');
    // No ticket in the URL: the number that was just typed is already registered,
    // and which ticket that is stays server-side.
    Route::post('duplicate/resend', [FairRegistrationController::class, 'resendDuplicate'])
        ->middleware('throttle:otp')->name('duplicate.resend');

    // Visitor pass: name, phone, QR. No account, no agenda.
    Route::get('quick', [QuickPassController::class, 'create'])->name('quick');
    Route::post('quick', [QuickPassController::class, 'store'])
        ->middleware('throttle:registration')->name('quick.store');

    Route::get('conference', [ConferenceRsvpController::class, 'create'])->name('conference');
    Route::post('conference', [ConferenceRsvpController::class, 'store'])
        ->middleware('throttle:registration')->name('conference.store');
    Route::get('conference/done/{registration}', [ConferenceRsvpController::class, 'done'])
        ->name('conference.done');
});

/* ------------------------------------------------- attendee accounts ------ */
/*
| A registration is the account: no password, the phone number is the identity
| and a WhatsApp code is the proof. Everything under /me needs that session;
| the sign-in and join pages are open, and so is the agenda toggle, which holds
| a guest's choice while they go and register.
*/
Route::get('signin', [SignInController::class, 'show'])->name('attendee.signin');
// Students sign in with the Next Step ID they made when they registered.
Route::post('signin', [SignInController::class, 'password'])
    ->middleware('throttle:otp-signin')->name('attendee.signin.password');
// Everyone else just wants their badge back, by phone and a code.
Route::post('signin/badge', [SignInController::class, 'send'])
    ->middleware('throttle:otp-signin')->name('attendee.signin.send');
Route::get('signin/code', [SignInController::class, 'showCode'])->name('attendee.signin.code');
Route::post('signin/code', [SignInController::class, 'verify'])
    ->middleware('throttle:otp-signin')->name('attendee.signin.verify');
Route::post('signout', [SignInController::class, 'signOut'])->name('attendee.signout');

Route::get('join', [ProfileController::class, 'join'])->name('attendee.join');
Route::post('agenda/save/{session}', [ProfileController::class, 'toggle'])->name('me.agenda.toggle');

/*
| Where a shared link lands. Public by design — it is opened by people who have
| never been here, and it carries no ticket and names nobody.
*/
Route::get('attending/{who?}', [ShareController::class, 'attending'])->name('attending');

// Following an offer out of the popup, counted on the way past.
Route::get('popup/go/{item}', [OfferPopupController::class, 'go'])->name('popup.go');

// The artwork, as a page. The finished PNGs are screenshots of this, and it
// stays reachable so a card can be checked in all three languages.
Route::get('share/card/{variant}/{format}', [ShareController::class, 'card'])->name('share.card');

Route::middleware('auth:attendee')->group(function () {
    Route::get('me', [ProfileController::class, 'show'])->name('me');
    Route::get('me/agenda', [ProfileController::class, 'agenda'])->name('me.agenda');

    // Opening an update marks it read and goes where it is about — the point of
    // a notification is the thing it points at, not a page listing notices.
    Route::get('me/updates/{notification}', [ProfileController::class, 'openUpdate'])
        ->name('me.updates.open');

    // Their own details, at their own pace, once nothing is riding on it.
    Route::get('me/edit', [ProfileController::class, 'edit'])->name('me.edit');
    Route::post('me/edit', [ProfileController::class, 'update'])->name('me.update');

    // Telling people they are coming, which is how most people hear about this.
    Route::get('me/share', [ShareController::class, 'show'])->name('me.share');

    // What they want to study, and who teaches it.
    Route::get('me/interests', [InterestsController::class, 'edit'])->name('me.interests');
    Route::post('me/interests', [InterestsController::class, 'update'])->name('me.interests.save');
    Route::get('me/matches', [InterestsController::class, 'matches'])->name('me.matches');
    Route::post('me/matches/{organization}', [InterestsController::class, 'shortlist'])->name('me.matches.shortlist');
});

/* ------------------------------------------------ exhibitor portal --------- */
/*
| Universities describe what they teach, see who wants it, and scan badges at the
| desk. A third guard, not a role: an exhibitor must never be one permission
| change away from the admin panel.
*/
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('signin', [PortalAuthController::class, 'show'])->name('signin');
    Route::post('signin', [PortalAuthController::class, 'send'])
        ->middleware('throttle:otp-signin')->name('signin.send');
    Route::get('signin/code', [PortalAuthController::class, 'showCode'])->name('signin.code');
    Route::post('signin/code', [PortalAuthController::class, 'verify'])
        ->middleware('throttle:otp-signin')->name('signin.verify');
    Route::post('signout', [PortalAuthController::class, 'signOut'])->name('signout');

    Route::get('register', [PortalAuthController::class, 'create'])->name('register');
    Route::post('register', [PortalAuthController::class, 'store'])
        ->middleware('throttle:registration')->name('register.store');

    Route::middleware('auth:institution')->group(function () {
        Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');
        Route::get('profile', [PortalController::class, 'editProfile'])->name('profile');
        Route::post('profile', [PortalController::class, 'updateProfile'])->name('profile.save');
        Route::get('students', [PortalController::class, 'students'])->name('students');
        Route::get('leads', [PortalController::class, 'leads'])->name('leads');
        Route::get('scanner', [PortalController::class, 'scanner'])->name('scanner');
        Route::post('scanner', [PortalController::class, 'scan'])
            ->middleware('throttle:checkin-scan')->name('scan');
    });
});

/* ------------------------------------------------------------- programme -- */
Route::get('agenda', [AgendaController::class, 'index'])->name('agenda');
Route::get('agenda/export.ics', [AgendaController::class, 'ics'])->name('agenda.ics');
Route::get('agenda/export.pdf', [AgendaController::class, 'pdf'])->name('agenda.pdf');
Route::get('seminars', [AgendaController::class, 'seminars'])->name('seminars');

Route::get('speakers', [SpeakerController::class, 'index'])->name('speakers');
Route::get('speakers/{speaker:slug}', [SpeakerController::class, 'show'])->name('speakers.show');

/* ------------------------------------------------------------- directory -- */
Route::get('universities', [DirectoryController::class, 'universities'])->name('universities');
Route::get('exhibitors', [DirectoryController::class, 'exhibitors'])->name('exhibitors');
Route::get('floor-plan', [DirectoryController::class, 'floorPlan'])->name('floorplan');
Route::get('partners', [DirectoryController::class, 'partners'])->name('partners');
Route::get('sponsors', [DirectoryController::class, 'sponsors'])->name('sponsors');

// A page behind each partnership mark. Every place a partner's logo appears
// links here, so a mark is an introduction rather than a decoration.
Route::get('partners/{partner}', [DirectoryController::class, 'partner'])->name('partner');

/* --------------------------------------------------------------- content -- */
Route::get('news', [NewsController::class, 'index'])->name('news');
Route::get('news/{post:slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('blog', [BlogController::class, 'index'])->name('blog');
Route::get('blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('media', [MediaController::class, 'index'])->name('media');
Route::get('media/photos', [MediaController::class, 'photos'])->name('media.photos');
Route::get('media/photos/{album:slug}', [MediaController::class, 'album'])->name('media.album');
Route::get('media/videos', [MediaController::class, 'videos'])->name('media.videos');

Route::get('archive', [ArchiveController::class, 'index'])->name('archive');
Route::get('archive/{year}', [ArchiveController::class, 'show'])
    ->whereNumber('year')->name('archive.show');

/* ----------------------------------------------------------------- leads -- */
Route::get('contact', [ContactController::class, 'show'])->name('contact');
Route::post('contact', [ContactController::class, 'store'])
    ->middleware('throttle:leads')->name('contact.store');

Route::get('exhibit', [LeadController::class, 'exhibit'])->name('exhibit');
Route::post('exhibit', [LeadController::class, 'storeExhibit'])
    ->middleware('throttle:leads')->name('exhibit.store');
Route::post('sponsor-enquiry', [LeadController::class, 'storeSponsor'])
    ->middleware('throttle:leads')->name('sponsor.store');

Route::post('newsletter', [NewsletterController::class, 'store'])
    ->middleware('throttle:leads')->name('newsletter.store');
