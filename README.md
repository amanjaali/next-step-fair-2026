# Next Step Fair 2026

The public website, registration platform and operations dashboard for **Next Step
Fair 2026** — the Kurdistan Region's higher-education fair and the Next Step
Conference that opens it, held under the patronage of the Ministry of Higher
Education and Scientific Research.

The site runs in three languages (English, Kurdish Sorani, Arabic) with a genuine
right-to-left mirror for KU and AR. Four ways in share one pipeline but are handled
separately end to end:

| Track                        | Who                                               | Asks for                                   | Confirmation                          | Account                |
| ---------------------------- | ------------------------------------------------- | ------------------------------------------ | ------------------------------------- | ---------------------- |
| **Expo — student** (magenta) | Students                                          | One short form, plus an email and password | WhatsApp code, then a QR badge        | Yes — the Next Step ID |
| **Expo — parent** (magenta)  | Parents                                           | Name, phone, city                          | WhatsApp code, then a QR badge        | No                     |
| **Visitor pass** (magenta)   | Anyone walking in                                 | Name and phone                             | WhatsApp code, then a QR badge        | No                     |
| **Conference** (cobalt)      | Government, official, private sector, individuals | Seven fields                               | Email with an A6 PDF badge and `.ics` | No                     |

**Students register once.** That one account carries them through the expo, the
panels, the seminars, the workshops, Zankoline and the National Scholarship
Program — no second sign-up, and no form that asks again for something already on
file. Everybody else gets a badge and is left alone.

## The National Scholarship Program

Forty fully funded first degrees, held in regional quotas — eight seats for each of
the four provinces, two for each of the four independent administrations — so a
student from Halabja competes against Halabja rather than against Sulaymaniyah.

It sits in the main menu at `/{lang}/scholarship` with its own pages: the quota,
the participating universities and the departments they have pledged seats to, the
published scoring rubric, the named committee, and the guidelines in full.

Applying passes three gates, all shown at once rather than discovered one at a
time: a Next Step account, a five-question eligibility check, then a four-step
application that saves as it goes. A student who already registered for the expo
has the first gate behind them and never signs up twice.

The rules live in `config/scholarship.php` — seats, regions, eligibility questions,
the rubric, the timeline and the participating universities. They are the
programme, not settings, and changing one between cycles is a committee decision.

---

## Stack

- PHP 8.4, Laravel 13
- MySQL 8.0 (utf8mb4 — required for Kurdish and Arabic)
- Filament v4 for the admin panel, brand-themed
- Livewire + Alpine.js, Tailwind CSS v4, Vite
- `endroid/qr-code` v6 (self-hosted QR — nothing leaves the server)
- `barryvdh/laravel-dompdf` for badge PDFs
- `spatie/laravel-permission`, `spatie/laravel-translatable`, `spatie/laravel-sitemap`
- Self-hosted TinyMCE (GPL) for every rich-text field

## Local setup

One command:

```bash
bash setup.sh
```

It checks PHP, Composer and Node are present, installs both sets of dependencies,
creates a `.env` pointed at a local SQLite file, generates the keys, migrates,
seeds and builds the assets — then prints what to open and which accounts to sign
in with.

Safe to re-run: it never overwrites an existing `.env`, never regenerates a key
that already exists, and never re-seeds a database that already holds
registrations. It also copies your settings to `~/.nextstep-env-backup`, which
matters if you ever unzip a new build over the top of this folder.

Then start the site:

```bash
php artisan serve
```

### Updating over an existing folder

Unzipping a new build on top of the old one keeps your `.env` and your database,
but it does not run migrations or clear the last build's compiled files. Run
`bash setup.sh` again — it does both, and it regenerates `APP_KEY` if the file has
lost it. **Without a key nothing decrypts, so every page returns a 500 and the
seeder dies halfway with a stack trace that never mentions `.env`.**

<details>
<summary>Doing it by hand instead</summary>

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan nextstep:qr-secret          # writes TICKET_QR_SECRET

php artisan migrate --seed              # schema + demo content + demo registrations
php artisan storage:link

