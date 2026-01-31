# 📚 Leafmark - Personal Book Tracking Web App

A Laravel-based web application for tracking your personal book collection and reading progress.

**🌐 Live Demo:** [www.leafmark.app](https://www.leafmark.app)

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
- **Deployment:** Docker / Coolify

## Quick Start

### Option 1: GitHub Codespaces (Recommended for Development)

1. Click **Code** → **Codespaces** → **Create codespace on main**
2. Wait for the environment to build (~2 min)
3. The app starts automatically at port 8000

See [CODESPACES.md](CODESPACES.md) for details.

### Option 2: Local Development with Docker

```bash
# Clone repository
git clone https://github.com/roberteinsle/leafmark.git
cd leafmark

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

### Option 3: Self-Hosted with Coolify

See [DEPLOY.md](DEPLOY.md) for detailed deployment instructions.

## Environment Variables

| Variable | Description | Required |
|----------|-------------|----------|
| `APP_KEY` | Laravel application key | Yes |
| `DB_PASSWORD` | Database password | Yes |
| `MYSQL_ROOT_PASSWORD` | MySQL root password | Yes |
| `GOOGLE_BOOKS_API_KEY` | Google Books API key | No |
| `ISBNDB_API_KEY` | ISBNdb API key | No |

## Development with Claude Code

This project supports development with [Claude Code](https://docs.anthropic.com/en/docs/claude-code). See [CLAUDE.md](CLAUDE.md) for project context and conventions.

```bash
# In Codespaces or local terminal
npm install -g @anthropic-ai/claude-code
claude
```

## Contributing

Contributions are welcome! Please open an issue or submit a pull request.

## License

MIT License - see [LICENSE](LICENSE) for details.
