# Leafmark - Multi-User Book Tracking Web App

A Laravel-based web application for tracking book collections and reading progress. Supports multi-user environments with admin controls for organizations, families, or communities.

## Features

### 📚 Book Management
- Add, edit, and organize your book collection
- Import from multiple sources: Google Books, Open Library, Amazon, BookBrainz
- Smart search auto-detects ISBN, author names, and book titles
- Multiple cover support with automatic fetching
- Series tracking and organization
- Purchase tracking (date, price, format)

### 📖 Reading Progress
- Track status: Want to Read, Currently Reading, Read
- Page progress tracking with visual graphs
- Reading history timeline
- Reading challenges with yearly goals
- Monthly achievement tracking

### 🏷️ Organization
- Custom tags with colors
- Default and custom tag system
- Filter and search by tags
- Series grouping

### 👥 Multi-User & Administration
- **Multi-user support** with individual book collections
- **Admin panel** for user management
- **Flexible registration modes:**
  - Open (anyone can register)
  - Domain-restricted (e.g., @yourcompany.com only)
  - Invitation-only (admins send invitations)
  - Code-required (personal registration code)
- User management with admin role assignment
- Invitation system with expiring links

### 🌍 Internationalization
- Multi-language support: English, German, French, Italian, Spanish, Polish
- User-specific language preferences
- Language-aware book searches

## Tech Stack

- **Backend:** Laravel 11 + PHP 8.2
- **Database:** MariaDB 11 (MySQL compatible)
- **Frontend:** Blade Templates + Tailwind CSS + Alpine.js
- **Deployment:** Docker + Docker Compose
- **Architecture:** Multi-container (App + Database)

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

# Configure environment variables
# The docker-entrypoint.sh will create .env from these
export APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
export DB_PASSWORD=$(openssl rand -base64 32)
export MYSQL_ROOT_PASSWORD=$(openssl rand -base64 32)

# Build and start containers (includes MariaDB)
docker compose up -d

# Wait for database to be healthy (30-60 seconds)
sleep 30

# Migrations run automatically via docker-entrypoint.sh
# Check status
docker compose ps
curl http://localhost:8000
```

### Admin Setup

**The first user to register will automatically become an admin.**

After registration, configure system settings:
1. Log in with your newly created account
2. Go to Admin → System Settings
3. Configure SMTP settings to enable email notifications
4. Choose registration mode:
   - Open (anyone can register)
   - Domain-restricted (e.g., @yourcompany.com only)
   - Invitation-only (admins send invitations)
   - Code-required (personal registration code)
5. Configure allowed domains or create invitations as needed

### Update Workflow

To update the application:

```bash
cd ~/leafmark/app-source

# Pull latest code from GitHub
git pull origin main

# Rebuild and restart containers (data persists in volumes)
docker compose down
docker compose up -d --build

# Run database migrations
docker compose exec app php artisan migrate --force

# Clear and rebuild caches
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
```

**⚠️ CRITICAL: NEVER use `docker compose down -v`** - the `-v` flag deletes volumes and ALL DATA!

### Data Persistence & Backups

The application uses Docker volumes for persistence:
- **`mariadb_data`** - MariaDB database
- **`storage_data`** - Uploaded book covers
- **`vendor`** - Composer dependencies

These volumes persist across container rebuilds.

**Creating Backups:**

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

# Backup uploaded files
docker run --rm \
  -v leafmark_storage_data:/data \
  -v $BACKUP_DIR:/backup \
  alpine tar czf /backup/storage-backup-${TIMESTAMP}.tar.gz -C /data .

echo "Backup created: $TIMESTAMP"
```

**Restoring from Backup:**

```bash
# Stop application
cd ~/leafmark/app-source
docker compose down

# Restore database (replace TIMESTAMP with your backup timestamp)
TIMESTAMP=20260110_120000
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

## Database

The application uses **MariaDB 11** (MySQL compatible) for production:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=leafmark
DB_USERNAME=leafmark
DB_PASSWORD=your_secure_password
```

MariaDB was chosen for:
- Multi-user support with concurrent access
- Better performance at scale
- Advanced features (triggers, stored procedures)
- Full MySQL compatibility

## Environment Variables

Core environment variables (set in docker-compose.yaml or via docker-entrypoint.sh):

```env
# Application
APP_NAME=Leafmark
APP_ENV=production
APP_KEY=base64:...  # Auto-generated
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (MariaDB)
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=leafmark
DB_USERNAME=leafmark
DB_PASSWORD=secure_password_here
MYSQL_ROOT_PASSWORD=root_password_here

# Optional: API Keys
GOOGLE_BOOKS_API_KEY=your_key_here
```

See `.env.example` for all available options.

## Admin Features

### User Management (`/admin/users`)
- View all registered users
- Grant or revoke admin privileges
- Delete users (except yourself)
- See user statistics (book count, join date)

### System Settings (`/admin/settings`)
- **Registration Control:**
  - Enable/disable registration completely
  - Choose registration mode
  - Configure allowed email domains
  - Set registration code

- **Registration Modes:**
  1. **Open** - Anyone can register
  2. **Domain-restricted** - Only specific email domains (e.g., @company.com)
  3. **Invitation-only** - Admins must send invitation links
  4. **Code-required** - Users need a registration code

### Invitation System (`/admin/settings#invitations`)
- Create invitations for specific email addresses
- Copy invitation links to share
- Track invitation status (pending/used/expired)
- Delete unused invitations
- Invitations expire after 7 days

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
