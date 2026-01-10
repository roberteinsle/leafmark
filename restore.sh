#!/bin/bash

###############################################################################
# Leafmark Restore Script
# Restores database and uploaded files from backup
#
# Usage: ./restore.sh [TIMESTAMP]
# Example: ./restore.sh 20260110_120000
#
# If no timestamp is provided, lists available backups
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

BACKUP_DIR=~/leafmark/backups
TIMESTAMP=$1

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Leafmark Restore Script${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if backup directory exists
if [ ! -d "$BACKUP_DIR" ]; then
    echo -e "${RED}✗ Backup directory does not exist: $BACKUP_DIR${NC}"
    exit 1
fi

# If no timestamp provided, list available backups
if [ -z "$TIMESTAMP" ]; then
    echo -e "${YELLOW}Available backups:${NC}"
    echo ""
    cd $BACKUP_DIR
    if ls db-backup-*.tar.gz >/dev/null 2>&1; then
        for backup in $(ls -t db-backup-*.tar.gz | head -10); do
            timestamp=$(echo $backup | sed 's/db-backup-\(.*\)\.tar\.gz/\1/')
            size=$(ls -lh $backup | awk '{print $5}')
            date_formatted=$(echo $timestamp | sed 's/\([0-9]\{4\}\)\([0-9]\{2\}\)\([0-9]\{2\}\)_\([0-9]\{2\}\)\([0-9]\{2\}\)\([0-9]\{2\}\)/\1-\2-\3 \4:\5:\6/')
            echo -e "  ${GREEN}$timestamp${NC} - $date_formatted ($size)"
        done
    else
        echo -e "${YELLOW}  No backups found${NC}"
    fi
    echo ""
    echo -e "${YELLOW}Usage: ./restore.sh TIMESTAMP${NC}"
    echo -e "Example: ./restore.sh 20260110_120000"
    exit 0
fi

# Check if backup files exist
DB_BACKUP="$BACKUP_DIR/db-backup-${TIMESTAMP}.tar.gz"
STORAGE_BACKUP="$BACKUP_DIR/storage-backup-${TIMESTAMP}.tar.gz"

if [ ! -f "$DB_BACKUP" ]; then
    echo -e "${RED}✗ Database backup not found: $DB_BACKUP${NC}"
    exit 1
fi

if [ ! -f "$STORAGE_BACKUP" ]; then
    echo -e "${YELLOW}⚠ Storage backup not found: $STORAGE_BACKUP${NC}"
    echo -e "${YELLOW}  Only database will be restored${NC}"
fi

# Confirm restore
echo -e "${YELLOW}⚠ WARNING: This will replace current data with backup from:${NC}"
echo -e "  ${timestamp}"
echo ""
read -p "Are you sure you want to continue? (yes/no): " -r
echo
if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    echo -e "${YELLOW}Restore cancelled${NC}"
    exit 0
fi

# Stop application
echo -e "${YELLOW}→ Stopping application...${NC}"
cd ~/leafmark/app-source
docker compose down || echo -e "${YELLOW}⚠ Container not running${NC}"
echo -e "${GREEN}✓ Application stopped${NC}"
echo ""

# Restore database
echo -e "${YELLOW}→ Restoring database...${NC}"
docker run --rm \
  -v app-source_sqlite_data:/data \
  -v $BACKUP_DIR:/backup \
  alpine sh -c "rm -rf /data/* && tar xzf /backup/db-backup-${TIMESTAMP}.tar.gz -C /data" \
  || { echo -e "${RED}✗ Database restore failed${NC}"; exit 1; }
echo -e "${GREEN}✓ Database restored${NC}"

# Restore storage if backup exists
if [ -f "$STORAGE_BACKUP" ]; then
    echo -e "${YELLOW}→ Restoring uploaded files...${NC}"
    docker run --rm \
      -v app-source_storage_data:/data \
      -v $BACKUP_DIR:/backup \
      alpine sh -c "rm -rf /data/* && tar xzf /backup/storage-backup-${TIMESTAMP}.tar.gz -C /data" \
      || { echo -e "${RED}✗ Storage restore failed${NC}"; exit 1; }
    echo -e "${GREEN}✓ Storage restored${NC}"
fi

# Start application
echo -e "${YELLOW}→ Starting application...${NC}"
docker compose up -d
echo -e "${GREEN}✓ Application started${NC}"
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✓ Restore completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
