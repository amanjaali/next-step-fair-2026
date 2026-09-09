# Deploying to a new server

For the team putting Next Step Fair 2026 onto a fresh AWS account. It assumes
Linux and nginx experience and no knowledge of this project.

Read the two warnings in **Before you start** first. Both are the kind that
cannot be undone later.

---

## The source

Public repository, no credentials needed to read it:

```
https://github.com/amanjaali/next-step-fair-2026
```

`main` carries the current version, so a plain clone is enough:

```
git clone https://github.com/amanjaali/next-step-fair-2026.git
```

(`scholarship-dashboard` points at the same commit and is kept as history.)

Built CSS and JS are committed under `public/build`, so Node is not needed on the
server unless you intend to change the front end there.

---

## Before you start — two things that cannot be undone

### 1. `APP_KEY` decrypts every attendee's phone number and email

Those columns are encrypted at rest with Laravel's application key. **If you
bring the existing database to the new server, you must bring `APP_KEY` with it.
A new key makes every phone number and email in that database permanently
unreadable** — there is no recovery, and the badges are already sent, so the
damage shows up as an unusable attendee list on the day.

- Starting with an empty database → generate a fresh key, and keep it safe.
- Moving the old database across → copy `APP_KEY` from the old server's `.env`
  verbatim, before you run anything.

Same rule for `TICKET_QR_SECRET`: it signs the QR on every badge. Change it and
every badge already sent stops scanning until you regenerate and resend them all.

### 2. Decide what happens to the old server

There is an existing EC2 instance at `13.49.238.82` in `eu-north-1`, on the other
AWS account, running an older version of this site. Before pointing a domain at
the new one:

- Does it hold **real registrations**, or only test data? Check the count in
  *Registrations* in its dashboard.
- If real: take a `mysqldump` **and** its `APP_KEY` before you touch anything.
- If test only: nothing to carry over. Start clean, which is simpler and safer.

Nobody currently has SSH access to that instance — the key pair was lost. If you
need to get in, attach an IAM role with `AmazonSSMManagedInstanceCore` and use
Session Manager.

---

## What the server needs

| | |
| --- | --- |
| PHP | 8.3 or newer, with `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `xml`, `zip` |
| Web server | nginx + PHP-FPM |
| Database | MySQL 8 or MariaDB 10.6+ |
| Composer | 2.x |
| Node | only if building assets on the server (20+) |
| Cache / queue | database driver works; Redis is better under load |

**`gd` is not optional.** It draws the QR badge picture and the registration
picture code. Without it registration fails at the last step.

**Install `imagick` if you can.** It is what lets an SVG logo be drawn onto a
badge. Everything works without it; SVG partner logos are simply refused at
upload instead.

Instance size: the old server fell over on its own once, almost certainly running
out of memory. `t3.small` is the floor for the fair; `t3.medium` if the budget
allows. Set PHP-FPM's `pm.max_children` against the real memory, not the default.

---

## Steps

```bash
# 1. Code
git clone https://github.com/amanjaali/next-step-fair-2026.git
cd next-step-fair-2026
composer install --no-dev --optimize-autoloader

# 2. Configuration
cp .env.example .env
php artisan key:generate            # NOT if you are importing the old database
php artisan nextstep:qr-secret      # NOT if you are importing the old database

# 3. Database — create it and the user first, then fill DB_* in .env
php artisan migrate --force

# 4. Content. Every seeder below is real content — pages, programme, taxonomy,
#    message templates. Run them in this order.
for s in RoleSeeder TaxonomySeeder SiteContentSeeder PageSeeder ProgrammeSeeder \
         NewsSeeder EditionSeeder MediaSeeder MessageTemplateSeeder \
         OpportunitySeeder OfferPopupSeeder; do
  php artisan db:seed --class=$s --force
done

#    Do NOT run `php artisan db:seed` on its own, and never DemoDataSeeder or
#    MatchingDemoSeeder: they invent ~700 fake registrations and their check-ins,
#    which is exactly what you do not want in a database that is about to take
#    real ones.

