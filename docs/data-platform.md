# The data platform

The fair is three days. The data is the year.

This document describes the part of the system that exists to capture what both
sides of the fair want, match them to each other, and leave Next Step with a
database worth planning the next edition from.

---

## The one idea everything rests on

**Both sides describe themselves in the same words.**

A student says "I want to study nursing". A university says "BSc Nursing, taught in
English, 4 years, $3,500". Written as free text those two facts can never be
counted together, and every report built on them is guesswork.

So there is one taxonomy — **12 sectors, 72 fields**, in English, Kurdish and
Arabic — and both sides pick from it. That single decision is what makes matching
possible, makes demand and supply comparable, and makes a chart of "which fields
attract the most interest" mean something.

The taxonomy lives in `config/taxonomy.php` and is seeded by `TaxonomySeeder`.
**Slugs are permanent identifiers. Translate the names freely; never rename a
slug** — stored student and institution choices point at them.

---

## What each side gives, and what it gets

Nobody is asked to give something for nothing. That is the design.

### Students and parents

At `/me/interests` they answer eight questions:

| Question | Why it is asked |
| --- | --- |
| Fields, up to five, **in order** | The core of matching. Order is preference and is weighted. |
| Degree level | A student wanting a diploma should not be shown a PhD programme. |
| Preferred countries | The destination story, and a hard filter for most students. |
| Language of instruction | The single most common reason a good academic match fails. |
| Budget band | Banded, not exact — nobody knows their tuition budget to the dollar. |
| Grade band | Against the institution's entry requirement. |
| Start year | Separates this year's applicants from next year's browsers. |
| Career goal | The "what for" that no admissions form asks. |

**They get** a ranked list of universities at `/me/matches`, each with the reasons
behind the score: which of their fields it teaches, in which country, in which
language, whether it is within budget, whether they meet the entry requirement.

### Universities

At `/portal` they describe what they teach: fields **by level**, campus countries,
languages of instruction, tuition range, scholarships, minimum grade, intake
capacity, application deadline and recruitment target.

**They get** three things: the students who match what they teach, the demand
profile of those students (which fields they most want — the single most useful
number for planning next year's stand), and a badge scanner that turns a passing
conversation into a recorded lead.

---

## Matching

`App\Services\Matching\MatchEngine` scores every student against every institution
that has listed programmes. Weights are in `config/taxonomy.php`:

| Signal | Weight | Notes |
| --- | --- | --- |
| Field overlap | 45 | Weighted by rank. **No shared field means no match at any price.** |
| Degree level | 15 | Must be taught at the level wanted. |
| Country | 15 | Half marks when the student has no preference — "undecided" is not a mismatch. |
| Budget | 10 | Half marks if unaffordable **but scholarships are offered**. |
| Language | 8 | |
| Grade | 7 | Meeting the entry requirement scores; failing it lowers the rank rather than hiding the institution. |

Anything below **35** is not stored. Matches are recomputed when a student changes
their answers or an institution changes its programmes.

Three design decisions worth knowing:

**Matches are stored, not computed per request.** A recruiter's list must not
reshuffle between page loads, and the score a student saw in September has to be
the score the post-event report explains.

**Every match carries its reasons.** A score with no explanation is a score nobody
acts on. `reasons` holds the components so the student is told *why*.

**Field overlap is measured against what the student asked for**, not what the
university teaches. Otherwise a large university that offers everything would
out-score a specialist one simply for being large.

---

## Consent

Two separate gates, deliberately.

**Matching always works.** A student is matched whether or not they agree to be
contacted, and their choices always count toward demand figures.

**Being contacted is opt-in.** The "let matched universities contact me" box is
unticked by default. A student who declines still appears in an institution's
counts — a university is entitled to know how much demand exists for what it
teaches — but appears as *Not shared*, with no name and no contact details.

Roughly half of people tick a clearly worded, unticked box. Plan for that rather
than assuming everyone shares.

---

## Interactions: the signal that matters most

A badge scanned at a desk is the strongest thing the fair produces. It is the only
interaction that cost both people time in the same place.

| Type | Weight | How it happens |
| --- | --- | --- |
| `booth_scan` | 40 | University staff scan a student badge at `/portal/scanner`. |
| `enquiry` | 30 | |
| `shortlist` | 20 | Student saves a university from their matches. |
| `brochure` | 10 | |
| `profile_view` | 5 | |

Scans are **idempotent per student per institution per day** — a recruiter who
scans the same badge twice mid-conversation has not met two people, and the
leaderboard must not reward them as though they had.

The scanner works from the camera where the browser supports it and from a typed
ticket reference where it does not, which is what a cracked screen at a noisy desk
actually requires.

---

## The questions the data answers

All of these are in the admin under **Platform → Insights**, and in
`App\Services\Matching\InsightService` if you want them in a query.

**Which fields of study attract the most interest?** — `demandByField()`, weighted
by preference order, so a field everyone lists as a fallback does not outrank one
people actually came for.

**What sectors are students most interested in?** — `demandBySector()`.

**Which countries are most popular?** — `demandByCountry()`.

**Where does demand outrun supply?** — `supplyGaps()`. *This is the most
commercially useful number the platform produces:* fields hundreds of students want
that almost nobody at the fair teaches. It is the exhibitor pitch for next year and
the evidence for a ministry conversation about which programmes the region lacks.

**Which universities generated the most engagement, and how qualified was it?** —
`engagementLeaderboard()`. "Qualified" counts only students whose declared field
the institution actually teaches, so an exhibitor who scanned four hundred badges
at random has a big number and a bad stand, and the report says so.

**Did matching actually work?** — `matchQuality()` and `matchToVisitConversion()`:
how many good matches walked to the desk.

**Where should next year's school visits go?** — `demandByCity()`.

---

## Using it after the event

The tables are built to be queried directly, not just read on a dashboard.

- `field_registration` — one row per student per field, with rank. Demand.
- `field_organization` — one row per institution per field per level. Supply.
- `interactions` — every touch, typed and timestamped. Engagement.
- `matches` — the computed fit, with reasons. Quality.

Year-on-year comparison works because slugs are stable: `demandByField()` run
against 2026 and 2027 data is comparing the same things.

For marketing and follow-up, `registrations.share_with_institutions` is the consent
flag, and the broadcast audience filter in the admin already honours it.

---

## Operating notes

**Approve institution claims.** A university that registers at `/portal/register`
arrives with `claim_status = pending` and `published = false`. It can complete its
programme list immediately, but does not appear in the public directory until an
admin publishes it. Approving is the moment it starts appearing in student matches.

**Matching only includes institutions with programmes listed.** An institution that
has not filled in its profile cannot be recommended to anybody, which is why the
portal dashboard leads with that nudge.

**Recompute after a bulk change.** `MatchEngine::forOrganization()` after editing
an institution, `forRegistration()` after editing a student. The portal and the
interests form both do this automatically.
