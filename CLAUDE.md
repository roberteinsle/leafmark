# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Leafmark is a **multi-user** book tracking web application built with Laravel 11 and PHP 8.2. Users can manage their book collections, track reading progress, organize books with tags, import book data from external APIs (Google Books, Open Library, BookBrainz, Big Book API) or CSV files (Goodreads), and set yearly reading goals.

The application supports multiple users with individual book collections, admin-controlled registration, and flexible user management. Perfect for organizations, book clubs, families, or communities.

## Development Commands

### Docker Development
```bash
# Note: This project uses docker-compose.yaml (not docker-compose.yml)
# Use 'docker compose' (v2) or 'docker-compose' (v1)

# Start app service
docker compose up -d

# Execute commands in app container
docker compose exec app <command>

# View logs
docker compose logs -f app

# Stop services
docker compose down
```

### Laravel Artisan Commands
```bash
# Run inside container with: docker compose exec app <command>

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
docker compose exec app vendor/bin/phpunit

# Run specific test suite
docker compose exec app vendor/bin/phpunit --testsuite=Feature
docker compose exec app vendor/bin/phpunit --testsuite=Unit

# Run specific test file
docker compose exec app vendor/bin/phpunit tests/Feature/ExampleTest.php

# Run with coverage (requires xdebug)
docker compose exec app vendor/bin/phpunit --coverage-html coverage
```

### Composer
```bash
# Install dependencies
docker compose exec app composer install

# Update dependencies
docker compose exec app composer update

# Add package
docker compose exec app composer require vendor/package
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

**Routes:**
- `/import` - Import interface
- `/import/upload` - POST to upload CSV
- `/import/execute` - POST to start import
- `/import/history` - View import history
- `/import/result/{importHistory}` - View detailed import results

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

**Routes:**
- `/books/toggle-view-mode` - POST to switch between grid/table view
- `/books/update-column-settings` - POST to update visible columns

### Authentication & Authorization

**Basic Authentication:**
- Uses Laravel's built-in authentication (`Illuminate\Foundation\Auth\User`)
- Custom controllers: `LoginController`, `RegisterController`
- All book/tag routes protected with `auth` middleware
- Authorization through relationship checks: books/tags must belong to authenticated user

**Admin System:**
- `IsAdmin` middleware protects admin routes
- Admin routes require both `auth` and `admin` middleware
- Admin users have `is_admin = true` in database
- Admin middleware registered as `'admin' => \App\Http\Middleware\IsAdmin::class`

**Registration Control:**
- `RegisterController` checks `SystemSetting` for registration rules
- Three registration modes: open, domain, code
- Registration can be completely disabled via admin settings

**Email Verification:**
- Laravel's built-in email verification system
- `VerificationController` handles verification and resend
- New users receive verification email after registration
- Email contains signed URL with expiration
- Unverified users can still access the application
- Email verification is optional but can be enforced via middleware

**Email System:**
- SMTP settings configured in Admin → System Settings
- `EmailLog` model tracks all sent emails (verification, password reset)
- Admin can view email logs at `/admin/email-logs` for debugging
- Test email functionality available in admin settings

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
- `/family` - GET to view family, POST to create family, DELETE to disband family
- `/family/create` - GET to show create family form
- `/family/join` - GET/POST to join a family using join code
- `/family/leave` - POST to leave current family
- `/family/regenerate-code` - POST to generate new join code (owner only)
- `/import` - GET to display import interface
- `/import/upload` - POST to upload and preview CSV
- `/import/execute` - POST to execute import
- `/import/cancel` - POST to cancel pending import
- `/import/history` - GET to view import history
- `/import/result/{importHistory}` - GET to view import results
- `/import/{importHistory}` - DELETE to remove import history

**Admin routes (requires auth + admin):**
- `/admin` - Admin dashboard with user statistics
- `/admin/users` - User management (list, toggle admin, delete)
- `/admin/users/{user}` - GET to edit user, PATCH to update user, DELETE to remove user
- `/admin/users/{user}/toggle-admin` - PATCH to grant/revoke admin privileges
- `/admin/settings` - System settings and registration control
- `/admin/settings` - PATCH to update system settings

- `/admin/email-logs` - View email sending logs and history

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
- Admins can access `/admin` routes and manage users
- At least one admin required (robert@einsle.com)

**system_settings table:**
- Key-value storage for application configuration
- Stores registration settings, allowed domains, registration code
- Keys: `registration_enabled`, `registration_mode`, `allowed_email_domains`, `registration_code`
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

**email_logs table:**
- Tracks all sent emails for debugging
- `user_id` - Recipient user
- `type` - Email type (verification, password_reset, etc.)
- `recipient` - Email address
- `subject` - Email subject line
- `status` - sent, failed
- `error_message` - Error details if failed
- `sent_at` - Timestamp

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
- Google Books API key configuration

**AdminController** handles:
- `index()` - Admin dashboard with statistics (total users, admins, books)
- `users()` - List all users with pagination
- `editUser()` - Display edit form for a user
- `updateUser()` - Update user details (name, email, admin status)
- `toggleAdmin()` - Grant/revoke admin privileges for a user
- `deleteUser()` - Delete a user (prevents self-deletion)
- `settings()` - Display system settings
- `updateSettings()` - Update registration mode and settings
- `emailLogs()` - View email sending history and logs

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
- Views organized by feature: `books/`, `tags/`, `auth/`, `settings/`, `challenge/`, `admin/`

**Admin views:**
- `admin/index.blade.php` - Dashboard with stats and quick links
- `admin/users.blade.php` - User list with admin toggle and delete actions
- `admin/edit-user.blade.php` - Edit individual user details
- `admin/settings.blade.php` - System settings form
- `admin/email-logs.blade.php` - Email sending history and logs
- Admin link in navigation dropdown (only visible to admins)

**Family views:**
- `family/index.blade.php` - Family overview, members list, join code display
- `family/create.blade.php` - Form to create a new family
- `family/join.blade.php` - Form to join a family with a code

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
- `app` - Laravel 11 + PHP 8.2 + Apache (exposed on port 8000)
- `db` - MariaDB 11 (exposed on port 3306)

**Container Details:**
- Base image (app): `php:8.2-apache`
- Base image (db): `mariadb:11`
- Document root: `/var/www/html/public`
- Apache mod_rewrite enabled
- PHP extensions: pdo_mysql, mbstring, exif, pcntl, bcmath, gd, zip
- Database health checks ensure app starts only after DB is ready

**Docker Volumes:**
- `mariadb_data` - MariaDB database persistence
- `storage_data` - Uploaded book covers
- `vendor` - Composer dependencies

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

**Environment variables are set via docker-compose.yaml:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=  # Will be generated
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=leafmark
DB_USERNAME=leafmark
DB_PASSWORD=  # Use secure password
MYSQL_ROOT_PASSWORD=  # Use secure password
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

# Create admin user (REQUIRED for multi-user system)
docker compose exec app php artisan db:seed --class=AdminUserSeeder

# Verify everything is running
docker compose ps
curl http://localhost:8080
```

