# Deploying CareerPath BN

Written for whoever sets this up on the VPS. It covers the steps
that are not obvious from the code, and the handful of settings
that make the difference between a site that works and one that
looks like it works.

Everything here was read off the repository at the time of
writing. Where something still needs confirming on a real server,
it says so.

---

## What the server needs

- **PHP 8.3 or newer.** `composer.json` requires `^8.3`; the app
  is developed on 8.4.
- **MySQL 8.** `.env.example` points at MySQL. Sessions, cache and
  queue are all configured to use the database, so no Redis is
  required.
- **Composer.**
- **Node 20+, for the asset build only.** Nothing at runtime needs
  Node. If the VPS is small, run `npm ci && npm run build` on a
  development machine and deploy `public/build` with the code.
- The usual Laravel PHP extensions. `composer install` names any
  that are missing; `smalot/pdfparser` wants `zlib`, and
  `barryvdh/laravel-dompdf` wants `dom` and `mbstring`.

---

## First deployment

```bash
git clone <repo> careerpath-bn && cd careerpath-bn

composer install --no-dev --optimize-autoloader

cp .env.example .env
php artisan key:generate          # first deployment only
# edit .env — see "Settings that matter" below

php artisan migrate --force

php artisan db:seed --force       # organisation groups, feature
                                  # definitions, plans, BIICF
                                  # reference scales

# The framework's own job roles. Must be its own command — see
# "The seeders" below.
php artisan db:seed --class=BiicfJobRoleSeeder --force

php artisan storage:link

npm ci && npm run build           # unless public/build is deployed

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Point the web server's document root at `public/`, and make
`storage/` and `bootstrap/cache/` writable by the web server user.

### Before you call it done

- Log in as an administrator and open the admin dashboard. If the
  recommendation and career tiles read 0 when there is data, the
  config cache has gone stale — `php artisan config:clear` and
  re-cache.
- Open a student account and generate recommendations. If this
  fails, read "The AI driver" below before anything else.
- Open the BIICF Explorer and check a role's Career Paths panel.

---

## Settings that matter

Everything else in `.env.example` can stay as it is.

### Mail, and why it gates the whole site

```
MAIL_HOST / MAIL_PORT / MAIL_USERNAME / MAIL_PASSWORD
MAIL_FROM_ADDRESS
AUTH_VERIFICATION_REQUIRED_FROM=2026-10-10
```

Any student account created on or after
`AUTH_VERIFICATION_REQUIRED_FROM` must confirm its email address
before it can use the site. Accounts created before that date are
never asked, so existing users are not locked out.

**If mail does not work, nobody who signs up can get in.** They
will register, be asked to confirm, and never receive anything.
Test a real registration before announcing the site.

Two escape hatches, in order of preference:

1. An administrator creating an account can tick **Skip email
   confirmation** on the user form, which waives confirmation for
   that account. This is the intended route for dummy and demo
   accounts.
2. Clearing `AUTH_VERIFICATION_REQUIRED_FROM` switches the
   requirement off entirely. Do this knowingly: it also switches
   it off for real students.

### The AI driver

```
CAREER_AI_DRIVER=groq
CAREER_ADVISER_DRIVER=groq
CAREER_AI_TRANSPORT=openrouter
OPENROUTER_API_KEY=
```

`CAREER_AI_DRIVER` decides the prompts and the response schema.
`CAREER_AI_TRANSPORT` decides who actually runs the model. They
are separate on purpose — the provider can change without
touching anything that affects what a student is told — so
`groq` over `openrouter` is a normal combination and not a
mistake.

`mock` is the offline driver. It needs no key and no network: it
scores each BIICF job role against the student's recorded
competencies and picks three. It answers in the same schema as
the real client and the competency analysis is computed from the
framework either way, so a demo on `mock` is a fair
representation of the feature — just without a model's
judgement or its wording.

**Set `OPENROUTER_API_KEY` if the driver is not `mock`.** Without
it, generation fails outright.

### Database and app

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<the real domain>
DB_DATABASE / DB_USERNAME / DB_PASSWORD
```

`APP_DEBUG=true` on a public server shows stack traces, database
credentials included, on any error page.

`APP_URL` has to be right or the links in confirmation emails
point at localhost.

---

## PHP limits, for the edition upload

