#!/bin/bash

###############################################################################
# InstaPrint Automatic Backup Script
# This script backs up the entire Laravel application and database to USB
###############################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/html/laravel"
BACKUP_LABEL="INSTAPRINT_BACKU"
BACKUP_DIR="/mnt/instaprint_backup"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║         InstaPrint Automatic Backup System                ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Find USB drive
echo -e "${YELLOW}[1/6] Detecting USB drive...${NC}"
USB_DEVICE=$(lsblk -o NAME,LABEL | grep "$BACKUP_LABEL" | awk '{print $1}' | head -1)

if [ -z "$USB_DEVICE" ]; then
    echo -e "${RED}❌ Error: USB drive with label '$BACKUP_LABEL' not found!${NC}"
    echo -e "${YELLOW}Please insert the backup USB drive.${NC}"
    exit 1
fi

# Remove partition number to get device name
USB_DEVICE=$(echo "$USB_DEVICE" | sed 's/[└─├─│]//g' | sed 's/[0-9]*$//')
USB_PARTITION="/dev/${USB_DEVICE}1"

echo -e "${GREEN}✓ Found USB drive: $USB_PARTITION${NC}"

# Create mount point if it doesn't exist
echo -e "${YELLOW}[2/6] Preparing mount point...${NC}"
sudo mkdir -p "$BACKUP_DIR"

# Mount USB drive
echo -e "${YELLOW}[3/6] Mounting USB drive...${NC}"
if mountpoint -q "$BACKUP_DIR"; then
    echo -e "${YELLOW}USB already mounted at $BACKUP_DIR${NC}"
else
    sudo mount "$USB_PARTITION" "$BACKUP_DIR"
    echo -e "${GREEN}✓ Mounted USB drive${NC}"
fi

# Create backup directory structure
BACKUP_PATH="$BACKUP_DIR/instaprint_backup_$TIMESTAMP"
sudo mkdir -p "$BACKUP_PATH"

echo -e "${YELLOW}[4/6] Backing up application files...${NC}"
# Backup entire Laravel application
sudo rsync -av --progress \
    --exclude 'node_modules' \
    --exclude 'vendor' \
    --exclude 'storage/logs/*' \
    --exclude 'storage/framework/cache/*' \
    --exclude 'storage/framework/sessions/*' \
    --exclude 'storage/framework/views/*' \
    "$APP_DIR/" "$BACKUP_PATH/laravel/"

echo -e "${GREEN}✓ Application files backed up${NC}"

echo -e "${YELLOW}[5/6] Backing up database...${NC}"
# Backup SQLite database
if [ -f "$APP_DIR/database/database.sqlite" ]; then
    sudo cp "$APP_DIR/database/database.sqlite" "$BACKUP_PATH/database.sqlite"
    echo -e "${GREEN}✓ Database backed up${NC}"
else
    echo -e "${YELLOW}⚠ Database file not found, skipping...${NC}"
fi

# Create a restore script on the USB drive
echo -e "${YELLOW}[6/6] Creating restore script...${NC}"
cat > "$BACKUP_DIR/RESTORE.sh" << 'EOF'
#!/bin/bash

###############################################################################
# InstaPrint Automatic Restore Script
# This script restores the Laravel application from backup
###############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

APP_DIR="/var/www/html/laravel"
BACKUP_DIR="/mnt/instaprint_backup"

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║         InstaPrint Automatic Restore System               ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Find latest backup
LATEST_BACKUP=$(ls -dt "$BACKUP_DIR"/instaprint_backup_* 2>/dev/null | head -1)

if [ -z "$LATEST_BACKUP" ]; then
    echo -e "${RED}❌ Error: No backup found on USB drive!${NC}"
    exit 1
fi

echo -e "${GREEN}Found backup: $(basename $LATEST_BACKUP)${NC}"
echo ""
echo -e "${YELLOW}⚠️  WARNING: This will replace your current system!${NC}"
echo -e "${YELLOW}Press ENTER to continue or Ctrl+C to cancel...${NC}"
read

echo -e "${YELLOW}[1/5] Stopping services...${NC}"
sudo systemctl stop laravel-server 2>/dev/null || true
sudo pkill -f "php artisan serve" 2>/dev/null || true
sleep 2

echo -e "${YELLOW}[2/5] Backing up current system...${NC}"
if [ -d "$APP_DIR" ]; then
    sudo mv "$APP_DIR" "${APP_DIR}_backup_$(date +%Y%m%d_%H%M%S)"
fi

echo -e "${YELLOW}[3/5] Restoring application files...${NC}"
sudo mkdir -p "$APP_DIR"
sudo rsync -av "$LATEST_BACKUP/laravel/" "$APP_DIR/"

echo -e "${YELLOW}[4/5] Restoring database...${NC}"
if [ -f "$LATEST_BACKUP/database.sqlite" ]; then
    sudo cp "$LATEST_BACKUP/database.sqlite" "$APP_DIR/database/database.sqlite"
fi

echo -e "${YELLOW}[5/5] Setting permissions...${NC}"
sudo chown -R www-data:www-data "$APP_DIR"
sudo chmod -R 755 "$APP_DIR"
sudo chmod -R 775 "$APP_DIR/storage"
sudo chmod -R 775 "$APP_DIR/bootstrap/cache"

echo -e "${GREEN}✓ Restore completed successfully!${NC}"
echo ""
echo -e "${YELLOW}Restarting services...${NC}"
cd "$APP_DIR"
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan route:clear

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  System restored successfully! You can now restart server ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
EOF

sudo chmod +x "$BACKUP_DIR/RESTORE.sh"

# Create metadata file
cat > "$BACKUP_PATH/backup_info.txt" << EOF
InstaPrint Backup Information
==============================
Backup Date: $(date)
Backup Location: $BACKUP_PATH
Application Directory: $APP_DIR

Contents:
- Laravel Application Files
- SQLite Database
- Configuration Files

To restore this backup:
1. Insert this USB drive
2. Run: sudo bash /mnt/instaprint_backup/RESTORE.sh

Or use the auto-restore feature by creating udev rule.
EOF

# Sync and unmount
echo -e "${YELLOW}Syncing data to USB drive...${NC}"
sudo sync

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║            Backup Completed Successfully!                  ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}Backup Details:${NC}"
echo -e "  Location: $BACKUP_PATH"
echo -e "  Size: $(du -sh $BACKUP_PATH | cut -f1)"
echo ""
echo -e "${YELLOW}To restore from this backup:${NC}"
echo -e "  1. Insert this USB drive"
echo -e "  2. Run: ${GREEN}sudo bash $BACKUP_DIR/RESTORE.sh${NC}"
echo ""
echo -e "${YELLOW}Keep the USB drive inserted to set up auto-restore on plugin...${NC}"
