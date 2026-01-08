#!/bin/bash

echo "🚀 Setting up Leafmark..."

# Install dependencies
echo "📦 Installing Composer dependencies..."
composer install

# Setup environment
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Update database config for Codespaces
echo "🔧 Configuring database..."
sed -i 's/DB_HOST=.*/DB_HOST=127.0.0.1/' .env
sed -i 's/DB_DATABASE=.*/DB_DATABASE=leafmark/' .env
sed -i 's/DB_USERNAME=.*/DB_USERNAME=leafmark/' .env
sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=leafmark/' .env

# Wait for database
echo "⏳ Waiting for database..."
sleep 5

# Run migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

echo "✅ Setup complete!"
echo ""
echo "To start the server, run:"
echo "  php artisan serve --host=0.0.0.0 --port=8000"
