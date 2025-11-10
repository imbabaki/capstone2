<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\PrintSetting;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use App\Models\PrintLog;
use App\Models\Voucher;
use App\Models\VoucherSetting;
use Carbon\Carbon;


class USBController extends Controller
{
    // ✅ Detect mounted USB
    private function getMountedUsbPath(): ?string
    {
        $possible = File::directories('/mnt');
        foreach ($possible as $path) {
            if (File::isDirectory($path) && File::isReadable($path)) {
                return $path; // e.g. /mnt/USBDrive
            }
        }
        return null;
    }

    private function pagesRegex(): string
    {
        return '/^(\d+|\d+-\d+)(,(\d+|\d+-\d+))*$/';
    }

    private function countPagesFromRanges(?string $ranges): int
    {
        if (!$ranges) return 0;
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
        $setting = PrintSetting::where('paper_size', $paperSize)
            ->where('color_option', strtolower($color))
            ->first();
        return $setting ? $setting->price : null;
    }

    private function getDefaultPrinter(): ?string
    {
        $out = trim(shell_exec("lpstat -d 2>/dev/null"));
        if (preg_match('/:\s*(.+)$/', $out, $m)) return trim($m[1]);
        return env('CUPS_PRINTER') ?: null;
    }

    // 🧩 List files from mounted USB
    public function index()
    {
        $usbPath = $this->getMountedUsbPath();
        $error = null;
        $files = collect();

        if (!$usbPath) {
            $error = 'No USB device detected or mounted.';
        } else {
            $parser = new Parser();
            $files = collect(File::files($usbPath))
                ->filter(fn($f) => strtolower($f->getExtension()) === 'pdf')
                ->map(function ($f) use ($parser) {
                    $pages = 1;
                    try {
                        $pdf = $parser->parseFile($f->getRealPath());
                        $pages = count($pdf->getPages()) ?: 1;
                    } catch (\Throwable $e) {}
                    return [
                        'name'  => $f->getFilename(),
                        'path'  => $f->getRealPath(),
                        'pages' => $pages,
                    ];
                })->values();

            if ($files->isEmpty()) $error = 'No PDF files found on USB drive.';
        }

        $pricing = PrintSetting::all()->mapWithKeys(function ($s) {
            $key = strtolower($s->paper_size . '_' . $s->color_option);
            return [$key => $s->price];
        });

        return view('USBFD', [
            'pdfFiles' => $files,
            'pricing'  => $pricing,
            'error'    => $error,
            'usbPath'  => $usbPath,
        ]);
    }

    // ✅ PDF Preview (with encoded path support)
    public function preview($filepath)
    {
        $decodedPath = urldecode($filepath);

        // 🩹 Handle cases where path includes encoded slashes (%2F)
        if (!file_exists($decodedPath)) {
            $decodedPath = str_replace('|', '/', $decodedPath); // optional fallback
        }

        if (!file_exists($decodedPath)) {
            return response()->json(['error' => 'File not found: ' . $decodedPath], 404);
        }

        return response()->file($decodedPath, ['Content-Type' => 'application/pdf']);
    }

  // ✅ Process payment (CREATE ORDER FROM FORM DATA)
public function processPayment(Request $request)
{
    // Validate incoming data
    $request->validate([
        'file' => 'required|string',
        'file_path' => 'required|string',
        'copies' => 'required|integer|min:1',
        'pages' => 'nullable|string',
        'color' => 'required|in:color,grayscale',
        'paper_size' => 'required|string',
        'total' => 'required|numeric|min:0',
    ]);

    // Get the file path and verify it exists
    $filePath = $request->input('file_path');
    if (!file_exists($filePath)) {
        return redirect()->route('usbfd.index')->with('error', 'File not found on USB.');
    }

    // Calculate pricing
    $paperSize = $request->input('paper_size');
    $color = $request->input('color');
    $rate = $this->getRatePerPage($paperSize, $color) ?? 0;
    
    // Count pages
    $pagesInput = trim($request->input('pages', ''));

    // If empty or "All", get the actual PDF page count
    if (empty($pagesInput) || strtolower($pagesInput) === 'all') {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $pageCount = max(1, count($pdf->getPages()));
        } catch (\Throwable $e) {
            $pageCount = 1;
        }
        $pagesInput = 'All';
    } else {
        // User specified specific pages
        $pageCount = $this->countPagesFromRanges($pagesInput);
    }

