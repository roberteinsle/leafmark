#!/bin/bash
set -e

# Always recreate .env file from environment variables
echo "Creating .env file from environment variables..."
cat > /var/www/html/.env << ENVFILE
APP_NAME="${APP_NAME:-Leafmark}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"
APP_LOCALE="${APP_LOCALE:-en}"

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=sqlite

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# API Keys
GOOGLE_BOOKS_API_KEY="${GOOGLE_BOOKS_API_KEY:-}"
BIGBOOK_API_KEY="${BIGBOOK_API_KEY:-}"

# Admin Contact
ADMIN_EMAIL="${ADMIN_EMAIL:-}"
ENVFILE

# Verify artisan exists
if [ ! -f /var/www/html/artisan ]; then
    echo "ERROR: artisan file not found!"
    ls -la /var/www/html/
    exit 1
fi

# Create database directory if it doesn't exist
mkdir -p /var/www/html/database

# Create SQLite database file if using SQLite
if [ "${DB_CONNECTION}" = "sqlite" ]; then
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        echo "Creating SQLite database file..."
        touch /var/www/html/database/database.sqlite
    fi
fi

# Fix permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Clear config cache to ensure new .env is loaded
php artisan config:clear || echo "Config clear failed, continuing..."

# Run migrations
echo "Running database migrations..."
php artisan migrate --force || echo "Migration failed, continuing..."

# Cache config
php artisan config:cache

# Start Apache
echo "Starting Apache..."
exec apache2-foreground
