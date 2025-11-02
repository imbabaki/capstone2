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
    $pagesInput = $request->input('pages', '');
    $pageCount = $this->countPagesFromRanges($pagesInput);
    
    // If no page range specified, get total pages from PDF
    if ($pageCount === 0) {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $pageCount = max(1, count($pdf->getPages()));
        } catch (\Throwable $e) {
            $pageCount = 1;
        }
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
    $order = Session::get('usb.order');
    
    if (!$order) {
        return redirect()->route('usbfd.index')->with('error', 'No order found.');
    }

    if (!empty($order['paid'])) {
        return redirect()->route('usbfd.instruction');
    }

    // Get coin total from Flask
    try {
        $response = Http::timeout(3)->get('http://127.0.0.1:5003/coin/total');
        $coinTotal = $response->json('total') ?? 0;
    } catch (\Exception $e) {
        $coinTotal = 0;
    }

    $orderTotal = $order['total'] ?? 0;

    if ($coinTotal < $orderTotal) {
        return back()->with('error', 'Insufficient payment. Please insert ₱' . number_format($orderTotal - $coinTotal, 2));
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

    return redirect()->route('usbfd.instruction')->with('message', 'Payment successful!');
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
    $pagesInput = $order['pages'] ?? '';
    $color = $order['color'] ?? 'grayscale';
    $paperSize = $order['paper_size'] ?? 'A4';
    $duplex = $order['duplex'] ?? 'one-sided';
    $fit = $order['fit'] ?? 'none';

    $cmd = [
        'lp',
        '-d', escapeshellarg($printer),
        '-n', escapeshellarg((string) max(1, $copies))
    ];

    // Add page range if specified
    if (!empty($pagesInput)) {
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
        'color_option' => $order['color_option'],
        'rate_per_page' => $order['rate'] ?? 0,
        'total_amount' => $order['calculated_total'] ?? 0,
    ]);

    // Keep order in session for success page, mark as completed
    $order['print_completed'] = true;
    Session::put('usb.order', $order);
    Session::save();

    return redirect()->route('usb.success')->with('message', 'Print job sent successfully!');
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
public function doFinalPrint(Request $request)
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
    $pagesInput = $order['pages'] ?? '';
    $color = $order['color'] ?? 'grayscale';
    $paperSize = $order['paper_size'] ?? 'A4';
    $duplex = $order['duplex'] ?? 'one-sided';
    $fit = $order['fit'] ?? 'none';

    $cmd = [
        'lp',
        '-d', escapeshellarg($printer),
        '-n', escapeshellarg((string) max(1, $copies))
    ];

    // Add page range if specified
    if (!empty($pagesInput)) {
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

    // ✅ LOG THE PRINT JOB
    PrintLog::create([
        'source' => 'USB',
        'file_name' => $order['file_name'],
        'copies' => $order['copies'],
        'page_count' => $order['page_count'],
        'paper_size' => $order['paper_size'],
        'color_option' => $order['color'],
        'rate_per_page' => $order['rate'],
        'total_amount' => $order['total'],
    ]);


    // Clear session order
    Session::forget('usb.order');
    Session::save();

    return redirect()->route('usb.success')->with('message', 'Print job sent successfully!');
}
}
