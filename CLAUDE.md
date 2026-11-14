# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Leafmark is a multi-user book tracking app: book collections with reading status/progress, tags, series, covers, yearly reading challenges, statistics, family groups, and imports from external book APIs, Amazon, Goodreads CSV and ZIP backups.

**Stack:** PHP 8.5 · Laravel 13 · Postgres 16 (production and tests) · Blade + Alpine.js + Tailwind 3, built with Vite · serversideup/php (FPM + Nginx) image · GitHub Actions → GHCR → Coolify.

Version: `app.version` in `config/app.php`, shown in the footer with the commit hash (from `VERSION_HASH`, written at image build).

## ⚠️ Things that bite

- **Book route keys are Unix timestamps of `added_at`, not IDs.** `Book::getRouteKey()` / `resolveRouteBinding()` resolve by timestamp (fallback: ID) and always scope to `auth()->id()`. Routes use `where('book', '[0-9]+')`.
- **Route order in `routes/web.php` matters:** cover routes before `DELETE /books/{book}`; specific `/books/...` routes before `Route::resource('books')`.
- **Postgres only** (production, CI and tests). Postgres `LIKE` is case-sensitive: use `whereLike()` for user-facing search. Tests need the Postgres container running (`docker compose up -d db`).
- **`APP_KEY` must never change in production**: 2FA secrets and recovery codes are stored with the `encrypted` cast.
- **`env()` only inside `config/`.** The container runs `config:cache` on start, so `env()` elsewhere returns null (e.g. `ADMIN_EMAIL` is read via `config('app.admin_email')`).
- **`SystemSetting` reads the DB directly (no cache)**; SMTP is configured at runtime from `system_settings` by `DynamicMailConfigServiceProvider`. Admin → System Settings edits registration, SMTP and API keys; secrets (`smtp_password`, `google_books_api_key`, `bigbook_api_key`) go through `SystemSetting::setSecret()` / `getSecret()` (encrypted with `APP_KEY`, legacy plaintext still readable).
- **Merging to `main` deploys to production.** The workflow pushes `ghcr.io/<owner>/leafmark:latest` and, because the repo variable `AUTO_DEPLOY=true` is set, triggers the Coolify webhook. Work on branches.
- **No personal data of the maintainer** in code, docs, commits or issues: no names, email addresses, private domains, locations or account-specific tokens. Use placeholders (`<owner>`, `example.com`) and environment variables (e.g. `APP_IMAGE`, `GOOGLE_SITE_VERIFICATION`).
- **One container only.** Nothing is queued (`QUEUE_CONNECTION=sync`) and nothing is scheduled (`routes/console.php` is empty), so there is no worker or scheduler. Adding queued jobs or scheduled tasks requires adding those containers to `deploy/docker-compose.coolify.yml`.

## Commands

```bash
php artisan test                         # full suite against Postgres database leafmark_test (see phpunit.xml)
php artisan test --filter=MagicLinkTest  # single class or method
php artisan test tests/Feature/StatsTest.php

php artisan serve & npm run dev          # local dev (Postgres from docker-compose.yml)
npm run build                            # production assets → public/build
docker compose up -d --build             # run the production image locally on :8080
```

There is no linter or formatter configured. Tests call `withoutVite()` (in `tests/TestCase.php`), so no asset build is needed to run them.

## Documentation

Update whatever a change affects, in the same commit:

- `handbuch/<Seite>.md` (German): all details for operators and users. It is the **source of the GitHub wiki**.
- `README.md`: only for a new module or a changed quick start; keep it short.
- `CLAUDE.md`: changes to architecture, rules or workflows.
- Wiki: regenerate with `python3 scripts/build-wiki.py --push` (pushes to `<repo>.wiki.git`). Never edit the wiki by hand; pages missing from `handbuch/` are removed.

Finish changes by committing and pushing to `main`.

## Deployment

Follows the owner's deployment standard: **Coolify never builds.**

1. `.github/workflows/build.yml`: tests (Postgres service) → Docker build (`pull: true`, weekly rebuild for PHP patches) → push to GHCR → print `php -v` → fail if the frontend bundle contains anything that looks like a key.
2. Coolify runs `deploy/docker-compose.coolify.yml` (app only) as a "Docker Compose Empty" resource and only pulls images. Postgres is a **separate Coolify resource**; the app reaches it via the external `coolify` network and `DB_HOST`. Do not add a `db` service to that compose file. Edits to that file must be pasted into Coolify by hand.
3. On container start, serversideup AUTORUN runs `migrate --force`, `storage:link` and the config/route/view caches. There is no entrypoint script.
4. Rollback: set `APP_TAG` in Coolify to a `sha-…` or semver tag and redeploy. Don't roll back to images built with an unpatched PHP.

