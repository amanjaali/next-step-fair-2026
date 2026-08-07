#!/usr/bin/env bash
#
# One-command local setup for Next Step Fair 2026.
#
# Safe to run more than once: it only fills in what is missing, and never
# overwrites an existing .env or an existing database. Run it after a fresh
# unzip, or any time the site stops working and you are not sure why.
#
#   bash setup.sh
#
set -uo pipefail

BOLD=$'\033[1m'; GREEN=$'\033[32m'; RED=$'\033[31m'; YELLOW=$'\033[33m'; OFF=$'\033[0m'
step()  { printf '\n%s==> %s%s\n' "$BOLD" "$1" "$OFF"; }
ok()    { printf '%s    ok%s %s\n' "$GREEN" "$OFF" "$1"; }
warn()  { printf '%s    !%s  %s\n' "$YELLOW" "$OFF" "$1"; }
die()   { printf '\n%s    ✗  %s%s\n\n' "$RED" "$1" "$OFF"; exit 1; }

# Value of one .env key, with quotes and any trailing comment stripped.
envval() { grep -E "^$1=" .env 2>/dev/null | head -1 | cut -d= -f2- | sed -e 's/[[:space:]]*#.*$//' -e 's/^"//' -e 's/"$//' -e 's/[[:space:]]*$//'; }

cd "$(dirname "$0")" || die "Could not enter the project directory."

# --------------------------------------------------------------- prerequisites
step "Checking what you have installed"

command -v php >/dev/null      || die "PHP is not installed. Install Laravel Herd from https://herd.laravel.com, quit Terminal completely (Cmd-Q), reopen it and run this again."
command -v composer >/dev/null || die "Composer is not installed. Laravel Herd includes it — install Herd, quit Terminal completely (Cmd-Q), reopen it and run this again."
command -v npm >/dev/null      || die "Node is not installed. Get the LTS installer from https://nodejs.org, then run this again."

PHP_MAJOR=$(php -r 'echo PHP_MAJOR_VERSION;')
PHP_MINOR=$(php -r 'echo PHP_MINOR_VERSION;')
if [ "$PHP_MAJOR" -lt 8 ] || { [ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -lt 3 ]; }; then
    die "PHP $PHP_MAJOR.$PHP_MINOR is too old — this needs PHP 8.3 or newer."
fi

ok "PHP $(php -r 'echo PHP_VERSION;')"
ok "Composer $(composer --version --no-ansi 2>/dev/null | awk '{print $3}')"
ok "Node $(node -v)"

# ---------------------------------------------------------------- stale caches
#
# Unzipping a new build over the old folder leaves the previous build's compiled
# views, cached config and cached routes behind. They are compiled against files
# that have just changed underneath them, which surfaces as a 500 on every page
# with nothing obviously wrong. Clearing them first costs nothing and removes a
# whole category of "it worked yesterday".
step "Clearing anything left from a previous build"

