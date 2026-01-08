# Leafmark - GitHub Codespaces Setup

## Quick Start

1. **Open in Codespaces**
   - Go to https://github.com/roberteinsle/leafmark
   - Click "Code" → "Codespaces" → "Create codespace on main"
   - Wait for the container to build (first time takes ~2-3 minutes)

2. **Setup the application**
   ```bash
   chmod +x setup.sh
   ./setup.sh
   ```

3. **Start the server**
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

4. **Access the app**
   - Click on the "Ports" tab in VS Code
   - Click on the globe icon next to port 8000
   - The app will open in your browser

## Manual Setup (if needed)

```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database (already in .env.example)
DB_HOST=127.0.0.1
DB_DATABASE=leafmark
DB_USERNAME=leafmark
DB_PASSWORD=leafmark

# Run migrations
php artisan migrate

# Start server
php artisan serve --host=0.0.0.0 --port=8000
```

## Database Access

The MariaDB database runs in the same container:
- Host: 127.0.0.1
- Port: 3306
- Database: leafmark
- Username: leafmark
- Password: leafmark
- Root password: root

## Development

```bash
# Watch Tailwind CSS changes (if needed later)
npm install
npm run dev

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Features Implemented

✅ User Authentication (Register/Login)
✅ Books CRUD (Create, Read, Update, Delete)
✅ Reading Progress Tracking
✅ Status Management (Want to Read, Currently Reading, Read)
✅ Filtering by Status

🚧 Shelves Management (Coming soon)
🚧 Book Search APIs (Coming soon)
