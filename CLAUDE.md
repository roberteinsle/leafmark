# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Leafmark is a **multi-user** book tracking web application built with Laravel 11 and PHP 8.2. Users can manage their book collections, track reading progress, organize books with tags, import book data from external APIs (Google Books, Open Library, BookBrainz, Big Book API) or CSV files (Goodreads), and set yearly reading goals.

The application supports multiple users with individual book collections, admin-controlled registration, and flexible user management. Perfect for organizations, book clubs, families, or communities.

## Infrastructure & Deployment

### Production Setup

```
User → Cloudflare (CDN/SSL) → Hetzner VM → Coolify/Traefik → Leafmark Container
                                                   ↓
                                            MariaDB Container
```

**Components:**
- **Hosting:** Hetzner VM
- **Deployment Platform:** Coolify (self-hosted PaaS)
- **Production URL:** https://www.leafmark.app
- **Admin Panel:** https://coolify.leafmark.app
- **CDN/DNS:** Cloudflare (proxied, orange cloud)
- **Database:** MariaDB 11 in separate container

### Development Environment

**Primary:** GitHub Codespaces
- Preconfigured dev environment
- Auto-starts services on port 8000
- See CODESPACES.md for details

**Local:** Docker Compose (optional)
- `docker-compose.yaml` for local development
- MariaDB + Laravel app services

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

### Docker Development (Local)

```bash
# Note: This project uses docker-compose.yaml (not docker-compose.yml)
# Use 'docker compose' (v2) or 'docker-compose' (v1)

# Start services
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
# Or directly in Codespaces

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
vendor/bin/phpunit
# Or in Docker:
docker compose exec app vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite=Feature
vendor/bin/phpunit --testsuite=Unit

# Run specific test file
vendor/bin/phpunit tests/Feature/ExampleTest.php

# Run with coverage (requires xdebug)
vendor/bin/phpunit --coverage-html coverage
```

### Composer

