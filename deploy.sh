#!/bin/bash

###############################################################################
# Leafmark Deployment Script
# Server: Hetzner (65.108.241.237)
# User: deploy
#
# Usage: ./deploy.sh
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Leafmark Deployment Script${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Change to application directory
APP_DIR=~/leafmark/app-source
echo -e "${YELLOW}→ Changing to application directory...${NC}"
cd $APP_DIR || { echo -e "${RED}✗ Failed to change to $APP_DIR${NC}"; exit 1; }
echo -e "${GREEN}✓ Current directory: $(pwd)${NC}"
echo ""

# Pull latest changes from Git
echo -e "${YELLOW}→ Pulling latest changes from GitHub...${NC}"
git pull origin main || { echo -e "${RED}✗ Git pull failed${NC}"; exit 1; }
echo -e "${GREEN}✓ Git pull completed${NC}"
echo ""

# Stop running containers
echo -e "${YELLOW}→ Stopping Docker containers...${NC}"
docker compose down || { echo -e "${RED}✗ Failed to stop containers${NC}"; exit 1; }
echo -e "${GREEN}✓ Containers stopped${NC}"
echo ""

# Build and start containers
echo -e "${YELLOW}→ Building and starting Docker containers...${NC}"
docker compose up -d --build || { echo -e "${RED}✗ Failed to build/start containers${NC}"; exit 1; }
echo -e "${GREEN}✓ Containers started${NC}"
echo ""

# Wait for containers to be ready
echo -e "${YELLOW}→ Waiting for containers to be ready (5 seconds)...${NC}"
sleep 5
echo -e "${GREEN}✓ Ready${NC}"
echo ""

# Run database migrations
echo -e "${YELLOW}→ Running database migrations...${NC}"
docker compose exec -T app php artisan migrate --force || { echo -e "${RED}✗ Migrations failed${NC}"; exit 1; }
echo -e "${GREEN}✓ Migrations completed${NC}"
echo ""

# Clear and cache configuration
echo -e "${YELLOW}→ Caching configuration...${NC}"
docker compose exec -T app php artisan config:cache || { echo -e "${RED}✗ Config cache failed${NC}"; exit 1; }
echo -e "${GREEN}✓ Configuration cached${NC}"
echo ""

# Cache routes
echo -e "${YELLOW}→ Caching routes...${NC}"
docker compose exec -T app php artisan route:cache || { echo -e "${RED}✗ Route cache failed${NC}"; exit 1; }
echo -e "${GREEN}✓ Routes cached${NC}"
echo ""

# Show container status
echo -e "${YELLOW}→ Container status:${NC}"
docker compose ps
echo ""

# Deployment complete
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✓ Deployment completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "Deployed at: $(date)"
echo ""
