# Production Deployment Guide

## Data Persistence

This application uses Docker volumes to ensure all data persists across container updates:

### Persistent Volumes

1. **`db_data`** - MySQL/MariaDB database
   - Location: `/var/lib/mysql` in container
   - Contains: All books, users, shelves, tags, and reading progress data

2. **`storage_data`** - Application storage
   - Location: `/var/www/html/storage/app` in container
   - Contains: Cover images, uploaded files, and other application data

3. **`vendor`** - Composer dependencies
   - Location: `/var/www/html/vendor` in container
   - Contains: PHP dependencies (speeds up container startup)

### Updating the Application

When you update the application, all data will be preserved:

```bash
# Pull latest code
git pull

# Rebuild and restart containers
docker-compose down
docker-compose up -d --build

# Run migrations (if needed)
docker-compose exec app php artisan migrate --force
```

The volumes (`db_data` and `storage_data`) are **NOT** deleted when you run `docker-compose down`, ensuring your data persists.

### Backup Strategy

To backup your data:

```bash
# Backup database
docker-compose exec db mysqldump -u root -p${MYSQL_ROOT_PASSWORD} leafmark > backup-$(date +%Y%m%d).sql

# Backup uploaded files (covers, etc.)
docker run --rm -v leafmark_storage_data:/data -v $(pwd):/backup alpine tar czf /backup/storage-backup-$(date +%Y%m%d).tar.gz -C /data .
```

### Restore from Backup

To restore data:

```bash
# Restore database
docker-compose exec -T db mysql -u root -p${MYSQL_ROOT_PASSWORD} leafmark < backup-20260109.sql

# Restore uploaded files
docker run --rm -v leafmark_storage_data:/data -v $(pwd):/backup alpine tar xzf /backup/storage-backup-20260109.tar.gz -C /data
```

### Important Notes

- Never delete Docker volumes unless you want to lose data
- Always backup before major updates
- The `.:/var/www/html` mount in development should be removed in production for better security
- Consider using named volumes with specific backup schedules in production

## Environment Variables

Required environment variables (set in `.env`):

- `APP_KEY` - Generate with `php artisan key:generate`
- `MYSQL_ROOT_PASSWORD` - Database root password
- `DB_PASSWORD` - Database user password (optional, root is used by default)
- `DB_DATABASE` - Database name (default: leafmark)

Optional:
- `APP_URL` - Your application URL
- `APP_DEBUG` - Set to `false` in production

## First-Time Setup

1. Copy `.env.example` to `.env` and fill in the required values
2. Generate application key: `docker-compose exec app php artisan key:generate`
3. Run migrations: `docker-compose exec app php artisan migrate --force`
4. Create storage symlink: `docker-compose exec app php artisan storage:link`
5. Create first user through registration page

## API Keys

Google Books API Key should be configured per user in the application settings (not in `.env`).
