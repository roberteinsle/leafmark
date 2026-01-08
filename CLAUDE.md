# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Leafmark is a personal book tracking web application built with Laravel 11 and PHP 8.2. Users can manage their book collections, track reading progress, organize books into shelves, and import book data from external APIs (Google Books, Open Library, ISBNdb).

## Development Commands

### Docker Development
```bash
# Start all services (app + MariaDB)
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

The application has three core models with the following relationships:

**User → Books (1:many)**
- Each user owns multiple books
- Books are scoped to individual users (user-specific collections)

**User → Shelves (1:many)**
- Each user creates their own shelves
- Shelves can be default (e.g., "Want to Read", "Currently Reading", "Read") or custom

**Books ↔ Shelves (many:many)**
- Books can belong to multiple shelves
- Pivot table: `shelf_books` with `added_at` timestamp
- Managed through `Shelf` model methods: `addBook()`, `removeBook()`

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
- `api_source` field stores: 'google', 'openlibrary', or 'isbndb'
- `external_id` stores the API's identifier for the book
- API keys configured via environment variables:
  - `GOOGLE_BOOKS_API_KEY`
  - `ISBNDB_API_KEY`

**Note:** Controller implementation for API integration is not yet present in the codebase. When implementing:
- Create service classes in `app/Services/` (e.g., `GoogleBooksService.php`)
- Use the `api_source` and `external_id` fields to prevent duplicate imports
- Store thumbnails/covers in `storage/app/public` or use external URLs

### Authentication & Authorization

- Uses Laravel's built-in authentication (`Illuminate\Foundation\Auth\User`)
- Custom controllers: `LoginController`, `RegisterController`
- All book/shelf routes protected with `auth` middleware
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
- `/shelves` - Resource routes for shelf management
- `/shelves/{shelf}/books/{book}` - POST/DELETE to add/remove books from shelves

### Database Schema Key Points

**books table:**
- Indexed on `[user_id, status]` and `[user_id, added_at]` for efficient filtering
- ISBN fields (`isbn`, `isbn13`) are indexed for lookups
- Status enum enforced at database level

**shelves table:**
- `is_default` flag distinguishes system shelves from custom ones
- `sort_order` controls display ordering

**shelf_books pivot:**
- Unique constraint on `[shelf_id, book_id]` prevents duplicates
- `added_at` timestamp tracks when book was added to shelf

### Controller Patterns

**BookController** handles:
- CRUD operations for books
- `updateProgress()` - Update current page
- `updateStatus()` - Change reading status (triggers timestamp updates)

**ShelfController** (referenced in routes but file not found):
- CRUD operations for shelves
- `addBook()` - Add book to shelf
- `removeBook()` - Remove book from shelf

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
2. Waits for database to be ready
3. Runs migrations with `--force`
4. Caches configuration
5. Starts Apache

**Required environment variables:**
- `APP_KEY` - Laravel encryption key (generate with `php artisan key:generate`)
- `MYSQL_ROOT_PASSWORD` - Database password
- `APP_ENV` - Use `local` for development

**Docker Services:**
- `app` - Laravel 11 + PHP 8.2 + Apache (exposed on port 80)
- `db` - MariaDB 11 (internal)
- Database persisted to `db_data` volume

**Container Details:**
- Base image: `php:8.2-apache`
- Document root: `/var/www/html/public`
- Apache mod_rewrite enabled
- PHP extensions: pdo_mysql, mbstring, exif, pcntl, bcmath, gd, zip

## Important Notes for Development

### Missing ShelfController
The routes reference `ShelfController` but the file doesn't exist yet. When implementing:
```php
// app/Http/Controllers/ShelfController.php
namespace App\Http\Controllers;

use App\Models\Shelf;
use App\Models\Book;

class ShelfController extends Controller
{
    public function addBook(Shelf $shelf, Book $book)
    {
        $shelf->addBook($book); // Uses model method
        return redirect()->back();
    }

    public function removeBook(Shelf $shelf, Book $book)
    {
        $shelf->removeBook($book); // Uses model method
        return redirect()->back();
    }
}
```

### Model Scopes Usage
All models define query scopes - use them for cleaner queries:
```php
// Books
$user->books()->currentlyReading()->get();
$user->books()->wantToRead()->get();

// Shelves
$user->shelves()->default()->get();
$user->shelves()->custom()->ordered()->get();
```

### Database Migrations
Migration files are dated `2026_01_08_*` - new migrations will run in order based on timestamp prefix. The three core tables must maintain their cascade delete relationships.
