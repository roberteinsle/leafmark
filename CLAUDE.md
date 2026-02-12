# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Leafmark is a **multi-user** book tracking web application built with Laravel 11 and PHP 8.2. Users can manage their book collections, track reading progress, view reading statistics, organize books with tags, import book data from external APIs (Google Books, Open Library, BookBrainz, Big Book API), Amazon scraping, CSV files (Goodreads), or ZIP library backups, and set yearly reading goals.

The application supports multiple users with individual book collections, admin-controlled registration, and flexible user management. Perfect for organizations, book clubs, families, or communities.

**Version:** Defined in `config/app.php` as `app.version`, displayed in the footer of every page.

**⚠️ Critical Pattern:** Books use Unix timestamps (from `added_at`) as route keys instead of sequential IDs. See [Book Route Key Binding](#book-route-key-binding) for details.

## Infrastructure & Deployment

### Production Setup

```
User → Cloudflare (CDN/SSL) → Hetzner VM → Coolify/Traefik → Leafmark Container
```

**Components:**
- **Hosting:** Hetzner VM
- **Deployment Platform:** Coolify (self-hosted PaaS)
- **Production URL:** https://www.leafmark.app
- **Admin Panel:** https://coolify.leafmark.app
- **CDN/DNS:** Cloudflare (proxied, orange cloud)
- **Database:** SQLite (file-based, included in container)

### Development Environment

**Primary:** GitHub Codespaces
- Preconfigured dev environment
- Auto-starts services on port 8000
- SQLite database (file-based)
- See CODESPACES.md for details

**Local:** Standard Laravel Development
- SQLite database (no Docker required)
- `php artisan serve` for local development
- `database/database.sqlite` for data storage

### Deployment Workflow

**Automatic deployments via Coolify:**
1. Push to `main` branch on GitHub
2. Coolify webhook triggers automatically
3. Docker image builds from Dockerfile
4. Migrations run via docker-entrypoint.sh
5. Container deployed with zero-downtime

**Manual deployment commands** (if needed):
```bash
# In Coolify terminal or SSH into container
docker exec -it <container-id> php artisan migrate --force
docker exec -it <container-id> php artisan config:cache
docker exec -it <container-id> php artisan route:cache
```

See [DEPLOY.md](DEPLOY.md) for detailed deployment instructions.

## Development Commands

### GitHub Codespaces

Services start automatically. Access the app at the forwarded port 8000.

```bash
# Development server (if needed manually)
php artisan serve

# Run tests
php artisan test

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Local Development (SQLite)

```bash
# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Start development server
php artisan serve

# Access at http://localhost:8000
```

### Laravel Artisan Commands

```bash
# Run directly in terminal (Codespaces or local)

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
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run specific test method
php artisan test --filter=test_example

# Run with coverage (requires xdebug)
php artisan test --coverage-html coverage
```

### Composer

```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Add package
composer require vendor/package
```

## Architecture

### Data Model & Relationships

The application has core models with the following relationships:

**User → Books (1:many)**
- Each user owns multiple books
- Books are scoped to individual users (user-specific collections)
- Each user has their own independent book collection

**User → Tags (1:many)**
- Each user creates their own tags
- Tags are used to organize and categorize books
- Tags are user-specific

**User → ReadingChallenges (1:many)**
- Each user can set yearly reading goals
- Challenges track books finished within the year
- Multiple challenges per user (one per year)

**User → Family (many:1)**
- Users can belong to a family group
- Family accounts allow shared membership (not shared books)
- Each family has one owner who manages the family

**Family → Users (1:many)**
- Family owners can see all family members
- Each family has a unique join code for new members
- Family membership is separate from book collections

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

### Multi-User Architecture

**User Model Extensions:**
- `is_admin` field - Boolean flag for admin privileges
- Admin users can access `/admin` routes
- Regular users can only access their own data

**Auto-Admin Assignment:**
- The **first user to register automatically becomes an admin**
- Users with email matching `ADMIN_EMAIL` env var automatically become admins
- No seeder required
- Implemented in RegisterController.php
- `ADMIN_EMAIL` should be set in Coolify environment variables for production

**System Settings:**
- `SystemSetting` model stores application-wide configuration
- Settings stored as key-value pairs in `system_settings` table
- Used for registration control and system configuration

**Family Accounts:**
- `Family` model for grouping users
- Each family has a `name`, `join_code`, and `owner_id`
- Join codes are 8-character uppercase random strings
- Users can create or join one family
- Family owners can regenerate join codes
- Membership tracked via `family_id` on users table

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
- `BookBrainzService` - BookBrainz API for additional metadata
- `BigBookApiService` - Big Book API integration with comprehensive book data (no language filtering supported)
- `AmazonScraperService` - Scrapes book data from Amazon URLs (DE/COM)
- `LibraryExportService` - Creates ZIP archive backups of entire library
- `LibraryImportService` - Imports ZIP archive backups with validation and duplicate handling
- `CoverImageService` - Handles cover image uploads and management
- `LanguageService` - Language code conversions and display names

**API Configuration:**
- `api_source` field stores: 'google', 'openlibrary', 'bookbrainz', or 'bigbook'
- `external_id` stores the API's identifier for the book
- Edition identifiers: `openlibrary_edition_id`, `goodreads_id`, `librarything_id`
- API keys configured system-wide in Admin → System Settings:
  - `google_books_api_key` - Google Books API key (optional)
  - `bigbook_api_key` - Big Book API key (optional, free tier: 60 req/min)

**Search Features:**
- Smart query detection automatically identifies ISBN, author names, or titles
- Multi-source search merges results from multiple APIs
- Language-aware search with fallback to language-neutral results (Google Books, Open Library, BookBrainz)
- Note: Big Book API does not support language filtering

### Amazon Book Scraping

Books can be imported by pasting an Amazon product URL:

**AmazonScraperService** provides:
- HTML scraping from Amazon.de and Amazon.com URLs
- Extracts: title, author, ISBN-10/13, publisher, publication date, page count, language, description, series info, cover image
- Multiple fallback patterns for each field (meta tags, HTML elements)
- Automatic cover image download and local storage as `BookCover`
- Sets imported books to `want_to_read` status
- 15-second HTTP timeout with minimal headers

**Route:** `POST /books/scrape-amazon` (name: `books.scrape-amazon`)

### Library Export/Import (ZIP Archive)

Users can export their entire library as a ZIP archive and import it on another instance:

**LibraryTransferController** handles:
- `export()` - Download ZIP archive of entire library
- `showImportForm()` - Display import form
- `upload()` - Upload and validate ZIP, show preview
- `execute()` - Process import with duplicate handling strategy
- `cancel()` - Cancel pending import
- `result()` - View import results

**LibraryExportService** creates ZIP archives containing:
- All books with full metadata (JSON)
- Book cover images (organized by book)
- Tags with color and ordering
- Reading progress history
- Reading challenges
- User metadata and schema version

**LibraryImportService** provides:
- ZIP validation with security checks (path traversal prevention, 50MB limit)
- Schema version validation (version 1)
- Preview before import (book count, tag count, etc.)
- Three duplicate handling strategies: `skip`, `overwrite`, `keep_both`
- Session-based workflow (upload → preview → execute)
- Import tracking via `ImportHistory` model

**Routes:**
- `GET /library/export` - Download ZIP
- `GET /library/import` - Show import form
- `POST /library/import/upload` - Upload and preview ZIP
- `POST /library/import/execute` - Execute import
- `POST /library/import/cancel` - Cancel import
- `GET /library/import/result/{importHistory}` - View results

### CSV Import System

Books can be imported from Goodreads CSV exports:

**ImportController** handles:
- `index()` - Display import interface and instructions
- `upload()` - Upload and parse CSV file, show preview
- `execute()` - Process the import with selected options
- `cancel()` - Cancel pending import
- `history()` - View past import history
- `result()` - View detailed results of a specific import

**GoodreadsImportService** provides:
- CSV parsing with header normalization (case-insensitive)
- Preview of first 100 rows before import
- Mapping of Goodreads fields to Book model
- Duplicate detection based on ISBN/title/author
- Automatic tag creation from Goodreads shelves
- Optional "imported from Goodreads" tag

**ImportHistory model:**
- Tracks all import operations per user
- Records success/skip/failure counts
- Stores error details for troubleshooting
- Statuses: pending, processing, completed, failed

**Import workflow:**
1. User uploads Goodreads CSV export
2. System parses and shows preview with statistics
3. User selects options (skip duplicates, create import tag)
4. System processes each row, creating books and tags
5. Results stored in ImportHistory for review

### Book View Preferences

Users can customize their book list view per status/shelf:

**BookViewPreference model:**
- Per-user, per-shelf view settings
- `view_mode` - 'grid' or 'table'
- `visible_columns` - JSON array of visible table columns
- `per_page` - Items per page (10, 25, 50, 100)

**Available columns:**
- Core: cover, title, author, series, tags
- Progress: status, current_page, rating
- Metadata: publisher, published_date, language, page_count
- Purchase: format, purchase_date, purchase_price
- Timestamps: added_at, started_at, finished_at

View preferences persist across sessions and are shelf-specific (e.g., different columns for "Currently Reading" vs "Read").

### Reading Statistics

The statistics page provides comprehensive reading analytics:

**StatsController** (`App\Http\Controllers\StatsController`) handles:
- `index()` - Display statistics dashboard with year selector

**Statistics displayed:**
- **Overview:** Total books read, total pages read, average rating, currently reading count
- **Yearly breakdown:** Books and pages per selected year, books per month chart (Chart.js)
- **Comparison:** Current year vs previous year (books and pages)
- **Time metrics:** Average days per book, average pages per day, best/worst reading month
- **Book extremes:** Longest and shortest book (with details)
- **Content analysis:** Top 10 languages, format distribution, top 10 authors
- **Challenge integration:** Progress bar if a reading challenge exists for the selected year

**Route:** `GET /stats` (name: `stats.index`)

**Technical notes:**
- Uses SQLite `strftime()` for date grouping
- Year selector auto-populated from years with finished books
- Empty state shown when no books have been finished

### Authentication & Authorization

**Basic Authentication:**
- Uses Laravel's built-in authentication (`Illuminate\Foundation\Auth\User`)
- Custom controllers: `LoginController`, `RegisterController`
- All book/tag routes protected with `auth` middleware
- Authorization through relationship checks: books/tags must belong to authenticated user
- Users are automatically logged in after successful registration

**Admin System:**
- `IsAdmin` middleware protects admin routes
- Admin routes require both `auth` and `admin` middleware
- Admin users have `is_admin = true` in database
- Admin middleware registered as `'admin' => \App\Http\Middleware\IsAdmin::class`

**Password Reset:**
- `ForgotPasswordController` - Shows form, sends reset link email
- `ResetPasswordController` - Shows reset form, processes password change
- Token stored in `password_reset_tokens` table (60-minute expiration)
- Sends `PasswordResetMail` via SMTP (if enabled)
- Optional Turnstile CAPTCHA protection

**Email Verification:**
- `VerificationController` - Handles verification link clicks and resend
- Uses signed URLs with SHA1 email hash
- 60-minute token expiration
- Sends `VerifyEmailMail` via SMTP (if enabled)
- Optional Turnstile CAPTCHA on resend

**Registration Control:**
- `RegisterController` checks `SystemSetting` for registration rules
- Three registration modes: open, domain, code
- Registration can be completely disabled via admin settings

**Cloudflare Turnstile CAPTCHA:**
- Optional bot protection on password reset and email verification forms
- Configured via Admin > System Settings (`turnstile_enabled`, `turnstile_site_key`, `turnstile_secret_key`)
- `TurnstileValid` validation rule validates against Cloudflare API
- Gracefully skips validation if not configured

**SMTP Email Configuration:**
- Dynamic SMTP configuration stored in `system_settings` table (not `.env`)
- Settings: `smtp_enabled`, `smtp_host`, `smtp_port`, `smtp_encryption`, `smtp_username`, `smtp_password`, `smtp_from_address`, `smtp_from_name`
- `DynamicMailConfigServiceProvider` loads SMTP config from database at runtime
- Mail classes: `PasswordResetMail`, `VerifyEmailMail`, `TestEmail`
- Helper methods: `SystemSetting::isSmtpEnabled()`, `SystemSetting::getSmtpConfig()`

### Internationalization

The application supports multiple languages through Laravel's translation system.

**Development Language:**
- **Primary development language: English (en)**
- All new features, UI text, and documentation should be written in English first
- English is the default language (`APP_LOCALE=en` in .env)

**Supported Languages:**
- English (en) - Default and development language
- German (de)
- French (fr)
- Italian (it)
- Polish (pl)
- Spanish (es)

**Important for Development:**
- **Always consider translations when developing new features**
- When adding new UI text, add translation keys to `lang/en/app.php` first
- Structure translation keys logically in nested arrays (e.g., `app.books.title`, `app.settings.email`)
- Use the `__()` helper in Blade templates: `{{ __('app.books.title') }}`
- Use the `__()` helper in controllers: `return redirect()->back()->with('success', __('app.books.book_added'));`

**Language Configuration:**
- Application language set via `APP_LOCALE` in `.env` file
- No URL prefixes (e.g., `/de/` or `/en/`)
- Single language per deployment
- Default: English (`en`)

**Translation Files:**
- All translations stored in `lang/{locale}/app.php`
- Organized in logical sections: nav, books, settings, admin, etc.
- Each language file returns an array with nested translation keys
- `LanguageService` provides language code conversions and display names

**Translation Key Structure:**
```php
// lang/en/app.php
return [
    'nav' => [
        'books' => 'Books',
        'tags' => 'Tags',
    ],
    'books' => [
        'title' => 'My Books',
        'add_new' => 'Add a New Book',
        'delete_confirm' => 'Are you sure you want to delete this book?',
    ],
];
```

**Using Translations in Code:**
```php
// In Blade templates
{{ __('app.books.title') }}
{{ __('app.books.delete_confirm') }}

// In controllers
return redirect()->back()->with('success', __('app.books.book_added'));

// With parameters
{{ __('app.books.refreshed_fields', ['count' => 5]) }}
```

### Routing Structure

Routes are defined in [routes/web.php](routes/web.php):

**Public routes:**
- `/` - Landing page (redirects to dashboard if authenticated)
- `/changelog` - Changelog page

**Guest-only routes:**
- `/register`, `/login` - Authentication forms

**Protected routes (requires auth):**
- `/books` - Resource routes (index, create, store, show, edit, update, destroy)
- `/books/scrape-amazon` - POST to import book from Amazon URL
- `/books/store-from-api` - Store book imported from external API
- `/books/bulk-delete` - Delete multiple books at once
- `/books/bulk-add-tags` - Add tags to multiple books
- `/books/bulk-remove-tag` - Remove tag from multiple books
- `/books/{book}/progress` - PATCH to update reading progress
- `/books/{book}/status` - PATCH to update reading status
- `/books/{book}/rating` - PATCH to update book rating
- `/books/{book}/progress/{entry}` - DELETE to remove progress entry
- `/books/{book}/fetch-api-data` - GET to fetch API data
- `/books/{book}/refresh-from-api` - POST to refresh book from API
- `/books/{book}/covers` - POST to upload new cover
- `/books/{book}/covers/{cover}` - DELETE to remove cover
- `/books/{book}/covers/{cover}/primary` - PATCH to set primary cover
- `/series/{series}` - View all books in a series
- `/books/toggle-view-mode` - POST to toggle grid/table view
- `/books/update-column-settings` - POST to update visible columns
- `/tags` - Resource routes for tag management
- `/tags/{tag}/books/{book}` - POST/DELETE to add/remove books from tags
- `/settings` - GET/PATCH for user settings
- `/challenge` - Reading challenge routes (index, store, update, destroy)
- `/family` - Family management routes
- `/import` - CSV import routes (Goodreads)
- `/library/export` - GET to download library ZIP archive
- `/library/import` - Library ZIP import routes (upload, execute, cancel, result)
- `/stats` - GET reading statistics dashboard

**Admin routes (requires auth + admin):**
- `/admin` - Admin dashboard with user statistics
- `/admin/users` - User management (list, toggle admin, delete)
- `/admin/users/{user}` - Edit, update, delete user
- `/admin/users/{user}/toggle-admin` - PATCH to grant/revoke admin privileges
- `/admin/settings` - System settings (GET/PATCH)

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

**users table (admin fields):**
- `is_admin` - Boolean flag for admin privileges (default: false)
- `preferred_language` - User's preferred interface language
- `email_verified_at` - Email verification timestamp
- Admins can access `/admin` routes and manage users
- First user to register automatically becomes admin
- Users with email matching ADMIN_EMAIL in .env automatically become admins

**system_settings table:**
- Key-value storage for application configuration
- Stores registration, API, SMTP, and Turnstile settings
- Registration keys: `registration_enabled`, `registration_mode`, `allowed_email_domains`, `registration_code`
- API keys: `google_books_api_key`, `bigbook_api_key`
- SMTP keys: `smtp_enabled`, `smtp_host`, `smtp_port`, `smtp_encryption`, `smtp_username`, `smtp_password`, `smtp_from_address`, `smtp_from_name`
- Turnstile keys: `turnstile_enabled`, `turnstile_site_key`, `turnstile_secret_key`
- No caching in model (direct database queries)

**families table:**
- Family grouping for users
- `name` - Family name
- `join_code` - Unique 8-character uppercase code for joining
- `owner_id` - Foreign key to users table (family owner)
- Users link to families via `family_id` on users table
- Cascade delete: removing family sets users' `family_id` to null

**import_history table:**
- Tracks CSV import operations per user
- `source` - Import source (e.g., 'goodreads')
- `filename` - Original uploaded filename
- `total_rows`, `imported_count`, `skipped_count`, `failed_count` - Statistics
- `errors` - JSON array of error messages
- `import_tag` - Optional tag applied to imported books
- `status` - pending, processing, completed, failed
- `started_at`, `completed_at` - Timestamps

**book_view_preferences table:**
- Per-user, per-shelf view customization
- `user_id` - Foreign key to users
- `shelf` - Shelf name (e.g., 'all', 'currently_reading', 'read')
- `view_mode` - 'grid' or 'table'
- `visible_columns` - JSON array of column names
- `per_page` - Items per page

### Controller Patterns

**BookController** handles:
- CRUD operations for books
- `updateProgress()` - Update current page, creates history entry
- `updateStatus()` - Change reading status (triggers timestamp updates)
- `updateRating()` - Update book rating and review
- `storeFromApi()` - Import book from external API search
- `scrapeAmazon()` - Import book from Amazon URL via scraping
- `bulkDelete()` - Delete multiple books
- `bulkAddTags()` - Add tags to multiple books
- `bulkRemoveTag()` - Remove tag from multiple books
- `showSeries()` - Display all books in a series
- `toggleViewMode()` - Switch between grid/table view
- `updateColumnSettings()` - Update visible table columns
- `deleteProgressEntry()` - Delete a reading progress entry
- `fetchApiData()` - Fetch book data from original API source
- `refreshFromApi()` - Refresh book metadata from API
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
- Google Books API key configuration

**AdminController** (`App\Http\Controllers\Admin\AdminController`) handles:
- `index()` - Admin dashboard with statistics (total users, admins, books)
- `users()` - List all users with pagination
- `editUser()` - Display edit form for a user
- `updateUser()` - Update user details (name, email, admin status)
- `toggleAdmin()` - Grant/revoke admin privileges for a user
- `deleteUser()` - Delete a user (prevents self-deletion)
- `settings()` - Display system settings (registration)
- `updateSettings()` - Update system settings

**FamilyController** handles:
- `index()` - Display family overview and members
- `create()` - Show form to create a new family
- `store()` - Create a new family (auto-generates join code)
- `showJoinForm()` - Display form to join a family with code
- `join()` - Join a family using join code
- `leave()` - Leave current family (owners cannot leave)
- `destroy()` - Disband family (owner only)
- `regenerateCode()` - Generate new join code (owner only)

**ImportController** handles:
- `index()` - Display import interface
- `upload()` - Upload and parse CSV, show preview
- `execute()` - Process import with user-selected options
- `cancel()` - Cancel pending import
- `history()` - View import history
- `result()` - View detailed import results
- `destroy()` - Delete import history record

**LibraryTransferController** handles:
- `export()` - Generate and download library ZIP archive
- `showImportForm()` - Display library import form
- `upload()` - Upload and validate ZIP, show preview
- `execute()` - Process library import with duplicate strategy
- `cancel()` - Cancel pending library import
- `result()` - View library import results

**StatsController** handles:
- `index()` - Display reading statistics dashboard with year selector

**Auth controllers:**
- `ForgotPasswordController` - Password reset request form and email sending
- `ResetPasswordController` - Password reset form and processing
- `VerificationController` - Email verification link handling and resend

When implementing controllers:
- Use route model binding: `public function show(Book $book)`
- Use custom route binding for books (timestamp-based, see `Book::resolveRouteBinding()`)
- Authorize access: `$this->authorize('view', $book)` or manual user checks
- Scope queries to authenticated user: `auth()->user()->books()`
- Return validation errors with appropriate messages

### Views & Frontend

- Blade templates in `resources/views/`
- Layout: [resources/views/layouts/app.blade.php](resources/views/layouts/app.blade.php)
- Uses Tailwind CSS (CDN, no build process)
- Alpine.js for interactive components
- Server-rendered Blade templates (no SPA framework)
- Views organized by feature: `books/`, `tags/`, `auth/`, `settings/`, `challenge/`, `admin/`, `stats/`, `library/`

**Admin views:**
- `admin/index.blade.php` - Dashboard with stats and quick links
- `admin/users.blade.php` - User list with admin toggle and delete actions
- `admin/edit-user.blade.php` - Edit individual user details
- `admin/settings.blade.php` - System settings form (registration)
- Admin link in navigation dropdown (only visible to admins)

**Family views:**
- `family/index.blade.php` - Family overview, members list, join code display
- `family/create.blade.php` - Form to create a new family
- `family/join.blade.php` - Form to join a family with a code

**Stats views:**
- `stats/index.blade.php` - Reading statistics dashboard with charts

**Library transfer views:**
- `library/import.blade.php` - Library ZIP import form
- `library/preview.blade.php` - Import preview before execution
- `library/result.blade.php` - Import results

**Auth views:**
- `auth/register.blade.php` - Registration form
- `auth/login.blade.php` - Login form
- `auth/forgot-password.blade.php` - Password reset request form
- `auth/reset-password.blade.php` - Password reset form
- `auth/verify-email.blade.php` - Email verification notice

**Public views:**
- `welcome.blade.php` - Landing page (localized)

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

### Route Generation in Code

When generating URLs in controllers or views, use standard Laravel route helpers:

```php
// In controllers
return redirect()->route('books.index');
return redirect()->route('books.show', $book);

// In Blade templates
<a href="{{ route('books.show', $book) }}">View Book</a>
<a href="{{ route('settings.edit') }}">Settings</a>
```

**Important:** Route names do NOT include locale suffixes. Use simple names like `books.index`, `login`, `settings.edit`.

### Database Migrations

Migration files are dated `2026_01_08_*` onwards - new migrations will run in order based on timestamp prefix. Core tables must maintain their cascade delete relationships:

- Deleting a user cascades to books, tags, reading challenges, owned families
- Deleting a book cascades to book covers, reading progress history
- Book-tag relationships cascade on both sides
- Deleting a family sets users' `family_id` to null (not cascade delete)

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

### Admin System & User Management

The application now supports multi-user environments with admin controls:

**Admin Access Control:**
- Protected by `IsAdmin` middleware on all `/admin` routes
- Middleware checks `auth()->user()->is_admin` flag
- Non-admin users get 403 Forbidden error
- Admin link only visible in navigation if user is admin

**First User Auto-Admin:**
- The first user to register automatically becomes an admin
- Implemented in `RegisterController::register()` (lines 76-84)
- No seeder required
- Check: `$isFirstUser = User::count() === 0;`

**User Management Features:**
- View all users with statistics (book count, join date)
- Toggle admin privileges for any user (except yourself)
- Delete users (except yourself)
- Pagination for large user lists

**Registration Control Modes:**

1. **Open (`registration_mode = 'open'`)**: Anyone can register
2. **Domain-restricted (`registration_mode = 'domain'`)**: Only specific email domains allowed
   - Configured via `allowed_email_domains` (comma-separated)
   - Example: `example.com,company.org`
3. **Code-required (`registration_mode = 'code'`)**: Users need registration code
   - Single shared code configured in settings
   - Example use: family sharing

**SystemSetting Model:**
- Key-value configuration storage
- **NO CACHING** - direct database queries to avoid cache table dependency
- Common settings: `registration_enabled`, `registration_mode`, `allowed_email_domains`, `registration_code`
- Helper methods: `isRegistrationEnabled()`, `getRegistrationMode()`, `isEmailDomainAllowed()`, `isSmtpEnabled()`, `getSmtpConfig()`, `isTurnstileEnabled()`, `getTurnstileSiteKey()`, `getTurnstileSecretKey()`

### Security Considerations

**User Data Isolation:**
- All user queries MUST scope to `auth()->user()` for security
- Book route binding automatically scopes to current user via `resolveRouteBinding()`
- Tags, reading challenges, and covers are user-scoped
- Family membership does NOT share book collections
- Each user can only access their own books, tags, and reading data

**Admin Privileges:**
- Admins can see all users but NOT their books
- Admins cannot delete themselves (prevented in controller)
- Admins cannot toggle their own admin status (prevented in controller)
- At least one admin should always exist in the system
- Admin status stored as boolean in `users.is_admin`

**Route Protection:**
- Cover routes ordered before destroy to prevent path conflicts
- Numeric constraints on routes prevent parameter pollution
- Book timestamps used as route keys (harder to enumerate than sequential IDs)
- All book/tag routes protected with `auth` middleware
- Admin routes protected with both `auth` and `admin` middleware
- Locale middleware sets language for all routes

**Important Route Ordering:**
```php
// CRITICAL: Cover routes MUST come BEFORE destroy route
Route::delete('/books/{book}/covers/{cover}', ...);
Route::delete('/books/{book}', ...); // This must be last
```

### Cloudflare Configuration

**DNS Settings:**
- A record: `@` → Server IP (Proxied, orange cloud)
- CNAME record: `www` → `leafmark.app` (Proxied, orange cloud)

**Redirect Rules:**
- Apex to www: `leafmark.app` → `https://www.leafmark.app` (301)

**SSL/TLS:**
- Mode: Full (strict)
- Coolify generates Let's Encrypt certificates
- Cloudflare provides edge SSL

**Performance:**
- Auto Minify: HTML, CSS, JS
- Brotli compression enabled
- Cache Level: Standard

## Documentation Structure

- **README.md** - User-facing documentation, quick start
- **CLAUDE.md** - This file, technical context for Claude Code
- **DEPLOY.md** - Coolify deployment guide
- **CODESPACES.md** - GitHub Codespaces setup (if exists)
- **.claude/context.md** - Short project context reference

## Common Development Tasks

### Adding a New Feature

1. **Plan the database changes** (if needed)
   - Create migration: `php artisan make:migration create_feature_table`
   - Update models and relationships

2. **Create routes** in `routes/web.php`
   - Use clear, RESTful route names
   - Apply appropriate middleware (`auth`, `admin`, etc.)

3. **Implement controller**
   - Scope queries to `auth()->user()` for user-specific data
   - Use standard redirects: `return redirect()->route('feature.index');`
   - Return translated messages: `->with('success', __('app.feature.created'));`

4. **Create Blade views**
   - Use layout: `@extends('layouts.app')`
   - Use translation helpers: `{{ __('app.feature.title') }}`
   - Use route helpers: `{{ route('feature.show', $item) }}`

5. **Add translations** in `lang/en/app.php` first
   - Create a new section for your feature
   - Add all UI text as translation keys
   - Consider translating to other languages (de, fr, it, pl, es)

6. **Test the feature**
   - Manual testing in Codespaces
   - Test with different languages (change `APP_LOCALE` in `.env`)
   - Create tests in `tests/Feature/`

7. **Commit and push**
   - Coolify auto-deploys to production

### Updating System Settings

New system settings can be added via Admin → System Settings UI or programmatically:

```php
use App\Models\SystemSetting;

// Set a setting
SystemSetting::set('new_feature_enabled', 'true');

// Get a setting
$value = SystemSetting::get('new_feature_enabled', 'false');

// Add helper method to SystemSetting model
public static function isNewFeatureEnabled(): bool
{
    return static::get('new_feature_enabled', 'false') === 'true';
}
```

### Adding a New Language

1. **Create translation file**: Copy `lang/en/app.php` to `lang/{locale}/app.php`
2. **Translate all keys**: Maintain the exact same array structure, only translate the values
3. **Update documentation**: Add the new language to README.md and CLAUDE.md
4. **Test thoroughly**:
   - Set `APP_LOCALE={locale}` in `.env`
   - Restart server with `php artisan serve`
   - Check all pages for missing translations
   - Verify special characters and encoding

**Translation Best Practices:**
- Keep the same array structure as English
- Don't remove or add keys - only translate values
- Test with actual native speakers when possible
- Pay attention to pluralization rules (Laravel supports this)
- Consider context: "read" can mean "gelesen" or "lesen" depending on context

### Working with Docker Entrypoint

The `docker-entrypoint.sh` script runs on container start:

1. Creates `.env` from environment variables
2. Runs migrations with `--force`
3. Caches configuration
4. Starts Apache

**Important:** Migrations run automatically on deployment via Coolify.

## References

- **GitHub Repository:** https://github.com/roberteinsle/leafmark
- **Production URL:** https://www.leafmark.app
- **Coolify Admin:** https://coolify.leafmark.app
- **Laravel Documentation:** https://laravel.com/docs/11.x
- **Tailwind CSS:** https://tailwindcss.com/docs
- **Alpine.js:** https://alpinejs.dev/
