# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Leafmark is a personal book tracking web application built with Laravel 11 and PHP 8.2. Users can manage their book collections, track reading progress, organize books with tags, import book data from external APIs (Google Books, Open Library, Amazon, BookBrainz), and set yearly reading goals.

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
php artisan route:cache
php artisan route:clear
php artisan view:clear

# Create storage symlink (for uploaded book covers)
php artisan storage:link

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

**User → ReadingChallenges (1:many)**
- Each user can set yearly reading goals
- Challenges track books finished within the year

**Books ↔ Tags (many:many)**
- Books can have multiple tags
- Tags can be applied to multiple books
- Managed through `book_tag` pivot table

**Book → BookCovers (1:many)**
- Books can have multiple uploaded covers
- One cover can be marked as primary
- Covers are ordered by `is_primary`, `sort_order`, and `id`

**Book → ReadingProgressHistory (1:many)**
- Tracks historical page progress over time
- Allows users to see their reading progress graph

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
- `ReadingProgressHistory` model tracks historical progress

### External API Integration

Books can be imported from external sources via Service classes:

**Service classes:**
- `GoogleBooksService` - Google Books API integration with auto-detection of ISBN/author/title
- `OpenLibraryService` - Open Library API integration (no key required)
- `AmazonProductService` - Amazon Product Advertising API (requires access key, secret key, associate tag)
- `BookBrainzService` - BookBrainz API for additional metadata
- `CoverImageService` - Handles cover image uploads and management
- `LanguageService` - Language code conversions and display names

**API Configuration:**
- `api_source` field stores: 'google', 'openlibrary', 'amazon', or 'bookbrainz'
- `external_id` stores the API's identifier for the book
- Edition identifiers: `openlibrary_edition_id`, `goodreads_id`, `librarything_id`
- API keys configured per user in settings (google_books_api_key, amazon credentials)

**Search Features:**
- Smart query detection automatically identifies ISBN, author names, or titles
- Multi-source search merges results from multiple APIs
- Language-aware search with fallback to language-neutral results

### Authentication & Authorization

- Uses Laravel's built-in authentication (`Illuminate\Foundation\Auth\User`)
- Custom controllers: `LoginController`, `RegisterController`
- All book/tag routes protected with `auth` middleware
- No role-based permissions (single-user scoping via relationships)
- Authorization through relationship checks: books/tags must belong to authenticated user

### Internationalization

The application supports multiple languages:
- Supported languages: English (en), German (de), French (fr), Italian (it), Spanish (es), Polish (pl)
- Language files in `lang/{locale}/app.php`
- Users can set `preferred_language` in their profile
- `SetUserLocale` middleware automatically sets locale based on user preference
- `LanguageService` provides language name display and code conversion

### Routing Structure

Routes are defined in [routes/web.php](routes/web.php):

**Public routes:**
- `/` - Landing page (redirects to dashboard if authenticated)

**Guest-only routes:**
- `/register`, `/login` - Authentication forms

**Protected routes (requires auth):**
- `/dashboard` - Redirects to `/books`
- `/books` - Resource routes (index, create, store, show, edit, update, destroy)
- `/books/store-from-api` - Store book imported from external API
- `/books/bulk-delete` - Delete multiple books at once
- `/books/{book}/progress` - PATCH to update reading progress
- `/books/{book}/status` - PATCH to update reading status
- `/books/{book}/rating` - PATCH to update book rating
- `/books/{book}/covers` - POST to upload new cover
- `/books/{book}/covers/{cover}` - DELETE to remove cover
- `/books/{book}/covers/{cover}/primary` - PATCH to set primary cover
- `/series/{series}` - View all books in a series
- `/tags` - Resource routes for tag management
- `/tags/{tag}/books/{book}` - POST/DELETE to add/remove books from tags
- `/settings` - GET/PATCH for user settings
- `/challenge` - Reading challenge routes (index, store, update, destroy)

**Important Routing Details:**
- Book routes use **Unix timestamp** from `added_at` as route key (not numeric ID)
- Numeric constraints (`where(['book' => '[0-9]+']`) ensure proper route matching
- Cover routes MUST come before destroy route to avoid conflicts
- Resource routes must come AFTER specific routes

### Database Schema Key Points