```bash
# Install dependencies
composer install
# Or in Docker:
docker compose exec app composer install

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

**First User Auto-Admin:**
- The **first user to register automatically becomes an admin**
- No seeder required
- Implemented in RegisterController.php:76-84

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

### Authentication & Authorization

**Basic Authentication:**
- Uses Laravel's built-in authentication (`Illuminate\Foundation\Auth\User`)
- Custom controllers: `LoginController`, `RegisterController`
- All book/tag routes protected with `auth` middleware
- Authorization through relationship checks: books/tags must belong to authenticated user

**Email Verification:**
- **Users must verify email before login** (not auto-logged in after registration)
- Laravel's built-in email verification system
- `VerificationController` handles verification and resend
- New users receive verification email after registration
- Email contains signed URL with expiration
- Redirects to verification notice page after registration

**Admin System:**
- `IsAdmin` middleware protects admin routes
- Admin routes require both `auth` and `admin` middleware
- Admin users have `is_admin = true` in database
- Admin middleware registered as `'admin' => \App\Http\Middleware\IsAdmin::class`

**Registration Control:**
- `RegisterController` checks `SystemSetting` for registration rules
- Three registration modes: open, domain, code
- Registration can be completely disabled via admin settings
- **Cloudflare Turnstile integration** for CAPTCHA protection (optional)

**Email System:**
- SMTP settings configured in Admin → System Settings
- `EmailLog` model tracks all sent emails (verification, password reset)
- Admin can view email logs at `/admin/email-logs` for debugging
- Test email functionality available in admin settings

**Cloudflare Turnstile (CAPTCHA):**
- Protects registration, login, password reset, and contact forms
- Configured in Admin → System Settings
- Settings: `turnstile_enabled`, `turnstile_site_key`, `turnstile_secret_key`
- `TurnstileValid` validation rule in `app/Rules/TurnstileValid.php`
- Optional - can be disabled

### Internationalization

The application supports multiple languages with **localized routing**:

**Supported Languages:**
- English (en), German (de), French (fr), Italian (it), Spanish (es), Polish (pl)

**Localized Routing Architecture:**
- All routes prefixed with locale: `/en/`, `/de/`, `/fr/`, `/it/`, `/es/`, `/pl/`
- `SetLocaleFromUrl` middleware handles locale detection and setting
- Named routes include locale suffix: `books.index.en`, `books.index.de`, etc.
- Root URL (`/`) auto-redirects to detected locale

**Locale Detection Priority:**
1. User's `preferred_language` field (if authenticated)
2. Browser `Accept-Language` header
3. Default: English (`en`)

**Localized Service Pages:**
- Different URLs per language for static pages
- Examples:
  - Imprint: `/imprint` (en), `/impressum` (de), `/mentions-legales` (fr)
  - Privacy: `/privacy` (en), `/datenschutz` (de), `/confidentialite` (fr)
  - Contact: `/contact` (en/fr/it), `/kontakt` (de/pl), `/contacto` (es)

**Backward Compatibility:**
- Old non-prefixed URLs redirect to localized versions (301)
- Example: `/login` → `/{locale}/login`
- Authenticated users redirected to their preferred language
- Guest users redirected to detected locale

**Language Files:**
- Translation files in `lang/{locale}/app.php`
- `LanguageService` provides language name display and code conversion

### Contact Form

**ContactController** provides a public contact form:
- Categories: support, feature, bug, privacy
- Turnstile CAPTCHA protection (optional)
- Sanitizes input (strips HTML tags)
- Sends emails to configurable `contact_email` (default: ews@einsle.com)
- Localized URLs per language

**Routes:**
- `/contact`, `/kontakt`, `/contacto`, `/contatto` (depending on locale)
- POST to same route for submission

**Email Configuration:**
- Uses system SMTP settings
- HTML email template in controller
- Reply-To set to user's email

### Routing Structure

Routes are defined in [routes/web.php](routes/web.php):

**Important:** All routes are wrapped in locale prefixes. Examples below show the pattern without locale.

**Public routes:**
- `/{locale}` - Landing page (redirects to dashboard if authenticated)
- `/{locale}/imprint`, `/{locale}/privacy`, `/{locale}/contact` - Service pages (localized URLs)
- `/{locale}/changelog` - Changelog page

**Guest-only routes:**
- `/{locale}/register`, `/{locale}/login` - Authentication forms
- `/{locale}/forgot-password`, `/{locale}/reset-password/{token}` - Password reset
- `/{locale}/verify-email` - Email verification notice

**Email verification routes:**
- `/{locale}/email/verify/{id}/{hash}` - Verify email (signed route)
- `/{locale}/email/resend` - Resend verification email

**Protected routes (requires auth):**
- `/{locale}/books` - Resource routes (index, create, store, show, edit, update, destroy)
- `/{locale}/books/store-from-api` - Store book imported from external API
- `/{locale}/books/bulk-delete` - Delete multiple books at once
- `/{locale}/books/bulk-add-tags` - Add tags to multiple books
- `/{locale}/books/bulk-remove-tag` - Remove tag from multiple books
- `/{locale}/books/{book}/progress` - PATCH to update reading progress
- `/{locale}/books/{book}/status` - PATCH to update reading status
- `/{locale}/books/{book}/rating` - PATCH to update book rating
- `/{locale}/books/{book}/progress/{entry}` - DELETE to remove progress entry
- `/{locale}/books/{book}/fetch-api-data` - GET to fetch API data
- `/{locale}/books/{book}/refresh-from-api` - POST to refresh book from API
- `/{locale}/books/{book}/covers` - POST to upload new cover
- `/{locale}/books/{book}/covers/{cover}` - DELETE to remove cover
- `/{locale}/books/{book}/covers/{cover}/primary` - PATCH to set primary cover
- `/{locale}/series/{series}` - View all books in a series
- `/{locale}/books/toggle-view-mode` - POST to toggle grid/table view
- `/{locale}/books/update-column-settings` - POST to update visible columns
- `/{locale}/tags` - Resource routes for tag management
- `/{locale}/tags/{tag}/books/{book}` - POST/DELETE to add/remove books from tags
- `/{locale}/settings` - GET/PATCH for user settings
- `/{locale}/challenge` - Reading challenge routes (index, store, update, destroy)
- `/{locale}/family` - Family management routes
- `/{locale}/import` - CSV import routes

**Admin routes (requires auth + admin):**
- `/{locale}/admin` - Admin dashboard with user statistics
- `/{locale}/admin/users` - User management (list, toggle admin, delete)
- `/{locale}/admin/users/{user}` - Edit, update, delete user
- `/{locale}/admin/users/{user}/toggle-admin` - PATCH to grant/revoke admin privileges
- `/{locale}/admin/settings` - System settings (GET/PATCH)
- `/{locale}/admin/settings/test-email` - POST to send test email
- `/{locale}/admin/email-logs` - View email sending logs and history

**Backward compatibility routes:**
- Old non-prefixed URLs redirect to localized versions with 301 status
- Examples: `/login` → `/{locale}/login`, `/books` → `/{locale}/books`

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

**system_settings table:**
- Key-value storage for application configuration
- Stores registration, SMTP, Turnstile, and API settings
- Keys: `registration_enabled`, `registration_mode`, `allowed_email_domains`, `registration_code`
- SMTP: `smtp_enabled`, `smtp_host`, `smtp_port`, `smtp_encryption`, `smtp_username`, `smtp_password`, `smtp_from_address`, `smtp_from_name`
- Turnstile: `turnstile_enabled`, `turnstile_site_key`, `turnstile_secret_key`
- Contact: `contact_email`
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

**AdminController** handles:
- `index()` - Admin dashboard with statistics (total users, admins, books)
- `users()` - List all users with pagination
- `editUser()` - Display edit form for a user
- `updateUser()` - Update user details (name, email, admin status)
- `toggleAdmin()` - Grant/revoke admin privileges for a user
- `deleteUser()` - Delete a user (prevents self-deletion)
- `settings()` - Display system settings (registration, SMTP, Turnstile, API keys)
- `updateSettings()` - Update system settings
- `sendTestEmail()` - Send test email to verify SMTP configuration
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

**ContactController** handles:
- `show()` - Display contact form
- `submit()` - Handle form submission with Turnstile verification
- Email sending with category-based subject lines

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
- `admin/settings.blade.php` - System settings form (registration, SMTP, Turnstile, API keys)
- `admin/email-logs.blade.php` - Email sending history and logs
- Admin link in navigation dropdown (only visible to admins)

**Family views:**
- `family/index.blade.php` - Family overview, members list, join code display
- `family/create.blade.php` - Form to create a new family
- `family/join.blade.php` - Form to join a family with a code

**Auth views:**
- `auth/register.blade.php` - Registration form with optional Turnstile
- `auth/login.blade.php` - Login form with optional Turnstile
- `auth/verify-email.blade.php` - Email verification notice
- `auth/forgot-password.blade.php` - Password reset request with optional Turnstile
- `auth/reset-password.blade.php` - Password reset form with optional Turnstile

**Public views:**
- `welcome.blade.php` - Landing page (localized)
- `kontakt.blade.php` - Contact form (localized URLs)
- `impressum.blade.php` - Imprint/legal notice
- `datenschutz.blade.php` - Privacy policy
- `changelog.blade.php` - Version history

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

### Localized Routes in Code

When generating URLs in controllers or views, always include the locale:

```php
// In controllers
return redirect()->route('books.index.' . app()->getLocale());

