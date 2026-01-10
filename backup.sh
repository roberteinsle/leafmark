#!/bin/bash

###############################################################################
# Leafmark Backup Script
# Creates a backup of the database and uploaded files
#
# Usage: ./backup.sh
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

BACKUP_DIR=~/leafmark/backups
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Leafmark Backup Script${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Backup SQLite database
echo -e "${YELLOW}→ Backing up database...${NC}"
docker run --rm \
  -v app-source_sqlite_data:/data \
  -v $BACKUP_DIR:/backup \
  alpine \
  tar czf /backup/db-backup-${TIMESTAMP}.tar.gz -C /data . \
  || { echo -e "${RED}✗ Database backup failed${NC}"; exit 1; }
echo -e "${GREEN}✓ Database backed up to: $BACKUP_DIR/db-backup-${TIMESTAMP}.tar.gz${NC}"

# Backup uploaded files (covers)
echo -e "${YELLOW}→ Backing up uploaded files...${NC}"
docker run --rm \
  -v app-source_storage_data:/data \
  -v $BACKUP_DIR:/backup \
  alpine \
  tar czf /backup/storage-backup-${TIMESTAMP}.tar.gz -C /data . \
  || { echo -e "${RED}✗ Storage backup failed${NC}"; exit 1; }
echo -e "${GREEN}✓ Storage backed up to: $BACKUP_DIR/storage-backup-${TIMESTAMP}.tar.gz${NC}"

# Keep only last 7 backups
echo -e "${YELLOW}→ Cleaning old backups (keeping last 7)...${NC}"
cd $BACKUP_DIR
ls -t db-backup-*.tar.gz | tail -n +8 | xargs -r rm
ls -t storage-backup-*.tar.gz | tail -n +8 | xargs -r rm
echo -e "${GREEN}✓ Old backups cleaned${NC}"

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✓ Backup completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "Backup timestamp: ${TIMESTAMP}"
echo -e "Backup location: ${BACKUP_DIR}"
echo ""
