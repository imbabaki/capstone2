# USB Detection & Total Calculation Troubleshooting Guide

## Issues Reported

1. ❌ **USB inserted but no PDF files appear**
2. ❌ **Total not calculating when selecting options**

## Diagnostic Steps

### Step 1: Check if USB Server is Running

```bash
ps aux | grep USB_server.py | grep -v grep
```

**Expected:** Should show python3 process running
**If not running:** Start it with `./start_usb_server.sh`

### Step 2: Check USB Server Can Detect Files

```bash
curl -s http://127.0.0.1:5004/usb/status | python3 -m json.tool
```

**Expected output:**
```json
{
    "status": "inserted",
    "files": [
        {
            "name": "document.pdf",
            "path": "/mnt/ESD-ISO/document.pdf",
            "pages": 15
        }
    ],
    "label": "USB_LABEL"
}
```

**If status is "waiting":** USB not detected by server
**If files array is empty:** No PDF files on USB

### Step 3: Check USB is Actually Mounted

```bash
lsblk -o NAME,FSTYPE,SIZE,MOUNTPOINT,LABEL | grep -E "sd|MOUNTPOINT"
```

**Expected:** Should show your USB device with a MOUNTPOINT
**Example:**
```
sda1      ntfs   14.3G /mnt/ESD-ISO   MY_USB
```

**If no MOUNTPOINT:** USB is not mounted. Try:
```bash
# List USB devices
lsblk

# Mount manually (replace sda1 with your device)
sudo mkdir -p /mnt/usb
sudo mount /dev/sda1 /mnt/usb

# Then trigger rescan
curl http://127.0.0.1:5004/usb/rescan
```

### Step 4: Check if Files Exist on USB

```bash
# Check mount points (use the path from lsblk)
ls -la /mnt/ESD-ISO/*.pdf
ls -la /media/instaprint/*.pdf
```

**Expected:** List of PDF files
**If empty:** No PDF files on USB drive

### Step 5: Open Browser Console

1. Open the USB page: `http://your-ip/USBFD`
2. Press F12 to open Developer Tools
3. Go to Console tab
4. Look for these messages:

**Expected console output:**
```
💰 Pricing data loaded: {a4_color: 10, a4_grayscale: 5, ...}
📱 USB Page initialized
🔌 Connecting USB stream to http://127.0.0.1:5004/usb/stream
✅ USB SSE connected to http://127.0.0.1:5004/usb/stream
📡 USB SSE message received: {status: "inserted", files: [...]}
📂 Found 6 PDF files
🖼️ Rendering file list with 6 files
📄 Adding file: document.pdf (15 pages)
✅ File list rendered successfully
```

### Step 6: Check for SSE Connection Errors

**In browser console, look for:**
- `❌ USB SSE error` - Connection failed
- `🔄 Retrying with URL index` - Trying backup URL

**If seeing SSE errors:**
1. USB server might not be running
2. Firewall blocking port 5004
3. Wrong IP address in connection

### Step 7: Test SSE Stream Manually

```bash
# This should show a continuous stream of data
curl -N http://127.0.0.1:5004/usb/stream
```

**Expected:** Should output `data: {JSON}` every second
**If fails:** USB server is not running or crashed

### Step 8: Check Pricing Data

**In browser console, check if pricing loaded:**
```javascript
prices
```

**Expected output:**
```javascript
{
  "a4_color": 10,
  "a4_grayscale": 5,
  "letter_color": 6,
  "letter_grayscale": 4,
  "legal_color": 10,
  "legal_grayscale": 8
}
```

**If empty or undefined:** Pricing not loaded from database

### Step 9: Test Total Calculation Manually

**In browser console:**
```javascript
// Set a PDF
currentPdfTotalPages = 15

// Manually trigger calculation
calculateTotal(15)

// Check the result
console.log(totalAmount.value)
```

**Expected:** Should show calculated total like `₱150.00`

## Common Problems & Solutions

### Problem: "No PDF files appear after inserting USB"

**Solution 1: Reload the page**
- The page may have loaded before USB was inserted
- Simply refresh the page: `Ctrl+F5` or `Cmd+R`

**Solution 2: Restart USB server**
```bash
./start_usb_server.sh
```

**Solution 3: Manually trigger rescan**
```bash
curl http://127.0.0.1:5004/usb/rescan
```
Then reload the page

### Problem: "Total shows 'No price configured'"

**Cause:** Pricing data not in database or not matching key

**Solution:**
```bash
# Check if pricing exists
php artisan tinker --execute="echo \App\Models\PrintSetting::count();"

# If 0, add pricing via admin panel at /admin/print-settings
```

