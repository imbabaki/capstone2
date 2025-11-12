#!/bin/bash

# Kill any existing USB server
pkill -f USB_server.py

# Wait a moment
sleep 1

# Start the USB server
cd /var/www/html/laravel
python3 USB_server.py > /tmp/usb_server.log 2>&1 &

# Wait and check if it started
sleep 2

if pgrep -f USB_server.py > /dev/null; then
    echo "✅ USB server started successfully on port 5004"
    echo "📋 Logs: tail -f /tmp/usb_server.log"
else
    echo "❌ Failed to start USB server"
    echo "Check logs: cat /tmp/usb_server.log"
    exit 1
fi
