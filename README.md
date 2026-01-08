# Leafmark - Personal Book Tracking Web App

A Laravel-based web application for tracking your personal book collection and reading progress.

## Features

- User Authentication
- Book Management (Add, Edit, Delete)
- Reading Status (Want to Read, Currently Reading, Read)
- Custom Shelves
- Book Search Integration (Google Books, Open Library, ISBNdb)
- Multi-language Support (English & German)

## Tech Stack

- **Backend:** Laravel 11 + PHP 8.2
- **Database:** MariaDB 11
- **Frontend:** Blade Templates + Tailwind CSS
- **Deployment:** Docker Compose

## Deployment with Docker

This project is configured for deployment using Docker Compose.

### Environment Variables

Configure these environment variables:

- `APP_KEY` - Laravel application key (generate with `php artisan key:generate`)
- `DB_PASSWORD` - Database password
- `MYSQL_ROOT_PASSWORD` - MySQL root password
- `GOOGLE_BOOKS_API_KEY` - (Optional) Google Books API key
- `ISBNDB_API_KEY` - (Optional) ISBNdb API key

### Docker Setup

- Port: **80** (mapped to host)
- Services: `app` (Laravel + Apache), `db` (MariaDB)
- Persistent storage: Database volume mounted for data persistence

## Local Development

```bash
# Copy environment file
cp .env.example .env

# Start services
docker-compose up -d

# Generate app key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Access the app
open http://localhost
```

## License

MIT License
