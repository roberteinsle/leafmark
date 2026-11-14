# Leafmark Project Context

This file provides context for Claude Code sessions.

## Project Overview

- **Name:** Leafmark
- **Type:** Personal Book Tracking Web App
- **Framework:** Laravel 13 + PHP 8.5
- **Database:** PostgreSQL 16 (tests also run on SQLite)
- **Frontend:** Blade Templates + Tailwind CSS + Alpine.js (Vite build)

## Repository

- **GitHub:** https://github.com/roberteinsle/leafmark
- **Production:** https://www.leafmark.app
- **Admin (Coolify):** https://coolify.leafmark.app

## Infrastructure

```
Development:  GitHub Codespaces
Production:   GitHub Actions → GHCR → Coolify (Hetzner VM)
CDN/DNS:      Cloudflare
Domain:       leafmark.app
```

## Key Files

| File | Purpose |
|------|---------|
| `CLAUDE.md` | Claude Code instructions and conventions |
| `CODESPACES.md` | Codespaces setup guide |
| `DEPLOY.md` | Production deployment guide |
| `docker-compose.yml` | Local dev: Postgres + production image |
| `deploy/docker-compose.coolify.yml` | What runs in Coolify (pasted by hand) |
| `.github/workflows/build.yml` | Tests, image build, push to GHCR |
| `Dockerfile` | Production container build |

## Development Workflow

1. Create/open Codespace on `main` branch
2. Make changes with Claude Code assistance
3. Test locally in Codespace
4. Merge to `main` → GitHub Action builds the image
5. Click Deploy in Coolify (or auto-deploy if `AUTO_DEPLOY=true`)

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
