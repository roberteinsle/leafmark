#!/bin/bash
set -e

# Always recreate .env file from environment variables
echo "Creating .env file from environment variables..."
cat > /var/www/html/.env << ENVFILE
APP_NAME="${APP_NAME:-Laravel}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST="${DB_HOST:-db}"
DB_PORT=3306
DB_DATABASE="${DB_DATABASE:-leafmark}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${MYSQL_ROOT_PASSWORD}"

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

GOOGLE_BOOKS_API_KEY="${GOOGLE_BOOKS_API_KEY}"
ISBNDB_API_KEY="${ISBNDB_API_KEY}"
ENVFILE

# Fix permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear config cache to ensure new .env is loaded
php artisan config:clear

# Wait for database to be ready
echo "Waiting for database..."
until php -r "new PDO('mysql:host=db;dbname=${DB_DATABASE:-leafmark}', 'root', '${MYSQL_ROOT_PASSWORD}');" 2>/dev/null; do
    echo "Database is unavailable - sleeping"
    sleep 2
done
echo "Database is up - continuing"

# Run migrations
php artisan migrate --force || echo "Migration failed, continuing..."

# Cache config
php artisan config:cache

# Start Apache
exec apache2-foreground
