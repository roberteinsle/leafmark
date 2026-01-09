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
- **Database:** SQLite
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

### Optional: Add Google Books API Key

After registering, you can optionally add a Google Books API key:
- Go to Settings in the app
- Add your API key there (recommended)
- Or add it globally in `.env` file

This enables better search results, but Open Library works without a key.

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
- Docker Compose with SQLite
- GitHub Actions for CI/CD
- Cloudflare Tunnel for HTTPS
- Hetzner Cloud for hosting

## Database

The application uses SQLite for both development and production:

```env
DB_CONNECTION=sqlite
```

The database file is created at `database/database.sqlite`.

## API Keys

### Google Books API (Optional)

The Google Books API key is optional. You can:
- Use it globally by setting it in `.env`
- Set it per-user in Settings (recommended for multi-user setups)
- Skip it and use only Open Library API

To get a free API key from [Google Cloud Console](https://console.cloud.google.com/):

1. Enable the Books API
2. Create credentials (API Key)
3. Add restrictions (HTTP referrers, API quotas)
4. Add to `.env` (optional):

```env
GOOGLE_BOOKS_API_KEY=your_api_key_here
```

Or add it in the app's Settings page after login.

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
