# Deployment Guide

This guide covers deploying Leafmark to production using Coolify on a self-hosted server.

## Prerequisites

- A server (VPS) with Docker installed
- Coolify installed and configured
- A domain name (e.g., `leafmark.app`)
- Cloudflare account (optional, for CDN/SSL)

## Architecture

```
User → Cloudflare (CDN/SSL) → Server → Coolify/Traefik → Leafmark Container
                                              ↓
                                         MariaDB Container
```

## Deployment with Coolify

### 1. Add Project in Coolify

1. Go to **Projects** → **Add Project**
2. Name: `Leafmark`
3. Click **Add Resource** → **Public Repository**

### 2. Configure Repository

- **Repository URL:** `https://github.com/roberteinsle/leafmark`
- **Branch:** `main`
- **Build Pack:** `Dockerfile` (auto-detected)

### 3. Environment Variables

Add these environment variables in Coolify:

```env
APP_NAME=Leafmark
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.leafmark.app

DB_CONNECTION=mysql
DB_HOST=leafmark-db
DB_PORT=3306
DB_DATABASE=leafmark
DB_USERNAME=leafmark
DB_PASSWORD=<secure-password>

# Optional API Keys
GOOGLE_BOOKS_API_KEY=<your-key>
ISBNDB_API_KEY=<your-key>
```

### 4. Database Service

Add a MariaDB service in the same project:

1. **Add Resource** → **Database** → **MariaDB**
2. Name: `leafmark-db`
3. Configure credentials to match your environment variables

### 5. Domain Configuration

- **Domain:** `www.leafmark.app`
- **Proxy:** Select `Cloudflare` if using Cloudflare

### 6. Deploy

Click **Deploy** – Coolify will:
1. Clone the repository
2. Build the Docker image
3. Start the container
4. Configure the reverse proxy

## DNS Configuration (Cloudflare)

Add these DNS records:

| Type | Name | Content | Proxy |
|------|------|---------|-------|
| A | @ | `<server-ip>` | Proxied (orange) |
| CNAME | www | `leafmark.app` | Proxied (orange) |

### Redirect apex to www

In Cloudflare **Rules** → **Redirect Rules**:

- **When:** Hostname equals `leafmark.app`
- **Then:** Redirect to `https://www.leafmark.app` (301)

## SSL Configuration

### With Cloudflare (Recommended)

1. In Coolify, select **Cloudflare** as proxy type
2. In Cloudflare **SSL/TLS** settings, set to **Full (strict)**

### Without Cloudflare

Coolify will automatically generate Let's Encrypt certificates.

## Post-Deployment

### Run Migrations

In Coolify, use the **Terminal** tab or SSH into the container:

```bash
docker exec -it <container-id> php artisan migrate --force
```

### Generate App Key (if not set)

```bash
docker exec -it <container-id> php artisan key:generate
```

### Clear Caches

```bash
docker exec -it <container-id> php artisan config:cache
docker exec -it <container-id> php artisan route:cache
docker exec -it <container-id> php artisan view:cache
```

## Automatic Deployments

Coolify automatically sets up a webhook. Every push to `main` triggers a new deployment.

To disable auto-deploy:
1. Go to your resource in Coolify
2. **Settings** → Disable **Auto Deploy**

## Monitoring

### Logs

In Coolify: **Resources** → **Leafmark** → **Logs**

Or via SSH:
```bash
docker logs -f <container-id>
```

### Health Check

The app exposes a health endpoint:
```bash
curl https://www.leafmark.app/health
```

## Backup

### Database Backup

```bash
docker exec leafmark-db mysqldump -u leafmark -p leafmark > backup.sql
```

### Automated Backups with Coolify

1. Go to **S3 Storages** in Coolify
2. Configure your S3-compatible storage
3. Enable backups for your database service

## Troubleshooting

### Container won't start

Check logs in Coolify or:
```bash
docker logs <container-id>
```

### 502 Bad Gateway

- Check if the container is running
- Verify the port configuration
- Check Traefik logs: `docker logs coolify-proxy`

### Database connection failed

- Verify DB_HOST matches the database container name
- Check if database container is running
- Verify credentials match

## Useful Commands

```bash
# Enter container shell
docker exec -it <container-id> bash

# Run artisan commands
docker exec -it <container-id> php artisan <command>

# View real-time logs
docker logs -f <container-id>

# Restart container
docker restart <container-id>
```
