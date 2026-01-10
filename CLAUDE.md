# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Leafmark is a personal book tracking web application built with Laravel 11 and PHP 8.2. Users can manage their book collections, track reading progress, organize books with tags, and import book data from external APIs (Google Books, Open Library).

## Development Commands

### Docker Development
```bash
# Start app service
docker-compose up -d

# Execute commands in app container
docker-compose exec app <command>

# View logs
docker-compose logs -f app

# Stop services
docker-compose down
```

### Laravel Artisan Commands
```bash
# Run inside container with: docker-compose exec app <command>

# Generate application key
php artisan key:generate

# Database migrations
php artisan migrate              # Run migrations
php artisan migrate:fresh        # Drop all tables and re-run migrations
php artisan migrate:rollback     # Rollback last migration batch

# Clear/cache configuration
php artisan config:clear
php artisan config:cache

# Database seeding (if seeders exist)
php artisan db:seed
```

### Testing
```bash
# Run all tests
docker-compose exec app vendor/bin/phpunit

# Run specific test suite
docker-compose exec app vendor/bin/phpunit --testsuite=Feature
docker-compose exec app vendor/bin/phpunit --testsuite=Unit

# Run specific test file
docker-compose exec app vendor/bin/phpunit tests/Feature/ExampleTest.php

# Run with coverage (requires xdebug)
docker-compose exec app vendor/bin/phpunit --coverage-html coverage
```

### Composer
```bash
# Install dependencies
docker-compose exec app composer install

# Update dependencies
docker-compose exec app composer update

# Add package
docker-compose exec app composer require vendor/package
```

## Architecture

### Data Model & Relationships

The application has core models with the following relationships:

**User → Books (1:many)**
- Each user owns multiple books
- Books are scoped to individual users (user-specific collections)

**User → Tags (1:many)**
- Each user creates their own tags
- Tags are used to organize and categorize books

**Books ↔ Tags (many:many)**
- Books can have multiple tags
- Tags can be applied to multiple books
- Managed through tag relationships

### Book Status Tracking

Books have three primary statuses (enum in database):
- `want_to_read` - Book is on wishlist
- `currently_reading` - Actively reading
- `read` - Finished reading

Status changes are tracked with timestamps:
- `added_at` - When book was added to collection
- `started_at` - When status changed to `currently_reading`
- `finished_at` - When status changed to `read`

Reading progress is tracked via:
- `current_page` - Current page number
- `page_count` - Total pages in book
- Computed `reading_progress` attribute (percentage)

### External API Integration

Books can be imported from external sources:
- `api_source` field stores: 'google' or 'openlibrary'
- `external_id` stores the API's identifier for the book
- API keys configured via environment variables:
  - `GOOGLE_BOOKS_API_KEY` (optional, can also be set per user)

**Service classes:**
- `GoogleBooksService` - Google Books API integration with auto-detection
- `OpenLibraryService` - Open Library API integration (no key required)
- Both services support smart query detection (ISBN, author, title)

### Authentication & Authorization

- Uses Laravel's built-in authentication (`Illuminate\Foundation\Auth\User`)
- Custom controllers: `LoginController`, `RegisterController`
- All book/tag routes protected with `auth` middleware
- No role-based permissions (single-user scoping via relationships)

### Routing Structure

Routes are defined in [routes/web.php](routes/web.php):

**Public routes:**
- `/` - Landing page (redirects to dashboard if authenticated)

**Guest-only routes:**
- `/register`, `/login` - Authentication forms

**Protected routes (requires auth):**
- `/dashboard` - Redirects to `/books`
- `/books` - Resource routes (index, create, store, show, edit, update, destroy)
- `/books/{book}/progress` - PATCH to update reading progress
- `/books/{book}/status` - PATCH to update reading status
- `/tags` - Resource routes for tag management
- `/tags/{tag}/books/{book}` - POST/DELETE to add/remove books from tags

### Database Schema Key Points

**books table:**
- Indexed on `[user_id, status]` and `[user_id, added_at]` for efficient filtering
- ISBN fields (`isbn`, `isbn13`) are indexed for lookups
- Status enum enforced at database level
- Supports multiple covers per book

**tags table:**
- User-created tags for organizing books
- Color customization support
- Each tag belongs to a user

### Controller Patterns

**BookController** handles:
- CRUD operations for books
- `updateProgress()` - Update current page
- `updateStatus()` - Change reading status (triggers timestamp updates)
- API-based book imports from Google Books and Open Library
- Cover management (upload, delete, set primary)

**TagController** handles:
- CRUD operations for tags
- `addBook()` - Add book to tag
- `removeBook()` - Remove book from tag

When implementing controllers:
- Use route model binding: `public function show(Book $book)`
- Authorize access: `$this->authorize('view', $book)` or manual user checks
- Scope queries to authenticated user: `auth()->user()->books()`

### Views & Frontend

- Blade templates in `resources/views/`
- Layout: [resources/views/layouts/app.blade.php](resources/views/layouts/app.blade.php)
- Uses Tailwind CSS (no build process configured yet)
- No JavaScript framework - server-rendered Blade templates

## Development Environment

### GitHub Codespaces Setup

This project runs in GitHub Codespaces using Docker Compose.

**Environment Configuration:**

