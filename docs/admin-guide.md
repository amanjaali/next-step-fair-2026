# Admin guide

The dashboard lives at **`/admin`**. This is a walkthrough for the team running the
fair, not for developers — no code required anywhere below.

---

## Signing in and accounts

Five roles ship with the system. Each one only sees the parts of the dashboard it
needs, so nobody has to learn screens they will never use.

| Role | Can do |
| --- | --- |
| **Super Admin** | Everything, including staff accounts and settings. |
| **Registration Manager** | Registrations and RSVPs, approvals, messaging, exports, QR campaigns, check-in. |
| **Content Editor** | News, blog, pages, speakers, sessions, media, downloads. |
| **Check-in Staff** | The gate scanner only. |
| **Sponsor Manager** | Exhibitors, sponsors and partner leads. |

> **Before launch:** the seeded accounts all use the password `password`. Change
> every one of them from *Platform → Staff accounts*, and delete any account the
> team does not need. Anyone with a badge-issuing role can issue an access
> credential, so treat these as keys to the venue.

Adding someone: *Platform → Staff accounts → New*. Set a role, and for gate staff
set a **default gate** so their scans are attributed to the right entrance without
them picking it each shift.

---

## The dashboard

The home screen reads top to bottom the way the week runs:

1. **Headline numbers** — total registered, fair students, fair parents, conference
   RSVPs, with the count added in the last 24 hours and how many are still pending
   approval.
2. **Registrations per day** — the last 14 days. This is the curve to watch after a
   school visit or a poster drop; a flat day usually means a campaign stalled.
3. **Conversion funnel** — form started → OTP verified → confirmed. A gap between
   the first two means people are not receiving the code; a gap between the last two
   means they are receiving it and not finishing.
4. **Registrations by city** and **how they heard about us** — where to spend the
   remaining outreach budget.
5. **Check-in rate** and **arrivals by hour** — live during the event.
6. **Session popularity** — how many people bookmarked each session, which is what
   hall allocation should follow.

---

## Registrations (the fair track)

*Registrations → Fair registrations.*

Tabs across the top carry live counts: **All**, **Students**, **Parents**,
**Visitor passes**, **Awaiting OTP**, **Checked in**, **Cancelled**.

**Visitor passes** are people who chose the short form — name and phone only. They
are counted, they have a QR, and the gate treats them like anyone else; they just
have no agenda and no profile. Expect a good share of walk-up traffic to be here,
and do not read a low student count as a problem until you have looked at this tab.

**"Awaiting OTP" is the tab to watch.** Anyone sitting there started the form and
never entered the code, so they have no ticket. A handful is normal. A spike means
WhatsApp delivery has a problem — check *Messaging → Delivery log* before assuming
people lost interest.

> **While WhatsApp is not yet connected,** nothing is actually sent, so the
> verification page shows the pending code itself in a dashed amber panel. That is
> how you test the flow end to end today. It is a real code with the real
> ten-minute expiry, not a master key, and the panel disappears as soon as the
> WhatsApp credentials go in. Every code is also readable in the delivery log.

Filters: type, status, city, language, attending day, registration date range,
checked-in yes/no, walk-in yes/no. Search covers name, phone, email and ticket
reference — the encrypted fields are searchable because each has a hashed lookup
column behind it, so typing a full phone number finds the person, though a partial
number will not.

### Row actions

- **Resend on WhatsApp** — re-queues the confirmation with the badge.
- **Regenerate badge** — re-renders the QR badge. Use after a name correction or a
  QR-secret rotation.
- **Download badge** — the PNG, for printing at the desk.
- **Cancel registration** — asks for a reason, which is recorded.

All four work as bulk actions too: tick rows, act once. Cancelling a hundred rows
sends a hundred cancellations, so read the selection count before confirming.

### Walk-ins

*Add walk-in*, top right. Fills the same form the public site uses, marks the record
as a walk-in so gate numbers stay honest, and issues a badge immediately without the
OTP step — the person is standing in front of you, so the phone is already verified
by the fact that they are there.

### Export

*Export* produces a CSV of the current filtered view — filters and tab apply, so
export what you are looking at. The file contains personal data: names, phone
numbers, emails. Do not put it in a shared drive or a WhatsApp group. Universities
asking for "the list of students" get the aggregate counts, not the export.