**books table:**
- Indexed on `[user_id, status]` and `[user_id, added_at]` for efficient filtering
- ISBN fields (`isbn`, `isbn13`) are indexed for lookups
- Status enum enforced at database level
- Supports series tracking (`series`, `series_position`)
- Rating and review fields for user feedback
- Purchase tracking (`purchase_date`, `purchase_price`, `purchase_currency`, `format`)
- External API identifiers for book matching

**tags table:**
- User-created tags for organizing books
- Color customization support (`color` field)
- Each tag belongs to a user
- `is_default` and `sort_order` for organization

**book_covers table:**
- Multiple covers per book
- `is_primary` flag to mark default cover
- `sort_order` for custom ordering
- Stored in `storage/app` directory

**reading_progress_history table:**
- Historical snapshot of reading progress
- Tracks `current_page` at different `recorded_at` timestamps
- Enables progress graphs and tracking

**reading_challenges table:**
- Yearly reading goals per user
- `year` and `goal` (number of books to read)
- Progress calculated from books with `status='read'` and matching `finished_at` year

### Controller Patterns

**BookController** handles:
- CRUD operations for books
- `updateProgress()` - Update current page, creates history entry
- `updateStatus()` - Change reading status (triggers timestamp updates)
- `updateRating()` - Update book rating and review
- `storeFromApi()` - Import book from external API search
- `bulkDelete()` - Delete multiple books
- `showSeries()` - Display all books in a series
- Cover management: `uploadCover()`, `deleteCover()`, `deleteSingleCover()`, `setPrimaryCover()`
- Multi-source API search integration

**TagController** handles:
- CRUD operations for tags
- `addBook()` - Add book to tag
- `removeBook()` - Remove book from tag

**ReadingChallengeController** handles:
- CRUD operations for yearly reading challenges
- Automatic progress calculation based on finished books

**UserSettingsController** handles:
- User profile updates (name, email, password)
- Language preference
- API keys (Google Books, Amazon credentials)

When implementing controllers:
- Use route model binding: `public function show(Book $book)`
- Use custom route binding for books (timestamp-based, see `Book::resolveRouteBinding()`)
- Authorize access: `$this->authorize('view', $book)` or manual user checks
- Scope queries to authenticated user: `auth()->user()->books()`
- Return validation errors with appropriate messages

### Views & Frontend

- Blade templates in `resources/views/`
- Layout: [resources/views/layouts/app.blade.php](resources/views/layouts/app.blade.php)
- Uses Tailwind CSS (no build process configured yet)
- No JavaScript framework - server-rendered Blade templates
- Views are organized by feature: `books/`, `tags/`, `auth/`, `settings/`, `challenge/`

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
$user->books()->read()->get();

// Tags
$user->tags()->ordered()->get();
$user->tags()->default()->get();
$user->tags()->custom()->get();
$tag->books()->get(); // Get all books with a specific tag

// Book covers
$book->covers()->primary()->first();
$book->covers()->ordered()->get();
```

### Book Route Key Binding

Books use a **custom route key binding** based on Unix timestamps:

```php
// In Book model
public function getRouteKey() {
    return $this->added_at ? $this->added_at->timestamp : $this->id;
}

public function resolveRouteBinding($value, $field = null) {
    // Resolves by timestamp OR falls back to ID for backwards compatibility
    // Always scoped to current user for security
}
```

This means book URLs use timestamps instead of sequential IDs, making them harder to enumerate.

### Database Migrations

Migration files are dated `2026_01_08_*` onwards - new migrations will run in order based on timestamp prefix. Core tables must maintain their cascade delete relationships:

- Deleting a user cascades to books, tags, reading challenges
- Deleting a book cascades to book covers, reading progress history
- Book-tag relationships cascade on both sides

### Cover Image Management

Cover images are managed through the `CoverImageService`:

- Uploaded covers stored in `storage/app/book-covers/{user_id}/{book_id}/`
- Multiple covers supported per book
- Primary cover designated with `is_primary` flag
- Automatic fallback: primary cover → first cover → legacy local_cover_path → external URL
- Cover deletion removes file from storage

### API Service Integration

When adding new book import sources:

1. Create service class in `app/Services/`
2. Implement search methods with consistent return format
3. Support smart query detection (ISBN, author, title)
4. Handle API errors gracefully with logging
5. Update `BookController::create()` to include new service
6. Add language support if applicable
