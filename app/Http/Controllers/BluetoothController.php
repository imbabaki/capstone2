<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser as PdfParser;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\PrintLog;
use App\Models\Voucher;
use App\Models\VoucherSetting;
use Carbon\Carbon;

class BluetoothController extends Controller
{
    public function index()
    {
        $path = "/home/instaprint/Downloads";

        $files = File::exists($path)
            ? collect(File::files($path))->map(fn($file) => $file->getFilename())
            : collect([]);

        return view('bluetooth.index', ['files' => $files]);
    }

    public function enable(Request $request)
    {
        // Enable Bluetooth with 5-minute timeout
        shell_exec('echo -e "power on\ndiscoverable on\ndiscoverable-timeout 300\npairable on\n" | sudo bluetoothctl');

        Log::info('Bluetooth enabled with 5-minute discoverability timeout');

        // Return JSON for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bluetooth is now discoverable for 5 minutes!'
            ]);
        }

        return back()->with('success', 'Bluetooth is now discoverable for 5 minutes! Pair from your phone.');
    }

    public function disable(Request $request)
    {
        shell_exec('echo -e "discoverable off\npairable off\n" | sudo bluetoothctl');

        Log::info('Bluetooth discoverability disabled');

        // Return JSON for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bluetooth discoverability has been disabled.'
            ]);
        }

        return back()->with('success', 'Bluetooth discoverability has been disabled.');
    }

    public function preview($filename)
    {
        $possiblePaths = [
            "/home/instaprint/Downloads/" . $filename,
            "/var/www/html/laravel/public/storage/uploads/" . $filename,
            storage_path('app/public/uploads/' . $filename),
            public_path('storage/uploads/' . $filename),
        ];

        $filePath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $filePath = $path;
                break;
            }
        }

        if (!$filePath) {
            Log::error("Bluetooth file not found: {$filename}. Checked paths: " . implode(', ', $possiblePaths));
            return redirect()->route('bluetooth.index')
                ->with('error', 'File not found: ' . $filename . '. Please resend the file.');
        }

        // Wait for file to be fully written
        $maxWait = 10;
        $waited = 0;
        while ($waited < $maxWait) {
            clearstatcache(true, $filePath);
            
            if (!file_exists($filePath)) {
                return redirect()->route('bluetooth.index')
                    ->with('error', 'File was deleted during processing. Please resend.');
            }
            
            $size1 = @filesize($filePath);
            if ($size1 === false) {
                sleep(1);
                $waited++;
                continue;
            }
            
            sleep(1);
            clearstatcache(true, $filePath);
            $size2 = @filesize($filePath);
            
            if ($size1 === $size2 && $size1 > 0) {
                break;
            }
            $waited++;
        }

        $fileSize = @filesize($filePath);
        if (!is_readable($filePath) || $fileSize === false || $fileSize < 100) {
            return redirect()->route('bluetooth.index')
                ->with('error', 'File is corrupted or still transferring. Please try again.');
        }

        // Copy to public storage for preview
        $publicPath = public_path('storage/uploads/' . $filename);
        if (!file_exists(dirname($publicPath))) {
            mkdir(dirname($publicPath), 0755, true);
        }
        
        if (!file_exists($publicPath)) {
            copy($filePath, $publicPath);
        }

        $fileUrl = asset('storage/uploads/' . $filename);
        
        // Get total pages if PDF
        $totalPages = 1;
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if ($extension === 'pdf') {
            try {
                $fileHandle = fopen($filePath, 'r');
                $header = fread($fileHandle, 5);
                fclose($fileHandle);
                
                if ($header !== '%PDF-') {
                    throw new \Exception('Invalid PDF header');
                }

                $parser = new PdfParser();
                $pdf = $parser->parseFile($filePath);
                $pages = $pdf->getPages();
                $totalPages = count($pages);
                
                if ($totalPages === 0) {
                    $totalPages = 1;
                }
            } catch (\Exception $e) {
                Log::warning("PDF parsing failed for {$filename}: " . $e->getMessage());
                $totalPages = 1;
            }
        }

        $pricing = \App\Models\PrintSetting::all()->toArray();

        return view('bluetooth.preview', [
            'filename' => $filename,
            'fileUrl' => $fileUrl,
            'totalPages' => $totalPages,
            'pricing' => $pricing
        ]);
    }

    private function countPagesFromRanges(?string $ranges): int
    {
        if (!$ranges || $ranges === 'All') return 0;
        $total = 0;
        foreach (explode(',', $ranges) as $part) {
            $part = trim($part);
            if ($part === '') continue;
            if (strpos($part, '-') !== false) {
                [$s, $e] = array_map('intval', explode('-', $part));
                if ($e >= $s) $total += ($e - $s + 1);
            } else {
                $total += 1;
            }
        }
        return $total;
    }

    private function getRatePerPage($paperSize, $color)
    {
        $setting = \App\Models\PrintSetting::where('paper_size', $paperSize)
            ->where('color_option', strtolower($color))
            ->first();
        return $setting ? $setting->price : null;
    }

    public function payment(Request $request)
    {
        $validated = $request->validate([
            'file_name' => 'required|string',
            'copies' => 'required|integer|min:1',
            'pages' => 'nullable|string',
            'color_option' => 'required|in:color,grayscale',
            'paper_size' => 'required|in:A4,Letter,Legal',
            'duplex' => 'required|in:one-sided,two-sided-long-edge,two-sided-short-edge',
            'fit' => 'required|in:none,fit-to-page',
            'calculated_total' => 'required|numeric'
        ]);

        // Calculate actual page count
        $pagesInput = trim($validated['pages'] ?? '');

        // If empty or "All", get the actual PDF page count
        if (empty($pagesInput) || strtolower($pagesInput) === 'all') {
            $possiblePaths = [
                "/home/instaprint/Downloads/" . $validated['file_name'],
                public_path('storage/uploads/' . $validated['file_name']),
            ];

            $pageCount = 1; // default
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    try {
                        $parser = new PdfParser();
                        $pdf = $parser->parseFile($path);
                        $pageCount = max(1, count($pdf->getPages()));
                        break;
                    } catch (\Exception $e) {
                        $pageCount = 1;
                    }
                }
            }
            // Store "All" for display purposes
            $pagesInput = 'All';
        } else {
            // User specified specific pages
            $pageCount = $this->countPagesFromRanges($pagesInput);
        }

        // Get existing order from session or create new
        $order = Session::get('bluetooth.order', []);
        
        // Keep voucher data if already applied
        $order = array_merge($order, [
            'file_name' => $validated['file_name'],
            'copies' => $validated['copies'],
            'pages' => $validated['pages'] ?? 'All',
            'page_count' => $pageCount,
            'color_option' => $validated['color_option'],
            'paper_size' => $validated['paper_size'],
            'duplex' => $validated['duplex'],
            'fit' => $validated['fit'],
            'rate' => $this->getRatePerPage($validated['paper_size'], $validated['color_option']),
            'paid' => false
        ]);

        // Calculate total (considering voucher if applied)
        if (!empty($order['voucher_applied'])) {
            $order['calculated_total'] = max(0, $order['original_total'] - $order['voucher_discount']);
        } else {
            $order['calculated_total'] = $validated['calculated_total'];
        }

        Session::put('bluetooth.order', $order);
        Session::save();

        return view('bluetooth.payment', ['order' => $order]);
    }

    // Handle GET requests to payment page (for page reloads/voucher applications)
    public function paymentView()
    {
        $order = Session::get('bluetooth.order');
        
        if (!$order) {
            return redirect()->route('bluetooth.index')
                ->with('error', 'No active order found. Please start a new print job.');
        }

        // If already paid, redirect to instruction
        if (!empty($order['paid'])) {
            return redirect()->route('bluetooth.instruction');
        }

        return view('bluetooth.payment', ['order' => $order]);
    }

    public function applyVoucher(Request $request)
    {
        Log::info('=== BLUETOOTH VOUCHER APPLICATION STARTED ===');
        Log::info('Request data:', $request->all());
        
        try {
            $request->validate([
                'code' => 'required|string',
                'source' => 'required|string'
            ]);

            $code = strtoupper(trim($request->code));
            Log::info("Validating voucher code: {$code}");

            $order = Session::get('bluetooth.order');
            
            if (!$order) {
                Log::error('No active order found in session');
                return response()->json([
                    'success' => false,
                    'message' => 'No active order found'
                ], 404);
            }

            Log::info('Order found:', $order);

            // Check if voucher already applied
            if (!empty($order['voucher_applied'])) {
                Log::warning('Voucher already applied to this order');
                return response()->json([
                    'success' => false,
                    'message' => 'A voucher has already been applied to this order'
                ], 400);
            }

            // Find voucher
            Log::info('Searching for voucher in database...');
            
            $voucher = Voucher::where('code', $code)->first();

            if (!$voucher) {
                Log::warning('Voucher not found in database');
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid voucher code. Please check and try again.'
                ], 400);
            }

            Log::info('Voucher found:', [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'amount' => $voucher->amount,
                'expires_at' => $voucher->expires_at
            ]);

            // Validate voucher
            if (!$voucher->isValid()) {
                if ($voucher->is_used || $voucher->is_redeemed) {
                    Log::warning('Voucher already used');
                    return response()->json([
                        'success' => false,
                        'message' => 'This voucher has already been used'
                    ], 400);
                }
                
                if ($voucher->isExpired()) {
                    Log::warning('Voucher has expired');
                    return response()->json([
                        'success' => false,
                        'message' => 'This voucher expired on ' . $voucher->expires_at->format('M d, Y')
                    ], 400);
                }
            }

            Log::info('✅ Voucher is valid');

            // Apply voucher discount
            $originalTotal = $order['calculated_total'];
            $discount = min($voucher->amount, $originalTotal);
            $newTotal = max(0, $originalTotal - $discount);

            Log::info('Calculating discount:', [
                'original_total' => $originalTotal,
                'voucher_amount' => $voucher->amount,
                'discount_applied' => $discount,
                'new_total' => $newTotal
            ]);

            // Update order with voucher info
            $order['voucher_applied'] = true;
            $order['voucher_code'] = $voucher->code;
            $order['voucher_id'] = $voucher->id;
            $order['voucher_discount'] = $discount;
            $order['original_total'] = $originalTotal;
            $order['calculated_total'] = $newTotal;

            Session::put('bluetooth.order', $order);
            Session::save();

            Log::info('Voucher applied successfully, session updated');
            Log::info('=== BLUETOOTH VOUCHER APPLICATION COMPLETED ===');

            return response()->json([
                'success' => true,
                'message' => 'Voucher applied! You saved ₱' . number_format($discount, 2),
                'discount' => $discount,
                'new_total' => $newTotal
            ]);
            
        } catch (\Exception $e) {
            Log::error('Voucher application exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handlePayment(Request $request)
    {
        Log::info('=== HANDLE PAYMENT STARTED ===');
        
        try {
            $order = Session::get('bluetooth.order');
            
            if (!$order) {
                Log::error('No order found in session');
                return response()->json(['success' => false, 'message' => 'No order found.'], 404);
            }

            Log::info('Order retrieved from session:', $order);

            if (!empty($order['paid'])) {
                Log::info('Order already paid, redirecting to instruction');
                return response()->json([
                    'success' => true, 
                    'message' => 'Already paid',
                    'redirect' => route('bluetooth.instruction')
                ]);
            }

            // Get coin total from Flask BEFORE resetting
            $coinTotal = 0;
            try {
                $response = Http::timeout(5)->get('http://127.0.0.1:5003/coin/total');
                $coinTotal = floatval($response->json('total') ?? 0);
                Log::info('Coin total from Flask:', ['amount' => $coinTotal, 'raw_response' => $response->json()]);
            } catch (\Exception $e) {
                Log::error('Failed to get coin total:', ['error' => $e->getMessage()]);
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to verify payment. Please try again.'
                ], 500);
            }

            $orderTotal = floatval($order['calculated_total'] ?? 0);
            Log::info('Payment check:', [
                'coin_total' => $coinTotal,
                'order_total' => $orderTotal,
                'sufficient' => $coinTotal >= $orderTotal,
                'difference' => $coinTotal - $orderTotal
            ]);

            // Check if payment is sufficient (with small tolerance for floating point)
            if ($coinTotal < ($orderTotal - 0.01)) {
                Log::warning('Insufficient payment', [
                    'received' => $coinTotal,
                    'required' => $orderTotal,
                    'shortage' => $orderTotal - $coinTotal
                ]);
                return response()->json([
                    'success' => false, 
                    'message' => 'Insufficient payment. Please insert ₱' . number_format($orderTotal - $coinTotal, 2)
                ], 400);
            }

            // Redeem applied voucher if exists
            if (!empty($order['voucher_applied']) && !empty($order['voucher_id'])) {
                Log::info('Attempting to redeem voucher:', ['voucher_id' => $order['voucher_id']]);
                
                $voucher = Voucher::find($order['voucher_id']);
                if ($voucher && $voucher->isValid()) {
                    if ($voucher->redeem()) {
                        Log::info('Voucher redeemed successfully', [
                            'code' => $voucher->code,
                            'order' => 'Bluetooth',
                            'used_at' => $voucher->used_at
                        ]);
                    } else {
                        Log::warning('Failed to redeem voucher', ['code' => $voucher->code]);
                    }
                } else {
                    Log::warning('Voucher not found or invalid', ['voucher_id' => $order['voucher_id']]);
                }
            }

            // Calculate change from coins inserted
            $change = $coinTotal - $orderTotal;
            $changeVoucher = null;

            // Generate new voucher for change if exists
            if ($change > 0) {
                Log::info('Generating change voucher:', ['change_amount' => $change]);
                
                $expirationDays = (int) VoucherSetting::getExpirationDays();
                
                $changeVoucher = Voucher::create([
                    'code' => Voucher::generateCode(),
                    'amount' => $change,
                    'source' => 'Bluetooth',
                    'expires_at' => Carbon::now()->addDays($expirationDays),
                    'expiration_days' => $expirationDays,
                    'is_redeemed' => false,
                ]);

                Log::info('Bluetooth Change Voucher generated', [
                    'code' => $changeVoucher->code,
                    'amount' => $change,
                    'expires_at' => $changeVoucher->expires_at,
                ]);
            }

            // Mark as paid
            $order['paid'] = true;
            $order['coins_inserted'] = $coinTotal;
            $order['change_voucher_code'] = $changeVoucher?->code;
            $order['change_amount'] = $change;
            $order['change_voucher_expires_at'] = $changeVoucher?->expires_at?->format('M d, Y');
            
            Session::put('bluetooth.order', $order);
            Session::save();

            Log::info('Payment marked as paid, session saved', [
                'coins_inserted' => $coinTotal,
                'order_total' => $orderTotal,
                'change' => $change
            ]);

            // Reset coin counter AFTER everything is saved
            try {
                $resetResponse = Http::timeout(3)->post('http://127.0.0.1:5003/coin/reset');
                Log::info('Coin counter reset successfully', ['response' => $resetResponse->json()]);
            } catch (\Exception $e) {
                Log::warning('Failed to reset coin total: ' . $e->getMessage());
            }

            // ✅ START DISPENSING PAPERS IMMEDIATELY AFTER PAYMENT
            $pagesInput = trim($order['pages'] ?? 'All');
            $pagesToPrint = $order['page_count'] ?? 1;

            // Calculate the page string to send to dispenser
            $dispenserPages = '';
            if (!empty($pagesInput) && strtolower($pagesInput) !== 'all') {
                // Specific pages selected (e.g., "1-3,5")
                $dispenserPages = $pagesInput;
            } else {
                // All pages - send format "1-X" where X is total pages
                $dispenserPages = "1-{$pagesToPrint}";
            }

            try {
                $dispenserResponse = Http::timeout(10)->post('http://127.0.0.1:5005/start', [
                    'paper_size' => $order['paper_size'],
                    'copies' => $order['copies'],
                    'pages' => $dispenserPages
                ]);

                Log::info("Dispenser started after payment:", [
                    'paper_size' => $order['paper_size'],
                    'copies' => $order['copies'],
                    'pages_sent' => $dispenserPages,
                    'calculated_sheets' => $pagesToPrint * $order['copies'],
                    'response' => $dispenserResponse->json(),
                    'status' => $dispenserResponse->status()
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to start dispenser after payment: " . $e->getMessage());
                // Don't fail the payment if dispenser fails, just log it
            }

            Log::info('=== HANDLE PAYMENT COMPLETED SUCCESSFULLY ===');

            return response()->json([
                'success' => true,
                'message' => 'Payment successful!',
                'redirect' => route('bluetooth.instruction'),
                'debug' => [
                    'coins_inserted' => $coinTotal,
                    'order_total' => $orderTotal,
                    'change' => $change
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('=== HANDLE PAYMENT EXCEPTION ===', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment processing error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function instruction()
    {
        $order = Session::get('bluetooth.order');
        
        if (!$order) {
            return redirect()->route('bluetooth.index')
                ->with('error', 'No active order found');
        }

        // Verify payment was completed
        if (empty($order['paid'])) {
            return redirect()->route('bluetooth.payment')->with('error', 'Please complete payment first.');
        }

        return view('bluetooth.instruction', ['order' => $order]);
    }

    public function printJob(Request $request)
    {
        $order = Session::get('bluetooth.order');

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'No order found.'
            ], 404);
        }

        // Verify payment was completed
        if (empty($order['paid'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete payment first.'
            ], 403);
        }

        $filename = $order['file_name'];

        // Find the file
        $possiblePaths = [
            "/home/instaprint/Downloads/" . $filename,
            "/var/www/html/laravel/public/storage/uploads/" . $filename,
            storage_path('app/public/uploads/' . $filename),
            public_path('storage/uploads/' . $filename),
        ];

        $filePath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $filePath = $path;
                break;
            }
        }

        if (!$filePath) {
            return response()->json([
                'success' => false,
                'message' => 'File not found: ' . $filename
            ], 404);
        }

        // Calculate actual pages to print
        $pagesInput = trim($order['pages'] ?? 'All');
        $pagesToPrint = $order['page_count'] ?? 1;

        // If "All" or empty, make sure we have the actual PDF page count
        if (empty($pagesInput) || strtolower($pagesInput) === 'all') {
            // If page_count is already set in order, use it
            // Otherwise, try to parse the PDF
            if (!isset($order['page_count']) || $order['page_count'] <= 1) {
                $possiblePaths = [
                    "/home/instaprint/Downloads/" . $filename,
                    "/var/www/html/laravel/public/storage/uploads/" . $filename,
                    storage_path('app/public/uploads/' . $filename),
                    public_path('storage/uploads/' . $filename),
                ];

                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        try {
                            $parser = new PdfParser();
                            $pdf = $parser->parseFile($path);
                            $pagesToPrint = max(1, count($pdf->getPages()));
                            Log::info("Parsed PDF page count: {$pagesToPrint}");
                            break;
                        } catch (\Exception $e) {
                            Log::warning("Failed to parse PDF: " . $e->getMessage());
                        }
                    }
                }
            }
            $pagesInput = ''; // Empty means "all" for CUPS
        } else {
            // Specific pages selected
            $pagesToPrint = $this->countPagesFromRanges($pagesInput);
        }

        Log::info("Bluetooth printing:", [
            'file' => $filename,
            'copies' => $order['copies'],
            'pages_input' => $pagesInput,
            'pages_to_print' => $pagesToPrint,
            'paper_size' => $order['paper_size'],
            'total_sheets' => $pagesToPrint * $order['copies']
        ]);

        // Build print command
        $printCmd = "sudo /usr/bin/lp -d EPSON_L120_Series_instaprinthotspot";
        $printCmd .= " -n " . $order['copies'];

        if ($order['color_option'] === 'grayscale') {
            $printCmd .= " -o ColorModel=Gray";
        }

        $printCmd .= " -o media=" . $order['paper_size'];

        if ($order['duplex'] === 'two-sided-long-edge') {
            $printCmd .= " -o sides=two-sided-long-edge";
        } elseif ($order['duplex'] === 'two-sided-short-edge') {
            $printCmd .= " -o sides=two-sided-short-edge";
        }

        // Only add page-ranges if specific pages are selected
        if (!empty($pagesInput)) {
            $printCmd .= " -o page-ranges=" . $pagesInput;
        }

        if ($order['fit'] === 'fit-to-page') {
            $printCmd .= " -o fit-to-page";
        }

        $printCmd .= " " . escapeshellarg($filePath) . " 2>&1";

        Log::info("Bluetooth print command: " . $printCmd);
        $output = shell_exec($printCmd);

        if ($output && (str_contains(strtolower($output), 'error') || str_contains(strtolower($output), 'failed'))) {
            Log::error("Bluetooth print failed: " . $output);
            return response()->json([
                'success' => false,
                'message' => 'Print failed: ' . $output
            ], 500);
        }

        // Extract job ID from lp output (format: "request id is PRINTER-JOBID")
        $jobId = null;
        if ($output && preg_match('/request id is .+-(\d+)/i', $output, $matches)) {
            $jobId = $matches[1];
            Log::info('Extracted job ID: ' . $jobId);
        }

        // Note: Dispenser was already started after payment confirmation
        // Papers should already be dispensed by now

        // Log the print job
        PrintLog::create([
            'source' => 'Bluetooth',
            'file_name' => $order['file_name'],
            'copies' => $order['copies'],
            'page_count' => $pagesToPrint,
            'paper_size' => $order['paper_size'],
            'color_option' => $order['color_option'],
            'rate_per_page' => $order['rate'] ?? 0,
            'total_amount' => $order['calculated_total'],
        ]);

        // ✅ Delete the file from both locations now that printing is complete
        try {
            $deleteResponse = Http::timeout(5)->post('http://127.0.0.1:5001/delete_file', [
                'filename' => $filename
            ]);

            Log::info("File deletion requested:", [
                'filename' => $filename,
                'response' => $deleteResponse->json(),
                'status' => $deleteResponse->status()
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to delete file after printing: " . $e->getMessage());
            // Don't fail the print job if deletion fails
        }

        // Mark as completed for session (monitoring will determine when to redirect)
        $order['print_completed'] = true;
        Session::put('bluetooth.order', $order);
        Session::save();

        return response()->json([
            'success' => true,
            'message' => 'Print job sent successfully',
            'output' => $output,
            'job_id' => $jobId
        ]);
    }

    public function markCompleted(Request $request)
    {
        $order = Session::get('bluetooth.order');

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'No order found'], 404);
        }

        // Mark as completed
        $order['print_completed'] = true;
        Session::put('bluetooth.order', $order);
        Session::save();

        Log::info('Bluetooth print marked as completed');

        return response()->json(['success' => true]);
    }

    public function success()
    {
        $order = Session::get('bluetooth.order');

        if (!$order) {
            return redirect()->route('bluetooth.index')
                ->with('error', 'No active order found');
        }

        // Verify that print was completed
        if (empty($order['print_completed'])) {
            return redirect()->route('bluetooth.instruction')
                ->with('error', 'Please complete printing first.');
        }

        // Clear session after displaying success
        Session::forget('bluetooth.order');
        Session::save();

        return view('bluetooth.success', ['order' => $order]);
    }

    public function complete()
    {
        $order = Session::get('bluetooth.order');

        if (!$order) {
            return redirect()->route('bluetooth.index')
                ->with('error', 'No active order found');
        }

        Session::forget('bluetooth.order');

        return redirect()->route('bluetooth.index')
            ->with('success', 'Print job completed! File: ' . $order['file_name']);
    }
}