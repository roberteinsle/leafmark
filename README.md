# Leafmark - Book Tracking Web App

A multi-user book tracking web application built with Laravel 11 and PHP 8.2. Manage your book collection, track reading progress, view statistics, and organize books with tags.

**Live:** [www.leafmark.app](https://www.leafmark.app)

## Features

### Book Management
- Add, edit, and delete books manually or via external sources
- Reading status tracking (Want to Read, Currently Reading, Read)
- Reading progress with page tracking and history graphs
- Book ratings and reviews
- Series tracking with position ordering
- Multiple cover images per book (upload, reorder, set primary)
- Purchase tracking (date, price, currency, format)
- Bulk operations (delete, add/remove tags)

### Book Import Sources
- **Google Books API** - Search by ISBN, author, or title
- **Open Library API** - Free, no API key required
- **BookBrainz API** - Additional metadata source
- **Big Book API** - Comprehensive book data
- **Amazon Scraping** - Import book data from Amazon URLs (DE/COM)
- **Goodreads CSV Import** - Import from Goodreads export files with duplicate detection
- **Library Import (ZIP)** - Restore a full library backup including books, covers, tags, and progress

### Library Export & Backup
- Export entire library as ZIP archive (books, covers, tags, progress, challenges)
- Import ZIP archives with preview and duplicate handling (skip, overwrite, keep both)

### Reading Statistics
- Books and pages read per year with month-by-month breakdown
- Year-over-year comparison charts (Chart.js)
- Average rating, average days per book, average pages per day
- Longest/shortest book, best/worst reading month
- Top authors, language distribution, format breakdown
- Reading challenge progress integration

### Organization
- Custom tags with color support
- Grid and table view modes (per shelf)
- Configurable table columns per shelf
- Per-page item count settings (10, 25, 50, 100)
- Series grouping view

### Reading Challenges
- Yearly reading goals
- Automatic progress tracking based on finished books

### Multi-User & Admin
- Individual book collections per user
- Admin dashboard with user statistics
- User management (create, edit, delete, toggle admin)
- First registered user becomes admin automatically
- Registration control: open, domain-restricted, or code-required
- Family accounts with join codes

### Internationalization
- 6 languages: English, German, French, Italian, Spanish, Polish
- Configured via `APP_LOCALE` in `.env`

### Security
- Password reset via email
- Email verification
- Cloudflare Turnstile CAPTCHA support (optional)
- Dynamic SMTP configuration via admin settings

## Tech Stack

- **Backend:** Laravel 11 + PHP 8.2
- **Database:** SQLite
- **Frontend:** Blade Templates + Tailwind CSS + Alpine.js
- **Charts:** Chart.js
- **Deployment:** Docker / Coolify

## Quick Start

### Option 1: GitHub Codespaces (Recommended)

1. Click **Code** > **Codespaces** > **Create codespace on main**
2. Wait for the environment to build (~2 min)
3. The app starts automatically at port 8000

See [CODESPACES.md](CODESPACES.md) for details.

### Option 2: Local Development

```bash
git clone https://github.com/roberteinsle/leafmark.git
cd leafmark

cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan storage:link
php artisan serve

# Access at http://localhost:8000
```

### Option 3: Self-Hosted with Coolify

See [DEPLOY.md](DEPLOY.md) for detailed deployment instructions.

## Environment Variables

| Variable | Description | Required |
|----------|-------------|----------|
| `APP_KEY` | Laravel application key | Yes |
| `APP_LOCALE` | Application language (de, en, fr, it, es, pl) | Yes |
| `ADMIN_EMAIL` | Auto-admin assignment for this email | No |
| `GOOGLE_BOOKS_API_KEY` | Google Books API key | No |
| `BIGBOOK_API_KEY` | Big Book API key | No |

SMTP, Turnstile CAPTCHA, and API keys can also be configured via Admin > System Settings in the UI.

## Development with Claude Code

This project supports development with [Claude Code](https://docs.anthropic.com/en/docs/claude-code). See [CLAUDE.md](CLAUDE.md) for project context and conventions.

```bash
npm install -g @anthropic-ai/claude-code
claude
```

## Testing

```bash
php artisan test                              # Run all tests
php artisan test --testsuite=Feature          # Feature tests only
php artisan test --testsuite=Unit             # Unit tests only
php artisan test --filter=test_example        # Specific test
```

## Contributing

Contributions are welcome! Please open an issue or submit a pull request.

## License

MIT License - see [LICENSE](LICENSE) for details.
