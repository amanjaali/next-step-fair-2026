# Next Step Fair 2026 — technical handover

The site, the dashboard behind it, and what still needs doing. Written for a
developer picking this up for the first time.

---

## What it is

The public site and the organisers' dashboard for Next Step Fair 2026 — the
Kurdistan Region's higher-education fair. It carries registration for both tracks
(fair and conference), the agenda, the exhibitor directory, the National
Scholarship Program end to end, the opportunities board, WhatsApp messaging, and
badge issue and check-in.

Three languages throughout — English, Kurdish (Sorani) and Arabic — with real
right-to-left layout, not a mirrored stylesheet. Every page lives under
`/{locale}/`.

## Stack

| | |
| --- | --- |
| PHP | 8.4 (the server runs 8.5; both are fine) |
| Laravel | 13 |
| Filament | 4 (the dashboard) |
| Livewire | 3 |
| Database | MySQL 8 with utf8mb4 in production, SQLite locally |
| Front end | Blade, Tailwind CSS 4, Alpine.js, Vite (Rolldown) |
| Tests | PHPUnit — 480 of them, all passing |

Notable packages: `spatie/laravel-permission` (roles), `spatie/laravel-translatable`
(the JSON translation columns), `simplesoftwareio/simple-qrcode` (badges).

## Running it locally

```
composer install
cp .env.example .env
php artisan key:generate
php artisan nextstep:qr-secret
touch database/database.sqlite     # and set DB_CONNECTION=sqlite in .env
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

Site at `/en`, dashboard at `/admin`. The seeded accounts are listed in the
README — **see the security note below before using them anywhere real.**

## How it is put together

**Three authentication guards, deliberately separate.** `web` is staff in the
dashboard; `attendee` is a registered student or visitor on the public site;
`institution` is an exhibitor in their own portal. An exhibitor is not a role on a
staff account — they are a different guard entirely, so no permission change can
put a university one step away from the admin panel.

**Content is editable from the dashboard, not from files.** Home page wording,
logos, partner pages, the Zankoline centres, opportunities, news, speakers,
sessions, popups. The pattern throughout: a `Setting` row overlays the
translation file, and an empty field means "use the shipped wording" rather than
"publish nothing". See `app/Support/helpers.php` — `ns_home()`, `ns_brand()`,
`ns_image()`, `ns_zankoline()`.

**Event facts are overlaid at boot.** `AppServiceProvider::applyEditedEventFacts()`
merges the dashboard's event settings onto `config('nextstep.event.*')`, so
editing the dates once corrects the badges, the calendar file, the WhatsApp
messages and the structured data together — about a hundred call sites.

**Editor HTML is filtered on output, not on save** (`app/Support/Html.php`). An
allowlist: `<script>`, `on*` handlers and `javascript:` URLs never survive.
Filtering on output rather than on save also covers rows already in the database.

**Attendee phone numbers and emails are encrypted at rest**, with a keyed hash
beside each for lookups. This is why `APP_KEY` on the server must never be
regenerated or replaced — doing so makes every stored phone number and email
unreadable, permanently.

## Deploying

The server is an EC2 instance in eu-north-1 running nginx + PHP-FPM + MySQL, with
the site at `/var/www/next-step-fair-2026`. The repository is public on GitHub, so
the server can pull directly.

```
cd /var/www/next-step-fair-2026
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:clear && php artisan view:clear
sudo chown -R www-data:www-data storage bootstrap/cache
sudo systemctl reload php8.5-fpm
```

Built CSS and JS are committed under `public/build`, so npm is not needed on the
server. If you do build there, run `npm ci && npm run build`.

Two traps that have already cost a day each:

- **Directory permissions.** Copying files with `rsync -a` carries the source
  directory's mode across. A project directory at `drwx------` makes nginx
  return 404 for every page while looking perfectly normal in `ls`. It must be
  `755`, and `namei -l /var/www/next-step-fair-2026/public/index.php` is the
  quickest way to see which component is wrong.
- **`URL::forceScheme('https')`.** It is keyed on `APP_URL` starting with
  `https://`, not on the environment name. Forcing https on a server without a
  certificate points every stylesheet at a closed port, and the page renders as
  unstyled HTML while still returning 200. `AssetSchemeTest` guards this.

Never run `migrate:fresh` or `db:seed` on the server once real registrations
exist. The seeders are for building a database, not for one in service; every
schema change that needs data ships as a migration instead.

## Outstanding — in priority order

1. **The five seeded dashboard passwords are `password`, and they are printed in
   the README of a public repository.** Change all five in *Platform → Staff
   accounts* and delete that table from the README. This is the first thing to
   do, before anything else on this list.
2. **No HTTPS.** Port 443 is open on the security group but nothing is listening
   and no certificate is installed. Certbot plus a domain name, then set
   `APP_URL=https://…` — in that order, or see the scheme trap above.
3. **The site went down on its own** (nginx 504 — PHP-FPM not answering) and came
   back with a reboot. Almost certainly the instance running out of memory. Check
   `free -m`, `/var/log/syslog` for the OOM killer, and the PHP-FPM `pm.max_children`
   setting against the instance size. It will happen again on the busiest day
   unless this is understood.
4. **Nobody has the SSH key.** It is on no machine we could find, and EC2 Instance
   Connect is not installed on the instance. Attach an IAM role with
   `AmazonSSMManagedInstanceCore` and use Session Manager, or create a new key
   pair and attach it through a rescue instance.
5. **Security headers** (CSP, `X-Frame-Options`, `Referrer-Policy`) are not set.
6. **Check whether MySQL's port is reachable from outside** the security group.
7. **Badge download URLs are unsigned** — a guessed ticket reference returns a
   badge. Signed URLs would close it.

## Things worth knowing before changing anything

- **The top menu holds six items and no more.** At 15px type it fits on one line
  from 1280px up, and a seventh puts it onto two rows in English and Kurdish.
  Anything new goes in the dropdown panel, which costs no width. A test asserts
  the counts; if it fails, that is the guard working, not a broken test.
- **Filament filters only run their query when switched on.** An "include drafts"
  toggle left off runs nothing and shows everything — which is why the scholarship
  queue's filter is written as `hide_drafts`, defaulting to on.
- **`User::canAccessPanel()` holds a hard-coded list of roles.** A new role gets
  403 at the dashboard door until it is added there, no matter what permissions
  it has.
- **Eloquent caches a table's column list** the first time it mass-assigns to it.
  A migration that writes through a model and a later migration in the same run
  that adds a column will silently drop writes to the new column. Order schema
  changes before data migrations.
- The admin guide for non-technical staff is `docs/admin-guide.md`. It is written
  for the organisers, and it is worth reading to see what the dashboard is
  expected to do.
