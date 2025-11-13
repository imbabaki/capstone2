#!/bin/bash

###############################################################################
# InstaPrint USB Manager
# Interactive menu for backup and restore operations
###############################################################################

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

clear
echo -e "${CYAN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║         InstaPrint USB Backup/Restore Manager             ║${NC}"
echo -e "${CYAN}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Check if USB drive is present
USB_CHECK=$(lsblk -o NAME,LABEL | grep "INSTAPRINT_BACKU" || true)

if [ -z "$USB_CHECK" ]; then
    echo -e "${RED}❌ Backup USB drive not detected!${NC}"
    echo -e "${YELLOW}Please insert the USB drive labeled 'INSTAPRINT_BACKU'${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Backup USB drive detected${NC}"
echo ""
echo -e "${BLUE}Select an option:${NC}"
echo ""
echo -e "  ${GREEN}1)${NC} Create Full Backup"
echo -e "  ${YELLOW}2)${NC} Restore from Backup"
echo -e "  ${CYAN}3)${NC} View Backup Information"
echo -e "  ${RED}4)${NC} Exit"
echo ""
echo -n "Enter your choice [1-4]: "
read choice

case $choice in
    1)
        echo ""
        echo -e "${YELLOW}Starting backup process...${NC}"
        sudo bash /var/www/html/laravel/backup-to-usb.sh
        ;;
    2)
        echo ""
        echo -e "${YELLOW}Starting restore process...${NC}"
        if [ -f "/mnt/instaprint_backup/RESTORE.sh" ]; then
            sudo bash /mnt/instaprint_backup/RESTORE.sh
        else
            echo -e "${RED}❌ No restore script found on USB drive!${NC}"
            echo -e "${YELLOW}Please create a backup first.${NC}"
        fi
        ;;
    3)
        echo ""
        echo -e "${BLUE}Backup Information:${NC}"
        echo ""
        if [ -d "/mnt/instaprint_backup" ]; then
            if mountpoint -q /mnt/instaprint_backup; then
                echo "USB is already mounted"
            else
                USB_DEVICE=$(lsblk -o NAME,LABEL | grep "INSTAPRINT_BACKU" | awk '{print $1}' | head -1)
                USB_DEVICE=$(echo "$USB_DEVICE" | sed 's/[└─├─│]//g' | sed 's/[0-9]*$//')
                sudo mount /dev/${USB_DEVICE}1 /mnt/instaprint_backup 2>/dev/null || true
            fi

            BACKUPS=$(ls -dt /mnt/instaprint_backup/instaprint_backup_* 2>/dev/null || true)
            if [ -z "$BACKUPS" ]; then
                echo -e "${YELLOW}No backups found on USB drive${NC}"
            else
                echo -e "${GREEN}Available backups:${NC}"
                echo ""
                for backup in $BACKUPS; do
                    BACKUP_NAME=$(basename "$backup")
                    BACKUP_SIZE=$(du -sh "$backup" 2>/dev/null | cut -f1)
                    BACKUP_DATE=$(echo "$BACKUP_NAME" | sed 's/instaprint_backup_//' | sed 's/_/ /')
                    echo -e "  📦 ${CYAN}${BACKUP_DATE}${NC} - Size: ${GREEN}${BACKUP_SIZE}${NC}"
                done
            fi
        else
            echo -e "${RED}USB drive not mounted${NC}"
        fi
        ;;
    4)
        echo ""
        echo -e "${GREEN}Goodbye!${NC}"
        exit 0
        ;;
    *)
        echo ""
        echo -e "${RED}Invalid choice!${NC}"
        exit 1
        ;;
esac

echo ""
