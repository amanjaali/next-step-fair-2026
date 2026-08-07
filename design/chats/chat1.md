# Website design prompt

_Started 2026-08-04 18:03 UTC_

---

## User

design the website according to the promot in the file

## Assistant

I'll read the prompt and brand guideline first.

_[tool: read_skill_prompt]_

_[tool: list_files]_

_[tool: run_script]_

The prompt PDF's text didn't extract fully — rendering its pages as images.

_[tool: run_script]_

_[tool: run_script]_

_[tool: run_script]_

_[tool: run_script]_

_[tool: run_script]_

_[tool: run_script]_

_[tool: run_script]_

_[tool: run_script]_

_[tool: run_script]_

I can read the brand guidelines and the sitemap page fully, but most of the prompt PDF's prose is unextractable (the body text is embedded without readable text data — only the code/route snippets came through). A few questions before I build:

_[tool: questions_v2]_

## User

Questions answered:
- prompt_text: Next Step Fair 2026 — Website & Platform Build Prompt

Paste this whole document as the brief. Build the full site, then iterate section by section.

1. What you are building

Design and build the official website for Next Step Fair 2026 — the 4th edition of the Kurdistan Region's largest higher-education fair and conference, organized by Next Step Organization in Sulaimani, Iraq.

This is not a single-event landing page. It is a two-track event platform:

Track A — The Expo/Fair (3 days): universities, institutes, exhibitors, seminars, workshops, tournaments. Audience: 12th-grade students, graduates, and parents.
Track B — The Conference (Day 1 only): a formal, invitation-led policy conference for government representatives, ministries, diplomatic missions, university leadership, and official delegations.

Each track has its own registration flow, its own confirmation channel, and its own badge/QR system. They must never share a form.

The site must also serve as a permanent archive of the fair (2023, 2024, 2025, 2026) and as the organization's content hub (news, activities, blog, media gallery).

2. Brand & visual direction

Positioning: institutional credibility meets youth energy. This is a KRG-partnered, UN-SDG-recognized event — it must look like a national institution, not a startup landing page. But the primary audience is 17–18 year olds, so it cannot feel bureaucratic.

Aesthetic: editorial and confident. Generous white space, strong typographic hierarchy, large photography, restrained motion. Think a well-designed university prospectus or a serious international conference site — not a template.

Palette

Role	Color	Use
Primary	Deep charcoal 
#231F20 (existing Next Step brand)	Headers, footer, hero overlays
Accent	Emerald 
#0F6B4F	CTAs, links, active states, SDG section
Secondary accent	Warm gold 
#C9A227	Conference track, VIP/official badges, awards
Surface	Off-white 
#FAF9F7	Page background
Ink	
#1A1A1A / 
#5C5C5C	Body text / muted text

Use gold exclusively for the Conference track and emerald exclusively for the Fair track — this becomes a wayfinding system across the whole site (badges, buttons, section headers, QR badge design).

Typography

Headings: Fraunces (serif, optical size, semi-bold) — editorial authority
Body/UI: Inter — clarity, excellent Latin rendering
Kurdish/Arabic: Noto Kufi Arabic or Rabar for headings, Noto Sans Arabic for body

Motion: subtle only. Fade-and-rise on scroll (max 300ms), no parallax, no auto-playing carousels that steal focus. Respect prefers-reduced-motion.

3. Languages & localization

Three languages, full site-wide:

English (default, LTR)
Kurdish Sorani (RTL) — full mirror layout
Arabic (RTL) — full mirror layout

Requirements:

Language switcher in the header, persistent across pages, stored in a cookie
URL pattern: /en/..., /ku/..., /ar/...
RTL is a real layout mirror, not just direction: rtl — flip navigation, card order, icon direction, form label alignment, timeline direction
Use Latin numerals (0–9) in all three languages, including dates, times, phone numbers, and statistics
Registration forms, confirmation emails, WhatsApp messages, and QR badges must all render in the language the registrant selected
Write Kurdish in plain, direct Sorani — no motivational filler, no translated-English phrasing
4. Site map
/                           Home
/about                      About Next Step (mission, history, team, SDG commitment)
/fair                       The Expo — overview, floor plan, who's exhibiting
/conference                 The Conference — Day 1 program, themes, speakers
/register/fair              Fair registration hub → Student | Parent
/register/conference        Conference RSVP → Government | Official
/agenda                     3-day agenda (day tabs, filter by type & track)
/speakers                   Speakers & Panelists directory
/speakers/[slug]            Individual speaker profile
/seminars                   Seminars, panels & workshops catalog
/universities               Participating universities & institutes
/exhibitors                 Exhibitors (commercial + non-academic)
/partners                   Strategic partners & institutional supporters
/sponsors                   Sponsors (tiered) + "Become a sponsor" CTA
/sdg                        UN SDG commitment (5 goals) + Act4SDGs verification
/news                       News & Activities (feed)
/news/[slug]                Article
/blog                       Blog (editorial, student-facing guidance)
/blog/[slug]                Post
/media                      Media Gallery hub
/media/photos               Photo albums by year & day
/media/videos               Video gallery (YouTube/Vimeo embeds + reels)
/archive                    Past editions hub
/archive/2023               Full 2023 edition
/archive/2024               Full 2024 edition
/archive/2025               Full 2025 edition
/reports                    Downloadable impact reports (PDF)
/scholarships               Link/bridge to scholarship.nextstepfair.com
/exhibit                    "Exhibit with us" — B2B lead form for universities/brands
/contact                    Contact + venue map
/privacy  /terms            Legal
/admin                      Admin dashboard (auth-protected)
/checkin                    Staff QR scanner (auth-protected, mobile-first PWA)
5. Home page — section order
Hero — Event name, "4th Edition", dates, venue, two primary CTAs side by side: Register for the Fair (emerald) and Conference RSVP (gold). Background: high-quality video loop or photo from 2025, dark overlay for contrast.
Live counters bar — Days until event · Universities confirmed · Registered attendees · Seminars & panels. (Pull real numbers from the database, not hardcoded.)
Two-track split — A clean 50/50 block explaining Fair vs Conference, each with its own CTA. This is the most important navigational decision on the page; make it unmissable.
About the event — 3 short paragraphs, one supporting image, link to /about.
Why attend — 4–6 icon cards (Zankoline application support, meet universities, workshops & panels, scholarships & discounts, career guidance, networking).
UN SDG commitment — five SDG tiles with official UN colors and icons, plus the Act4SDGs verification badge and outbound link. See §9.
Featured speakers — 6–8 speaker cards, "View all" link.
Agenda preview — Day 1 / Day 2 / Day 3 tabs showing 4 highlights each, "Full agenda" link.
Participating universities — logo grid (auto-scrolling marquee acceptable here), count badge, link.
Partners & sponsors — tiered logo walls (Strategic Partners → Institutional Supporters → Sponsors → Exhibitors).
Latest news & activities — 3 most recent cards.
Media gallery teaser — masonry strip of 6 images + a video thumbnail, link to /media.
Past editions — three cards (2023, 2024, 2025) with cover image, headline stat, link into archive.
Venue & directions — map embed, address in all three languages, parking/transport notes.
Footer — nav, contact, social, newsletter signup, Next Step Organization info, SDG badge.
6. Registration system — the core build
6.1 Architecture

Two entirely separate flows, one shared backend registration table with a track discriminator.

	Fair Registration	Conference RSVP
Page	/register/fair	/register/conference
Types	Student, Parent	Government, Official
Confirmation channel	WhatsApp	Email (RSVP confirmation)
Badge	QR code	QR code + Name + Institution
Accent color	Emerald	Gold
Approval	Auto-confirmed	Optional manual approval before badge issue

Both flows end at a confirmation page that displays the QR code on screen, plus a "Download badge" button (PNG + PDF) and "Add to calendar" (.ics).

6.2 Fair registration — Student

Step 1 — Personal

Full name *
Date of birth *
Gender (Male / Female / Prefer not to say)
Phone number * — country selector defaulting to +964, with real-time format validation
Email (optional)
City * — dropdown with the full Kurdistan/Iraq city list already used on the current site (Erbil, Banaslawa, Shaqlawa, Koya, Mergasur, Choman, Rawandz, Sulaimanyah, Qaladze, Ranya, Dukan, Sayed Sadiq, Sharbazher, Darbandikhan, Penjwen, Sharazoor, Chamchamal, Qaradagh, Kalar, Halabja, Biara, Khurmal, Sirwan, Duhok, Zakho, Amedi, Semel, Bardarash, Akre)
Preferred language for communication * (Kurdish / Arabic / English)

Step 2 — Academic

Current status * (12th grade student / Recent graduate / University student / Other)
School name (autocomplete, free text fallback)
Stream (Scientific / Literary / Vocational / Other)
Intended field of study (multi-select: Medicine, Engineering, IT & AI, Business, Law, Arts & Design, Education, Agriculture, Vocational/Technical, Undecided)
Interested in studying abroad? (Yes / No / Maybe)

Step 3 — Attendance

Which day(s) will you attend? * (multi-select, Day 1 / Day 2 / Day 3)
Why are you attending? * (multi-select — keep the existing options: Complete Zankoline application, Meet universities and institutes, Join workshops and panels, Learn about offers and discounts, Networking and opportunities, Other)
Seminars/workshops you want to attend (multi-select from the live catalog)
How did you hear about Next Step? (Instagram, Facebook, TikTok, School, Friend/Family, TV/Radio, Other)