rm -f bootstrap/cache/config.php bootstrap/cache/routes-*.php bootstrap/cache/events.php
rm -f bootstrap/cache/services.php bootstrap/cache/packages.php
rm -rf storage/framework/views/*.php
ok "Cleared"

# ---------------------------------------------------------------- dependencies
step "Installing dependencies (a few minutes the first time)"

if [ -f vendor/autoload.php ]; then
    ok "PHP packages already installed"
    # The package manifest was just deleted above, so rebuild it. A new build can
    # add a package the old manifest has never heard of.
    php artisan package:discover --ansi >/dev/null 2>&1
else
    composer install --no-interaction || die "composer install failed. Scroll up for the reason."
    ok "PHP packages installed"
fi

if [ -d node_modules ]; then
    ok "JavaScript packages already installed"
else
    npm install || die "npm install failed. Scroll up for the reason."
    ok "JavaScript packages installed"
fi

# -------------------------------------------------------------------- settings
step "Setting up the configuration file"

if [ -f .env ]; then
    ok ".env already exists — leaving your settings alone"
else
    cp .env.example .env

    # .env.example targets production: MySQL, no debug, real mail. Point it at a
    # local SQLite file so nothing outside this folder needs to exist.
    sed -i '' \
        -e 's|^APP_ENV=production|APP_ENV=local|' \
        -e 's|^APP_DEBUG=false|APP_DEBUG=true|' \
        -e 's|^APP_URL=.*|APP_URL=http://127.0.0.1:8000|' \
        -e 's|^LOG_LEVEL=warning|LOG_LEVEL=debug|' \
        -e 's|^DB_CONNECTION=mysql|DB_CONNECTION=sqlite|' \
        -e 's|^DB_HOST=|#DB_HOST=|' \
        -e 's|^DB_PORT=|#DB_PORT=|' \
        -e 's|^DB_DATABASE=|#DB_DATABASE=|' \
        -e 's|^DB_USERNAME=|#DB_USERNAME=|' \
        -e 's|^DB_PASSWORD=|#DB_PASSWORD=|' \
        -e 's|^SESSION_DRIVER=database|SESSION_DRIVER=file|' \
        -e 's|^CACHE_STORE=database|CACHE_STORE=file|' \
        -e 's|^QUEUE_CONNECTION=database|QUEUE_CONNECTION=sync|' \
        -e 's|^MAIL_MAILER=smtp|MAIL_MAILER=log|' \
        .env 2>/dev/null || sed -i \
        -e 's|^APP_ENV=production|APP_ENV=local|' \
        -e 's|^APP_DEBUG=false|APP_DEBUG=true|' \
        -e 's|^APP_URL=.*|APP_URL=http://127.0.0.1:8000|' \
        -e 's|^LOG_LEVEL=warning|LOG_LEVEL=debug|' \
        -e 's|^DB_CONNECTION=mysql|DB_CONNECTION=sqlite|' \
        -e 's|^DB_HOST=|#DB_HOST=|' \
        -e 's|^DB_PORT=|#DB_PORT=|' \
        -e 's|^DB_DATABASE=|#DB_DATABASE=|' \
        -e 's|^DB_USERNAME=|#DB_USERNAME=|' \
        -e 's|^DB_PASSWORD=|#DB_PASSWORD=|' \
        -e 's|^SESSION_DRIVER=database|SESSION_DRIVER=file|' \
        -e 's|^CACHE_STORE=database|CACHE_STORE=file|' \
        -e 's|^QUEUE_CONNECTION=database|QUEUE_CONNECTION=sync|' \
        -e 's|^MAIL_MAILER=smtp|MAIL_MAILER=log|' \
        .env

    ok "Created .env pointed at a local SQLite database"
fi

grep -q '^APP_KEY=base64:' .env || { php artisan key:generate --no-interaction >/dev/null && ok "Generated the app key"; }
grep -q '^TICKET_QR_SECRET=.\{10,\}' .env || { php artisan nextstep:qr-secret >/dev/null 2>&1 && ok "Generated the QR signing secret"; }

# Without a key nothing decrypts, so every page is a 500 and the seeder dies
# halfway with a stack trace that says nothing about .env. Stop here instead,
# where it is one line to read and one line to fix.
grep -q '^APP_KEY=base64:' .env \
    || die "APP_KEY is still empty in .env. Run 'php artisan key:generate' and then this script again."

# Keep a copy outside the project so a careless unzip cannot wipe it again.
cp .env "$HOME/.nextstep-env-backup" 2>/dev/null && ok "Backed up your settings to ~/.nextstep-env-backup"

php artisan config:clear >/dev/null 2>&1

# -------------------------------------------------------------------- database
step "Preparing the database"

DB_PATH=$(envval DB_DATABASE)
if [ "$(envval DB_CONNECTION)" = "sqlite" ]; then
    [ -n "$DB_PATH" ] || DB_PATH="database/database.sqlite"
    touch "$DB_PATH"
fi

if php artisan migrate --no-interaction --force >/dev/null 2>&1; then
    ok "Schema is up to date"
else
    die "Migrations failed. Run 'php artisan migrate' on its own to see why."
fi

# Which database are we actually talking to? Worth saying out loud, because
# "the site is empty" and "the site is pointed at a different database" look
# identical from the browser.
DB_KIND=$(envval DB_CONNECTION)
if [ "$DB_KIND" = "sqlite" ]; then
    ok "Using SQLite at ${DB_PATH}"
else
    ok "Using $DB_KIND database '$(envval DB_DATABASE)'"
fi

# Seed when the *content* is missing, not just when registrations are. A database
# with tables but no pages renders a site where The Expo and The Conference 404
# and every listing is empty — which reads as "broken", not "unseeded".
count_rows() {
    php artisan tinker --execute="echo \\DB::table('$1')->count();" 2>/dev/null | tr -dc '0-9'
}

PAGE_COUNT=$(count_rows pages)
SECTOR_COUNT=$(count_rows sectors)
REG_COUNT=$(count_rows registrations)

if [ -z "$PAGE_COUNT" ] || [ "$PAGE_COUNT" = "0" ] || [ -z "$SECTOR_COUNT" ] || [ "$SECTOR_COUNT" = "0" ]; then
    printf '    loading content and demo data, this takes a minute or two...\n'
    php artisan db:seed --no-interaction --force >/dev/null 2>&1 || die "Seeding failed. Run 'php artisan db:seed' on its own to see why."
    ok "Loaded pages, taxonomy, programme and demo registrations"
else
    ok "Content already loaded (${PAGE_COUNT} pages, ${SECTOR_COUNT} sectors, ${REG_COUNT} registrations)"
    printf '        (to start clean: php artisan migrate:fresh --seed)\n'
fi

php artisan storage:link >/dev/null 2>&1

# ---------------------------------------------------------------------- assets
step "Building the styles and scripts"
npm run build >/dev/null 2>&1 || die "npm run build failed. Run it on its own to see why."
ok "Built"

# ----------------------------------------------------------------------- done
printf '\n%s────────────────────────────────────────────────────%s\n' "$BOLD" "$OFF"
printf '%s  Ready. Start the site with:%s\n\n' "$BOLD" "$OFF"
printf '      php artisan serve\n\n'
printf '  Then open  http://127.0.0.1:8000\n\n'
printf '  Dashboard   http://127.0.0.1:8000/admin\n'
printf '              admin@nextstepfair.com  /  password\n\n'
printf '  Student     http://127.0.0.1:8000/en/signin\n'
printf '              student1@example.com  /  password\n'
printf '              (this is what opens the signed-in home page,\n'
printf '               the opportunities board and the scholarship)\n\n'
printf '  Exhibitor   http://127.0.0.1:8000/en/portal/signin\n'
printf '              admissions@university-of-sulaimani.edu\n'
printf '              (the sign-in code appears on screen in test mode)\n'
printf '%s────────────────────────────────────────────────────%s\n\n' "$BOLD" "$OFF"
