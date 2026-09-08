# WhatsApp templates — submission and approval

Everything on the fair track — the confirmation that carries the QR badge, the
reminders — goes out over WhatsApp. Meta only permits **pre-approved templates**
outside a 24-hour customer-service window, and every one of our sends is outside
that window, so each message below has to be submitted and approved before it can
be delivered.

> **Sending through OTPIQ instead of Meta directly?** The bodies below are still
> the source of truth — copy them into OTPIQ's template builder — but the
> account setup, the keys and the delivery webhook are different. See
> **[whatsapp-otpiq.md](whatsapp-otpiq.md)**.
>
> The `next_step_otp` template is kept here for the day phone verification is
> switched back on. Registration does not use it: the forms are protected by a
> picture code typed on the page, and no code is sent to anybody.

Approval usually takes minutes but Meta allows itself **up to 24 hours**, and a
rejection restarts the clock. Submit all templates in all three languages **at least
two weeks before registration opens.**

Until then the platform runs on `WHATSAPP_DRIVER=log`: messages are rendered and
written to the delivery log with their exact final body, but nothing is sent. The
queue, the retry schedule, the admin resend action and the OTP verification flow all
behave identically, so the whole flow can be tested and demoed before Meta is
involved.

---

## Before submitting

1. A **Meta Business account** verified against the organising entity.
2. A **WhatsApp Business Account (WABA)** inside it.
3. A **phone number** registered to the WABA — a dedicated number, not anyone's
   personal line, and one that has never been used on the consumer WhatsApp app.
4. A **display name** approved for that number ("Next Step Fair").
5. A **permanent system-user access token** with `whatsapp_business_messaging` and
   `whatsapp_business_management`. Do not ship the 24-hour token from the API-setup
   screen — it will expire mid-campaign.

Then fill in `.env`:

```
WHATSAPP_DRIVER=cloud_api
WHATSAPP_PHONE_NUMBER_ID=...
WHATSAPP_BUSINESS_ACCOUNT_ID=...
WHATSAPP_TOKEN=...
WHATSAPP_WEBHOOK_VERIFY_TOKEN=<any random string>
```

### Messaging limits

A new number starts at **1,000 business-initiated conversations per 24 hours** and
steps up (10k → 100k → unlimited) as quality stays high. Registration for a fair
this size can clear 1,000 in a day during a launch push. Request the tier increase
early, and if the cap is hit the queue simply backs off and retries — nothing is
lost, but confirmations arrive late. Watch the delivery log's failure rate in the
admin during launch week.

---

## Webhook

In the Meta app → WhatsApp → Configuration:

- **Callback URL:** `https://nextstepfair.com/webhooks/whatsapp`
- **Verify token:** the value of `WHATSAPP_WEBHOOK_VERIFY_TOKEN`
- **Subscribe to:** `messages` (this carries the `statuses` payload)

Delivery receipts land on this endpoint and update each row in the delivery log to
`sent` → `delivered` → `read`, or `failed` with Meta's error code. Without the
webhook, the admin can only show that a message was accepted, not that it arrived.

---

## Variable mapping

Meta templates use positional placeholders — `{{1}}`, `{{2}}`, `{{3}}`. The app
passes variables positionally in the order listed for each template below, so the
**order in the submitted body must match the order in this document exactly**. The
translated bodies were written so that the placeholders stay in the same sequence in
Kurdish and Arabic even where the sentence order differs.

The application copy lives in `lang/{en,ku,ar}/notifications.php` and is mirrored
into the `message_templates` table by `MessageTemplateSeeder`. Edit the copy there,
not in Meta first — then submit the matching text to Meta and mark the template
approved from *Messaging → Message templates* in the admin.

Meta language codes used: `en`, `ku`, `ar` (`config/whatsapp.language_codes`).

---

## The templates

### 1. `next_step_otp` — authentication

- **Category:** Authentication
- **Variables:** `{{1}}` = the 6-digit code

> Your Next Step Fair verification code is **{{1}}**. It expires in 10 minutes. Do not share it with anyone.

**KU:** کۆدی پشتڕاستکردنەوەی پێشانگای هەنگاوی داهاتوو: **{{1}}**. لە ماوەی ١٠ خولەکدا بەسەردەچێت. لەگەڵ کەس هاوبەشی مەکە.

**AR:** رمز التحقق الخاص بمعرض Next Step هو **{{1}}**. تنتهي صلاحيته خلال ١٠ دقائق. لا تشاركه مع أحد.