Step 4 — Consent

Accept terms & privacy policy * (checkbox, links out)
Consent to receive WhatsApp updates about the event * (checkbox — required, this is the delivery channel)
Consent to appear in event photography (checkbox, optional)
6.3 Fair registration — Parent

Same Step 1 and Step 4. Replace Steps 2–3 with:

Relationship to student * (Father / Mother / Guardian / Other)
Number of children attending
Child's current grade
Why are you attending? * (multi-select — To support my child, Meet universities and institutes, Panels and discussions, Zankoline consultation, Learn about offers and discounts, Other)
Which day(s) *
Topics of interest (Scholarships & financial aid, Zankoline system, Studying abroad, Career prospects, Vocational pathways)
6.4 Conference RSVP — Government
Full name * (as it should appear on the badge)
Title / Position *
Ministry / Directorate / Government body * (the "institution name" printed on the badge)
Department / Unit
Official email * (validated; prefer institutional domains, warn softly on free-mail)
Mobile number * (+964 default)
City *
Delegation size (self only / +1 / +2 / +3 or more)
Will you require an Arabic or Kurdish interpreter? (Yes / No)
Sessions attending * (multi-select from Day 1 conference program)
Are you speaking or presenting? (Yes / No → if yes, reveal session title + short bio + headshot upload)
Dietary requirements (None / Vegetarian / Halal-only / Other → free text)
Accessibility requirements (free text)
Official letter of invitation required? (Yes / No)
Notes to the organizers (free text)
Consent checkboxes
6.5 Conference RSVP — Official

Same as Government, with these differences:

Organization type * (University / International Organization / NGO / Diplomatic Mission / Private Sector / Media / Association / Other)
Organization / Institution name * (badge field)
Organization website
LinkedIn profile (optional)
Media accreditation needed? (Yes / No → if yes: outlet name, press ID upload, crew size)
6.6 Form UX requirements
Multi-step wizard with a visible progress indicator; save partial state to localStorage so a refresh doesn't wipe input
Inline validation on blur, never a wall of errors on submit
Mobile-first — the majority of student registrations will come from a phone over mobile data. Keep the form lightweight, use native inputs, large tap targets (min 44px)
Duplicate prevention: block a second registration on the same phone number (Fair) or email (Conference) for the same track; instead show "You're already registered" and offer to resend the QR
Honeypot field + rate limiting + Cloudflare Turnstile for bot protection
Full keyboard navigation and screen-reader labels (WCAG 2.1 AA)
Success page must render correctly in RTL
7. Confirmation, QR codes & check-in
7.1 QR code design (critical — do this correctly)

Do not encode personal data in the QR. Encode a signed, opaque token:

https://nextstepfair.com/verify/{ticketId}?sig={hmac}
ticketId = UUID v4
sig = HMAC-SHA256 of the ticketId using a server-side secret
Scanning resolves server-side to the registrant record — so a leaked QR image never leaks a phone number or ID

Store on each registration: ticketId, track, type, status (pending / confirmed / checked-in / cancelled), checkedInAt, checkedInBy, dayCheckins[].

7.2 Badge artwork (generated server-side as PNG + PDF)

Fair badge (emerald):

Next Step Fair 2026 logo + edition mark
Registrant full name (large)
Type chip: STUDENT or PARENT
City
Days attending
QR code (min 300×300px, error correction level M)
Ticket ID in small text under the QR

Conference badge (gold):

Next Step Conference 2026 lockup
Full name (large)
Institution / Ministry name (prominent, second line) — this is an explicit requirement
Title/position
Type chip: GOVERNMENT or OFFICIAL
QR code
Ticket ID

Both badges must be print-ready at A6 and legible when displayed on a phone screen at 50% brightness.

7.3 WhatsApp confirmation (Fair track)

Use the Meta WhatsApp Business Cloud API with pre-approved message templates. Plan for template approval lead time — this is not instant.

Flow:

On submit → send a 6-digit OTP to the phone number, verify it in the form (this guarantees the number is real and reachable before you issue a ticket)
On verification → create registration, generate ticket + badge image
Send template message in the registrant's chosen language containing: greeting with name, confirmation statement, event dates/times/venue, and the badge image as an attached media message
Include a "Save this message — show the QR at the entrance" line
Provide a fallback: if WhatsApp delivery fails, send SMS + email and surface a "Download your badge" link on the confirmation page

Template messages needed (each in EN/KU/AR):