### Admin Setup

After deployment, log in with the admin account:

```
Email: robert@einsle.com
Password: password
```

**⚠️ CRITICAL: Change the admin password immediately after first login!**

Then configure registration settings:
1. Go to Admin → System Settings
2. Choose registration mode (recommended: domain-restricted)
3. Configure allowed domains or registration code as needed

### Update Workflow

To update the application:

```bash
cd ~/leafmark/app-source

# Pull latest code from GitHub
git pull origin main

# Rebuild and restart containers (volumes are preserved - data is NOT deleted!)
docker compose down
docker compose up -d --build

# Run database migrations
docker compose exec app php artisan migrate --force

# Ensure storage symlink exists
docker compose exec app php artisan storage:link

# Clear and rebuild caches
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
```

**⚠️ CRITICAL:**
- **NEVER use `docker compose down -v`** - the `-v` flag deletes volumes and ALL DATA!
- **NEVER delete Docker volumes manually** - they contain all user data and uploaded files
- Data persists in Docker volumes across container rebuilds

### Data Persistence

All data is persisted in Docker volumes:

- **`mariadb_data`** - MariaDB database at `/var/lib/mysql`
- **`storage_data`** - Uploaded files (covers) at `/var/www/html/storage/app`
- **`vendor`** - Composer dependencies

These volumes persist across container restarts and rebuilds.

### Backup & Restore

**Creating a Backup:**

```bash
# Create backup directory
BACKUP_DIR=~/leafmark/backups
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# Backup MariaDB database
docker run --rm \
  -v leafmark_mariadb_data:/data \
  -v $BACKUP_DIR:/backup \
  alpine tar czf /backup/db-backup-${TIMESTAMP}.tar.gz -C /data .

# Backup uploaded files (covers)
docker run --rm \
  -v leafmark_storage_data:/data \
  -v $BACKUP_DIR:/backup \
  alpine tar czf /backup/storage-backup-${TIMESTAMP}.tar.gz -C /data .

echo "Backup created: $TIMESTAMP"
```

This creates:
- `db-backup-YYYYMMDD_HHMMSS.tar.gz` - MariaDB database backup
- `storage-backup-YYYYMMDD_HHMMSS.tar.gz` - Uploaded files backup

**Restoring from a Backup:**

```bash
# Stop application
cd ~/leafmark/app-source
docker compose down

# Set the backup timestamp to restore
TIMESTAMP=20260110_120000

# Restore database
docker run --rm \
  -v leafmark_mariadb_data:/data \
  -v ~/leafmark/backups:/backup \
  alpine sh -c "rm -rf /data/* && tar xzf /backup/db-backup-${TIMESTAMP}.tar.gz -C /data"

# Restore uploaded files
docker run --rm \
  -v leafmark_storage_data:/data \
  -v ~/leafmark/backups:/backup \
  alpine sh -c "rm -rf /data/* && tar xzf /backup/storage-backup-${TIMESTAMP}.tar.gz -C /data"

# Restart application
docker compose up -d
```

**⚠️ WARNING:** Restore will permanently replace current data with backup data!

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
4. **Code-required (`registration_mode = 'code'`)**: Users need registration code
   - Single shared code configured in settings
   - Example use: family sharing

**SystemSetting Model:**
- Key-value configuration storage
- **NO CACHING** - direct database queries to avoid cache table dependency
- Common settings: `registration_enabled`, `registration_mode`, `allowed_email_domains`, `registration_code`
- Helper methods: `isRegistrationEnabled()`, `getRegistrationMode()`, `isEmailDomainAllowed()`

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

**Important Route Ordering:**
```php
// CRITICAL: Cover routes MUST come BEFORE destroy route
Route::delete('/books/{book}/covers/{cover}', ...);
Route::delete('/books/{book}', ...); // This must be last
```

