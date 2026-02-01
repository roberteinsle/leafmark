# Leafmark Project Context

This file provides context for Claude Code sessions.

## Project Overview

- **Name:** Leafmark
- **Type:** Personal Book Tracking Web App
- **Framework:** Laravel 11 + PHP 8.2
- **Database:** SQLite (development), MariaDB/MySQL (optional for production)
- **Frontend:** Blade Templates + Tailwind CSS

## Repository

- **GitHub:** https://github.com/roberteinsle/leafmark
- **Production:** https://www.leafmark.app
- **Admin (Coolify):** https://coolify.leafmark.app

## Infrastructure

```
Development:  GitHub Codespaces
Production:   Hetzner VM → Coolify → Docker
CDN/DNS:      Cloudflare
Domain:       leafmark.app
```

## Key Files

| File | Purpose |
|------|---------|
| `CLAUDE.md` | Claude Code instructions and conventions |
| `CODESPACES.md` | Codespaces setup guide |
| `DEPLOY.md` | Production deployment guide |
| `docker-compose.yaml` | Local Docker setup |
| `Dockerfile` | Production container build |

## Development Workflow

1. Create/open Codespace on `main` branch
2. Make changes with Claude Code assistance
3. Test locally in Codespace
4. Commit and push to `main`
5. Coolify auto-deploys to production

## Common Tasks

### Start Development Server
```bash
php artisan serve
```

### Run Tests
```bash
php artisan test
```

### Run Migrations
```bash
php artisan migrate
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
```

## Current Focus Areas

- UI/UX improvements
- Book search integration
- Performance optimization
- Multi-language support

## Owner

Robert Einsle (Einsle Web Services)