# 5. Files and permissions
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 6. Caches, once the domain is in APP_URL
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Point nginx's root at **`public/`**, never at the project directory.

### The two background processes

Neither is optional. Without the first, no WhatsApp message is ever sent; without
the second, no reminder goes out.

**Queue worker** — supervisor, systemd, whatever you prefer:

```
php artisan queue:work --tries=3 --timeout=90
```

**Scheduler** — one cron line:

```
* * * * * cd /var/www/next-step-fair-2026 && php artisan schedule:run >> /dev/null 2>&1
```

### After every deploy

```
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart          # workers keep the old code until told
sudo systemctl reload php8.3-fpm
```

`queue:restart` is the one people forget: a running worker holds the code it
started with, so a fix to a message goes out with the old text until it restarts.

---

## `.env` — what actually has to be filled in

| Key | Notes |
| --- | --- |
| `APP_URL` | The real https address. Getting this wrong breaks stylesheets — see the trap below. |
| `APP_ENV=production`, `APP_DEBUG=false` | Debug on a public server prints the environment, keys included. |
| `APP_KEY` | Generated, or carried from the old server. See the warning above. |
| `DB_*` | Database host, name, user, password. |
| `TICKET_QR_SECRET` | Signs badge QRs. |
| `SEED_STAFF_PASSWORD` | Set this before seeding, or the seeder invents one and prints it once. |
| `WHATSAPP_DRIVER` | `log` until OTPIQ is ready, then `otpiq`. |
| `OTPIQ_*` | Keys and ids from OTPIQ — see `docs/whatsapp-otpiq.md`. |
| `MAIL_*` | Conference RSVPs are confirmed by email; without this they silently do not arrive. |

**The HTTPS trap:** the app forces the https scheme based on `APP_URL` beginning
with `https://`, not on the environment. Set it to https **before** the
certificate exists and every stylesheet points at a closed port — the page
returns 200 and renders as unstyled HTML. Get the certificate first, then change
`APP_URL`.

---

## Before it is reachable from the internet

1. **Change the seeded staff passwords**, or set `SEED_STAFF_PASSWORD` before
   seeding. These accounts can issue entry credentials.
2. **HTTPS.** Certbot and a domain. Then `APP_URL`, in that order.
3. **SSH restricted to known addresses.** Never `0.0.0.0/0`.
4. **MySQL not reachable from outside** the security group.
5. **Backups.** A nightly `mysqldump` somewhere off the instance. Losing the
   registration list a week before the fair has no recovery path.
6. **Keep `APP_KEY` and `TICKET_QR_SECRET` somewhere other than the server** —
   a password manager. The server is the one place that can burn down.

## Never, once real registrations exist

- `php artisan migrate:fresh` — drops every table.
- `php artisan db:seed` on its own — the demo seeder invents registrations.
- Regenerating `APP_KEY` — see the first warning.
- Regenerating `TICKET_QR_SECRET` without reissuing every badge.

---

## Checking it works, in order

1. The home page loads **with styling**. Unstyled means the `APP_URL` trap.
2. `/en/register/quick` shows a **picture code** above the button and it changes
   on reload. If the box is empty, PHP is missing `gd`.
3. Register a test person. The badge page appears with a QR **and both partner
   logos**.
4. *Messaging → Delivery log* in `/admin` shows the WhatsApp message with its
   full text. On the `log` driver that is the whole test; on `otpiq` the message
   should arrive on the phone.
5. Scan the QR with the check-in app at `/checkin` signed in as gate staff.
6. Wait a minute and confirm the queue worker is running: `php artisan queue:work`
   in the foreground should show jobs being processed, not sitting.

---

## Where the rest is written down

- `docs/handover.md` — how the project is put together, and the traps that have
  already cost a day each.
- `docs/whatsapp-otpiq.md` — connecting WhatsApp, with the template parameters.
- `docs/admin-guide.md` — for the organising team, not developers.