---

## Conference RSVPs

*Registrations → Conference RSVPs.*

Tabs: **Pending approval**, **All**, **Government**, **Official**, **Invitation
letters**, **Media**.

**Pending approval is a queue that has to be worked daily.** An RSVP lands there
when it was submitted from a free mail address (gmail, outlook, yahoo and the like)
rather than an institutional one. A conference badge is an access credential for a
room with ministers in it, so we do not issue one automatically to an address anyone
can create in thirty seconds.

Working the queue: open the record, check the name, the institution and the job
title against the address. If it is plainly the person's personal address and the
institution checks out, **Approve** — that confirms the RSVP, issues the badge and
sends the email with the PDF attached. If you cannot tell, phone the institution
before approving. Reject the ones that are clearly not who they claim to be.

**Invitation letters** lists everyone who asked for a formal letter — usually needed
for travel authorisation or a visa. These have lead times attached to them and
should be handled first each morning.

Row actions mirror the fair track, except **Resend** sends the email rather than a
WhatsApp message, and it re-attaches the badge PDF and the calendar invite.

---

## Scholarship applications

*Registrations → Scholarship applications*. The number beside it is how many are
waiting to be looked at.

Every application a student submits lands here. Nothing has to be forwarded and
nothing is emailed around — the committee reads them in the dashboard.

**The queue** is ordered oldest first, so whoever applied on the first day is
looked at first. Unfinished drafts are hidden: a draft is a student still
writing, not an application. Turn off *Hide unfinished drafts* if you want to see
those too. Filter by region, by stage, or by decision.

**Opening one** shows everything as it was submitted: who they are and how to
reach them, the regional quota they compete in and how many seats it holds, their
grade 12 average and school, both university choices, the five eligibility
answers with what they were told, the personal statement and the proposal in
full, and which of the four documents are in.

Nothing a student wrote can be edited here. What the committee adds is separate:

| Field | What it is |
| --- | --- |
| **Stage** | Submitted → Screening → Shortlisted → Interview → Decision |
| **Scores** | Academic out of 40, proposal out of 30, interview out of 30 |
| **Committee notes** | Internal. Never shown to the applicant |
| **Decision** | Awarded, reserve list, or not selected — set at the Decision stage |

**Moving an application on.** The blue button on each row moves it one stage and
records the date. To move several at once, tick them and use *Move to screening*.
For anything more than a stage change — scores, notes, a decision — use **Review**.

**The applicant sees this.** The stage you set is what their own tracker shows,
with the date beside it. Move something to Interview and the student sees
Interview the next time they open the page. That is why the stage should be moved
when the work is actually done, not in advance.

**Who can see it.** Super Admin, and the *Scholarship Committee* role. A
committee member sees applications and nothing else — not registrations, not
messaging, not staff accounts — because committee members are often from outside
the organisation. Create their accounts under *Platform → Staff accounts* with
that role.

---

## Messaging

### Delivery log

*Messaging → Delivery log.* Every outbound message, with its status: queued → sent →
delivered → read, or failed with the provider's error.

This is the first place to look when someone says they did not receive anything.
Search their phone number, look at the row. `delivered` means it reached the handset
and the conversation is on their side. `failed` shows the reason — most commonly a
number that is not on WhatsApp, which is the one case where the person needs to be
called.

**Retry** re-queues a failed message. The system already retries automatically on
its own schedule (after 1 minute, 5 minutes, then 30 minutes), so a message that is
still `failed` has already had four attempts — retrying immediately without fixing
the cause will fail again.

### Message templates

*Messaging → Message templates.* The exact wording of every automated message, in
all three languages, for both channels.

Editing a WhatsApp template here changes what the system renders, but **WhatsApp
itself will only deliver wording that Meta approved.** If the copy has to change,
edit it here, submit the matching text to Meta, and mark the template approved once
it clears. Email templates need no approval and take effect immediately.

Full submission instructions, including the copy in all three languages, are in
`docs/whatsapp-templates.md`.

### Broadcasts

*Messaging → Broadcasts.* A one-off message to a filtered audience: pick the
channel, pick a template, then narrow by track, type, attending day, city, language
and status.

