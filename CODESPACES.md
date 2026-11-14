# GitHub Codespaces Setup

The devcontainer (`.devcontainer/devcontainer.json`) uses the same PHP version as production (8.5), plus Node 22 and Docker-in-Docker. Postgres runs as a container from `docker-compose.yml`.

## Quick Start

1. **Code** → **Codespaces** → **Create codespace on main**
2. `.devcontainer/setup.sh` runs automatically: it installs the PHP extensions (pdo_pgsql, zip, intl, gd), runs `composer install` and `npm ci`, creates `.env`, starts Postgres and runs the migrations.
3. Start the app:
   ```bash
   php artisan serve      # port 8000
   npm run dev            # Vite dev server (port 5173), in a second terminal
   ```

Postgres is started again on every codespace start (`postStartCommand`).

## Everyday commands

```bash
php artisan test                          # tests (SQLite in-memory, no Postgres needed)
php artisan migrate                       # migrations against the Postgres container
php artisan migrate:fresh                 # drop everything and re-migrate
docker compose exec db psql -U leafmark   # database shell
docker compose up -d --build              # run the production image locally on :8080
```

## Database

| Setting | Value |
|---|---|
| Host | `127.0.0.1` |
| Port | `5432` |
| Database | `leafmark` |
| User / password | `leafmark` / `leafmark` |

Data persists in the `db-data` Docker volume until the codespace is deleted. Reset it with `docker compose down -v && docker compose up -d db && php artisan migrate`.

## Passkeys in Codespaces

WebAuthn requires the page origin to match `APP_URL`. Set `APP_URL` in `.env` to the forwarded URL of port 8000 (`https://<codespace>-8000.app.github.dev`) to test passkeys there.

## Email

`MAIL_MAILER=log` writes mails to `storage/logs/laravel.log`. Magic links only appear on the login page when SMTP is enabled under Admin → System Settings.

## Cost

The free plan includes 120 core hours per month (60 h on a 2-core machine). Recommended settings: idle timeout 30 min, retention 7 days (GitHub → Settings → Codespaces).
