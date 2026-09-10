# Sending on WhatsApp through OTPIQ

This is the runbook for connecting the site to **OTPIQ**, the provider that holds
the WhatsApp Business account for Iraq and Kurdistan, so that a student, a parent,
a visitor or a conference delegate gets their badge on WhatsApp the moment they
register.

The message bodies themselves — English, Kurdish and Arabic, with every
placeholder in order — are in **[whatsapp-templates.md](whatsapp-templates.md)**.
This document is about the wiring: where the keys go, what to put in OTPIQ's
template builder, and how to prove it works before three thousand people depend
on it.

---

## The one thing that has to be true first

**The site must be live on a public HTTPS address.**

Not `127.0.0.1`, not a laptop. WhatsApp fetches the badge picture from a link we
give it, and the person tapping the button in the message opens a page on our
server. Both come from the open internet. On a laptop the sends will be accepted
and the badge will arrive broken.

So: deploy first, then connect WhatsApp. Until then, leave `WHATSAPP_DRIVER=log`
— every message is written to *Messaging → Delivery log* with its exact final
text, and the whole flow can be walked through without spending a message.

---

## 1. What to collect from OTPIQ

From the OTPIQ dashboard:

| What | Where it is | Goes into |
|---|---|---|
| API key (`sk_live_…`) | Dashboard → API keys | `OTPIQ_API_KEY` |
| WhatsApp account id | Dashboard → WhatsApp → the account | `OTPIQ_WHATSAPP_ACCOUNT_ID` |
| WhatsApp phone id | Dashboard → WhatsApp → the number | `OTPIQ_WHATSAPP_PHONE_ID` |

The two ids are **OTPIQ's**, not Meta's. They look like `68c46fecc509cdcec8fb3ef2`.
A Meta phone number id in that field is the single most likely reason for a send
that is accepted by us and then never arrives.

The webhook secret is not collected — you invent it (any long random string),
put it in `.env`, and paste the same value into OTPIQ's delivery-report settings.

**The API key is a credential.** It sends messages and spends credit. It belongs
in `.env` on the server and nowhere else — not in the repository, not in a
WhatsApp group, not in a screenshot.

---

## 2. Build the templates in OTPIQ

Every message we start is a **template**: WhatsApp only allows free text in the
24 hours after somebody writes to *us*, and nobody writes to us before they
register.

Create each confirmation below **once per language**, using the `_en_2026` /
`_ku_2026` / `_ar_2026` names in the table. Bodies, in all three languages, are in
[whatsapp-templates.md](whatsapp-templates.md); copy them exactly.

| Template name (per language) | Sent when | `{{1}}` | `{{2}}` | `{{3}}` |
|---|---|---|---|---|
| `rsvp_confirmed_{en\|ku\|ar}_2026` | conference RSVP confirmed | name | ticket (ku) | — |
| `registration_confirmed_student_{en\|ku\|ar}_2026` | a student finishes registering | name | days | ticket |
| `registration_confirmed_parent_{en\|ku\|ar}_2026` | a parent finishes registering | name | days | ticket |
| `registration_confirmed_visitor` | a visitor pass is issued | name | days | ticket |
| `event_reminder_3days` | three days before | name | ticket | — |
| `event_reminder_1day` | the day before | name | ticket | — |
| `day_of_directions` | 08:00 on each event day | name | ticket | — |
| `session_reminder_15min` | 15 minutes before a booked session | session title | hall | — |
| `post_event_thankyou_survey` | after the fair | name | survey link | — |
| `next_step_otp` | only if phone verification is switched back on | code | — | — |

OTPIQ creates **one template name per language** (e.g. `rsvp_confirmed_en_2026`),
not one name with three language codes. Names, ids, body slots and header-image
flags live in **`config/whatsapp.php` → `otpiq_templates`**. Paste each dashboard
id into the matching `OTPIQ_TEMPLATE_*_ID` env var. Only RSVP needs ids filled
today; student and parent names are already wired — add their ids when approved.

### The numbers are positions, not names

OTPIQ take the values as `1`, `2`, `3` — a position, not a label. The order the
app sends them in is the order in the table above. So a template whose body reads
"Hello {{2}}" will greet somebody by their ticket number, and nothing anywhere
will report an error. **Check each body against the table before submitting it.**

The Kurdish and Arabic bodies in the templates document were written so the
placeholders stay in the same order as the English, even where the sentence turns
around. Keep that property if you edit them.

### The badge — two ways in the same message

The QR is the point of the message, and it can travel two ways:

1. **As the picture at the top** (a media header). The app hands over a link to
   the badge PNG; WhatsApp fetches it once and keeps its own copy, so the picture
   survives after the link expires.
