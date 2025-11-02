#!/bin/bash
# Configure Bluetooth to be non-discoverable on boot
# This script runs at system startup

LOG_FILE="/var/log/bluetooth-boot-setup.log"

log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

log_message "Starting Bluetooth boot configuration..."

# Wait for Bluetooth service to be ready
sleep 5

# Ensure Bluetooth is powered on but not discoverable
echo -e "power on\ndiscoverable off\npairable off\n" | bluetoothctl > /dev/null 2>&1

if [ $? -eq 0 ]; then
    log_message "✅ Bluetooth configured: powered ON, discoverable OFF, pairable OFF"
else
    log_message "❌ Failed to configure Bluetooth on boot"
fi

log_message "Bluetooth boot configuration complete."