registration_confirmed_student
registration_confirmed_parent
event_reminder_3days
event_reminder_1day
day_of_directions
post_event_thankyou_survey

Build an outbound message queue with retry and delivery-status webhooks — log sent / delivered / read / failed per message in the admin dashboard.

7.4 Email RSVP confirmation (Conference track)

Use a transactional provider (Resend, SendGrid, or Amazon SES) with a verified sending domain, SPF/DKIM/DMARC configured.

The RSVP confirmation email must include:

Personalized greeting with name and title
Formal RSVP confirmation statement
Badge PDF attached (with name + institution + QR)
QR also rendered inline as an image in the email body
Day 1 conference agenda summary
Venue address, map link, parking and entrance instructions for official delegations
.ics calendar attachment
Contact for the protocol/organizing team
"Modify or cancel your RSVP" link (signed token, no login required)

Design the email in table-based HTML for Outlook compatibility, with a plain-text alternative. Support RTL layout for Kurdish and Arabic versions.

Also send: a reminder 3 days out, a reminder the day before, and a thank-you with the conference report link afterward.

7.5 Check-in scanner (/checkin)

A mobile-first, installable PWA for gate staff:

Camera QR scan → instant verify against the signed token
Large result screen: green = valid (show name, type, institution, days), amber = already checked in today (show timestamp), red = invalid/cancelled
Multi-day support: separate check-in per day
Manual search fallback by name or phone (for people who lost their QR)
Offline queue — cache valid ticket hashes locally and sync when connection returns (venue Wi-Fi will be unreliable)
Staff accounts with role-based access; log which staff member scanned which ticket
8. Content sections
8.1 News & Activities (/news)
Card feed with cover image, category chip, date, excerpt
Categories: Announcements, Partnerships, Activities, Press Coverage, Government Relations, SDG Initiatives
Filter by category and by year
Article page: hero image, author, date, share buttons, related articles, inline image galleries
Featured/pinned post support
8.2 Blog (/blog)

Distinct from News in voice and purpose: News = organizational updates. Blog = student-facing guidance.

Topics: choosing a major, Zankoline application walkthrough, scholarship applications, studying abroad, CV and interview prep, vocational vs academic pathways, AI and future careers
Reading-time estimate, author bio card, table of contents for long posts
Related-posts block, newsletter CTA at the end
8.3 Media Gallery (/media)
Photos: albums organized by year → day → session. Masonry grid, lightbox with keyboard navigation, lazy loading, download-full-resolution option for press. Alt text required on every image.
Videos: YouTube/Vimeo embeds with custom poster frames, plus a vertical short-form section for reels/TikTok content. Group by year.
Filter bar: Year · Type (Photo/Video) · Category (Opening ceremony, Panels, Booths, Workshops, Tournaments, Awards, Behind the scenes)
Press kit download: logos, high-res photos, fact sheet
8.4 Speakers & Panelists (/speakers)

Directory grid. Each card:

Professional headshot (square crop, consistent treatment — apply a subtle duotone or uniform background so mixed-quality photos still look cohesive)
Full name
Title / position
Institution or workplace name (required)
Country flag (for international speakers)
Session chips linking to their agenda slots

Filters: Year · Track (Fair/Conference) · Type (Speaker / Panelist / Moderator / Workshop leader) · Institution.

Profile page (/speakers/[slug]): large photo, full bio, institution with link, social links, all sessions they appear in, related speakers.

Support a "2026 speakers to be announced" placeholder state that gracefully lists past-year speakers instead of showing an empty page.

8.5 Sponsors (/sponsors)

Tiered display with visually distinct treatment per tier:

Strategic Partners (e.g., Ministry of Higher Education and Scientific Research) — largest, top of page
Platinum / Gold / Silver / Bronze Sponsors
Institutional Partner Supporters (MJ Holding, Qaiwan Group, Halabja Group, Click Iraq, Lafarge)
Media Partners
Exhibitors — logo grid

Each sponsor: logo, name, one-line description, website link, tier badge. Include a "Become a Sponsor" section with tier benefits comparison table and a lead form that routes to the sales team, plus a downloadable sponsorship deck (PDF).

8.6 Archive (/archive/[year])

Each past edition gets a full mini-site, not a photo dump:

Hero with year, dates, venue, edition number
Key statistics row (attendees, universities, exhibitors, seminars, panels, workshops)
That year's full agenda (read-only)
That year's speakers grid
That year's universities and exhibitors
Photo album and video reel
Press coverage links
Downloadable impact report (PDF)
"Explore 2026" CTA at the bottom

Populate 2023, 2024, and 2025 from existing archives. Design the year as a route parameter so 2027 requires no code change.

