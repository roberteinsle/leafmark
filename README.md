# 📚 Leafmark - Personal Book Tracking Web App

A Laravel-based web application for tracking your personal book collection and reading progress.

**🌐 Live Demo:** [www.leafmark.app](https://www.leafmark.app)

## Features

- User Authentication
- Book Management (Add, Edit, Delete)
- Reading Status (Want to Read, Currently Reading, Read)
- Custom Tags for Organization
- Book Search Integration (Google Books, Open Library, BookBrainz, Big Book API)
- CSV Import (Goodreads)
- Reading Challenges & Progress Tracking
- Multi-user Support with Admin Controls
- Family Accounts
- Configurable Language (de, en, fr, it, es, pl)

## Tech Stack

- **Backend:** Laravel 11 + PHP 8.2
- **Database:** SQLite
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

# Generate app key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Start development server
php artisan serve

# Access the app
open http://localhost:8000
```

### Option 3: Self-Hosted with Coolify

See [DEPLOY.md](DEPLOY.md) for detailed deployment instructions.

## Environment Variables

| Variable | Description | Required |
|----------|-------------|----------|
| `APP_KEY` | Laravel application key | Yes |
| `APP_LOCALE` | Application language (de, en, fr, it, es, pl) | Yes |
| `GOOGLE_BOOKS_API_KEY` | Google Books API key | No |
| `BIGBOOK_API_KEY` | Big Book API key | No |
| `MAIL_*` | SMTP configuration for email | No |
| `TURNSTILE_*` | Cloudflare Turnstile CAPTCHA | No |

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