    $copies = (int) $request->input('copies', 1);
    $calculatedTotal = $rate * $pageCount * $copies;

    // Create order in session
    $order = [
        'file_name'  => $request->input('file'),
        'file_path'  => $filePath,
        'copies'     => $copies,
        'pages'      => $pagesInput,
        'page_count' => $pageCount,
        'color'      => $color,
        'paper_size' => $paperSize,
        'duplex'     => $request->input('duplex', 'one-sided'),
        'fit'        => $request->input('fit', 'none'),
        'rate'       => $rate,
        'subtotal'   => $calculatedTotal,
        'total'      => $calculatedTotal,
        'paid'       => false,
    ];

    Session::put('usb.order', $order);
    Session::save();

    // Now redirect to payment page
    return redirect()->route('usbfd.payment');
}

// 💰 Payment page - SHOW THE PAYMENT INTERFACE
public function paymentPage()
{
    $order = Session::get('usb.order');
    
    if (!$order) {
        return redirect()->route('usbfd.index')->with('error', 'No order found. Please select a file first.');
    }

    // Get coin total from Flask server
    try {
        $response = Http::timeout(3)->get('http://127.0.0.1:5003/coin/total');
        $coinTotal = $response->json('total') ?? 0;
    } catch (\Exception $e) {
        $coinTotal = 0;
    }

    return view('USBFD.payment', [
        'order' => $order,
        'coinTotal' => $coinTotal,
    ]);
}
public function handlePayment()
{
    Log::info('=== USB HANDLE PAYMENT STARTED ===');

    $order = Session::get('usb.order');

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
            'redirect' => route('usbfd.instruction')
        ]);
    }

    // Get coin total from Flask
    try {
        $response = Http::timeout(3)->get('http://127.0.0.1:5003/coin/total');
        $coinTotal = $response->json('total') ?? 0;
        Log::info('Coin total from Flask:', ['amount' => $coinTotal]);
    } catch (\Exception $e) {
        Log::error('Failed to get coin total:', ['error' => $e->getMessage()]);
        $coinTotal = 0;
    }

    $orderTotal = $order['total'] ?? 0;

    if ($coinTotal < $orderTotal) {
        Log::warning('Insufficient payment', ['inserted' => $coinTotal, 'required' => $orderTotal]);
        return response()->json([
            'success' => false,
            'message' => 'Insufficient payment. Please insert ₱' . number_format($orderTotal - $coinTotal, 2)
        ], 400);
    }

    // ✅ Calculate change
    $change = $coinTotal - $orderTotal;
    $voucher = null;

    // ✅ Generate voucher if there's change
    if ($change > 0) {
        $expirationDays = (int) VoucherSetting::getExpirationDays(); // ✅ Cast to int
        
        $voucher = Voucher::create([
            'code' => Voucher::generateCode(),
            'amount' => $change,
            'source' => 'USB',
            'expires_at' => Carbon::now()->addDays($expirationDays), // ✅ Now properly typed
            'expiration_days' => $expirationDays,
        ]);

        Log::info('Voucher generated', [
            'code' => $voucher->code,
            'amount' => $change,
            'expires_at' => $voucher->expires_at,
        ]);
    }

    // Mark as paid
    $order['paid'] = true;
    $order['voucher_code'] = $voucher?->code;
    $order['change_amount'] = $change;
    $order['voucher_expires_at'] = $voucher?->expires_at?->format('M d, Y');
    Session::put('usb.order', $order);
    Session::save();

    // Reset coin counter
    try {
        Http::timeout(2)->post('http://127.0.0.1:5003/coin/reset');
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

        Log::info("Dispenser started after payment (USB):", [
            'paper_size' => $order['paper_size'] ?? 'A4',
            'copies' => $order['copies'] ?? 1,
            'pages_sent' => $dispenserPages,
            'calculated_sheets' => $pagesToPrint * ($order['copies'] ?? 1),
            'response' => $dispenserResponse->json(),
            'status' => $dispenserResponse->status()
        ]);
    } catch (\Exception $e) {
        Log::error("Failed to start dispenser after payment (USB): " . $e->getMessage());
        // Don't fail the payment if dispenser fails, just log it
    }

    Log::info('Payment successful, redirecting to instruction page');
    return response()->json([
        'success' => true,
        'message' => 'Payment successful!',
        'redirect' => route('usbfd.instruction')
    ]);
}
// 🖨️ Execute the actual print job
public function printNow(Request $request)
{
    $order = Session::get('usb.order');
    
    if (!$order) {
        return redirect()->route('usbfd.index')->with('error', 'No order found.');
    }

    // Verify payment was completed
    if (empty($order['paid'])) {
        return redirect()->route('usbfd.payment')->with('error', 'Please complete payment first.');
    }

    // Verify file still exists
    $filePath = $order['file_path'];
    if (!file_exists($filePath)) {
        return redirect()->route('usbfd.index')->with('error', 'File not found on USB.');
    }

    // Get printer
    $printer = $order['printer'] ?? $this->getDefaultPrinter();
    if (!$printer) {
        return back()->with('error', 'No printer configured.');
    }

    // Build print command
    $copies = (int) ($order['copies'] ?? 1);
    $pagesInput = trim($order['pages'] ?? 'All');
    $color = $order['color'] ?? 'grayscale';
    $paperSize = $order['paper_size'] ?? 'A4';
    $duplex = $order['duplex'] ?? 'one-sided';
    $fit = $order['fit'] ?? 'none';

    $cmd = [
        'lp',
        '-d', escapeshellarg($printer),
        '-n', escapeshellarg((string) max(1, $copies))
    ];

    // Add page range only if specific pages are selected (not "All" or empty)
    if (!empty($pagesInput) && strtolower($pagesInput) !== 'all') {
        $cmd[] = '-P ' . escapeshellarg($pagesInput);
    }

    // Color mode
    $cmd[] = '-o ' . ($color === 'grayscale' ? 'ColorModel=Gray' : 'ColorModel=RGB');

    // Paper size
    if ($paperSize) {
        $cmd[] = '-o media=' . escapeshellarg($paperSize);
    }

    // Duplex
    if (in_array($duplex, ['one-sided', 'two-sided-long-edge', 'two-sided-short-edge'], true)) {
        $cmd[] = '-o sides=' . escapeshellarg($duplex);
    }

    // Fit to page
    if ($fit === 'fit-to-page') {
        $cmd[] = '-o fit-to-page';
    }

    // Add file path
    $cmd[] = escapeshellarg($filePath);

    // Execute print command
    $finalCmd = implode(' ', $cmd) . ' 2>&1';
    $output = shell_exec($finalCmd);

    Log::info('USB Print Command', [
        'command' => $finalCmd,
        'output' => $output,
        'order' => $order
    ]);

    // Log the print job
    PrintLog::create([
        'source' => 'USB',
        'file_name' => basename($order['file_path']),
        'copies' => $order['copies'],
        'page_count' => $order['page_count'] ?? 1,
        'paper_size' => $order['paper_size'],
        'color_option' => $order['color'] ?? 'grayscale',
        'rate_per_page' => $order['rate'] ?? 0,
        'total_amount' => $order['total'] ?? 0,
    ]);

    // Keep order in session for success page, mark as completed
    $order['print_completed'] = true;
    Session::put('usb.order', $order);
    Session::save();

    // Return JSON response for AJAX request
    return response()->json([
        'success' => true,
        'message' => 'Print job sent successfully',
        'output' => $output
    ]);
}
    // 📄 Instruction page
    public function instruction()
    {
        $order = session('usb.order');
        if (!$order) {
            return redirect()->route('usbfd.index')->with('error', 'No order found.');
        }

        return view('USBFD.instructions', compact('order'));
    }

    // ✅ Success page
    public function success()
    {
        $order = Session::get('usb.order');

        if (!$order) {
            return redirect()->route('usbfd.index')
                ->with('error', 'No active order found');
        }

        // Verify that print was completed
        if (empty($order['print_completed'])) {
            return redirect()->route('usbfd.instruction')
                ->with('error', 'Please complete printing first.');
        }

        // Clear session after displaying success
        Session::forget('usb.order');
        Session::save();

        return view('USBFD.success', ['order' => $order]);
    }
    // 🖨️ Execute the actual print job (in USBController.php)
// 🖨️ Execute the actual print job (in USBController.php)
    public function doFinalPrint(Request $request)
    {
        // Just call printNow which has the correct implementation
        return $this->printNow($request);
    }
}