The audience is a saved filter, not a frozen list — the count is calculated at send
time, so a broadcast queued on Monday for Tuesday reaches everyone who registered in
between. Schedule with **Send at**, or leave it blank to send now.

Two cautions. WhatsApp will not deliver free-text outside a 24-hour window, so the
**body override** field only affects email and the log driver — for WhatsApp the
approved template wins. And every extra promotional message costs quality rating on
the number, which throttles the confirmations that actually matter. Broadcast
sparingly.

---

## Programme

*Programme → Agenda sessions* is the three-day schedule. Each session has a day, a
time, a hall, speakers, a track (fair or conference — this drives the colour on the
public site), and a **bookable** flag. Bookable sessions appear in the personal
agenda builder and trigger the 15-minute reminder; leave it off for anything with
open seating and no capacity concern.

*Speakers* and *Halls & booths* feed the session records and the venue map. A
speaker added here appears on the speakers page as soon as they are published.

Nothing is visible to the public until **Published** is on. Draft freely.

---

## Content

*News & blog*, *Pages*, *Past editions*, *Media albums*, *Downloads*, *SDG goals*,
*Why-attend cards*.

Every text field is a full rich-text editor with headings, lists, links, images,
tables, alignment, a left-to-right / right-to-left switch, full-screen mode and a
source view for when you need the raw HTML. Video goes in through the **media**
button — paste the YouTube or Vimeo URL into that dialog rather than into the body,
which is what produces a working embed. Short fields such as a speaker biography get
a cut-down toolbar: bold, italic, lists and links only.

### The home page

*Content → Home page* — the first item in the group.

The words and the photographs on the front page. Each text field is edited per
language under **EN / KU / AR** tabs.

**A box left empty keeps what the site was built with** — the grey text inside
each box is what will be used if you type nothing. Clearing a box you filled in
restores the original; it never publishes an empty heading. Filling in English
does not blank Kurdish or Arabic.

**The hero photograph.** *The opening → Background photograph*. The hero is plain
black by design and stays that way while this is empty. Upload a wide photograph
and it sits behind the title, darkened so the white text stays readable; remove it
and the black comes back.

**The event itself.** The name, the edition, both dates, the venue, the city and
the opening hours. These are not only front-page text — the same values print on
every badge, go out in every WhatsApp message, are written into the calendar file
people add to their phones, and are given to Google as the event's structured
data. Correcting a date here corrects all of them at once. An empty box keeps the
value the site was built with, so clearing the venue cannot blank the badges.

**The two buttons**, the **figures** in the black bar (leave them empty to keep
counting live), the **two cards** under Two tracks, and the **About** section with
its own photograph.

### Logos and brand marks

*Content → Logos & brand marks*.

| Slot | Where it shows |
| --- | --- |
| **Logo — for light backgrounds** | The header, the browser tab, the dashboard |
| **Logo — for dark backgrounds** | The footer, the share card, the check-in app |
| **Ministry of Higher Education** | Beside the Next Step logo in the header, and in the footer strip |
| **Kurdistan Regional Government** | The partnership strip in the footer |
| **Kurdistan Students Association** | Third in the header after the ministry mark, the footer strip, the share card, and the partners page |
| **Default share picture** | What Facebook, WhatsApp and LinkedIn show for a page with no picture of its own — 1200 × 630 |

Same rule: **empty means the logo the site was built with.** Clearing an upload
restores it rather than leaving a gap in the header of every page.

**The Kurdistan Students Association is the exception**, because no file for it
ships with the site: while its box is empty, nothing at all is drawn for it —
no gap, no broken image. Upload the logo and it appears in the header in the
agreed order (Next Step · MOHE · KSA), in the footer strip, on the share card
and on the partners page, in all three languages, on every page at once. A PNG
with a transparent background is best; any shape works, since the header caps
the mark at 40px tall and 64px wide so a wide logo cannot push the menu onto a
second line.

The ministry and the regional government also appear as partners in the directory
and on the sponsors page. Those are separate records under *Universities,
exhibitors & sponsors*, but for these three partners the sponsors page reads the
logo from **this** screen, so the file only ever has to be uploaded once.

### Every other picture