The Docker entrypoint ([docker-entrypoint.sh](docker-entrypoint.sh)) automatically:
1. Creates `.env` from environment variables
2. Runs migrations with `--force`
3. Caches configuration
4. Starts Apache

**Required environment variables:**
- `APP_KEY` - Laravel encryption key (generate with `php artisan key:generate`)
- `APP_ENV` - Use `local` for development
- `GOOGLE_BOOKS_API_KEY` - (Optional) Google Books API key

**Docker Services:**
- `app` - Laravel 11 + PHP 8.2 + Apache (exposed on port 8080)
- Database: SQLite file at `database/database.sqlite` (persisted to `sqlite_data` volume)

**Container Details:**
- Base image: `php:8.2-apache`
- Document root: `/var/www/html/public`
- Apache mod_rewrite enabled
- PHP extensions: pdo_mysql, mbstring, exif, pcntl, bcmath, gd, zip

## Production Deployment

### Initial Server Setup

```bash
# Create directory and clone repository
cd ~/leafmark
git clone https://github.com/roberteinsle/leafmark.git app-source
cd app-source

# Create and configure .env
cp .env.example .env
nano .env
```

**Required .env settings for production:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=  # Will be generated in next step
DB_CONNECTION=sqlite
GOOGLE_BOOKS_API_KEY=  # Optional
```

**Start the application:**
```bash
# Build and start containers
docker compose up -d

# Wait for containers to be ready
sleep 10

# Generate application key
docker compose exec app php artisan key:generate

# Run migrations
docker compose exec app php artisan migrate --force

# Create storage symlink for uploaded files (covers)
docker compose exec app php artisan storage:link

# Verify everything is running
docker compose ps
curl http://localhost:8080
```

### Update Workflow

**⚠️ IMPORTANT:** The deploy script automatically creates backups before each deployment!

**Recommended: Use the deploy script** (automatically creates backup):
```bash
cd ~/leafmark/app-source
./deploy.sh
```

The deploy script will:
1. **Automatically backup database and files** before any changes
2. Pull latest code from GitHub
3. Stop and rebuild containers (data persists in Docker volumes)
4. Run database migrations
5. Create storage symlink
6. Clear and rebuild caches
7. Keep last 10 backups automatically

**Manual update** (if you need more control):
```bash
cd ~/leafmark/app-source

# 1. ALWAYS create backup first!
./backup.sh

# 2. Pull latest code
git pull origin main

# 3. Rebuild and restart (volumes are preserved - data is NOT deleted!)
docker compose down
docker compose up -d --build

# 4. Run migrations
docker compose exec app php artisan migrate --force

# 5. Ensure storage symlink exists
docker compose exec app php artisan storage:link

# 6. Clear caches
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
```

**⚠️ CRITICAL:**
- **NEVER use `docker compose down -v`** - the `-v` flag deletes volumes and ALL DATA!
- **NEVER delete Docker volumes manually** - they contain all user data and uploaded files
- **ALWAYS use `./deploy.sh` or create backup with `./backup.sh` before updates**
- Data persists in Docker volumes across container rebuilds

### Data Persistence

All data is persisted in Docker volumes:

- **`sqlite_data`** - SQLite database at `/var/www/html/database/database.sqlite`
- **`storage_data`** - Uploaded files (covers) at `/var/www/html/storage/app`
- **`vendor`** - Composer dependencies

These volumes persist across container restarts and rebuilds.

### Backup & Restore

**⚠️ Automated Backups:**
- The `deploy.sh` script **automatically creates backups** before each deployment
- Backups are stored in `~/leafmark/backups/`
- Last 10 backups are kept automatically

**Manual Backup:**
```bash
cd ~/leafmark/app-source
./backup.sh
```

This creates:
- `db-backup-YYYYMMDD_HHMMSS.tar.gz` - Database backup
- `storage-backup-YYYYMMDD_HHMMSS.tar.gz` - Uploaded files backup

**List Available Backups:**
```bash
cd ~/leafmark/app-source
./restore.sh
```

**Restore from Backup:**
```bash
cd ~/leafmark/app-source
./restore.sh YYYYMMDD_HHMMSS
```

Example:
```bash
./restore.sh 20260110_120000
```

**⚠️ WARNING:** Restore will replace current data with backup data!

**Manual Backup (advanced):**
```bash
BACKUP_DIR=~/leafmark/backups
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# Backup database
docker run --rm -v app-source_sqlite_data:/data -v $BACKUP_DIR:/backup alpine \
  tar czf /backup/db-backup-${TIMESTAMP}.tar.gz -C /data .

# Backup uploaded files
docker run --rm -v app-source_storage_data:/data -v $BACKUP_DIR:/backup alpine \
  tar czf /backup/storage-backup-${TIMESTAMP}.tar.gz -C /data .
```

## Important Notes for Development

### Model Scopes Usage
All models define query scopes - use them for cleaner queries:
```php
// Books
$user->books()->currentlyReading()->get();
$user->books()->wantToRead()->get();

// Tags
$user->tags()->get();
$tag->books()->get(); // Get all books with a specific tag
```

### Database Migrations
Migration files are dated `2026_01_08_*` - new migrations will run in order based on timestamp prefix. Core tables must maintain their cascade delete relationships.