8.7 Agenda (/agenda)
Day 1 / Day 2 / Day 3 tabs
Filter by: Track (Conference / Fair) · Type (Seminar, Panel, Workshop, Roundtable, Ceremony, Activity, Break) · Topic · Language
Timeline layout on desktop, stacked cards on mobile
Each session: time, title, subtitle, type chip, speakers (linked), location/hall, language, "Add to my agenda"
Personal agenda builder: logged-in registrants select sessions, get a personal schedule, and receive a WhatsApp reminder 15 minutes before each saved session
Export full agenda as PDF and .ics
9. UN SDG section (/sdg)

Next Step is a recognized member of the UN SDG Acceleration Actions community. Give this real weight — it is a credibility asset, not a footer badge.

Content:

Opening statement on Next Step's commitment to the 2030 Agenda
The five SDGs, each as a tile using the official UN icon and official UN color:
SDG 4 — Quality Education: expanding access with digital literacy and AI integration
SDG 8 — Decent Work & Economic Growth: preparing the next generation with future-ready skills
SDG 9 — Industry, Innovation & Infrastructure: leveraging AI and technology for sustainable growth
SDG 13 — Climate Action: innovation and awareness supporting climate resilience
SDG 17 — Partnerships for the Goals: strengthening global and local partnerships across education, technology and sustainability
For each SDG: what Next Step actually does, and a measurable indicator (students reached, sessions delivered, carbon footprint reduced, partnerships formed)
Official recognition block: "Next Step is officially registered as an SDG Acceleration Action with the UN Department of Economic and Social Affairs" with a verification button linking to https://act4sdgs.org/profile/click_iraq (opens in a new tab, rel="noopener")
Green event practices section (waste reduction, digital-first materials, the carbon footprint report)
Link to the downloadable SDG impact report

Place a compact version of this block on the home page and an SDG badge in the footer on every page.

10. Admin dashboard (/admin)

Role-based access: Super Admin, Registration Manager, Content Editor, Check-in Staff, Sponsor Manager.

Registrations

Unified table with filters: track, type, status, city, day, date range, check-in status
Search by name, phone, email, ticket ID
Detail view per registrant with full submission and message delivery log
Bulk actions: approve, cancel, resend WhatsApp, resend email, regenerate badge
CSV / Excel export with column selection
Manual registration entry (for walk-ins at the door)

Analytics

Registrations over time (line chart), by type, by city (map or bar), by day selected
Conversion funnel: page view → form start → OTP verified → confirmed
Check-in rate: registered vs actually attended, per day, hourly arrival curve
Traffic source breakdown
Session popularity (from personal agenda picks)

Messaging

Template manager (EN/KU/AR)
Broadcast composer with audience segmentation (e.g., "all Day 2 students in Sulaimani")
Delivery status log with failure reasons and retry

Content management

CRUD for: news, blog, speakers, sessions, universities, exhibitors, partners, sponsors, media albums, archive years
Draft / scheduled / published states
Image upload with automatic WebP conversion and responsive sizes
Per-language content fields with a translation-completeness indicator
11. Technical requirements

Recommended stack: Next.js 14+ (App Router) · TypeScript · Tailwind CSS · Supabase (Postgres + Auth + Storage) · next-intl for i18n · Resend for email · Meta WhatsApp Cloud API · qrcode + sharp for badge generation · Cloudflare Turnstile · Vercel or AWS hosting.

Non-negotiables:

Lighthouse ≥ 90 on Performance, Accessibility, Best Practices, SEO
LCP under 2.5s on a 3G connection — students will register on mobile data
All images: WebP/AVIF, responsive srcset, lazy loaded below the fold
WCAG 2.1 AA: contrast ratios, focus indicators, semantic HTML, ARIA where needed, skip-to-content link
SEO: unique meta per page, Open Graph and Twitter cards, hreflang tags for all three languages, XML sitemap, Event and Organization JSON-LD schema
Analytics: GA4 + Meta Pixel + TikTok Pixel, with conversion events on registration completion
Security: rate limiting on all form endpoints, input sanitization, CSRF protection, encrypted PII at rest, signed URLs for badge downloads
Privacy: clear privacy policy, explicit consent capture with timestamp, data retention policy, deletion request mechanism
Fully responsive: 360px → 1920px, tested on low-end Android
12. Build order
Design system, layout shell, i18n scaffolding with RTL working end to end
Home page and all static content pages
Database schema + admin authentication
Fair registration flow → OTP → QR generation → WhatsApp confirmation
Conference RSVP flow → email confirmation → PDF badge with name + institution
Check-in PWA scanner
Content modules (news, blog, media, speakers, agenda)
Archive years 2023–2025
Admin dashboard and analytics
Performance pass, accessibility audit, load test the registration endpoint
13. Deliver