There is no page where a picture can only be changed in the code. Each is edited
on the screen that owns the thing it pictures: speaker portraits under *Speakers*,
article covers under *News & blog*, edition covers under *Past editions*, album
photographs under *Media albums*, partner logos under *Universities, exhibitors &
sponsors*, and a student's own photograph by the student, under *Edit your
details*.

### Translations

Content is edited in three languages side by side, under tabs marked **EN / KU /
AR**. English is the required one — the others fall back to it if left empty, so a
page is never blank in one language, but a visitor reading Kurdish gets English text
until someone fills it in. Kurdish and Arabic fields switch to right-to-left as you
type, which is what the public site shows.

Slugs are generated from the English title and stay stable once published; changing
a slug breaks any link already shared, so change it only before you publish.

---

## Partners and leads

*Universities, exhibitors & sponsors* holds the directory that appears on the public
site, with logos, tier and booth allocation. *Leads* collects enquiries from the
exhibit and sponsor forms — assign an owner, set a status, and keep notes on the
record so the follow-up survives a handover. *Newsletter* lists subscribers with the
date and source of each opt-in.

---

## The home page popup

*Content → Home page popup.*

The box that greets somebody the first time they open the site. Built to be
rewritten before breakfast: one screen, one toggle.

**Several offers, one popup.** Press *Add another offer* as many times as you need
and drag them into the order you want. They appear in one box. Interrupting somebody
is a cost you can only spend once — spend it on everything worth saying, not on one
offer at a time.

**Each offer takes** a title, a line or two, an optional small label ("Closes Friday",
"40 places") and an optional link. An offer with no link is simply an announcement.

**Switching it on.** One toggle, *Show this popup*. Only one popup shows at a time —
the most recently edited one that is switched on — so leaving yesterday's switched on
does no harm. That matters when you are editing in a hurry.

**Each person sees it once, and again every time you edit it.** The browser remembers
which version it dismissed, and saving changes the version. So rewriting it daily
actually reaches people; leaving it alone does not nag them. The *Last edited* column
is that version.

**Who sees it** follows the same audiences as the opportunities board. A popup aimed
at students never appears in front of a parent.

**Followed out** counts how many people clicked an offer inside it. That number is
how you decide whether the popup is earning the interruption — if it stays near zero
for a week, the wording is not working.

---

## Opportunities

*Content → Opportunities.*

This is the board at `/opportunities` — the scholarships, offers, programmes and
training that partners put in front of people who registered. A ministry scholarship
round, a tuition reduction a university agreed with Next Step, a free summer school:
each one is a record here, written in all three languages like any other content.

**Who sees it** is the field that matters most. Pick the narrowest audience that is
true:

| Audience | Reaches |
| --- | --- |
| Everyone | Anyone with a badge, students and parents and delegates alike |
| Students | Student accounts only |
| Grade 12 and leavers | Students in their final year or just out of school |
| Parents | Parents registered for the expo |

Showing a school-leaver scholarship to *everyone* is not generous, it is a waste of
the reader's evening — most of them cannot take it. Nobody who is signed out sees any
of them; they are told what is behind the board and offered registration.

**Opens and closes.** Set *Closes on* whenever there is a real deadline. The record
leaves the board by itself the day after, and its page starts returning "not found",
so nothing keeps taking applications after the partner stopped reading them. *Opens
on* holds a record back until the date — useful for writing one a fortnight early.
Leave both empty for something with no deadline. A countdown appears on the card only
inside the final two weeks.

**Where it points.** *Action link* is the partner's own form or page, and *Action
label* is the words on the button ("Apply on the MOHE portal"). The list carries two
counts: **Opened**, how many people read the record, and **Went through**, how many
followed the link to the partner. The second is the number to give a partner
afterwards — reading is interest, going through is intent.

**Featured** lifts a record into the strip at the top of the board and onto the home
page of everyone it is for. Use it for two or three at a time; featuring everything
features nothing.

Attach the partner either by picking an existing **university or sponsor**, or by
typing a name and uploading a logo for an organisation that is not in the directory.

---

## What attendees post

Every confirmed registration is offered a card and a caption — on the confirmation
page, and again any time from *My Next Step → Share*. It is not something you
operate; it runs itself. Two things are worth knowing.

**The captions are per audience, in three languages**, and they live in
`lang/{en,ku,ar}/share.php`. If the tone needs to change for a particular group, that
is the file. There is no dashboard screen for it because the wording is written once
per edition, not maintained week to week.