Submit this under the **Authentication** category, not Utility. Meta applies a
fixed, faster-approving format there and authentication templates are exempt from
some marketing-quality checks. If the authentication editor forces its own copy,
accept Meta's wording — the code is the only part that matters, and
`config/whatsapp.otp.length` (6) must match the code length configured in Meta.

---

### 2. `registration_confirmed_student` — utility

- **Category:** Utility
- **Header:** Image (the QR badge, passed as a link at send time)
- **Variables:** `{{1}}` = name, `{{2}}` = days, `{{3}}` = ticket reference

> Hello **{{1}}**, your registration for Next Step Fair 2026 is confirmed.
>
> 28–30 September 2026, Cultural Factory, Sulaimani. Your days: **{{2}}**. Entry is free.
>
> Your badge is attached. Save this message — show the QR at the entrance.
> Ticket: **{{3}}**

**KU:** سڵاو **{{1}}**، تۆمارکردنت بۆ پێشانگای هەنگاوی داهاتوو ٢٠٢٦ پشتڕاست کرایەوە. ٢٨–٣٠ی ئەیلولی ٢٠٢٦، کارگەی کولتوری، سلێمانی. ڕۆژەکانت: **{{2}}**. چوونەژوورەوە بەخۆڕاییە. باجەکەت هاوپێچە. ئەم نامەیە پاشەکەوت بکە — QRەکە لە دەروازە پیشان بدە. بلیت: **{{3}}**

**AR:** مرحباً **{{1}}**، تم تأكيد تسجيلك في معرض Next Step 2026. ٢٨–٣٠ أيلول ٢٠٢٦، مصنع الثقافة، السليمانية. أيامك: **{{2}}**. الدخول مجاني. بطاقتك مرفقة. احفظ هذه الرسالة — واعرض رمز QR عند المدخل. التذكرة: **{{3}}**

The image header is what carries the badge. At send time the app supplies a signed,
expiring URL to `ticket/{id}/badge.png` (TTL from `config/nextstep.badge.download_link_ttl`).
Meta fetches and caches the image, so the recipient keeps seeing it after the link
expires. When submitting, upload any sample badge PNG as the header example.

---

### 3. `registration_confirmed_parent` — utility

Identical to the student template except for the entry line. Same category, header
and variable order.

> Hello **{{1}}**, your registration for Next Step Fair 2026 is confirmed.
>
> 28–30 September 2026, Cultural Factory, Sulaimani. Your days: **{{2}}**. Entry is free for you and your child.
>
> Your badge is attached. Save this message — show the QR at the entrance.
> Ticket: **{{3}}**

**KU:** سڵاو **{{1}}**، تۆمارکردنت بۆ پێشانگای هەنگاوی داهاتوو ٢٠٢٦ پشتڕاست کرایەوە. ٢٨–٣٠ی ئەیلولی ٢٠٢٦، کارگەی کولتوری، سلێمانی. ڕۆژەکانت: **{{2}}**. چوونەژوورەوە بۆ خۆت و منداڵەکەت بەخۆڕاییە. باجەکەت هاوپێچە. ئەم نامەیە پاشەکەوت بکە — QRەکە لە دەروازە پیشان بدە. بلیت: **{{3}}**

**AR:** مرحباً **{{1}}**، تم تأكيد تسجيلك في معرض Next Step 2026. ٢٨–٣٠ أيلول ٢٠٢٦، مصنع الثقافة، السليمانية. أيامك: **{{2}}**. الدخول مجاني لك ولابنك أو ابنتك. بطاقتك مرفقة. احفظ هذه الرسالة — واعرض رمز QR عند المدخل. التذكرة: **{{3}}**

---

### 3b. `registration_confirmed_visitor` — utility

The visitor pass. Same category, header and variable order as the two above, but
the pass covers the whole run, so `{{2}}` always reads as all three days.

- **Variables:** `{{1}}` = name, `{{2}}` = days, `{{3}}` = ticket reference

> Hello **{{1}}**, your visitor pass for Next Step Fair 2026 is ready.
>
> 28–30 September 2026, Cultural Factory, Sulaimani. Valid all three days. Entry is free.
>
> Your badge is attached. Save this message — show the QR at the entrance.
> Ticket: **{{3}}**

