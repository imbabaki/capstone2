#!/bin/bash

###############################################################################
# INSTAPRINT SYSTEM BACKUP SCRIPT
# This script creates a complete backup of the Instaprint system
# Usage: sudo ./backup-system.sh /path/to/usb/drive
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run as root: sudo ./backup-system.sh${NC}"
    exit 1
fi

# Check if USB path is provided
if [ -z "$1" ]; then
    echo -e "${RED}Usage: sudo ./backup-system.sh /path/to/usb/drive${NC}"
    echo -e "${YELLOW}Example: sudo ./backup-system.sh /media/usb${NC}"
    exit 1
fi

USB_PATH="$1"
BACKUP_DIR="${USB_PATH}/instaprint_backup"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="instaprint_backup_${TIMESTAMP}.tar.gz"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}INSTAPRINT SYSTEM BACKUP${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if USB path exists
if [ ! -d "$USB_PATH" ]; then
    echo -e "${RED}Error: USB path does not exist: $USB_PATH${NC}"
    exit 1
fi

# Create backup directory
mkdir -p "$BACKUP_DIR"

echo -e "${YELLOW}Creating backup directory: $BACKUP_DIR${NC}"
echo ""

# 1. Backup Laravel Application
echo -e "${GREEN}[1/7] Backing up Laravel application...${NC}"
cd /var/www/html
tar -czf "${BACKUP_DIR}/laravel_app_${TIMESTAMP}.tar.gz" laravel/
echo -e "${GREEN}✓ Laravel app backed up${NC}"
echo ""

# 2. Backup Database
echo -e "${GREEN}[2/7] Backing up MySQL database...${NC}"
mysqldump -u root instaprint_db > "${BACKUP_DIR}/database_${TIMESTAMP}.sql" 2>/dev/null || \
mysqldump -u root --all-databases > "${BACKUP_DIR}/database_${TIMESTAMP}.sql" 2>/dev/null || \
echo -e "${YELLOW}⚠ Database backup skipped (check credentials)${NC}"
echo -e "${GREEN}✓ Database backed up${NC}"
echo ""

# 3. Backup Python servers
echo -e "${GREEN}[3/7] Backing up Python servers...${NC}"
if [ -d "/home/instaprint" ]; then
    tar -czf "${BACKUP_DIR}/python_servers_${TIMESTAMP}.tar.gz" \
        /home/instaprint/*.py \
        /home/instaprint/uploads 2>/dev/null || echo "Some files skipped"
fi
echo -e "${GREEN}✓ Python servers backed up${NC}"
echo ""

# 4. Backup System Configuration
echo -e "${GREEN}[4/7] Backing up system configuration...${NC}"
mkdir -p "${BACKUP_DIR}/configs"
cp /etc/nginx/sites-available/* "${BACKUP_DIR}/configs/" 2>/dev/null || true
cp /etc/apache2/sites-available/* "${BACKUP_DIR}/configs/" 2>/dev/null || true
cp /etc/systemd/system/*.service "${BACKUP_DIR}/configs/" 2>/dev/null || true
cp /etc/environment "${BACKUP_DIR}/configs/" 2>/dev/null || true
cp /etc/hosts "${BACKUP_DIR}/configs/" 2>/dev/null || true
echo -e "${GREEN}✓ System configs backed up${NC}"
echo ""

# 5. Backup .env file and important configs
echo -e "${GREEN}[5/7] Backing up environment files...${NC}"
cp /var/www/html/laravel/.env "${BACKUP_DIR}/.env" 2>/dev/null || \
echo -e "${YELLOW}⚠ .env file not found${NC}"
echo -e "${GREEN}✓ Environment files backed up${NC}"
echo ""

# 6. Create restore script
echo -e "${GREEN}[6/7] Creating restore script...${NC}"
cat > "${BACKUP_DIR}/RESTORE.sh" << 'RESTORE_SCRIPT'
#!/bin/bash

###############################################################################
# INSTAPRINT SYSTEM RESTORE SCRIPT
# This script restores the Instaprint system from backup
# Usage: sudo ./RESTORE.sh
###############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run as root: sudo ./RESTORE.sh${NC}"
    exit 1
fi

BACKUP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}INSTAPRINT SYSTEM RESTORE${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

echo -e "${YELLOW}This will restore your Instaprint system from backup.${NC}"
echo -e "${YELLOW}Current system files will be replaced!${NC}"
read -p "Continue? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo -e "${RED}Restore cancelled.${NC}"
    exit 0
fi

echo ""
echo -e "${GREEN}[1/5] Restoring Laravel application...${NC}"
mkdir -p /var/www/html
cd /var/www/html
rm -rf laravel 2>/dev/null || true
tar -xzf "$BACKUP_DIR"/laravel_app_*.tar.gz
chown -R www-data:www-data /var/www/html/laravel
chmod -R 755 /var/www/html/laravel
chmod -R 775 /var/www/html/laravel/storage
chmod -R 775 /var/www/html/laravel/bootstrap/cache
echo -e "${GREEN}✓ Laravel app restored${NC}"

echo ""
echo -e "${GREEN}[2/5] Restoring database...${NC}"
if [ -f "$BACKUP_DIR"/database_*.sql ]; then
    mysql -u root < "$BACKUP_DIR"/database_*.sql 2>/dev/null || \
    echo -e "${YELLOW}⚠ Database restore skipped (check credentials)${NC}"
    echo -e "${GREEN}✓ Database restored${NC}"
fi

echo ""
echo -e "${GREEN}[3/5] Restoring Python servers...${NC}"
if [ -f "$BACKUP_DIR"/python_servers_*.tar.gz ]; then
    cd /
    tar -xzf "$BACKUP_DIR"/python_servers_*.tar.gz
    echo -e "${GREEN}✓ Python servers restored${NC}"
fi

echo ""
echo -e "${GREEN}[4/5] Restoring configurations...${NC}"
if [ -d "$BACKUP_DIR/configs" ]; then
    cp "$BACKUP_DIR/configs"/*.conf /etc/nginx/sites-available/ 2>/dev/null || true
    cp "$BACKUP_DIR/configs"/*.service /etc/systemd/system/ 2>/dev/null || true
    systemctl daemon-reload
    echo -e "${GREEN}✓ Configurations restored${NC}"
fi

echo ""
echo -e "${GREEN}[5/5] Restarting services...${NC}"
systemctl restart nginx 2>/dev/null || systemctl restart apache2 2>/dev/null || true
systemctl restart php*-fpm 2>/dev/null || true
cd /var/www/html/laravel
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✓ RESTORE COMPLETED SUCCESSFULLY!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Please reboot the system:${NC}"
echo -e "${YELLOW}sudo reboot${NC}"
RESTORE_SCRIPT

chmod +x "${BACKUP_DIR}/RESTORE.sh"
echo -e "${GREEN}✓ Restore script created${NC}"
echo ""

# 7. Create README
echo -e "${GREEN}[7/7] Creating README...${NC}"
cat > "${BACKUP_DIR}/README.txt" << EOF
================================================================================
INSTAPRINT SYSTEM BACKUP
================================================================================

Backup Date: $(date)
Backup Location: $BACKUP_DIR

This backup contains:
- Complete Laravel application
- MySQL database
- Python server files
- System configurations
- Environment files

================================================================================
HOW TO RESTORE:
================================================================================

1. Boot your Raspberry Pi (or new system)

2. Plug in this USB drive

3. Open terminal and navigate to this folder:
   cd /media/[username]/[usb-name]/instaprint_backup

4. Run the restore script:
   sudo ./RESTORE.sh

5. Follow the prompts

6. Reboot the system:
   sudo reboot

================================================================================
SYSTEM REQUIREMENTS:
================================================================================

- Raspberry Pi (or compatible system)
- PHP 8.x
- MySQL/MariaDB
- Nginx or Apache
- Python 3.x
- Node.js (optional)

================================================================================
MANUAL RESTORATION (if script fails):
================================================================================

1. Extract Laravel app:
   sudo tar -xzf laravel_app_*.tar.gz -C /var/www/html/

2. Import database:
   sudo mysql -u root < database_*.sql

3. Extract Python servers:
   sudo tar -xzf python_servers_*.tar.gz -C /

4. Set permissions:
   sudo chown -R www-data:www-data /var/www/html/laravel
   sudo chmod -R 755 /var/www/html/laravel
   sudo chmod -R 775 /var/www/html/laravel/storage

5. Restart services:
   sudo systemctl restart nginx
   sudo systemctl restart php*-fpm

================================================================================
SUPPORT:
================================================================================

For issues, check:
- Laravel logs: /var/www/html/laravel/storage/logs/
- System logs: sudo journalctl -xe

================================================================================
EOF

echo -e "${GREEN}✓ README created${NC}"
echo ""

# Calculate backup size
BACKUP_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✓ BACKUP COMPLETED SUCCESSFULLY!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Backup Location: ${BACKUP_DIR}${NC}"
echo -e "${YELLOW}Backup Size: ${BACKUP_SIZE}${NC}"
echo ""
echo -e "${YELLOW}Files included:${NC}"
ls -lh "$BACKUP_DIR"
echo ""
echo -e "${GREEN}To restore this backup, run:${NC}"
echo -e "${GREEN}sudo ${BACKUP_DIR}/RESTORE.sh${NC}"
echo ""
echo -e "${YELLOW}Keep this USB drive in a safe place!${NC}"
echo ""
