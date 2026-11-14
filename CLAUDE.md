# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Leafmark is a multi-user book tracking app: book collections with reading status/progress, tags, series, covers, yearly reading challenges, statistics, family groups, and imports from external book APIs, Amazon, Goodreads CSV and ZIP backups.

**Stack:** PHP 8.5 · Laravel 13 · Postgres 16 (production; SQLite works for tests) · Blade + Alpine.js + Tailwind 3, built with Vite · serversideup/php (FPM + Nginx) image · GitHub Actions → GHCR → Coolify.

Version: `app.version` in `config/app.php`, shown in the footer with the commit hash (from `VERSION_HASH`, written at image build).

## ⚠️ Things that bite

- **Book route keys are Unix timestamps of `added_at`, not IDs.** `Book::getRouteKey()` / `resolveRouteBinding()` resolve by timestamp (fallback: ID) and always scope to `auth()->id()`. Routes use `where('book', '[0-9]+')`.
- **Route order in `routes/web.php` matters:** cover routes before `DELETE /books/{book}`; specific `/books/...` routes before `Route::resource('books')`.
- **Code must run on both Postgres and SQLite.** No `strftime()`/driver-specific SQL; use `whereYear()`, `whereLike()` (case-insensitive on both), or group in PHP. CI runs the suite against both.
- **`APP_KEY` must never change in production**: 2FA secrets and recovery codes are stored with the `encrypted` cast.
- **`env()` only inside `config/`.** The container runs `config:cache` on start, so `env()` elsewhere returns null (e.g. `ADMIN_EMAIL` is read via `config('app.admin_email')`).
- **`SystemSetting` reads the DB directly (no cache)**; SMTP is configured at runtime from `system_settings` by `DynamicMailConfigServiceProvider`.
- Merging to `main` builds and pushes `ghcr.io/roberteinsle/leafmark:latest`. It only goes live when someone clicks Deploy in Coolify, or automatically if the repo variable `AUTO_DEPLOY=true` is set.

## Commands

```bash
php artisan test                         # full suite (SQLite in-memory, see phpunit.xml)
php artisan test --filter=MagicLinkTest  # single class or method
php artisan test tests/Feature/StatsTest.php

# Same suite against Postgres (docker compose up -d db)
DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_DATABASE=leafmark_test DB_USERNAME=leafmark DB_PASSWORD=leafmark php artisan test

php artisan serve & npm run dev          # local dev (Postgres from docker-compose.yml)
npm run build                            # production assets → public/build
docker compose up -d --build             # run the production image locally on :8080
```

There is no linter or formatter configured. Tests call `withoutVite()` (in `tests/TestCase.php`), so no asset build is needed to run them.

## Deployment

Follows the owner's deployment standard: **Coolify never builds.**

1. `.github/workflows/build.yml`: tests (SQLite + Postgres matrix) → Docker build (`pull: true`, weekly rebuild for PHP patches) → push to GHCR → print `php -v` → fail if the frontend bundle contains anything that looks like a key.
2. Coolify runs `deploy/docker-compose.coolify.yml` (app only) as a "Docker Compose Empty" resource and only pulls images. Postgres is a **separate Coolify resource**; the app reaches it via the external `coolify` network and `DB_HOST`. Do not add a `db` service to that compose file. Edits to that file must be pasted into Coolify by hand.
3. On container start, serversideup AUTORUN runs `migrate --force`, `storage:link` and the config/route/view caches. There is no entrypoint script.
4. Rollback: set `APP_TAG` in Coolify to a `sha-…` or semver tag and redeploy. Don't roll back to images built with an unpatched PHP.

**PHP version lives in three places, changed together:** `Dockerfile` (`ARG PHP_VERSION`), `composer.json` (`"php"`), `.devcontainer/devcontainer.json` (image tag).

`deploy/MIGRATION.md` is the one-off cutover guide from the old SQLite deployment. `php artisan leafmark:import-sqlite <file>` copies a legacy SQLite DB into the current DB, keeping IDs.