Production-ready, well-commented code with a clear folder structure, seeded demo data (realistic Kurdish and Arabic names, Sulaimani institutions, IQD where currency appears), a README covering environment variables and the WhatsApp template approval process, and a short admin user guide.

Start with the design system and the home page. Show me those before building the registration flows.
- palette_conflict: Prompt palette — ink/green/gold
- scope: Homepage only, full length, Homepage + registration flow (student/parent), Homepage + agenda/speakers, Homepage + universities/exhibitors directory, Homepage + SDG page, Staff check-in / QR screens (mobile), Admin dashboard, Decide for me
- fidelity: Clickable prototype (nav, tabs, filters, form steps work)
- viewport: Both, side by side
- variations: No, one strong direction
- event_facts: yes
- audience_priority: Decide for me
- languages: Show one Kurdish or Arabic RTL screen too
- photography: Use grey placeholder frames (guideline-compliant)
- registration_detail: Full multi-step form with QR ticket confirmation
- nav_depth: Decide for me

## Assistant

Got it. Building a clickable prototype in the prompt's palette (charcoal/emerald/gold, Fraunces + Inter): full homepage, fair registration wizard with QR confirmation, agenda, speakers, directory, SDG, admin, mobile check-in screens, and a Kurdish RTL screen.

_[tool: dc_write]_

You're right — switching to the brand's own palette: Next Ink, Step Magenta for the Fair track, Next Cobalt for the Conference track, Bone as the page ground. No green, no gold.

_[tool: dc_write]_

_[tool: ready_for_verification]_

Rebuilt on the brand palette: Next Ink for structure, Step Magenta for the Fair track, Next Cobalt for the Conference track, Bone as the page ground, plus Space Grotesk / Manrope per the guideline. Wayfinding colors on the floor plan use cobalt/teal/slate, since magenta marks the brand and never a zone.

Use the bar at the bottom to move between screens: Home, Register (4-step wizard → QR badge), Agenda, Speakers, Exhibitors, SDG, Mobile + check-in, Admin, Kurdish RTL.

## User

Found issues — fixing…

## Assistant

_[tool: dc_js_str_replace]_

Now the header overflow (both LTR and RTL):

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

Now the same fix on the Kurdish RTL header:

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: ready_for_verification]_

Fixed both: badges now draw their QR locally (no CDN dependency), and the header no longer overflows at narrow widths — nav shrinks and scrolls off while the language switcher and CTA stay intact, in LTR and RTL.

## User

Found issues — fixing…

## Assistant

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: grep]_

_[tool: dc_html_str_replace]_

_[tool: ready_for_verification]_

Nav now wraps to a second line instead of clipping mid-word (both LTR and RTL), and the unused QR script is removed so the console is clean.

## User

Found issues — fixing…

## Assistant

Root cause: the nav active check compares `page === n[1]`, and both "The Conference" and "News" map to the `'home'` page, so both match while on Home. Fixing by keying active state on a distinct per-item id.

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: ready_for_verification]_

Nav highlight now tracks the clicked item by its own id, so exactly one link is underlined and none on Home.

## User

you didnt design the News section make sure to design it perfectly

## Assistant

Adding a full News & Activities page — pinned featured story, category and year filters, and the article feed.

_[tool: dc_html_str_replace]_

Now the logic: news data, filters, and routing.

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

Finally, wire the homepage "All news" link to the new page:

_[tool: dc_html_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: ready_for_verification]_

News & Activities page added: pinned lead story on ink, working category + year filters with an empty state, nine-article feed with category accents, and press-kit / newsletter blocks at the foot. Reachable from the top nav, the prototype bar, and the homepage "All news" link.

## User

Found issues — fixing…

## Assistant

_[tool: dc_html_str_replace]_

_[tool: ready_for_verification]_

Placeholder text on the magenta and ink newsletter fields is now legible. (The reported hero overlap is the placeholder label sitting behind the gradient overlay — not visible.)

## User

you didin't create the RSVP conference section make sure to separate this. section to be dedicated for RSVP Conference and the field will be different and even less field 

fix it

## Assistant

Building a dedicated Conference RSVP flow — cobalt track, Government/Official types, two short steps instead of four, and an email-confirmation screen.

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

Now make the news cards and the pinned story open the article, then add the logic.

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

Now the logic for both screens:

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

Now the conference + article renderVals:

_[tool: dc_js_str_replace]_

Now the article data and the `go` handlers on news cards:

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

Now route Conference RSVP everywhere it's referenced, and add it to the prototype bar:

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

Also remove the now-misleading dual-purpose label on the fair form and keep the header CTA on the fair:

_[tool: dc_html_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: ready_for_verification]_

Two new screens:

**Conference RSVP** — its own cobalt-track page, separate from fair registration. Government / Official types, two steps instead of four, six required fields (name, title, institution, official email, mobile, city), free-mail addresses flagged for protocol review, and an email-confirmation screen with a cobalt badge carrying name + institution + QR. Each form links to the other so nobody lands in the wrong one.

**Article detail** — hero, standfirst, author sidebar with share, pull quote, inline image with caption, a facts panel, and related news. Every news card and the pinned story open it.

## User

Found issues — fixing…

## Assistant

Root cause 1: two fixed rails (240+260) plus 2×56px gaps consume 612px of a 764px content box, so `minmax(0,1fr)` gets only the 152px remainder. Switching to a wrapping flex layout so the body column can't be squeezed.

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

Root cause 2: `bodyFor` used `n.excerpt` for both the standfirst and `body[0]`. Giving each article a distinct opening paragraph:

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: ready_for_verification]_

Article layout now uses a wrapping flex row — the body column can't be squeezed by the rails, and the author/share strip sits above the text as a full-width band. Each article also has its own opening paragraph, distinct from the standfirst.

## User

I didn't sponsers section in the website Last year many plance sponsered also I want in the top beside our logo add The Ministry of Higher Education and Scientific Research as partner, write in Partnership with The Ministry of Higher Education and Scientific Research (MOHE) make sure to write MOHE and the logo as well also in the footer add the logo of MOHE and KRG logo as well 

also fix the logo of NEXT step make sure to adapt with the background color make sure to remove the white background for NExt Step Logo

## Assistant

_[tool: run_script]_

_[tool: run_script]_

_[tool: run_script]_

_[tool: view_image]_

_[tool: view_image]_

Logos are ready. Now the header partnership lockup:

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

Now the footer — white Next Step logo plus MOHE and KRG marks:

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

Now the dedicated Sponsors page:

_[tool: dc_html_str_replace]_

_[tool: dc_js_str_replace]_

Now wire it into nav, prototype bar, and the homepage sponsor CTAs:

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

Finally, the strategic-partner logos on the homepage sponsor tier should use the real marks:

_[tool: dc_js_str_replace]_

_[tool: ready_for_verification]_

Four changes:

- **Next Step logo** — white background stripped, so it sits cleanly on Bone; a white knockout version is used in the dark footer.
- **Header** — MOHE logo beside it with "In partnership with The Ministry of Higher Education and Scientific Research (MOHE)", divided by a rule per the co-branding rule. Mirrored in Kurdish.
- **Footer** — a partnership band carrying the MOHE and KRG marks on white.
- **Sponsors page** — new, reachable from the nav, prototype bar and both homepage CTAs: strategic partners with the real MOHE/KRG marks, institutional supporters, platinum, gold & silver, media partners, plus a tier benefits comparison table and "Become a sponsor" block.

Sponsor names below the strategic tier are placeholders — send me the real 2025 sponsor list and logo files and I'll drop them in.

## User

you didn't di the Past editions make sure for each year make a good structure to have the Themes we had, also the main speech of the Organizer, also speakers and panels, also the partner and the sponsors

## Assistant

Building a full past-edition page with year tabs — themes, the organizer's address, speakers, panels, and that year's partners and sponsors.

_[tool: dc_html_str_replace]_

Now the per-year data:

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

Now wire the homepage cards and prototype bar:

_[tool: dc_js_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: ready_for_verification]_

Past editions page added, with 2025 / 2024 / 2023 tabs — each year has its own edition header and six-stat band, that year's themes with outcomes, the organizer's opening address (pull quote plus full text and video link), speakers grid, panels and seminars with attendance figures, that year's partners and sponsors by tier, gallery strip and impact report. Reachable from the prototype bar, the footer, and the homepage past-edition cards.

The copy is plausible placeholder history — send me the real themes, speech text, speaker lists and sponsor logos per year and I'll replace them.

## User

change the Organizer of Next Step Avin Qadir, remove the founder

## Assistant

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: ready_for_verification]_

Organizer is now Avin Qadir across all three editions, with the founder title removed.

## User

change it Organizer and Co-Founder of Next Step

## Assistant

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: ready_for_verification]_

Updated to "Organizer and Co-Founder of Next Step".

## User

i don't see Home in the menu this is impirtant to have

## User

make sure to create Icons set for Why Attend section

## User

make sure to create Icons set for Why Attend section

## User

the icon set is not nice I need something very minimal

## User

the icon set is not nice I need something very minimal

## Assistant

Rebuilding them from the brand's own element — solid bars at 0°/90°, one magenta per icon, nothing else.

_[tool: dc_html_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: ready_for_verification]_

