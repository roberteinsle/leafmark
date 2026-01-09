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

### Initial Setup

```bash
# Create directory and clone repository
cd ~/leafmark
git clone https://github.com/roberteinsle/leafmark.git app-source
cd app-source

# Create .env file
cp .env.example .env
nano .env  # Edit: Set APP_KEY, APP_URL, APP_ENV=production, APP_DEBUG=false

# Build and start containers
docker compose up -d

# Wait for containers to be ready
sleep 10

# Generate application key (if not set in .env)
docker compose exec app php artisan key:generate

# Run migrations
docker compose exec app php artisan migrate --force

# Check status
docker compose ps
```

### Test Deployment

```bash
# Test locally
curl http://localhost:8080
```

### Update Workflow

When deploying updates from GitHub:

```bash
cd ~/leafmark/app-source
git pull
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
```

### Key Components

- **Docker Compose** with SQLite
- **GitHub Actions** for CI/CD (optional)
- **Cloudflare Tunnel** for HTTPS (optional)
- **Hetzner Cloud** for hosting (or any VPS)

## Database

The application uses SQLite for both development and production:

```env
DB_CONNECTION=sqlite
```

The database file is created at `database/database.sqlite`.

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
