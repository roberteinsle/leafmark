# Deployment Guide

Leafmark follows the pattern **GitHub → GHCR → Coolify**: GitHub Actions builds the image, GitHub Container Registry stores it, and Coolify only pulls and runs it. Coolify never builds.

```
push to main ──► GitHub Action (tests on Postgres, docker build, PHP 8.5 base image pulled fresh)
weekly rebuild ─┘        │
                         ▼
             ghcr.io/roberteinsle/leafmark:latest  (+ sha-xxxxxxx, semver tags)
                         │
                         ▼
             Coolify "Docker Compose Empty" resource ──► Deploy = docker compose pull && up -d
```


## One-time setup

1. **GHCR pull access on the Coolify server:** create a GitHub personal access token (classic) with only the `read:packages` scope, then on the server:
   ```bash
   echo '<PAT>' | docker login ghcr.io -u roberteinsle --password-stdin
   ```
2. **Postgres resource:** Project → Environment `production` → New Resource → **PostgreSQL 16** as a standalone Coolify service (not publicly available). Note its internal hostname from "Postgres URL (internal)".
3. **App resource:** New Resource → **Docker Compose Empty**. Paste [deploy/docker-compose.coolify.yml](deploy/docker-compose.coolify.yml). The app joins the external `coolify` network to reach the Postgres resource.
4. **Environment variables** in Coolify:

   | Variable | Value |
   |---|---|
   | `APP_KEY` | `php artisan key:generate --show`. **Never change it afterwards**: it encrypts users' 2FA secrets |
   | `APP_URL` | `https://www.leafmark.app` (also the passkey relying party) |
   | `DB_HOST` | internal hostname of the Postgres resource |
   | `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | credentials of the Postgres resource |
   | `ADMIN_EMAIL` | optional, auto-admin on registration |
   | `APP_LOCALE` | optional default language |
   | `APP_TAG` | leave empty (= `latest`); set for rollbacks |

5. **Domain:** on service `app`, set the domain with internal port **8080**.
   If the container restart-loops with "storage is not writable": Coolify created the `app-storage` volume empty and owned by root. Fix once:
   ```bash
   docker run --rm -u root -v <resource-uuid>_app-storage:/s --entrypoint sh ghcr.io/roberteinsle/leafmark:latest -c 'chown -R 82:82 /s'
   ```
6. **Backups:** enable daily backups on the Postgres resource. The `app-storage` volume (covers) is covered by the server backup.
7. **Optional auto-deploy:** add the repo secrets `COOLIFY_WEBHOOK` and `COOLIFY_TOKEN`, plus the repo variable `AUTO_DEPLOY=true`.

## Everyday deploy

Merge to `main` → wait for the action to turn green → click **Deploy** in Coolify. On start, the container runs migrations, `storage:link` and the caches (serversideup AUTORUN). Expect a few seconds of downtime.

After deploying, check:
- `https://www.leafmark.app/up` returns 200
- `docker exec <app-container> php -v` shows the current patched PHP 8.5

## Releases and rollback

```bash
git tag -a v1.6.0 -m "Passkeys, Postgres, PHP 8.5"
git push origin v1.6.0      # adds tags 1.6.0 and 1.6
```

Rollback: set `APP_TAG` to an older tag (`sha-a1b2c3d` or `1.5.0`) → Deploy. Clear `APP_TAG` to return to `latest`. Migrations are **not** rolled back; restore the DB backup first if the rollback crosses a migration. Never roll back to an image built before 2026-10-21 (unpatched PHP; see the build log for its PHP version).

## PHP updates

- **Critical patch:** Actions → Build & Push → Run workflow with `no_cache=true` (or `gh workflow run build.yml -f no_cache=true`), check the PHP version in the log, then Deploy.
- **Regular patch:** nothing to do; the weekly rebuild picks it up.
- **New minor version:** change `Dockerfile` (`ARG PHP_VERSION`), `composer.json` (`"php"`) and `.devcontainer/devcontainer.json` in one commit, run `composer update` and the tests, merge, tag a minor release.

## Changing the compose file

Edit `deploy/docker-compose.coolify.yml`, commit, paste the new content into Coolify, save, Deploy. This is the only manual double step.
