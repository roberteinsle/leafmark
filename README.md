# Leafmark - Multi-User Book Tracking Web App

A Laravel-based web application for tracking book collections and reading progress. Supports multi-user environments with admin controls for organizations, families, or communities.

## Features

### 📚 Book Management
- Add, edit, and organize your book collection
- Import from multiple sources: Google Books, Open Library, BookBrainz, Big Book API
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
  - Code-required (personal registration code)
- User management with admin role assignment
- Family accounts for grouping users

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

### Optional: Configure API Keys

After registering, you can optionally configure API keys for enhanced book search:
- Go to **Admin → System Settings** in the app
- **Google Books API**: Add your Google Books API key for better metadata
- **Big Book API**: Add your Big Book API key (free tier: 60 requests/minute)
- These API keys are used globally for all users

Open Library and BookBrainz work without API keys, so this is completely optional.

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
   - Code-required (personal registration code)
5. Configure allowed domains or registration code as needed

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
  3. **Code-required** - Users need a registration code

- **API Configuration:**
  - Configure Google Books API key for enhanced book search
  - Configure Big Book API key for comprehensive book data (free tier available)
  - Applies to all users system-wide
  - Optional - Open Library and BookBrainz work without API keys

- **SMTP Configuration:**
  - Configure email settings for password reset and verification
  - Test email functionality
  - View email sending logs

- **Email Logs** (`/admin/email-logs`):
  - Track all sent emails (verification, password reset)
  - Debug email delivery issues
  - View email content and error messages

## Features in Detail

### Smart Book Search

The search automatically detects:
- **ISBN** (10 or 13 digits): `3551354030` → searches by ISBN
- **Author names**: `Stephen King` → searches by author
- **Book titles**: `harry potter` → searches by title

### Multi-source Search

Choose your search provider:
- **Open Library** - Free, no API key required, language-aware
- **BookBrainz** - Free, community-driven database, language-aware
- **Big Book API** - Comprehensive data, requires API key (free tier: 60 req/min), no language filtering
- **Google Books** - Requires API key, extensive metadata, language-aware
- **All Sources** - Searches all configured sources and merges results

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