**Check pricing keys match:**
- Frontend uses: `a4_color`, `a4_grayscale`, `letter_color`, etc.
- Database uses: paper_size="A4", color_option="color"
- Key format: `lowercase(paper_size) _ lowercase(color_option)`

### Problem: "Total shows ₱0.00"

**Possible causes:**
1. currentPdfTotalPages is 0 or 1
2. Copies is 0
3. Price per page is 0
4. Calculation error

**Debug in console:**
```javascript
console.log('Pages:', currentPdfTotalPages)
console.log('Copies:', copiesEl.value)
console.log('Paper:', paperSize.value)
console.log('Color:', colorSel.value)
console.log('Price key:', paperSize.value.toLowerCase() + '_' + colorSel.value.toLowerCase())
console.log('Price:', prices[paperSize.value.toLowerCase() + '_' + colorSel.value.toLowerCase()])
```

### Problem: "SSE connection keeps failing"

**Cause:** USB server not running or wrong URL

**Solution 1: Check USB server**
```bash
ps aux | grep USB_server
cat /tmp/usb_server.log
```

**Solution 2: Test connection manually**
```bash
curl http://127.0.0.1:5004/usb/status
```

**Solution 3: Check firewall**
```bash
sudo ufw status
# If blocking port 5004, allow it:
sudo ufw allow 5004
```

### Problem: "Files appear but clicking does nothing"

**Cause:** Preview URL or click handler broken

**Debug in console:**
```javascript
// Click a file and check console
// Should see: "Selected file: filename.pdf (15 pages)"
```

**Manual test:**
```javascript
previewPDF('test-url', 'test.pdf', '/path/to/test.pdf', 15)
```

## Complete Workflow Test

### Test 1: Fresh Start
```bash
# 1. Start USB server
./start_usb_server.sh

# 2. Insert USB with PDF files

# 3. Check server detected it
curl http://127.0.0.1:5004/usb/status

# 4. Open browser to http://your-ip/USBFD

# 5. Check console for:
#    - "Pricing data loaded"
#    - "USB SSE connected"
#    - "Found X PDF files"

# 6. Files should appear automatically
```

### Test 2: Total Calculation
```
1. Click a PDF file
2. Console should show: "Selected file: X (Y pages)"
3. Leave pages blank (means "All")
4. Change paper size → Total updates
5. Change color → Total updates
6. Change copies → Total updates
7. Formula: rate × pages × copies
```

### Test 3: Page Count Verification
```
1. Select a multi-page PDF (e.g., 15 pages)
2. Check placeholder shows: "All pages (15 pages)"
3. Leave pages blank
4. Set copies = 2
5. Total = rate × 15 × 2
6. Submit form
7. Check payment page shows correct total
```

## Log Files to Check

1. **USB Server Log:**
   ```bash
   tail -f /tmp/usb_server.log
   ```

2. **Laravel Log:**
   ```bash
   tail -f /var/www/html/laravel/storage/logs/laravel.log
   ```

3. **Browser Console:**
   - F12 → Console tab
   - Look for errors in red

## Quick Fixes

### Fix 1: Restart Everything
```bash
# Stop USB server
pkill -f USB_server.py

# Start USB server
./start_usb_server.sh

# Clear Laravel cache
php artisan cache:clear
php artisan config:clear

# Restart web server (if needed)
sudo systemctl restart nginx  # or apache2
```

### Fix 2: Force Page Reload
- Hard refresh: `Ctrl+Shift+R` (Windows/Linux) or `Cmd+Shift+R` (Mac)
- Clear browser cache
- Try incognito/private window

### Fix 3: Manual Rescan
```bash
# Trigger USB rescan
curl http://127.0.0.1:5004/usb/rescan

# Then reload browser page
```

## Debug Checklist

- [ ] USB server is running (`ps aux | grep USB_server`)
- [ ] USB is mounted (`lsblk`)
- [ ] PDF files exist on USB (`ls /mnt/*/`)
- [ ] USB server detects files (`curl localhost:5004/usb/status`)
- [ ] SSE stream works (`curl -N localhost:5004/usb/stream`)
- [ ] Browser console shows pricing data
- [ ] Browser console shows SSE connection
- [ ] Browser console shows files rendered
- [ ] Clicking file triggers preview
- [ ] Total calculation works
- [ ] Form submits correctly

## Still Not Working?

1. **Check all logs**
2. **Test each component individually**
3. **Restart from fresh state**
4. **Try different USB drive**
5. **Try different PDF files**
6. **Check file permissions** (`ls -la /mnt/*/`)
7. **Check PyPDF2 installation** (`python3 -c "import PyPDF2"`)