2. **As a button under the message** — the one already in your template, `RSVP`,
   pointing at `https://www.nextstepfair.com/{{1}}`. The app fills `{{1}}` with
   `b/<ticket>`, which opens that person's badge page: the QR, the download
   buttons, and the delivery status. It never expires, which matters — this is the
   link somebody opens in October to find the badge they were sent in September.

**Ask OTPIQ support two questions before relying on the first one:** does this
account accept a **header image** on a template, and does it accept a **URL button
parameter** — and in what shape. Their public documentation covers the body slots
and nothing else. When they confirm, switch the matching flag on:

```
OTPIQ_SEND_HEADER_IMAGE=true
OTPIQ_SEND_BUTTON_LINK=true
```

Both are off by default on purpose. A payload shape the provider does not expect
is a **rejected send** — the whole message lost, not the picture. With them off,
the badge still reaches everybody through the button link and the badge page.

---

## 3. Fill in `.env` on the server

```
WHATSAPP_DRIVER=otpiq

OTPIQ_API_KEY=sk_live_…
OTPIQ_WHATSAPP_ACCOUNT_ID=…
OTPIQ_WHATSAPP_PHONE_ID=…
OTPIQ_WEBHOOK_SECRET=<a long random string you invent>

OTPIQ_SEND_HEADER_IMAGE=false
OTPIQ_SEND_BUTTON_LINK=false
```

Then, on the server:

```
php artisan config:clear
php artisan queue:restart
```

`queue:restart` matters. Messages are sent by a background worker, and a worker
that started before the change is still running with the old settings — the
classic "I updated .env and nothing changed".

---

## 4. Delivery reports

In OTPIQ's delivery-report settings, point the webhook at:

```
https://<your domain>/webhooks/otpiq
```

and paste in the same secret as `OTPIQ_WEBHOOK_SECRET`.

Without this the delivery log can only ever say **sent** — the moment we handed
the message over. With it, rows move to **delivered**, or to **failed** with the
provider's reason. That difference is what the desk works from on the day:
*delivered* means stop looking, *failed* means fix the number and press **Resend
on WhatsApp**.

A report that arrives without the right secret is refused. The address is public,
and without that check anybody could mark every badge delivered — which at the
desk reads as "they have it, stop helping them".

---

## 5. Prove it, in this order

1. **Still on `log`.** Register a test student. *Messaging → Delivery log* shows
   the row with the full final text. Open the badge page; the QR is there.
2. **Switch to `otpiq`,** and register with **your own number**. You should get
   the message. Check: right language, name in the right slot, ticket in the
   right slot, button opens *your* badge page.
3. Repeat with a **parent**, a **visitor pass** and a **conference RSVP** — four
   different templates, and the conference one only sends when the protocol team
   approves the delegate.
4. Look at the delivery log again: the row should have moved to **delivered**.
   If it is stuck at *sent*, the webhook is not arriving — check the URL and the
   secret.
5. Force a failure: register a number with no WhatsApp on it. The row should turn
   **failed** with a reason. That is the path the desk will actually use.

---

## When something is wrong

| What you see | What it usually is |
|---|---|
| Row stays **queued** | The queue worker is not running on the server. |
| **failed**, "OTPIQ is not configured" | A key or an id is empty, or config was not cleared. |
| **failed**, "template not found" | The template name or the language variant is not approved in OTPIQ. Names must match `config/whatsapp.otpiq_templates` exactly. |
| Accepted, never arrives | Usually a Meta phone number id in `OTPIQ_WHATSAPP_PHONE_ID` instead of OTPIQ's. |
| Message arrives, **picture missing** | The account or template has no image header. Leave `OTPIQ_SEND_HEADER_IMAGE=false`; the button link still carries the badge. |
| Message arrives, **button goes nowhere** | The approved button address must end in `/{{1}}`, and the site must be reachable at that domain over HTTPS. |
| Stuck at **sent**, never delivered | The delivery webhook is not configured, or the secret does not match. |

Nothing here needs a code change: the driver, the keys, the two badge flags and
the template names are all settings.

---

## What this does not do

- **It does not verify anybody's phone number.** Registration is protected by the
  picture code on the form; the number proves itself by being where the badge
  arrives. A mistyped digit sends the badge to a stranger, which is why the desk
  needs *Registrations → correct the number → Resend badge*.
- **It does not read replies.** If somebody answers the message, it does not reach
  the dashboard.
- **It does not send to a number that has no WhatsApp.** There is an SMS fallback
  setting for the code, unused now that the code is gone.