**KU:** سڵاو **{{1}}**، پاسی سەردانکەریت بۆ پێشانگای هەنگاوی داهاتوو ٢٠٢٦ ئامادەیە. ٢٨–٣٠ی ئەیلولی ٢٠٢٦، کارگەی کولتوری، سلێمانی. بۆ هەر سێ ڕۆژەکە بەکاردێت. چوونەژوورەوە بەخۆڕاییە. باجەکەت هاوپێچە. ئەم نامەیە پاشەکەوت بکە — QRەکە لە دەروازە پیشان بدە. بلیت: **{{3}}**

**AR:** مرحباً **{{1}}**، تصريح الزائر الخاص بك لمعرض Next Step 2026 جاهز. ٢٨–٣٠ أيلول ٢٠٢٦، مصنع الثقافة، السليمانية. صالح طوال الأيام الثلاثة. الدخول مجاني. بطاقتك مرفقة. احفظ هذه الرسالة — واعرض رمز QR عند المدخل. التذكرة: **{{3}}**

---

### 3c. `rsvp_confirmed` — utility

The conference track. It is not sent when the form is submitted: a delegate is
confirmed by the protocol team, and this goes out with that decision. Note the
shorter variable list — **the ticket is `{{2}}` here, not `{{3}}`.**

- **Header:** Image (the delegate badge)
- **Variables:** `{{1}}` = name, `{{2}}` = ticket reference

> Hello **{{1}}**, your place at the Next Step Conference 2026 is confirmed.
>
> 28 September 2026, Cultural Factory, Sulaimani. Doors 09:00, the opening session begins at 10:00.
>
> Your badge is attached. Save this message — show the QR at the delegate entrance.
> Ticket: **{{2}}**

**KU:** سڵاو **{{1}}**، شوێنەکەت لە کۆنفرانسی هەنگاوی داهاتوو ٢٠٢٦ پشتڕاست کرایەوە. ٢٨ی ئەیلولی ٢٠٢٦، کارگەی کولتوری، سلێمانی. دەرگاکان ٠٩:٠٠، دانیشتنی کردنەوە لە ١٠:٠٠ دەست پێدەکات. باجەکەت هاوپێچە. ئەم نامەیە پاشەکەوت بکە — QRەکە لە دەروازەی نوێنەران پیشان بدە. بلیت: **{{2}}**

**AR:** مرحباً **{{1}}**، تم تأكيد مقعدك في مؤتمر Next Step 2026. ٢٨ أيلول ٢٠٢٦، مصنع الثقافة، السليمانية. الأبواب ٠٩:٠٠، وتبدأ الجلسة الافتتاحية ١٠:٠٠. بطاقتك مرفقة. احفظ هذه الرسالة — واعرض رمز QR عند مدخل المدعوين. التذكرة: **{{2}}**

---

### 4. `event_reminder_3days` — utility

- **Variables:** none

> Next Step Fair 2026 opens in three days, on 28 September at the Cultural Factory in Sulaimani. Doors 10:00. Bring your QR badge and your grades.

**KU:** پێشانگای هەنگاوی داهاتوو ٢٠٢٦ دوای سێ ڕۆژ دەکرێتەوە، ٢٨ی ئەیلول لە کارگەی کولتوری، سلێمانی. دەرگاکان کاتژمێر ١٠:٠٠. باجی QR و نمرەکانت لەگەڵ خۆت بهێنە.

**AR:** يفتح معرض Next Step 2026 بعد ثلاثة أيام، في ٢٨ أيلول في مصنع الثقافة بالسليمانية. الأبواب تفتح ١٠:٠٠. أحضر بطاقة QR ودرجاتك.

---

### 5. `event_reminder_1day` — utility

- **Variables:** none

> Next Step Fair 2026 opens tomorrow at 10:00, Cultural Factory, Sulaimani. Show the QR in this chat at Gate A. Free parking behind Hall C.

**KU:** پێشانگای هەنگاوی داهاتوو ٢٠٢٦ سبەینێ کاتژمێر ١٠:٠٠ دەکرێتەوە، کارگەی کولتوری، سلێمانی. QRی ناو ئەم چاتە لە دەروازەی A پیشان بدە. پارکینگ بەخۆڕایی لە پشتی هۆڵی C.

**AR:** يفتح معرض Next Step 2026 غداً الساعة ١٠:٠٠، مصنع الثقافة، السليمانية. اعرض رمز QR الموجود في هذه المحادثة عند البوابة A. موقف مجاني خلف القاعة C.

---

### 6. `day_of_directions` — utility

- **Variables:** `{{1}}` = name

> Good morning **{{1}}**. Next Step Fair is open today from 10:00 to 20:00 at the Cultural Factory, Salim Street, Sulaimani. Gate A is step-free. Your QR badge is in this chat.

