#!/bin/bash

###############################################################################
# InstaPrint USB Auto-Handler
# This script is triggered by udev when USB drive is inserted
###############################################################################

# Log file
LOGFILE="/var/log/instaprint-usb.log"
BACKUP_LABEL="INSTAPRINT_BACKU"

# Log function
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOGFILE"
}

log_message "USB device detected, checking for InstaPrint backup drive..."

# Wait for device to be ready
sleep 2

# Check if this is our backup drive
USB_DEVICE=$(lsblk -o NAME,LABEL | grep "$BACKUP_LABEL" | awk '{print $1}' | head -1)

if [ -z "$USB_DEVICE" ]; then
    log_message "Not an InstaPrint backup drive, ignoring."
    exit 0
fi

log_message "InstaPrint backup drive detected!"

# Mount the drive
USB_DEVICE=$(echo "$USB_DEVICE" | sed 's/[└─├─│]//g' | sed 's/[0-9]*$//')
USB_PARTITION="/dev/${USB_DEVICE}1"
MOUNT_POINT="/mnt/instaprint_backup"

sudo mkdir -p "$MOUNT_POINT"

if ! mountpoint -q "$MOUNT_POINT"; then
    sudo mount "$USB_PARTITION" "$MOUNT_POINT" 2>> "$LOGFILE"
    log_message "Mounted USB drive at $MOUNT_POINT"
fi

# Check if restore script exists
if [ -f "$MOUNT_POINT/RESTORE.sh" ]; then
    log_message "Restore script found on USB drive"

    # Create a desktop notification or indicator that restore is available
    # You can uncomment this if you want automatic restore
    # sudo bash "$MOUNT_POINT/RESTORE.sh" >> "$LOGFILE" 2>&1
    # log_message "Auto-restore completed"

    # For now, just log that it's available
    log_message "USB drive ready. To restore, run: sudo bash $MOUNT_POINT/RESTORE.sh"
else
    log_message "No restore script found on USB drive"
fi

exit 0
