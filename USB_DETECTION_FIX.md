# USB Detection & Page Calculation Fix

## Problems Identified

1. **Missing Python Dependencies**
   - `flask-cors` was not installed → USB server crashed on startup
   - `PyPDF2` was not installed → Could not count PDF pages (always defaulted to 1)

2. **No Startup Detection**
   - USB server only detected NEW insertions
   - Did NOT scan for already-mounted USB drives on startup
   - If USB was inserted before server started, it would not be detected

3. **No Auto-Mount Detection**
   - Server tried to manually mount to `/mnt/`
   - Did not detect system auto-mounted drives at `/media/instaprint/`
   - No periodic rescanning for auto-mounted drives

## Fixes Applied

### 1. Installed Missing Dependencies
```bash
pip3 install flask-cors PyPDF2 --break-system-packages
```

### 2. Added Startup Scan Function
- New `scan_existing_usb()` function
- Scans common mount points: `/media/instaprint/`, `/mnt/usb/`, `/mnt/*`
- Runs on server startup BEFORE monitoring for new insertions
- Counts PDF pages using PyPDF2

### 3. Improved Auto-Mount Detection
- Better parsing of `lsblk` output to find any mounted partition
- Fallback to `scan_existing_usb()` if manual mount fails
- Detects mounts at ANY location (not just `/media/`)

### 4. Added Periodic Rescan
- Background thread rescans every 5 seconds
- Only rescans when status is "waiting" (no USB detected)
- Catches USB drives mounted by system automounter

### 5. Added Manual Rescan Endpoint
- New endpoint: `GET http://127.0.0.1:5004/usb/rescan`
- Manually triggers a rescan of mount points
- Useful for debugging

## How to Start USB Server

### Method 1: Using Startup Script
```bash
./start_usb_server.sh
```

### Method 2: Manual Start
```bash
cd /var/www/html/laravel
python3 USB_server.py > /tmp/usb_server.log 2>&1 &
```

### Check if Running
```bash
ps aux | grep USB_server
```

### View Logs
```bash
tail -f /tmp/usb_server.log
```

## How It Works Now

1. **Server Starts** → Immediately scans for existing USB drives
2. **Every 5 seconds** → Rescans if no USB detected (catches auto-mounts)
3. **USB Inserted** → pyudev detects and triggers immediate scan
4. **PDF Files Found** → PyPDF2 counts actual pages in each file
5. **SSE Stream** → Sends file info with page counts to Laravel frontend
6. **Frontend Receives** → Shows "All pages (X pages)" with correct count
7. **Price Calculated** → `rate × copies × actual_pages`

## Testing

1. **Test with Already-Mounted USB:**
   - Insert USB drive
   - Wait for system to auto-mount
   - Start USB server
   - Should immediately detect and count pages

2. **Test with New Insertion:**
   - Start USB server
   - Insert USB drive
   - Should detect within 1-2 seconds

3. **Test Page Counting:**
   - Create PDFs with different page counts
   - Verify correct count shows in placeholder: "All pages (X pages)"
   - Verify price calculates correctly

## API Endpoints

- `GET http://127.0.0.1:5004/usb/stream` - SSE stream for real-time updates
- `GET http://127.0.0.1:5004/usb/status` - Current USB status
- `GET http://127.0.0.1:5004/usb/rescan` - Manually trigger rescan

## Troubleshooting

### USB Not Detected
```bash
# Check if server is running
ps aux | grep USB_server

# Check logs
cat /tmp/usb_server.log

# Manual rescan
curl http://127.0.0.1:5004/usb/rescan

# Check mount points
lsblk -o NAME,MOUNTPOINT,LABEL
ls -la /media/instaprint/
ls -la /mnt/
```

### Pages Show as 1
```bash
# Check if PyPDF2 is installed
python3 -c "import PyPDF2; print('OK')"

# Check server logs for PDF parsing errors
tail -f /tmp/usb_server.log
```

### Server Won't Start
```bash
# Check for missing dependencies
python3 -c "import flask, flask_cors, pyudev, PyPDF2"

# Install if missing
pip3 install flask flask-cors pyudev PyPDF2 --break-system-packages
```