// In Blade templates
<a href="{{ route('books.show.' . app()->getLocale(), $book) }}">View Book</a>

// Or use helper
@php $locale = app()->getLocale() @endphp
<a href="{{ route('books.show.' . $locale, $book) }}">View Book</a>
```

**Important:** All named routes include locale suffix (e.g., `books.index.en`, `login.de`).

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
- Helper methods: `isRegistrationEnabled()`, `getRegistrationMode()`, `isEmailDomainAllowed()`
- SMTP methods: `isSmtpEnabled()`, `getSmtpConfig()`
- Turnstile methods: `isTurnstileEnabled()`, `getTurnstileSiteKey()`, `getTurnstileSecretKey()`

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

**Email Verification:**
- Users must verify email before accessing the application
- Verification emails tracked in `email_logs` table
- Admins can view email logs for debugging
- SMTP must be configured for email verification to work

**Cloudflare Turnstile:**
- Optional CAPTCHA protection for registration and login
- Configured in Admin → System Settings
- Uses `TurnstileValid` validation rule
- Verifies token via Cloudflare API

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
   - Remember to add for ALL supported locales
   - Include locale in named routes

3. **Implement controller**
   - Scope queries to `auth()->user()`
   - Use localized redirects

4. **Create Blade views**
   - Use layout: `@extends('layouts.app')`
   - Include locale in route helpers

5. **Add translations** in `lang/{locale}/app.php`

6. **Test the feature**
   - Manual testing in Codespaces
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

1. Create translation file: `lang/{locale}/app.php`
2. Add locale to `SetLocaleFromUrl` middleware (line 15, 42)
3. Add locale to `routes/web.php` `$supportedLocales` array (line 24)
4. Add localized routes in foreach loop
5. Update views with new locale option

### Debugging Email Issues

1. Check SMTP configuration in Admin → System Settings
2. Send test email via Admin → System Settings → Test Email
3. Check email logs in Admin → Email Logs
4. Verify Laravel logs: `storage/logs/laravel.log`
5. Check Coolify logs in production

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
- **Cloudflare Turnstile:** https://developers.cloudflare.com/turnstile/
