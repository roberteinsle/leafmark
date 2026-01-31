#!/bin/bash
set -e

echo "🚀 Setting up Leafmark development environment..."

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-interaction

# Copy environment file if it doesn't exist
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Wait for MariaDB to be ready
echo "⏳ Waiting for MariaDB to be ready..."
until php artisan db:monitor --database=mysql > /dev/null 2>&1; do
    echo "   Waiting for database connection..."
    sleep 2
done
echo "✅ MariaDB is ready!"

# Run migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Create storage symlink
echo "🔗 Creating storage symlink..."
php artisan storage:link

# Install Claude Code CLI
echo "🤖 Installing Claude Code CLI..."
npm install -g @anthropic-ai/claude-code

echo ""
echo "✅ Setup complete! You can now:"
echo "   - Start dev server: php artisan serve"
echo "   - Run tests: php artisan test"
echo "   - Use Claude Code: claude"
echo ""
