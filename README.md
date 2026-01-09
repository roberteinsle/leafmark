# Leafmark - Personal Book Tracking Web App

A Laravel-based web application for tracking your personal book collection and reading progress.

## Features

- 📚 **Book Management** - Add, edit, and organize your book collection
- 📖 **Reading Progress** - Track your reading status (Want to Read, Currently Reading, Read)
- 🏷️ **Tags System** - Organize books with custom tags and colors
- 🔍 **Smart Search** - Auto-detects ISBN, author names, and book titles
- 🌐 **Multi-source API** - Search Google Books and Open Library
- 🌍 **Multi-language** - English & German support
- 📊 **Reading Statistics** - Track reading progress and history
- 🎨 **Cover Management** - Multiple covers support with automatic fetching

## Tech Stack

- **Backend:** Laravel 11 + PHP 8.2
- **Database:** SQLite (default) / MariaDB 11 (production)
- **Frontend:** Blade Templates + Tailwind CSS
- **Deployment:** GitHub Actions + Docker + Cloudflare Tunnel

## Quick Start (GitHub Codespaces)

The easiest way to get started is with GitHub Codespaces:

1. **Create a Codespace** from this repository
2. **Wait for setup** - The devcontainer will automatically:
   - Install dependencies
   - Set up SQLite database
   - Run migrations
3. **Open the app** - Port 8000 will be forwarded automatically
4. **Register** your account and start tracking books!

### First-time Setup

```bash
# The database is already created, but you need to add your Google Books API key
# Edit .env and add your API key:
GOOGLE_BOOKS_API_KEY=your_key_here

# Restart the server (it auto-restarts in Codespaces)
```

## Local Development

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 20+ (optional, for asset building)

### Setup

```bash
# Clone the repository
git clone https://github.com/roberteinsle/leafmark.git
cd leafmark

# Install dependencies
composer install

# Copy and configure environment
cp .env.example .env
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Start development server
php artisan serve
```

Access the app at `http://localhost:8000`

## Production Deployment

For production deployment with Docker, see the deployment guide in `CLAUDE.md`.

**Key components:**
- Docker Compose with MariaDB
- GitHub Actions for CI/CD
- Cloudflare Tunnel for HTTPS
- Hetzner Cloud for hosting

## Database

### SQLite (Development)

Default configuration for local development and Codespaces:

```env
DB_CONNECTION=sqlite
```

The database file is created at `database/database.sqlite`.

### MariaDB (Production)

For production deployment with Docker:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=leafmark
DB_USERNAME=root
DB_PASSWORD=your_secure_password
```

## API Keys

### Google Books API (Recommended)

Get your free API key from [Google Cloud Console](https://console.cloud.google.com/):

1. Enable the Books API
2. Create credentials (API Key)
3. Add restrictions (HTTP referrers, API quotas)
4. Add to `.env`:

```env
GOOGLE_BOOKS_API_KEY=your_api_key_here
```

### Open Library (No key required)

Open Library API works without authentication but has lower rate limits.

## Environment Variables

```env
APP_NAME=Leafmark
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite

GOOGLE_BOOKS_API_KEY=your_key_here
```

See `.env.example` for all available options.

## Features in Detail

### Smart Book Search

The search automatically detects:
- **ISBN** (10 or 13 digits): `3551354030` → searches by ISBN
- **Author names**: `Stephen King` → searches by author
- **Book titles**: `harry potter` → searches by title

### Multi-source Search

Choose your search provider:
- **Open Library** - Free, no API key required
- **Google Books** - Requires API key, better metadata
- **All Sources** - Searches both and merges results

### Reading Progress Tracking

- Mark books as: Want to Read, Currently Reading, or Read
- Track current page and total pages
- View reading statistics and history

### Tags & Organization

- Create custom tags with colors
- Organize books by genre, series, or custom categories
- Filter and search by tags

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

MIT License

---

Built with ❤️ using Laravel and Claude Code
