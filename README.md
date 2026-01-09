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

### Optional: Add Google Books API Key

After registering, you can optionally add a Google Books API key:
- Go to Settings in the app
- Add your API key there (recommended)

This enables better search results, but Open Library works without a key.

## Production Deployment

### Initial Setup

```bash
# Create directory and clone repository
cd ~/leafmark
git clone https://github.com/roberteinsle/leafmark.git app-source
cd app-source

# Create .env file for Docker Compose (environment variables)
cat > .env << 'EOF'
APP_NAME=Leafmark
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://www.leafmark.app
DB_CONNECTION=sqlite
GOOGLE_BOOKS_API_KEY=
EOF

# Build and start containers
docker compose up -d

# Wait for containers to be ready
sleep 10

# Generate application key and update .env file
NEW_KEY=$(docker compose exec -T app php artisan key:generate --show)
sed -i "s|APP_KEY=|APP_KEY=$NEW_KEY|" .env

# Restart containers to load the new key
docker compose restart

# Wait for restart
sleep 5

# Run migrations
docker compose exec app php artisan migrate --force

# Check status
docker compose ps
curl -I http://localhost:8080
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

**Important:** Your data is safe during updates! The application uses Docker volumes to persist:
- **Database:** `sqlite_data` volume stores your SQLite database
- **Uploads:** `storage_data` volume stores book covers and other files

These volumes remain intact even when containers are rebuilt or updated.


## Database

The application uses SQLite for both development and production:

```env
DB_CONNECTION=sqlite
```

The database file is created at `database/database.sqlite` and is **NOT tracked in Git** - ensuring your personal data stays private. Data persistence in production is managed through Docker volumes (`sqlite_data`).

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
