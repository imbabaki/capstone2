# USB "All Pages" Calculation Fix

## Problem

When selecting "All pages" in USB print settings, the system was:
1. ❌ Not using the actual page count from the PDF
2. ❌ Not calculating the correct price
3. ❌ Not dispensing the correct number of papers
4. ❌ Backend was re-parsing PDF instead of using count from USB server

## Root Cause

The USB server (with PyPDF2) was correctly counting pages and sending them to the frontend, but:
- The frontend displayed the correct page count in the placeholder
- BUT the page count was NOT being sent to the backend when submitting the form
- Backend tried to re-parse the PDF using Smalot parser (less reliable)
- This caused inconsistent page counts and wrong calculations

## Solution

### 1. Added Hidden Field for Total Pages
**File:** `/var/www/html/laravel/resources/views/USBFD.blade.php`

Added hidden input to store the actual PDF page count:
```html
<input type="hidden" name="pdf_total_pages" id="hidden_pdf_total_pages" value="1">
```

### 2. Updated JavaScript to Set Total Pages
**File:** `/var/www/html/laravel/resources/views/USBFD.blade.php` (line 757)

When a PDF is selected, store its page count:
```javascript
function previewPDF(previewUrl, fileName, realPath, pdfPages = 1) {
    currentPdfTotalPages = pdfPages;
    // ... other code ...

    // Set the hidden field for total pages
    document.getElementById('hidden_pdf_total_pages').value = pdfPages;
}
```

### 3. Updated Backend to Use Frontend Page Count
**File:** `/var/www/html/laravel/app/Http/Controllers/USBController.php`

Modified `processPayment()` method:
```php
// Get the total pages in the PDF (from frontend which got it from USB server)
$pdfTotalPages = (int) $request->input('pdf_total_pages', 1);

// If empty or "All", use the total page count from the PDF
if (empty($pagesInput) || strtolower($pagesInput) === 'all') {
    $pageCount = $pdfTotalPages;  // Use count from USB server
    $pagesInput = 'All';
} else {
    // User specified specific pages
    $pageCount = $this->countPagesFromRanges($pagesInput);
}

$calculatedTotal = $rate * $pageCount * $copies;
```

## Complete Flow Now

```
1. USB Server (USB_server.py)
   ↓ Uses PyPDF2 to count pages
   ↓ Sends via SSE: {name: "file.pdf", path: "/mnt/...", pages: 15}

2. Frontend (USBFD.blade.php)
   ↓ Receives files with page counts
   ↓ User clicks file → previewPDF(..., 15)
   ↓ Sets currentPdfTotalPages = 15
   ↓ Shows placeholder: "All pages (15 pages)"
   ↓ Sets hidden field: pdf_total_pages = 15

3. User Configures Print Settings
   ↓ Selects paper size: A4
   ↓ Selects color: grayscale
   ↓ Selects copies: 2
   ↓ Pages field: blank (means "All")

4. JavaScript Calculation
   ↓ parsePageRange('', 15) → returns 15
   ↓ calculateTotal: rate × 15 × 2
   ↓ Shows correct total price

5. Form Submission
   ↓ Sends to backend:
   ↓   - file_path: "/mnt/usb/document.pdf"
   ↓   - pages: "" (empty = All)
   ↓   - pdf_total_pages: 15
   ↓   - copies: 2
   ↓   - color: grayscale
   ↓   - paper_size: A4

6. Backend (USBController::processPayment)
   ↓ Receives pdf_total_pages = 15
   ↓ Pages input is empty → use pdf_total_pages
   ↓ pageCount = 15
   ↓ calculatedTotal = rate × 15 × 2
   ↓ Stores in session: order['page_count'] = 15

7. Payment Page
   ↓ Shows correct total to pay
   ↓ User inserts coins

8. Payment Confirmed (USBController::handlePayment)
   ↓ Gets order['page_count'] = 15
   ↓ Dispenser pages = "1-15"
   ↓ Sends to dispenser: {paper_size: "A4", copies: 2, pages: "1-15"}

9. Dispenser
   ↓ Receives: 15 pages × 2 copies = 30 sheets
   ↓ Dispenses 30 sheets of A4 paper

10. Print Job (USBController::printNow)
    ↓ Pages input = "All"
    ↓ Does NOT add -P flag to lp command
    ↓ Prints ALL pages (default behavior)
    ↓ Command: lp -d printer -n 2 /mnt/usb/document.pdf
```

## What Gets Fixed

### ✅ Correct Page Count
- Frontend gets actual count from USB server (PyPDF2)
- Backend uses this count instead of re-parsing
- Consistent across entire flow

### ✅ Correct Price Calculation
- Price = rate_per_page × actual_pages × copies
- Example: ₱2/page × 15 pages × 2 copies = ₱60

### ✅ Correct Paper Dispensing
- Dispenser receives exact page count
- Dispenses: pages × copies sheets
- Example: 15 pages × 2 copies = 30 sheets

### ✅ Correct Printing
- Prints all pages when pages field is empty
- lp command without -P flag = print all pages
- Respects the -n flag for copies

## Testing

### Test Case 1: All Pages
1. Insert USB with 15-page PDF
2. Select the file
3. Verify shows: "All pages (15 pages)"
4. Leave pages field blank
5. Set copies = 2
6. Verify price = ₱2 × 15 × 2 = ₱60
7. Pay and print
8. Verify 30 sheets dispensed
9. Verify all 15 pages printed twice

### Test Case 2: Specific Pages
1. Insert USB with 15-page PDF
2. Select the file
3. Enter pages: "1-5"
4. Set copies = 1
5. Verify price = ₱2 × 5 × 1 = ₱10
6. Pay and print
7. Verify 5 sheets dispensed
8. Verify pages 1-5 printed

### Test Case 3: Single Page PDF
1. Insert USB with 1-page PDF
2. Select the file
3. Verify shows: "All pages (1 page)"
4. Leave pages blank
5. Set copies = 3
6. Verify price = ₱2 × 1 × 3 = ₱6
7. Pay and print
8. Verify 3 sheets dispensed
9. Verify 1 page printed 3 times

## Files Modified

1. `/var/www/html/laravel/resources/views/USBFD.blade.php`
   - Added `pdf_total_pages` hidden input
   - Updated `previewPDF()` to set total pages

2. `/var/www/html/laravel/app/Http/Controllers/USBController.php`
   - Updated `processPayment()` validation to accept `pdf_total_pages`
   - Changed logic to use `pdf_total_pages` for "All" pages
   - Added logging for debugging

## Verification

Check logs to verify correct calculation:
```bash
tail -f /var/www/html/laravel/storage/logs/laravel.log | grep "USB Payment"
```

Expected output when selecting "All pages":
```
USB Payment - Using all pages
  pdf_total_pages: 15
  page_count: 15
USB Payment Calculation
  rate: 2
  page_count: 15
  copies: 2
  total: 60
```