Icons are now solid bar compositions taken straight from the logo's Step element — right angles only, one magenta bar each: descending fields (application), three equal booths, a four-panel grid, bars beside a block (scholarships), ascending steps (career), offset bars (networking).

## User

I want for each speaker have dedicated page to have profile in our website

## Assistant

Adding an individual speaker profile page, opened from any speaker card.

_[tool: dc_html_str_replace]_

Now the profile data, and make the speaker cards open it:

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

Now point the speaker cards (directory + homepage) at the profile:

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: ready_for_verification]_

Each speaker now has a profile page — large portrait, track/role marker, title and institution, a metadata card with links, full biography, their sessions as clickable rows into the agenda, topic chips, and an "Also speaking" row. Opened by clicking any speaker card on the homepage or the directory; breadcrumb returns to the list.

Two speakers have written biographies; the rest fall back to a generated stub until you send real bios and headshots.

## User

Found issues — fixing…

## Assistant

Root cause: fixed rails plus gaps consume the row before `minmax(0,1fr)` gets anything. Switching both to wrapping flex.

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: ready_for_verification]_

Fixed: the portrait rail now shrinks instead of starving the bio column, and the session rows wrap so the title always has room — the time and "View in agenda" hold their size.

## User

the Floor Plan should be clickable and go to a dedicated page and show the floor plan in details

## Assistant

Adding a dedicated floor plan page — clickable halls, zone detail, and the booth list.

_[tool: dc_html_str_replace]_

Now the logic, and make the directory's floor plan block clickable:

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

Now make the directory's floor plan block clickable:

_[tool: dc_html_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: ready_for_verification]_

The floor plan on the exhibitor page is now clickable and opens a dedicated page: a plan with the three halls plus service points as selectable blocks, a zone key pairing each colour with a letter, and the full booth list for whichever hall is selected — 32 university booths in A, the conference infrastructure in B, 12 exhibitors in C, and the four staffed service points. Below it, entrances and accessibility notes, and a PDF download.

## User

Found issues — fixing…

## Assistant

Root cause: `halls` runs before `dirUniversities`/`dirExhibitors` are declared. Moving the block below them.

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: ready_for_verification]_

Fixed — the hall definitions are now built by a factory called after the exhibitor lists exist, so the temporal-dead-zone error is gone and the page renders again.

## User

all the pages and sections should have footer as well because footer should be there in all pages

## Assistant

_[tool: grep]_

_[tool: read_file]_

Moving the footer out of the homepage so every page shares it.

_[tool: run_script]_

_[tool: read_file]_

_[tool: read_file]_

Now an RTL footer for the Kurdish page:

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: dc_js_str_replace]_

_[tool: ready_for_verification]_

The footer is now shared — it renders on every page (home, registration, conference RSVP, agenda, speakers, speaker profile, exhibitors, floor plan, sponsors, past editions, news, article, SDG) rather than only the homepage. The Kurdish page gets its own mirrored RTL footer with the same MOHE and KRG partnership band.

## User

now remove the prototype in the footer no need to be there

## Assistant

_[tool: snip]_

_[tool: read_file]_

_[tool: dc_html_str_replace]_

_[tool: ready_for_verification]_

Prototype bar removed. Navigation is now through the site itself — the header menu, in-page links, and CTAs.

Note: a few screens had no in-site link (speaker profile is reachable from speaker cards, the article from news cards, the floor plan from the exhibitor page). The mobile, check-in and admin screens no longer have an entry point — tell me if you want a link to them somewhere, or I can restore a small switcher.

## User

replace the SDG to News and news to SDG place

## Assistant

_[tool: dc_js_str_replace]_

_[tool: ready_for_verification]_

Swapped — News now sits before SDG in the menu.

## User

Create page for 
Privacy
Terms
Press kit in the footer

## Assistant

Adding the three pages, opened from the footer links.

_[tool: dc_html_str_replace]_

_[tool: dc_js_str_replace]_

Now the document content for all three:

_[tool: dc_js_str_replace]_

Now wire the footer links (both English and Kurdish):

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: dc_html_str_replace]_

_[tool: ready_for_verification]_

Three pages added, all opened from the footer:

**Privacy** — eight sections covering what's collected on each form, why, consent with timestamps and minor releases, who sees it, 24-month retention, your rights, and why the QR carries no personal data.

**Terms** — registration and entry, badge rules, programme changes, conduct, photography, exhibitor obligations, liability, cancellation.

**Press kit** — naming and logo rules from the brand guideline, photography credit terms, then six downloadable assets (logo pack, 2025/2024 photo sets, fact sheet, impact report, b-roll), a fact sheet table, and media accreditation.

The press-kit references on the news page and article sidebar now point here too.

