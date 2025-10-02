<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\PrintSetting;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;

class USBController extends Controller
{
    private function getMountedUsbPath(): ?string
    {
        $user = trim(shell_exec('whoami'));
        $candidates = ["/media/$user", "/media/usb", "/mnt/usb"];

        foreach ($candidates as $root) {
            if (File::exists($root)) {
                $dirs = File::isDirectory($root) ? File::directories($root) : [];
                if (!empty($dirs)) return $dirs[0];
                return $root;
            }
        }
        return null;
    }

    private function safeJoin(string $base, string $name): ?string
    {
        $full = realpath($base . DIRECTORY_SEPARATOR . $name);
        if ($full === false) return null;
        if (strpos($full, realpath($base)) !== 0) return null;
        return $full;
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

    private function sanitizeOption(string $value): ?string
    {
        return preg_match('/^[A-Za-z0-9._-]+$/', $value) ? $value : null;
    }

    public function index()
    {
        $usbPath = $this->getMountedUsbPath();
        $error = null;
        $files = collect();

        if (!$usbPath) {
            $error = 'No USB device detected.';
        } else {
            if (File::isDirectory($usbPath) && File::isReadable($usbPath)) {
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

                if ($files->isEmpty()) $error = 'No PDF files found on the USB drive.';
            } else {
                $error = 'Cannot read USB directory. Check permissions.';
            }
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

    public function review(Request $request)
    {
        $request->validate([
            'file'       => 'required|string',
            'copies'     => 'required|integer|min:1|max:100',
            'color'      => 'required|in:color,grayscale',
            'paper_size' => 'required|string',
            'pages'      => ['nullable', 'string'],
            'duplex'     => 'nullable|in:one-sided,two-sided-long-edge,two-sided-short-edge',
            'fit'        => 'nullable|in:none,fit-to-page',
            'printer'    => 'nullable|string',
        ]);

        $usbPath = $this->getMountedUsbPath();
        if (!$usbPath) return back()->with('error', 'USB not mounted.');

        $fileRealPath = $this->safeJoin($usbPath, $request->input('file'));
        if (!$fileRealPath || !file_exists($fileRealPath)) {
            return back()->with('error', 'File not found on USB.');
        }

        $pagesInput = trim((string)$request->input('pages', ''));
        if ($pagesInput !== '' && !preg_match($this->pagesRegex(), $pagesInput)) {
            return back()->with('error', 'Invalid page format. Use "1,2,4-6" etc.');
        }

        $copies    = (int)$request->input('copies', 1);
        $paperSize = $request->input('paper_size');
        $color     = $request->input('color');
        $rate      = $this->getRatePerPage($paperSize, $color);
        if ($rate === null) return back()->with('error', 'Pricing not configured for that paper/color.');

        $pageCount = $this->countPagesFromRanges($pagesInput);
        if ($pageCount === 0) {
            try {
                $parser = new Parser();
                $pdf    = $parser->parseFile($fileRealPath);
                $pageCount = max(1, count($pdf->getPages()));
            } catch (\Throwable $e) {
                $pageCount = 1;
            }
        }

        $subtotal = $copies * $pageCount * $rate;

        $order = [
            'file_name'  => basename($fileRealPath),
            'file_path'  => $fileRealPath,
            'copies'     => $copies,
            'pages'      => $pagesInput,
            'page_count' => $pageCount,
            'color'      => $color,
            'paper_size' => $paperSize,
            'duplex'     => $request->input('duplex', 'one-sided'),
            'fit'        => $request->input('fit', 'none'),
            'printer'    => $request->input('printer') ?? $this->getDefaultPrinter(),
            'rate'       => $rate,
            'subtotal'   => $subtotal,
            'total'      => $subtotal,
            'paid'       => false,
        ];

        session(['usb.order' => $order]);

        return redirect()->route('usbfd.payment');
    }



public function paymentPage(Request $request)
{
    $order = session('usb.order');
    if (!$order) return redirect()->route('usbfd.index')->with('error', 'No job in progress.');

    try {
        $response = Http::timeout(2)->get('http://192.168.0.101:5000/coin/total');
        $coinTotal = $response->json('total') ?? 0;
    } catch (\Exception $e) {
        $coinTotal = 0; // fallback if Pi is offline
    }

    return view('USBFD.payment', [
        'order'     => $order,
        'coinTotal' => $coinTotal,
    ]);
}


    public function doFinalPrint(Request $request)
    {
        $order = session('usb.order');
        if (!$order) return redirect()->route('usbfd.index')->with('error', 'No job in progress.');
        if (empty($order['paid'])) return redirect()->route('usbfd.payment')->with('error', 'Please complete payment first.');

        $usbPath = $this->getMountedUsbPath();
        if (!$usbPath) return redirect()->route('usbfd.index')->with('error', 'USB not mounted.');

        $real = realpath($order['file_path']);
        if (!$real || strpos($real, realpath($usbPath)) !== 0) {
            return redirect()->route('usbfd.index')->with('error', 'File path invalid or not on USB.');
        }

        // ✅ Initialize variables from $order
        $printer = $order['printer'] ?? $this->getDefaultPrinter();
        $copies  = (int)($order['copies'] ?? 1);
        $pages   = $order['pages'] ?? '';
        $color   = $order['color'] ?? 'color';
        $paper   = $order['paper_size'] ?? null;
        $duplex  = $order['duplex'] ?? 'one-sided';
        $fit     = $order['fit'] ?? 'none';

        if (!$printer) return back()->with('error', 'No printer configured.');

        $cmd = ['lp', '-d', $printer, '-n', (string) max(1, $copies)];

        if (!empty($pages)) {
            $cmd[] = '-o';
            $cmd[] = 'page-ranges=' . $pages;
        }

        $cmd[] = '-o';
        $cmd[] = ($color === 'grayscale') ? 'ColorModel=Gray' : 'ColorModel=RGB';

        if ($paper && ($opt = $this->sanitizeOption($paper))) {
            $cmd[] = '-o';
            $cmd[] = 'media=' . $opt;
        }

        if (in_array($duplex, ['one-sided','two-sided-long-edge','two-sided-short-edge'], true)) {
            $cmd[] = '-o';
            $cmd[] = 'sides=' . $duplex;
        }

        if ($fit === 'fit-to-page') {
            $cmd[] = '-o';
            $cmd[] = 'fit-to-page';
        }

        $cmd[] = $real;

        $escaped = array_map('escapeshellarg', $cmd);
        $final = implode(' ', $escaped) . ' 2>&1';
        $output = shell_exec($final);
        Log::info('CUPS print command', ['cmd' => $final, 'output' => $output]);

        session()->forget('usb.order');

        return redirect()->route('usb.success')->with('message', 'Print job sent to CUPS.');
    }

    public function preview($filepath)
    {
        $filepath = urldecode($filepath);
        if (!file_exists($filepath)) abort(404, "File not found");

        return Response::file($filepath, ['Content-Type' => 'application/pdf']);
    }

   public function processPayment(Request $request)
{
    $usbPath = $this->getMountedUsbPath();
    if (!$usbPath) return redirect()->route('usbfd.index')->with('error', 'USB not mounted.');

    $realPath = realpath($request->input('file_path'));
    if (!$realPath || strpos($realPath, realpath($usbPath)) !== 0) {
        return redirect()->route('usbfd.index')->with('error', 'Invalid file path.');
    }

    $copies    = (int) $request->input('copies', 1);
    $pages     = $request->input('pages');
    $color     = $request->input('color');
    $paperSize = $request->input('paper_size');
    $rate      = $this->getRatePerPage($paperSize, $color) ?? 0;

    $pageCount = $this->countPagesFromRanges($pages);
    if ($pageCount === 0) {
        try {
            $parser = new Parser();
            $pdf    = $parser->parseFile($realPath);
            $pageCount = max(1, count($pdf->getPages()));
        } catch (\Throwable $e) {
            $pageCount = 1;
        }
    }

    $subtotal = $copies * $pageCount * $rate;

    $order = [
        'file_name'  => basename($realPath),
        'file_path'  => $realPath,
        'copies'     => $copies,
        'pages'      => $pages,
        'page_count' => $pageCount,
        'color'      => $color,
        'paper_size' => $paperSize,
        'duplex'     => $request->input('duplex', 'one-sided'),
        'rate'       => $rate,
        'subtotal'   => $subtotal,
        'total'      => $subtotal,
        'paid'       => false,
    ];

    session(['usb.order' => $order]);

    // ✅ Add coinTotal here
    try {
        $response = Http::timeout(2)->get('http://192.168.0.101:5000/coin/total');
        $coinTotal = $response->json('total') ?? 0;
    } catch (\Exception $e) {
        $coinTotal = 0;
    }

    return view('USBFD.payment', [
        'order'     => $order,
        'coinTotal' => $coinTotal,
    ]);
}


    public function handlePayment(Request $request)
    {
        $order = session('usb.order');
        if (!$order) return redirect()->route('usbfd.index')->with('error', 'No order found.');

        $order['paid'] = true;
        session(['usb.order' => $order]);

        return redirect()->route('usbfd.instruction');
    }
public function instruction()
    {
        $order = session('usb.order');
        if (!$order) return redirect()->route('usbfd.index')->with('error', 'No order found.');

        return view('USBFD.instructions', compact('order'));
    }
    public function success()
    {
        // You can return a view
        return view('USBFD.success');

        // Or just return a simple response
        // return response()->json(['message' => 'USB operation successful']);
    }
    
}