**The cards are pre-rendered images.** After changing the artwork, the line printed
on a card, or the event dates, somebody has to re-run one command or the pictures
will still show the old dates:

```bash
node tools/build-share-cards.cjs
```

Put it on the release checklist next to `npm run build`.

**The link in the post is `/attending`, not the home page.** That page exists so the
preview LinkedIn and Facebook draw shows the card rather than a generic logo, and so
somebody who clicked because a friend is going lands on a page about that, with a
register button on it. If you ever change the wording or the artwork, ask LinkedIn
and Facebook to re-read the page — both cache the first version they see:

- LinkedIn: <https://www.linkedin.com/post-inspector/>
- Facebook: <https://developers.facebook.com/tools/debug/>

Neither needs an account of ours or any API key. A share button is a plain link, and
the preview is read straight off the page.

**The person's name is drawn onto the card in their browser**, not stored on the
picture. That is why every card in `public/assets/share` is blank where the name
goes — it is not a mistake, and re-rendering will not change it.

**No badge QR appears on any of them**, and it never should. That code is what opens
the gate — posted publicly, anybody who screenshots it can walk in on that ticket. If
somebody asks for their badge in a shareable format, the story card is the answer.

---

## QR campaigns

*Platform → QR campaigns.*

Create a tracked QR for any URL: give it a name and a target, pick the medium
(poster, school visit, partner material, social), optionally set UTM parameters and
a track colour. Save, then **Download QR (SVG)** — vector, so it prints at any size
from a business card to a building banner.

Each campaign shows total scans, unique scans and how many registrations followed.
This is how a school visit gets compared against a poster run: same message,
different code, and the numbers tell you which one worked.

Codes resolve through the short link and then redirect, so the target URL can be
changed after the posters are printed. If a venue address changes, repoint the code
rather than reprinting.

---

## The gate: check-in

**`/checkin`** on a phone, signed in with a Check-in Staff account. Add it to the
home screen — it installs as an app and works better full screen.

Point the camera at the QR on a badge, whether it is on a phone screen or printed.
Green means a valid ticket and the person is checked in; amber means already checked
in, with the time of the first scan; red means the code is not valid for this event.

**It keeps working when the Wi-Fi drops.** On load it downloads the list of valid
tickets, so scanning continues offline and check-ins queue on the device. When the
connection returns they sync automatically. Do not clear the browser data on a gate
phone mid-shift — unsynced check-ins live there. If a device runs out of battery
before syncing, those check-ins are lost, so keep chargers at the gate.

**Someone without a badge:** use search. Name, phone, or ticket reference will find
them, and they can be checked in by hand. If they never registered, register them as
a walk-in from the desk — the badge issues immediately.

**Someone whose QR will not scan:** a cracked screen or a bad print. Search by name
instead; do not turn anyone away over a scanner problem.

The same badge scanned at two gates counts once. The duplicate is recorded but does
not inflate the numbers.

---

## During the event: the daily rhythm

**Morning, before doors.** Check the delivery log for overnight failures. Work the
conference pending queue. Confirm the gate phones are charged, signed in and have
loaded the ticket manifest.

**During the day.** Watch arrivals by hour on the dashboard — the curve tells you
when to move staff between gates. Keep an eye on the check-in rate against
registrations; a large gap late in the day is a no-show pattern worth knowing about
for next year.

**End of day.** Confirm every gate device has synced before the phones go home. The
scanner shows a pending count when anything is still queued.

---

## Things worth knowing

**Personal data.** Names, phone numbers and email addresses are encrypted in the
database, and the QR on a badge contains no personal information at all — a
photographed badge gives away nothing. That protection ends at the export button.
Exports are plain CSV.

**The public verify page.** Anyone can open the link in a ticket QR and see whether
it is a valid ticket. They do not see who it belongs to — only signed-in staff see
the person.

**Consent.** Each registration records what the person agreed to, per channel. The
broadcast audience filter honours it, so someone who opted out of follow-up contact
will not receive the post-event message.

**Getting help.** If registrations stop appearing, or messages stop being delivered,
capture what the dashboard shows — the delivery log status, the funnel, the time it
started — before changing anything. That is what makes it diagnosable.