**KU:** بەیانیت باش **{{1}}**. پێشانگای هەنگاوی داهاتوو ئەمڕۆ لە ١٠:٠٠ بۆ ٢٠:٠٠ کراوەیە لە کارگەی کولتوری، شەقامی سالم، سلێمانی. دەروازەی A بێ پلیکانەیە. باجی QRەکەت لەم چاتەدایە.

**AR:** صباح الخير **{{1}}**. معرض Next Step مفتوح اليوم من ١٠:٠٠ حتى ٢٠:٠٠ في مصنع الثقافة، شارع سالم، السليمانية. البوابة A خالية من الدرج. بطاقة QR في هذه المحادثة.

---

### 7. `session_reminder_15min` — utility

- **Variables:** `{{1}}` = session title, `{{2}}` = hall

> **{{1}}** starts in 15 minutes in **{{2}}**. Seats are allocated on arrival.

**KU:** **{{1}}** دوای ١٥ خولەک لە **{{2}}** دەست پێدەکات. کورسییەکان بەپێی هاتن دابەش دەکرێن.

**AR:** تبدأ **{{1}}** بعد ١٥ دقيقة في **{{2}}**. المقاعد تُوزع عند الوصول.

Sent only to people who bookmarked that session, every five minutes during event
hours by `nextstep:session-reminders`. Note the config key is `session_reminder`
but the Meta template name is `session_reminder_15min` — the mapping is in
`config/whatsapp.templates`.

---

### 8. `post_event_thankyou_survey` — marketing

- **Category:** Marketing
- **Variables:** `{{1}}` = name, `{{2}}` = survey link

> Thank you for coming to Next Step Fair 2026, **{{1}}**. Two minutes on what you found useful helps us plan 2027: **{{2}}**

**KU:** سوپاس بۆ هاتنت بۆ پێشانگای هەنگاوی داهاتوو ٢٠٢٦، **{{1}}**. دوو خولەک بۆ گوتنی ئەوەی بەسوود بوو یارمەتیمان دەدات بۆ پلاندانانی ٢٠٢٧: **{{2}}**

**AR:** شكراً لحضورك معرض Next Step 2026، **{{1}}**. دقيقتان تخبرنا فيهما بما كان مفيداً تساعداننا في التخطيط لعام ٢٠٢٧: **{{2}}**

This is the one **Marketing** template in the set. Marketing templates are subject
to per-user frequency capping and are the ones users report as spam, which lowers
the number's quality rating and can throttle the utility templates that matter. Send
it once, and only to people who consented to follow-up contact — the consent flag is
recorded per registration and the broadcast audience filter honours it.

---

## Getting templates approved

Common rejection reasons and how to avoid them:

- **Placeholder at the very start or end of the body.** Meta rejects a body that
  begins or ends with `{{1}}`. None of the templates above do — keep it that way
  when editing copy.
- **Consecutive placeholders** (`{{1}} {{2}}`) are also rejected. Keep literal text
  between variables.
- **Sample values missing.** Every variable needs a realistic example at submission
  time. Use a real-looking name, `Day 1, Day 2`, and a ticket like `8F2C-41A9-D77E`.
- **Category mismatch.** A confirmation submitted as Marketing gets rejected or
  reclassified; a promotional message submitted as Utility gets flagged later, which
  is worse. The categories above are deliberate.
- **Wrong variable count.** The number of placeholders in the body must equal the
  number of samples and the number the app sends.

If a template is rejected, fix and resubmit — there is no appeal queue worth
waiting on. Track approval status in the admin under *Messaging → Message
templates*: each row carries `approval_status`, and everything ships as `pending`
until you mark it approved.

---

## Switching over

1. Set the credentials in `.env` and `WHATSAPP_DRIVER=cloud_api`.
2. `php artisan config:cache`
3. Restart the queue worker — workers hold the old config until they do.
4. Send a test registration to a staff number and confirm the OTP arrives, then the
   confirmation with the badge image.
5. Check the delivery log: the row should move to `delivered` within seconds once
   the webhook is live.

If a send fails, the job retries on the backoff schedule in
`config/whatsapp.retry_backoff` (60s, 5m, 30m; `max_attempts` 4). After that the row
sits in the delivery log as `failed` with Meta's error message, and can be retried
by hand from the admin. Reverting to `WHATSAPP_DRIVER=log` is safe at any moment and
stops all outbound traffic without losing queued work.