npm run build                           # or: npm run dev
php artisan serve
```

`.env.example` targets **production** — MySQL, no debug, real mail. For local work
also set `APP_ENV=local`, `APP_DEBUG=true`, `DB_CONNECTION=sqlite`,
`SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync` and
`MAIL_MAILER=log` — which is exactly what `setup.sh` does for you.

</details>

Sign in at `/admin`. The seeder creates one account per role.

**The password depends on where you are.** On a local machine (`APP_ENV=local`)
it is `password`, for convenience. Anywhere else the seeder either uses
`SEED_STAFF_PASSWORD` from your `.env`, or invents a strong one and prints it
once as it runs — save it then, because it is not shown again. These accounts
can issue entry credentials, so a known password on them is a way into the
venue, not a placeholder.

| Email                           | Role                 |
| ------------------------------- | -------------------- |
| `admin@nextstepfair.com`        | Super Admin          |
| `registration@nextstepfair.com` | Registration Manager |
| `editor@nextstepfair.com`       | Content Editor       |
| `gate@nextstepfair.com`         | Check-in Staff       |
| `partnerships@nextstepfair.com` | Sponsor Manager      |

`DemoDataSeeder` generates ~700 registrations, check-ins, delivery-log entries and
QR-campaign scans so the dashboard widgets have something to draw. Skip it on
production: run the seeders individually, or `php artisan db:seed --class=RoleSeeder`
followed by the content seeders you want.

**To see the site as a registered student**, sign in at `/en/signin` with
`student1@example.com` and the password `password` — the demo students are seeded as
grade 12 with an account, which is what opens the signed-in home page, the
opportunities board and the scholarship application. `student1` through `student6`
all work. These exist only because `DemoDataSeeder` ran; they are not created on
production.

### Database

MySQL is the target. `.env.example` is already pointed at it:

```
DB_CONNECTION=mysql
DB_DATABASE=nextstep
```

Create the schema with `utf8mb4_unicode_ci`:

```sql
CREATE DATABASE nextstep CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

The test suite runs on an in-memory SQLite connection (`phpunit.xml`), so tests
need no MySQL server. If you want a zero-dependency local dev database, set
`DB_CONNECTION=sqlite` and `touch database/database.sqlite` — every migration is
written to work on both.

## Environment variables

Everything below lives in `.env`. Values that must be set before go-live are
marked **required**.

### Application

| Variable       | Notes                                                                                                                  |
| -------------- | ---------------------------------------------------------------------------------------------------------------------- |
| `APP_KEY`      | **Required.** Also the key used to derive the PII lookup hashes — changing it makes existing phone/email lookups miss. |
| `APP_URL`      | **Required.** Used for QR payloads, badge URLs, canonical tags and the sitemap.                                        |
| `APP_TIMEZONE` | `Asia/Baghdad`. Event times, reminders and the arrival curve all read this.                                            |
| `APP_LOCALE`   | `en`. The fallback locale for language-neutral pages.                                                                  |

### Database, queue, cache

| Variable           | Notes                                                                                    |
| ------------------ | ---------------------------------------------------------------------------------------- |
| `DB_*`             | **Required.** MySQL 8.0+, utf8mb4.                                                       |
| `QUEUE_CONNECTION` | `database` works; Redis is recommended — every WhatsApp send and badge render is queued. |
| `CACHE_STORE`      | Redis recommended. Rate limiters and the OTP throttle use the cache.                     |
| `SESSION_DRIVER`   | `database`.                                                                              |

### Storage

| Variable          | Notes                                                                                                                                                   |
| ----------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `FILESYSTEM_DISK` | `public` locally.                                                                                                                                       |
| `BADGE_DISK`      | Where rendered badges are written. Use `s3` in production so badge downloads are served from signed, expiring URLs rather than a guessable public path. |
| `AWS_*`           | Required only when `BADGE_DISK=s3`.                                                                                                                     |

### Mail — conference track

Conference RSVPs are confirmed by email with the badge PDF and a calendar invite
attached, so mail is not optional for that track.

| Variable            | Notes                                                                                                                                                              |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `MAIL_MAILER`       | `smtp`, or `resend` with `RESEND_KEY`.                                                                                                                             |
| `MAIL_FROM_ADDRESS` | **Required.** Must be on a domain with SPF, DKIM and DMARC configured — these messages go to ministries and university leadership, and they must not land in spam. |

### WhatsApp — fair track

| Variable                        | Notes                                                                      |
| ------------------------------- | -------------------------------------------------------------------------- |
| `WHATSAPP_DRIVER`               | `log` (default) or `cloud_api`.                                            |
| `WHATSAPP_BASE_URL`             | `https://graph.facebook.com/v21.0`.                                        |
| `WHATSAPP_PHONE_NUMBER_ID`      | From the Meta app, WhatsApp → API setup.                                   |
| `WHATSAPP_BUSINESS_ACCOUNT_ID`  | WABA id, used for template lookups.                                        |
| `WHATSAPP_TOKEN`                | Permanent system-user token, not the 24-hour test token.                   |
| `WHATSAPP_WEBHOOK_VERIFY_TOKEN` | Any random string; paste the same value into Meta's webhook configuration. |
| `OTP_SMS_FALLBACK`              | Reserved for an SMS fallback provider.                                     |