## Architecture

### Authentication (order of preference)

1. **Passkeys:** `laravel/passkeys` (package routes under `/passkeys/*` and `/user/passkeys*`) plus the `@laravel/passkeys` JS client. The Alpine components `passkeyLogin` and `passkeyRegister` live in `resources/js/app.js`. Managing passkeys requires the `password.confirm` middleware (`ConfirmPasswordController`).
2. **Magic link:** `MagicLinkController` + `LoginToken` (SHA-256 hashed, 15 min, single use, uniform response). Only available when SMTP is enabled. The GET from the email shows a confirm page and the POST consumes the token, so mail scanners can't burn it. The first magic-link login offers passkey setup once (`users.passkey_prompted_at`).
3. **Password + mandatory TOTP 2FA:** `LoginController` only validates the password and stores `login.id` in the session. `TwoFactorLoginController` then shows the challenge, or forces 2FA setup when none is configured. `TwoFactorService` wraps google2fa (replay-protected via cache) and the recovery codes. Admins can reset 2FA.

All logins set `last_login_at` through a `Login` event listener in `AppServiceProvider`. Settings → "Sign-in & security" (`SecuritySettingsController`, `/settings/security`) manages passkeys and 2FA.

First registered user, or the user whose email matches `ADMIN_EMAIL`, becomes admin (`RegisterController`). Registration modes (open / domain / code) come from `SystemSetting`. `ForgotPasswordController`, `ResetPasswordController` and `VerificationController` exist but are **not routed**.

### Rate limiting

Named limiters live in `AppServiceProvider::configureRateLimiting()`: `login` (password, magic link, 2FA, confirm), `register`, `external-api` (30/min and 500/day per user, applied in `BookController::middleware()` to every action that calls external book APIs or Amazon), and `expensive` (imports and exports). New endpoints that are costly or call external services need one of these.

### Data model

User → Books, Tags, ReadingChallenges, ImportHistory, BookViewPreferences, Passkeys, LoginTokens; User ↔ Family (`family_id`, family `owner_id`, so the relationship is circular). Book → BookCovers, ReadingProgressHistory; Book ↔ Tag via `book_tag`. Books have status `want_to_read` / `currently_reading` / `read` with `added_at` / `started_at` / `finished_at`. Everything is user-scoped. Family membership does **not** share books, and admins cannot see other users' books.

### External book data

Services in `app/Services/`: Google Books, Open Library, BookBrainz, Big Book API, Amazon scraper. API keys are system settings (Admin → System Settings), with a `GOOGLE_BOOKS_API_KEY` env fallback. `BookController::create()` merges multi-source search results. Covers are stored on the `public` disk under `covers/` (`CoverImageService`); fallback order is primary cover → first cover → `local_cover_path` → external URL.

### Imports and exports

- Goodreads CSV: `ImportController` + `GoodreadsImportService`, with a session-based upload → preview → execute flow.
- ZIP library backup: `LibraryTransferController` + `LibraryExportService` / `LibraryImportService`. Schema version 1, path-traversal checks, duplicate strategies `skip` / `overwrite` / `keep_both`.

Both record runs in `ImportHistory`.

### Frontend

Blade views per feature in `resources/views/`; layout `layouts/app.blade.php`. The main navigation is one `$navItems` array rendered as a top bar on desktop and a bottom tab bar on mobile. Alpine and the passkey client are bundled in `resources/js/app.js`; Chart.js only loads on the stats page (`resources/js/charts.js`). Tailwind scans `resources/views`, `resources/js`, `app` and `lang`, so dynamic class names must appear literally somewhere.

### Internationalization

English is the development language. **Every UI string goes into `lang/en/app.php` and the same keys into `de`, `fr`, `it`, `pl`, `es`.** The locale is per user (`users.preferred_language`, set by `SetUserLocale` middleware), falling back to `APP_LOCALE`. URLs carry no locale prefix, and old `/de/...` URLs 301-redirect.