**PHP version lives in three places, changed together:** `Dockerfile` (`ARG PHP_VERSION`), `composer.json` (`"php"`), `.devcontainer/devcontainer.json` (image tag).


## Architecture

### Authentication (order of preference)

1. **Passkeys:** `laravel/passkeys` (package routes under `/passkeys/*` and `/user/passkeys*`) plus the `@laravel/passkeys` JS client. The Alpine components `passkeyLogin` and `passkeyRegister` live in `resources/js/app.js`. Managing passkeys requires the `password.confirm` middleware (`ConfirmPasswordController`).
2. **Magic link:** `MagicLinkController` + `LoginToken` (SHA-256 hashed, 15 min, single use, uniform response). Only available when SMTP is enabled. The GET from the email shows a confirm page and the POST consumes the token, so mail scanners can't burn it. The first magic-link login offers passkey setup once (`users.passkey_prompted_at`).
3. **Password + mandatory TOTP 2FA:** `LoginController` only validates the password and stores `login.id` in the session. `TwoFactorLoginController` then shows the challenge, or forces 2FA setup when none is configured. `TwoFactorService` wraps google2fa (replay-protected via cache) and the recovery codes. Admins can reset 2FA.

All logins set `last_login_at` through a `Login` event listener in `AppServiceProvider`. Settings → "Sign-in & security" (`SecuritySettingsController`, `/settings/security`) manages passkeys and 2FA.

First registered user, or the user whose email matches `ADMIN_EMAIL`, becomes admin (`RegisterController`). Registration modes (open / domain / code) come from `SystemSetting`.

### Rate limiting

Named limiters live in `AppServiceProvider::configureRateLimiting()`: `login` (password, magic link, 2FA, confirm), `register`, `external-api` (30/min and 500/day per user, applied in `BookController::middleware()` to every action that calls external book APIs or Amazon), and `expensive` (imports and exports). New endpoints that are costly or call external services need one of these.

### Data model

User → Books, Tags, ReadingChallenges, ImportHistory, BookViewPreferences, Passkeys, LoginTokens; User ↔ Family (`family_id`, family `owner_id`, so the relationship is circular). Book → BookCovers, ReadingProgressHistory; Book ↔ Tag via `book_tag`. Books have status `want_to_read` / `currently_reading` / `read` with `added_at` / `started_at` / `finished_at`. Everything is user-scoped. Family membership does **not** share books, and admins cannot see other users' books.

### External book data

Services in `app/Services/`: Google Books, Open Library, BookBrainz, Big Book API, Deutsche Nationalbibliothek (`DnbService`, SRU + MARC21-xml, results cached 24 h, text normalized to Unicode NFC because the DNB delivers decomposed umlauts), Amazon scraper. `GoogleBooksService` searches ISBNs as `isbn:` and passes everything else unchanged to Google (no title/author guessing), uses a 10 s timeout, retries without key when Google rejects it, and exposes `lastError` (`quota`, `key_rejected`, `unavailable`), which the search page shows as a notice. Google Books and Big Book only appear as sources when a key is set. `BookController::mergeSearchResults()` takes results in source order and dedupes by ISBN-13 and ISBN-10; DNB results go first for German ISBNs (978-3…) or a German UI. API keys are system settings (Admin → System Settings), with a `GOOGLE_BOOKS_API_KEY` env fallback. `BookController::create()` merges multi-source search results. Covers are stored on the `public` disk under `covers/` (`CoverImageService`); fallback order is primary cover → first cover → `local_cover_path` → external URL.

### Imports and exports

- Goodreads CSV: `ImportController` + `GoodreadsImportService`, with a session-based upload → preview → execute flow.
- ZIP library backup: `LibraryTransferController` + `LibraryExportService` / `LibraryImportService`. Schema version 1, path-traversal checks, duplicate strategies `skip` / `overwrite` / `keep_both`.

Both record runs in `ImportHistory`.

### Frontend

Blade views per feature in `resources/views/`; layout `layouts/app.blade.php`. The main navigation is one `$navItems` array rendered as a top bar on desktop and a bottom tab bar on mobile. Alpine and the passkey client are bundled in `resources/js/app.js`; Chart.js only loads on the stats page (`resources/js/charts.js`). Tailwind scans `resources/views`, `resources/js`, `app` and `lang`, so dynamic class names must appear literally somewhere.

### Internationalization

English is the development language. **Every UI string goes into `lang/en/app.php` and the same keys into `de`, `fr`, `it`, `pl`, `es`.** The locale is per user (`users.preferred_language`, set by `SetUserLocale` middleware), falling back to `APP_LOCALE`. URLs carry no locale prefix, and old `/de/...` URLs 301-redirect.
