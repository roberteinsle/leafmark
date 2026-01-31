# GitHub Codespaces Setup

This guide explains how to develop Leafmark using GitHub Codespaces.

## Quick Start

1. Go to https://github.com/roberteinsle/leafmark
2. Click **Code** → **Codespaces** → **Create codespace on main**
3. Wait for the environment to build (~2-3 minutes)
4. The setup script automatically:
   - Installs Composer dependencies
   - Creates `.env` file from `.env.example`
   - Generates application key
   - Waits for MariaDB to be ready
   - Runs database migrations
   - Creates storage symlink
   - Installs Claude Code CLI

## Architecture

The Codespace uses Docker Compose with two services:

```
┌─────────────────────────────────────┐
│  GitHub Codespaces Container        │
│                                     │
│  ┌──────────────┐  ┌─────────────┐ │
│  │  App Service │  │  MariaDB 11 │ │
│  │  PHP 8.2     │──│  Database   │ │
│  │  Laravel 11  │  │             │ │
│  └──────────────┘  └─────────────┘ │
│                                     │
│  Port 8000 (App)                    │
│  Port 3306 (Database)               │
└─────────────────────────────────────┘
```

**Database:** MariaDB 11 (same as production for consistency)

## Development Workflow

### Starting the Development Server

The app doesn't start automatically. Start it manually:

```bash
php artisan serve
```

The app will be available at the forwarded port 8000.

### Running Tests

```bash
php artisan test
```

### Database Management

```bash
# Run migrations
php artisan migrate

# Fresh migrations (drops all tables)
php artisan migrate:fresh

# Rollback last migration
php artisan migrate:rollback

# Open database CLI
mysql -h db -u leafmark -pleafmark_password leafmark
```

### Clearing Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Using Claude Code

Claude Code CLI is pre-installed:

```bash
claude
```

See [CLAUDE.md](CLAUDE.md) for project-specific context and conventions.

## Environment Variables

The `.env` file is automatically created from `.env.example` on first setup:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=leafmark
DB_USERNAME=leafmark
DB_PASSWORD=leafmark_password
```

**Important:** The database credentials match the MariaDB service in `.devcontainer/docker-compose.yml`.

## Database Parity

Codespaces uses **MariaDB 11**, matching the production database exactly:
- Same SQL dialect
- Same foreign key behavior
- Same date/time handling
- Migrations work identically

This prevents "works in dev, breaks in production" issues.

## Troubleshooting

### Database Connection Failed

If you see database connection errors:

1. Check if MariaDB is running:
   ```bash
   docker ps
   ```

2. Wait for database to be ready:
   ```bash
   until mysql -h db -u leafmark -pleafmark_password -e "SELECT 1" > /dev/null 2>&1; do
       echo "Waiting for database..."
       sleep 2
   done
   ```

3. Run migrations:
   ```bash
   php artisan migrate
   ```

### Port Already in Use

If port 8000 is in use:

```bash
# Use a different port
php artisan serve --port=8001
```

### Composer Install Fails

```bash
# Clear Composer cache and retry
composer clear-cache
composer install
```

### Reset Everything

To start fresh:

1. Stop the Codespace
2. Delete the Codespace
3. Create a new Codespace

Or reset the database:

```bash
php artisan migrate:fresh
```

## File Watching

Codespaces supports file watching for automatic reloads:

```bash
# Watch for file changes (requires npm run dev)
npm run dev
```

## Extensions

The following VS Code extensions are pre-installed:

- **PHP Intelephense** - PHP IntelliSense
- **Laravel Blade Snippets** - Blade template support
- **Tailwind CSS IntelliSense** - Tailwind autocomplete
- **DotENV** - .env file syntax highlighting

## Ports

Forwarded ports:

| Port | Service | Access |
|------|---------|--------|
| 8000 | Laravel App | Public (with notification) |
| 3306 | MariaDB | Private |

## Persistence

**Important:** Data in Codespaces persists as long as the Codespace exists:

- Database data is stored in a Docker volume
- Uploaded files are stored in `storage/app`
- When you stop the Codespace, data persists
- When you **delete** the Codespace, all data is lost

**Recommendation:** Don't use Codespaces for long-term data storage. Use it for development only.

## Performance

Codespaces provides:
- 2-core CPU
- 4 GB RAM
- 32 GB storage

This is sufficient for development. For better performance:
- Use 4-core machine type (Settings → Machine type)
- Close unused Codespaces to free resources

## Security

**Never commit secrets:**
- `.env` is in `.gitignore`
- Use `.env.example` for templates
- Store production secrets in Coolify environment variables

## Comparison: Codespaces vs Local Docker

| Aspect | Codespaces | Local Docker |
|--------|-----------|--------------|
| Setup Time | ~2 min | ~5 min |
| Consistency | ✅ Always same | Depends on host |
| Portability | ✅ Access anywhere | Only on local machine |
| Performance | Good | Better (native) |
| Cost | Free tier available | Free (uses local resources) |

## Cost

GitHub provides free Codespaces hours per month:
- **Personal accounts:** 120 core-hours/month
- **Pro accounts:** 180 core-hours/month

A 2-core Codespace = 2 core-hours per hour of use.

**Tip:** Stop Codespaces when not in use to conserve hours.

## Next Steps

- Read [CLAUDE.md](CLAUDE.md) for project architecture and conventions
- See [DEPLOY.md](DEPLOY.md) for production deployment
- Check [README.md](README.md) for feature overview

---

**Happy coding!** 🚀