An administrator can upload a new edition of the framework as a
PDF, and the app accepts files up to **64 MB**
(`max:65536` in `BiicfEditionController`). PHP's own defaults are
smaller, so the upload fails before the app sees it.

In `php.ini`:

```ini
upload_max_filesize = 64M
post_max_size = 72M        ; above upload_max_filesize
memory_limit = 512M        ; PDF text extraction holds a lot
max_execution_time = 300   ; a full edition takes minutes
```

If the web server is nginx, raise its own limit too:

```nginx
client_max_body_size 72M;
```

The 512M and 300s figures are starting points rather than
measurements. If an upload dies silently, these are the first
things to raise.

---

## The seeders

`php artisan db:seed` runs `DatabaseSeeder`, which seeds
organisation groups, feature definitions, plans and the BIICF
reference scales — and **creates a user called "Test User"
(`test@example.com`)**. Delete that account on a public server.

`DatabaseSeeder` deliberately does **not** run the job role
seeder. From `BiicfJobRoleSeeder`'s own notes:

> It must not be called from a seeder using `WithoutModelEvents`,
> including `DatabaseSeeder`: a removed tier is cleared by a model
> hook rather than a database cascade.

So it goes in a command of its own:

```bash
php artisan db:seed --class=BiicfJobRoleSeeder --force
```

It loads the 38 roles of the second edition from
`database/data/biicf-job-roles.json`, with their descriptions,
duties, trainings, tiers and competency tables. It matches
existing roles by title, so it is safe to re-run and does not
orphan recommendations already pointing at a role.

### One decision to make: career path edges

`biicf_career_path_edges` — the "comes from / leads to"
progression behind the Explorer's Career Paths panel — is written
by **`BiicfExplorerSeeder` only**. `BiicfJobRoleSeeder` repoints
edges when it merges duplicate roles but creates none, and the
edition import neither writes edges nor reads progression out of
the PDF.

That leaves two options:

- **Book only** (`BiicfJobRoleSeeder` alone). The 38 roles exactly
  as the framework states them, with no progression until someone
  draws it. The admin job role form now edits both directions by
  hand, so this is a real option rather than a dead end.
- **Explorer first, then the book.** `BiicfExplorerSeeder` brings
  24 earlier hand-written roles and 15 progression links;
  `BiicfJobRoleSeeder` then overlays the book's roles, matching by
  title. Some progression exists immediately, at the cost of
  leaving behind any explorer role the book does not list.

**Confirm whichever order you pick on a throwaway database
first.** The combination has not been run end to end as part of a
deployment, and running the explorer seeder after the book's would
not give the same result.

---

## Updating a deployment

```bash
php artisan down

git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build          # if assets changed

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan up
```

Re-run `config:cache` after **any** `.env` change. A cached config
ignores the file.

---

## Things that will bite you

**Never run `migrate:fresh` on the server.** Applied editions live
only in the database. `EditionApplier` writes the decided changes
to the BIICF tables and nothing exports them back to a seeder, so
`migrate:fresh --seed` silently returns the framework to the
seeder's content and discards every edition applied since. It also
takes every student account, profile and recommendation with it.
Back up before any migration you are unsure about:

```bash
mysqldump -u <user> -p <database> > backup-$(date +%F).sql
```

**The scheduler is optional.** `premium:notify-expired` runs every
five minutes if a cron entry exists, and page loads do the same
work otherwise (see `CheckPremiumExpiry`). To add it:

```cron
* * * * * cd /path/to/careerpath-bn && php artisan schedule:run >> /dev/null 2>&1
```

**No queue worker is needed.** `QUEUE_CONNECTION=database` is
configured but nothing in the app queues work, so mail is sent
during the request. If sign-up feels slow, that is why — and a
worker plus `php artisan queue:work` would be the fix.

**`php artisan storage:link` is not optional.** Profile pictures,
avatars, Innovation Lab images and advertisements are all served
from `public/storage`. Without the symlink every one of them is a
broken image, and nothing in the logs says so.

---

## Uncertain, and worth a trial run

Two things in here have not been exercised on a real server:

1. The seeder order for career path edges, above.
2. The PDF upload limits. 64 MB is what the application accepts;
   whether 512M of memory and 300 seconds are enough for the
   second edition in one pass has not been measured.

Both are worth ten minutes on a scratch database before the real
deployment rather than during it.
