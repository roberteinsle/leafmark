# 🍃 Leafmark

**Your personal book tracking web app**

Leafmark helps you organize your books and keep track of your reading progress. No more forgotten books or lost page counts.

## ✨ Features

- 📖 **Manage books** – Add, edit, and organize your personal library
- 📊 **Track reading progress** – Log which page you're on
- 📚 **Shelves & lists** – Sort books into "Read", "Currently Reading", and "Want to Read"
- 🔍 **Book search** – Find books quickly by ISBN or title
- 📈 **Statistics** – Get insights into your reading habits (coming soon)
- 🎯 **Reading goals** – Set yearly goals and track your progress (coming soon)

## 🚀 Tech Stack

- **Frontend:** React 18 + TypeScript + Vite + Tailwind CSS
- **Backend:** Node.js + Fastify + TypeScript
- **Database:** MariaDB
- **ORM:** Prisma
- **Authentication:** JWT (Email/Password)
- **Deployment:** Docker Compose on Coolify
- **API Integration:** Google Books, Open Library, ISBNdb (with fallback chain)

## 📁 Project Structure

This is a monorepo managed with npm workspaces:

```
leafmark/
├── apps/
│   ├── backend/          # Fastify API server
│   └── frontend/         # React application
├── packages/
│   └── shared/           # Shared types and constants
├── .github/workflows/    # CI/CD configuration
├── docker-compose.yml    # Local development
└── docker-compose.production.yml  # Production deployment
```

## 🛠️ Setup & Development

### Prerequisites

- Node.js 20 or higher
- Docker & Docker Compose
- Git

### Installation

1. Clone the repository:
```bash
git clone https://github.com/roberteinsle/leafmark.git
cd leafmark
```

2. Install dependencies:
```bash
npm install
```

3. Setup environment variables:
```bash
cp .env.example .env
# Edit .env with your configuration
```

4. Generate JWT secrets:
```bash
openssl rand -base64 32  # Use for JWT_SECRET
openssl rand -base64 32  # Use for JWT_REFRESH_SECRET
```

5. Start the database:
```bash
docker compose up db -d
```

6. Run database migrations:
```bash
npm run prisma:migrate
```

7. Seed the database (optional):
```bash
npm run prisma:seed
```

8. Start development servers:
```bash
npm run dev
```

This will start:
- Backend API: http://localhost:3001
- Frontend: http://localhost:5173
- Database: localhost:3306

## 🐳 Docker Development

Start all services with Docker Compose:

```bash
# Start all services
npm run docker:dev

# Start with rebuild
npm run docker:dev:build

# View logs
npm run docker:logs

# Stop all services
npm run docker:down
```

## 📝 Available Scripts

### Root
- `npm run dev` - Start both frontend and backend
- `npm run build` - Build all workspaces
- `npm run lint` - Lint all TypeScript files
- `npm run format` - Format code with Prettier

### Backend
- `npm run dev:backend` - Start backend dev server
- `npm run prisma:migrate` - Run database migrations
- `npm run prisma:studio` - Open Prisma Studio

### Frontend
- `npm run dev:frontend` - Start frontend dev server

## 🚢 Deployment

The application is configured for automatic deployment to Coolify:

1. Push to `main` branch triggers GitHub Actions
2. GitHub Actions connects to Coolify server via SSH
3. Docker images are built and deployed
4. Database migrations run automatically

### Required GitHub Secrets

- `COOLIFY_HOST` - Your Coolify server hostname/IP
- `COOLIFY_USER` - SSH username
- `COOLIFY_SSH_KEY` - Private SSH key

## 🌍 Internationalization

The application supports multiple languages:
- 🇬🇧 English
- 🇩🇪 German

More languages can be added easily via the i18n system.

## 🔒 Security

- JWT-based authentication
- bcrypt password hashing
- CORS protection
- Rate limiting
- Security headers (Helmet)
- SQL injection prevention (Prisma)
- Environment variable protection

## 📝 Roadmap

- [x] Basic book management
- [x] Book search via API (Google Books, Open Library, ISBNdb)
- [x] Shelves system
- [x] User authentication
- [ ] Reading progress tracking
- [ ] Statistics & yearly overview
- [ ] Export/Import functionality
- [ ] Mobile-optimized view
- [ ] Additional languages

## 🤝 Contributing

Contributions are welcome! Feel free to open an issue or submit a pull request.

## 📄 License

[MIT](LICENSE)

## 👤 Author

**Robert Einsle**
- Email: robert@einsle.com
- GitHub: [@roberteinsle](https://github.com/roberteinsle)

---

**Leafmark** – Because every book you read counts. 🍃
