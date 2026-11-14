#!/bin/bash
set -e

echo "🚀 Setting up Leafmark development environment..."

# PHP-Erweiterungen, die das Devcontainer-Image nicht mitbringt
if ! php -m | grep -qi pdo_pgsql || ! php -m | grep -qi '^zip$'; then
    echo "📦 Installing PHP extensions (pdo_pgsql, zip, intl, gd)..."
    sudo apt-get update -qq
    sudo apt-get install -y -qq libpq-dev libzip-dev libicu-dev libpng-dev postgresql-client
    sudo docker-php-ext-install pdo_pgsql zip intl gd
fi

composer install --no-interaction
npm ci

if [ ! -f .env ]; then
    cp .env.example .env
fi
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate
fi

echo "⏳ Starting Postgres..."
docker compose up -d db
until docker compose exec -T db pg_isready -U leafmark > /dev/null 2>&1; do
    sleep 2
done

php artisan migrate --force
php artisan storage:link || true

npm install -g @anthropic-ai/claude-code

echo ""
echo "✅ Setup complete:"
echo "   - Dev server: php artisan serve   (plus: npm run dev)"
echo "   - Tests:      php artisan test"
