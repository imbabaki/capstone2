#!/bin/bash
# Auto-disable Bluetooth discoverability when not in use
# This script checks if Bluetooth is discoverable and disables it if no active transfers

LOG_FILE="/var/log/bluetooth-auto-disable.log"

log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

# Check if Bluetooth is discoverable
DISCOVERABLE=$(bluetoothctl show | grep "Discoverable: yes")

if [ -z "$DISCOVERABLE" ]; then
    log_message "Bluetooth is not discoverable. No action needed."
    exit 0
fi

log_message "Bluetooth is discoverable. Checking for active connections..."

# Check for active Bluetooth connections/transfers
CONNECTED_DEVICES=$(bluetoothctl devices Connected | wc -l)

if [ "$CONNECTED_DEVICES" -gt 0 ]; then
    log_message "Active Bluetooth connections detected ($CONNECTED_DEVICES devices). Keeping discoverable."
    exit 0
fi

# Check if any file transfer is happening (check Downloads directory activity)
DOWNLOADS_DIR="/home/instaprint/Downloads"
if [ -d "$DOWNLOADS_DIR" ]; then
    # Check if any file was modified in the last 2 minutes
    RECENT_FILES=$(find "$DOWNLOADS_DIR" -type f -mmin -2 | wc -l)

    if [ "$RECENT_FILES" -gt 0 ]; then
        log_message "Recent file activity detected. Keeping discoverable."
        exit 0
    fi
fi

# No active connections or transfers - disable discoverability
log_message "No active connections or transfers. Disabling Bluetooth discoverability..."
echo -e "discoverable off\npairable off\n" | bluetoothctl > /dev/null 2>&1

if [ $? -eq 0 ]; then
    log_message "✅ Bluetooth discoverability disabled successfully."
else
    log_message "❌ Failed to disable Bluetooth discoverability."
fi
