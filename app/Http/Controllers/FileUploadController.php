<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Models\PrintSetting;
use App\Events\FileUploaded;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use App\Models\PrintLog;
use App\Models\Voucher;
use App\Models\VoucherSetting;
use Carbon\Carbon;



class FileUploadController extends Controller
{
    // Show upload form
    public function showForm()
    {
        return view('upload_form');
    }

    // Handle file upload (from phone)
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,jpg,png|max:10240', // max 10MB
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();

        // Use public/storage/uploads
        $uploadPath = public_path('storage/uploads');

        if (!File::exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if (!is_writable($uploadPath)) {
            return back()->withErrors(['file' => 'Uploads folder is not writable. Check permissions.']);
        }

        try {
            $file->move($uploadPath, $filename);
        } catch (\Exception $e) {
            Log::error('File upload failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['file' => 'Failed to save the file. Check permissions.']);
        }

        // Save latest upload for kiosk polling
        $latestPath = storage_path('app/latest_upload.json');
        file_put_contents($latestPath, json_encode([
            'filename'  => $filename,
            'timestamp' => now()->toDateTimeString(),
        ]));

        // Broadcast event
        event(new FileUploaded($filename));

        return response()->view('upload_success', [
            'filename' => $filename
        ]);
    }

    // Show edit page (kiosk after auto-redirect)
    public function edit($filename)
    {
        $path = storage_path("app/public/uploads/$filename");
        $fileUrl = asset("storage/uploads/$filename");

        // Get total pages (if it's a PDF)
        $totalPages = 1;
        if (Str::endsWith($filename, '.pdf')) {
            $pdf = new \Smalot\PdfParser\Parser();
            $document = $pdf->parseFile($path);
            $totalPages = count($document->getPages());
        }

        $pricing = PrintSetting::all();
        $order = session('order', []);

        return view('edit_upload', compact('fileUrl', 'filename', 'pricing', 'order', 'totalPages'));
    }



    // Final print command
    public function doFinalPrint(Request $request)
    {
        $order = Session::get('upload.order');

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

        $filePath = public_path('storage/uploads/' . $order['file_name']);

        if (!File::exists($filePath)) {
            Log::error('Print failed: File not found', ['path' => $filePath]);
            return response()->json([
                'success' => false,
                'message' => 'File not found: ' . $order['file_name']
            ], 404);
        }

        $printer = $order['printer'] ?? 'EPSON_L120_Series_instaprinthotspot';
        $copies  = (int)($order['copies'] ?? 1);
        $pagesInput = trim($order['pages'] ?? 'All');
        $color   = $order['color'] ?? 'color';
        $paper   = $order['paper_size'] ?? null;
        $duplex  = $order['duplex'] ?? 'one-sided';
        $fit     = $order['fit'] ?? 'none';

        $cmd = ['lp', '-d', $printer, '-n', (string) max(1, $copies)];

        // Only add page-ranges if specific pages are selected (not "All" or empty)
        if (!empty($pagesInput) && strtolower($pagesInput) !== 'all') {
            $cmd[] = '-o';
            $cmd[] = 'page-ranges=' . $pagesInput;
        }

        $cmd[] = '-o';
        $cmd[] = ($color === 'grayscale') ? 'ColorModel=Gray' : 'ColorModel=RGB';

        if ($paper) {
            $cmd[] = '-o';
            $cmd[] = 'media=' . $paper;
        }

        if (in_array($duplex, ['one-sided','two-sided-long-edge','two-sided-short-edge'], true)) {
            $cmd[] = '-o';
            $cmd[] = 'sides=' . $duplex;
        }

        if ($fit === 'fit-to-page') {
            $cmd[] = '-o';
            $cmd[] = 'fit-to-page';
        }

        $cmd[] = $filePath;

        $escaped = array_map('escapeshellarg', $cmd);
        $final   = implode(' ', $escaped) . ' 2>&1';

        Log::info('CUPS print command', ['cmd' => $final]);

        $output  = shell_exec($final);

        Log::info('CUPS output', ['output' => $output]);

        if ($output && (str_contains(strtolower($output), 'error') || str_contains(strtolower($output), 'failed'))) {
            Log::error("Upload print failed: " . $output);
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

        // Log the print job
        PrintLog::create([
            'source' => 'QR',
            'file_name' => $order['file_name'],
            'copies' => $order['copies'],
            'page_count' => $order['page_count'] ?? 1,
            'paper_size' => $order['paper_size'],
            'color_option' => $order['color'] ?? $order['color_option'] ?? 'color',
            'rate_per_page' => $order['rate'] ?? 0,
            'total_amount' => $order['calculated_total'],
        ]);

        // Mark as completed for session (monitoring will determine when to redirect)
        $order['print_completed'] = true;
        Session::put('upload.order', $order);
        Session::save();

        return response()->json([
            'success' => true,
            'message' => 'Print job sent successfully',
            'output' => $output,
            'job_id' => $jobId
        ]);
    }

    // Payment summary
    public function paymentPage(Request $request)
    {
        // Get existing order from session or create new
        $order = Session::get('upload.order', []);

        // Merge with new request data
        $order = array_merge($order, $request->all());
        $order['color'] = $order['color'] ?? $order['color_option'] ?? null;
        $order['paid'] = false;

        // Calculate page count
        $pagesInput = trim($request->input('pages', ''));

        // If empty or "All", get the actual PDF page count
        if (empty($pagesInput) || strtolower($pagesInput) === 'all') {
            $filePath = public_path('storage/uploads/' . $order['file_name']);

            if (file_exists($filePath) && Str::endsWith($order['file_name'], '.pdf')) {
                try {
                    $pdf = new \Smalot\PdfParser\Parser();
                    $document = $pdf->parseFile($filePath);
                    $order['page_count'] = max(1, count($document->getPages()));
                } catch (\Exception $e) {
                    Log::warning("Failed to parse PDF: " . $e->getMessage());
                    $order['page_count'] = 1;
                }
            } else {
                $order['page_count'] = 1;
            }
            $order['pages'] = 'All';
        } else {
            // User specified specific pages - count them
            $order['page_count'] = $this->countPagesFromRanges($pagesInput);
            $order['pages'] = $pagesInput;
        }

        Session::put('upload.order', $order);
        Session::save();

        return view('upload.payment', [
            'order' => $order
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

    // Handle GET requests to payment page (for page reloads/voucher applications)
    public function paymentView()
    {
        $order = Session::get('upload.order');

        if (!$order) {
            return redirect()->route('upload.form')
                ->with('error', 'No active order found. Please start a new print job.');
        }

        // If already paid, redirect to instruction
        if (!empty($order['paid'])) {
            return redirect()->route('upload.instructions');
        }

        return view('upload.payment', ['order' => $order]);
    }

    public function applyVoucher(Request $request)
    {
        Log::info('=== UPLOAD VOUCHER APPLICATION STARTED ===');
        Log::info('Request data:', $request->all());

        try {
            $request->validate([
                'code' => 'required|string',
                'source' => 'required|string'
            ]);

            $code = strtoupper(trim($request->code));
            Log::info("Validating voucher code: {$code}");

            $order = Session::get('upload.order');

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
                    return response()->json([
                        'success' => false,
                        'message' => 'This voucher has already been used'
                    ], 400);
                }

                if ($voucher->isExpired()) {
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

            Session::put('upload.order', $order);
            Session::save();

            Log::info('Voucher applied successfully, session updated');
            Log::info('=== UPLOAD VOUCHER APPLICATION COMPLETED ===');

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

    // Instructions
    public function instruction()
    {
        $order = Session::get('upload.order');

        if (!$order) {
            return redirect()->route('upload.form')
                ->with('error', 'No active order found');
        }

        // Verify payment was completed
        if (empty($order['paid'])) {
            return redirect()->route('upload.payment')->with('error', 'Please complete payment first.');
        }

        return view('upload.instructions', ['order' => $order]);
    }

    // Kiosk polling
    public function checkUpload()
    {
        $latestPath = storage_path('app/latest_upload.json');

        if (!File::exists($latestPath)) {
            return response()->json(['filename' => null]);
        }

        $data = json_decode(file_get_contents($latestPath), true);

        if (!empty($data['filename'])) {
            $filename = $data['filename'];
            file_put_contents($latestPath, json_encode(['filename' => null]));

            return response()->json([
                'filename'  => $filename,
                'timestamp' => $data['timestamp'] ?? null,
            ]);
        }

        return response()->json(['filename' => null]);
    }


    public function handlePayment(Request $request)
    {
        Log::info('=== UPLOAD HANDLE PAYMENT STARTED ===');

        try {
            $order = Session::get('upload.order');

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
                    'redirect' => route('upload.instructions')
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

            // Check if payment is sufficient
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
                            'order' => 'Upload',
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
                    'source' => 'Upload',
                    'expires_at' => Carbon::now()->addDays($expirationDays),
                    'expiration_days' => $expirationDays,
                    'is_redeemed' => false,
                ]);

                Log::info('Upload Change Voucher generated', [
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

            Session::put('upload.order', $order);
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
                    'paper_size' => $order['paper_size'] ?? 'A4',
                    'copies' => $order['copies'] ?? 1,
                    'pages' => $dispenserPages
                ]);

                Log::info("Dispenser started after payment (Upload/QR):", [
                    'paper_size' => $order['paper_size'] ?? 'A4',
                    'copies' => $order['copies'] ?? 1,
                    'pages_sent' => $dispenserPages,
                    'calculated_sheets' => $pagesToPrint * ($order['copies'] ?? 1),
                    'response' => $dispenserResponse->json(),
                    'status' => $dispenserResponse->status()
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to start dispenser after payment (Upload/QR): " . $e->getMessage());
                // Don't fail the payment if dispenser fails, just log it
            }

            Log::info('=== UPLOAD HANDLE PAYMENT COMPLETED SUCCESSFULLY ===');

            return response()->json([
                'success' => true,
                'message' => 'Payment successful!',
                'redirect' => route('upload.instructions'),
                'debug' => [
                    'coins_inserted' => $coinTotal,
                    'order_total' => $orderTotal,
                    'change' => $change
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('=== UPLOAD HANDLE PAYMENT EXCEPTION ===', [
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
    
    public function markCompleted(Request $request)
    {
        $order = Session::get('upload.order');

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'No order found'], 404);
        }

        // Mark as completed
        $order['print_completed'] = true;
        Session::put('upload.order', $order);
        Session::save();

        Log::info('QR Upload print marked as completed');

        return response()->json(['success' => true]);
    }

    public function success()
    {
        $order = Session::get('upload.order');

        if (!$order) {
            return redirect()->route('upload.form')
                ->with('error', 'No active order found');
        }

        // Verify that print was completed
        if (empty($order['print_completed'])) {
            return redirect()->route('upload.instructions')
                ->with('error', 'Please complete printing first.');
        }

        // Clear session after displaying success
        Session::forget('upload.order');
        Session::save();

        return view('upload.success', ['order' => $order]);
    }


}