**The `log` driver is the default and the system is fully functional on it.** Every
message is written to the delivery log with its rendered body; the queue, the retry
schedule, the admin "resend" action and the OTP flow all behave identically. Point
`WHATSAPP_DRIVER=cloud_api` once the credentials and approved templates are in
hand — no code changes. See **`docs/whatsapp-templates.md`** for the templates that
must be submitted to Meta and the approval lead times.

#### Testing registration before WhatsApp is connected

No message is sent on the `log` driver, so there is no code to read off a phone.
While `WHATSAPP_DRIVER=log` **and** `APP_DEBUG=true`, the verification page prints
the pending code in a dashed test-mode panel — type it in and the flow completes.

There is no fixed or master code: each registration still gets a real random one,
with the same expiry and attempt limits as production. Setting
`WHATSAPP_DRIVER=cloud_api` or `APP_DEBUG=false` removes the panel, so it cannot
reach a live site. The code is also always visible in _Messaging → Delivery log_.

### Ticketing

| Variable           | Notes                                                                                                                                                                                                                              |
| ------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `TICKET_QR_SECRET` | **Required.** HMAC key that signs every ticket QR. Generate with `php artisan nextstep:qr-secret`. Rotating it invalidates every badge already issued — after a rotation run `php artisan nextstep:badges:regenerate` and re-send. |

### Bot protection and analytics

| Variable                                      | Notes                                              |
| --------------------------------------------- | -------------------------------------------------- |
| `TURNSTILE_ENABLED`                           | `false` locally. Turn on in production.            |
| `TURNSTILE_SITE_KEY` / `TURNSTILE_SECRET_KEY` | Cloudflare Turnstile keys.                         |
| `GA4_MEASUREMENT_ID`                          | Conversion events fire on registration completion. |
| `META_PIXEL_ID`, `TIKTOK_PIXEL_ID`            | Optional; scripts are only emitted when set.       |

## Event configuration

Dates, venue, days, city list, phone countries, badge dimensions and retention
windows live in **`config/nextstep.php`** — not in the database, because they are
facts about the event rather than editable content. Changing the event dates there
re-times the agenda, the countdown, the calendar exports and the reminder schedule
in one place.

Editable content — pages, news, speakers, sessions, halls, exhibitors, downloads,
media albums, past editions — is all in the database and managed from `/admin`.

## Background work

Two processes must be running in production.

**Queue worker** — WhatsApp sends, badge rendering and mail:

```bash
php artisan queue:work --queue=default --tries=1 --timeout=120
```

Retries are handled by the message dispatcher on its own backoff schedule
(`config/whatsapp.php` → `retry_backoff`), which is why the worker itself runs with
`--tries=1`.

**Scheduler** — reminders, sitemap and pruning:

```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

The schedule is in `routes/console.php`: three-day, one-day and day-of reminders
timed off the event start date; personal agenda reminders every five minutes during
event hours; a nightly sitemap rebuild.

## Console commands

| Command                                            | What it does                                                                      |
| -------------------------------------------------- | --------------------------------------------------------------------------------- |
| `nextstep:qr-secret`                               | Generates and writes `TICKET_QR_SECRET`. `--force` to rotate.                     |
| `nextstep:badges:regenerate`                       | Re-renders badge assets. Use after a QR-secret rotation or a badge design change. |
| `nextstep:reminders {three-days\|one-day\|day-of}` | Queues the reminder wave. Skips anyone who already received that template.        |
| `nextstep:session-reminders`                       | 15-minute reminders for bookmarked sessions.                                      |
| `nextstep:sitemap`                                 | Rebuilds `public/sitemap.xml` across all three locales.                           |

## Three ways to attend

|                                              | Who it is for                    | What they get                                                                         |
| -------------------------------------------- | -------------------------------- | ------------------------------------------------------------------------------------- |
| **Visitor pass** (`/register/quick`)         | Anyone who just wants to walk in | Name and phone, one screen. A QR badge on WhatsApp, valid all three days. No account. |
| **Full registration** (`/register/fair`)     | Students and parents             | Everything above, plus a personal agenda, session reminders and a profile.            |
| **Conference RSVP** (`/register/conference`) | Government and officials         | Delegate badge by email as an A6 PDF, plus a calendar invite.                         |

A visitor pass is a real registration underneath, so the gate scanner, the head
count and the capacity figures all include it.

**Completing a pass.** "Complete my registration" on `/me` opens the four-step form
with the name and number already on it, the phone shown locked because the code
that issued the pass already proved it, and the days pre-ticked. Submitting writes
onto the same row: same ticket reference, same QR, no second code to type, and the
type changes from `visitor` to `student` or `parent`. Nothing is asked twice.

**One number, one badge.** Both registration forms check the phone against the
existing list. A number that is already registered gets told so on the page it was
typed on, with a button to resend that badge on WhatsApp and a link to sign in —
never a silent jump to somebody else's ticket, and never a second row.

## The data platform

The fair is three days; the data is the year. Both sides of the fair describe
themselves in **one shared taxonomy** — 12 sectors, 72 fields, in all three
languages — which is what makes demand and supply comparable and matching possible
at all.

- **Students** answer eight questions at `/me/interests` (fields in preference
  order, level, countries, language, budget, grade, start year, career goal) and
  get a ranked list of universities with the reasons behind each score.
- **Universities** describe what they teach at `/portal` — fields by level,
  countries, languages, tuition, scholarships, entry requirements, capacity — and
  get their matched students, the demand profile of those students, and a badge
  scanner that turns a conversation into a recorded lead.
- **Next Step** gets the answers: which fields attract the most interest, which
  countries are most wanted, **where demand outruns supply**, which exhibitors
  generated qualified engagement, and whether matching actually moved anyone to a
  desk. Admin → _Platform → Insights_.

Consent is a separate gate from matching: a student who declines to be contacted
still counts toward an institution's demand figures but appears unnamed.

Full write-up, including the scoring weights and the queries behind each dashboard:
**`docs/data-platform.md`**.

## What registering is worth

A registration form is a cost paid up front against a promise. **`/opportunities`**
is where that promise is paid back: scholarships the ministry has opened, a tuition
reduction a university negotiated with Next Step, a free summer school, a place on a
programme. Partners' offers are entered in the dashboard under _Platform →
Opportunities_ in all three languages, and only badge holders can open them.

- **The audience is chosen per offer** — everyone, students, parents, or grade 12 and
  recent leavers. A parent is never shown a school-leaver scholarship, and a
  university student is never shown one they have already passed.
- **An offer closes itself.** Past `closes_at` it leaves the board and its own page
  returns 404, so nobody applies to something that stopped taking applications.
- **A countdown is only printed inside the last fortnight** — noise on something six
  months away, the reason to read on when it closes next week.
- **Reads and follows are counted separately** — how many opened the record, and how
  many went on to the partner's form. That second number is what a partner is owed at
  the end of the season; one column for both would flatter every placement equally.

**The home page is not the same page twice.** Signed out it sells the fair and its one
large button says _register_. Signed in that button is already spent: the hero greets
the person by name and ticket, and the space the registration pitch used to hold now
carries their agenda, the scholarship, the opportunities, Zankoline, the exhibitor
list and the seminars — with the cards filtered to what that person can actually use.

## Attendee accounts

**A registration is the account.** Students set an email and a password when they
register and sign in with them — a scholarship application runs for months and has
to be reachable from a school computer or a borrowed laptop, which a code sent to
one phone is not.

Nobody else is given a password. A parent, a visitor or a conference delegate signs
in the way their badge was issued: phone number, then a WhatsApp code. Both routes
live at `/signin`, and the attendee's own area at `/me`.

Verifying the code during registration also signs the new registrant in, so nobody
is asked for the same number twice in a row.

**`/me` shows everything one attendee has done:** their badge and QR, their personal
agenda, which days they were scanned in at the gate, every message the system sent
them, their details and their consents.

**`/me/edit` is where the rest goes in.** The registration form is deliberately short
— it is the last thing between somebody and a badge — so the details it leaves out
are added here afterwards, at their own pace, along with a photo. The phone number is
shown and not editable: it is the identity behind the badge, it was proved with a
code, and letting a signed-in session change it would be a way to move somebody
else's badge onto your own handset. That one goes through the desk.

**The agenda and registration are connected.** Pressing _add to my agenda_ as a
guest holds that session, offers the choice of registering or signing in, and
attaches the session the moment either finishes — so the click is never wasted and
nobody has to find the session again afterwards.

Attendees run on their own `attendee` guard, entirely separate from the `web` guard
that staff and the Filament panel use. A signed-in student can never be mistaken
for an admin session.

## How registration works

**Fair track** — a four-step wizard (type → details → interests/days → confirm).
Submitting creates a `pending` registration and sends a 6-digit OTP over WhatsApp.
The ticket is only issued once the OTP is verified: verification confirms the
registration, mints a ticket reference, renders the QR badge and queues the
confirmation message. Wizard state persists in `localStorage`, so a dropped
connection on a phone doesn't cost the applicant their answers.

**Conference track** — a two-step form. Institutional email addresses are confirmed
immediately; addresses on free mail domains (`config/nextstep.free_mail_domains`)
land in an approval queue, because a delegate badge is an access credential. On
confirmation the registrant receives an HTML email with the badge as an A6 PDF
attachment and a calendar invite.

**At the gate** — `/checkin` is a PWA scanner. It downloads a manifest of valid
ticket hashes on load, so it keeps scanning when the hall Wi-Fi drops, queues
check-ins in `localStorage` and syncs them idempotently when the connection comes
back. Manual search by name, phone or ticket reference is available for anyone who
arrives without a badge, and walk-ins can be registered from the desk.

## QR

Two separate systems, both self-hosted:

1. **Ticket QR** — the payload is `/verify/{uuid}?sig=…`, an opaque identifier plus
   a truncated HMAC. No name, no phone, no email is encoded, so a photographed
   badge leaks nothing. `/verify/{uuid}` shows a public "valid ticket" page with no
   personal data; only an authenticated scanner sees the registrant.
2. **Campaign QR** — generate a tracked code for any URL from _QR campaigns_ in the
   admin. Codes resolve at `/q/{code}`, record a scan with referrer and user agent,
   then redirect. Used for posters, school visits and partner materials, with scan
   counts on the dashboard.

## Privacy

Registrant names, phones and emails are encrypted at rest. Because encrypted
columns can't be indexed or searched, each one has a companion keyed-hash column
(`phone_hash`, `email_hash`) derived with `hash_hmac('sha256', …, APP_KEY)` — that
is what duplicate detection and admin search query, so lookups stay fast without
storing plaintext. Consent flags are captured per registration and per channel, and
retention windows are in `config/nextstep.php`.

## Tests

```bash
php artisan test
```

Feature coverage spans the public site in all three locales, both registration
flows end to end (including OTP verification and the free-mail approval path),
ticket verification and check-in sync, and the admin panel's registration queues.

## Documentation

### The system map

Open **`docs/system-map/index.html`** in a browser. No server, no build step, no
internet — double-clicking the file is enough.

The same content also exists as **one downloadable file**,
`docs/system-map/next-step-fair-2026-system-map.html` — everything inside a single
document, so it can be emailed or kept on its own without the folder around it.

It is the whole platform drawn out: seven pages, twenty-one sections, one diagram
per flow, each followed by the files it lives in. Every audience and every path
they can take, from a poster QR code to a lead in a university's dashboard.

| Page              | Covers                                                                                                                                     |
| ----------------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| Start here        | The four audiences and five surfaces; how a URL finds its language                                                                         |
| Getting a badge   | The three doors, the four-step wizard, completing a visitor pass, the duplicate-number check, the conference RSVP                          |
| After registering | The Next Step ID and the agenda, the opportunities board, the National Scholarship Program, the interest questions and the matching engine |
| Exhibitors        | Claiming an institution, the portal, booth scans, leads and the reports they answer                                                        |
| Event days        | Gate check-in online and offline, messaging, campaign QR codes                                                                             |
| Behind it         | The dashboard, the data model, what runs when, the three logins and every rate limit                                                       |
| Every URL         | The complete route reference                                                                                                               |

The diagrams are pre-rendered SVG, so the pages carry no diagram code. To change
one, edit `docs/system-map/_source/map.html` and re-run the two commands in
`docs/system-map/_source/README.md` — the pages are generated and hand edits to
them are overwritten.

The map documents internals: rate limits, file paths, which data is encrypted. It
lives outside `public/`, so it is not served with the site. Copy it in only if you
want it reachable on the web.

### Written guides

- **`docs/whatsapp-templates.md`** — every template to submit to Meta, with body
  copy in all three languages, variable mapping and the approval process.
- **`docs/admin-guide.md`** — a walkthrough of the dashboard for the team.
- **`docs/data-platform.md`** — the taxonomy, the matching weights and the reports.
- **`design/`** — the original Claude Design handoff bundle and its transcripts.
