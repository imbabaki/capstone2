<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\PrintSetting;

class USBController extends Controller
{
    public function index()
    {
        $usbPath = storage_path('app/usb_files');

        $files = collect(File::files($usbPath))
            ->filter(fn($file) => $file->getExtension() === 'pdf')
            ->map(fn($file) => [
                'name' => $file->getFilename(),
                'path' => $file->getRealPath(),
            ]);

        $pricing = PrintSetting::all()->mapWithKeys(function ($setting) {
            $key = strtolower($setting->paper_size . '_' . $setting->color_option);
            return [$key => $setting->price];
        });

        return view('USBFD', [
            'pdfFiles' => $files,
            'pricing' => $pricing,
        ]);
    }

    public function download(Request $request)
    {
        $filePath = $request->input('filepath');

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return response()->download($filePath);
    }

    public function print(Request $request)
    {
        $file = $request->input('file');
        $copies = (int) $request->input('copies', 1);
        $pagesInput = $request->input('pages');
        $color = $request->input('color');
        $paperSize = $request->input('paper_size');

        $usbPath = '/media/usb';
        $filePath = $usbPath . '/' . $file;

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        if (!empty($pagesInput) && !preg_match('/^(\d+|\d+-\d+)(,(\d+|\d+-\d+))*$/', $pagesInput)) {
            return redirect()->back()->with('error', 'Invalid page format. Use numbers separated by commas or ranges, e.g., "1,2,10" or "1-3,5".');
        }

        $pages = 0;
        if (!empty($pagesInput)) {
            $parts = explode(',', $pagesInput);
            foreach ($parts as $part) {
                if (strpos($part, '-') !== false) {
                    [$start, $end] = explode('-', $part);
                    $pages += max(0, (int)$end - (int)$start + 1);
                } else {
                    $pages += 1;
                }
            }
        }

        $ratePerPage = $this->getRatePerPage($paperSize, $color);
        if ($ratePerPage === null) {
            return back()->withErrors(['Invalid pricing selected.']);
        }

        $calculated_total = $copies * $pages * $ratePerPage;

        return redirect()->route('USBFD.processPayment', [
            'calculated_total' => $calculated_total,
            'total' => $calculated_total,
            'file' => $file,
            'copies' => $copies,
            'pages' => $pagesInput,
            'color' => $color,
            'paper_size' => $paperSize,
        ]);
    }

    public function processPayment(Request $request)
    {
        $params = [
            'calculated_total' => $request->input('calculated_total'),
            'file' => $request->input('file'),
            'copies' => $request->input('copies'),
            'pages' => $request->input('pages'),
            'color' => $request->input('color'),
            'total' => $request->input('total'),
        ];

        return redirect()->route('USBFD.payment', $params);
    }

    public function paymentPage(Request $request)
    {
        return view('USBFD.payment', [
            'calculated_total' => $request->input('calculated_total'),
            'file' => $request->input('file'),
            'copies' => $request->input('copies'),
            'pages' => $request->input('pages'),
            'color' => $request->input('color'),
            'total' => $request->input('total'),
        ]);
    }

    public function handlePayment(Request $request)
    {
        $file = $request->input('file');
        $copies = (int) $request->input('copies', 1);
        $pagesInput = $request->input('pages');
        $color = $request->input('color');

        $usbPath = '/media/usb';
        $filePath = $usbPath . '/' . $file;

        if (!file_exists($filePath)) {
            return redirect()->route('USBFD')->with('error', 'File not found.');
        }

        $cmd = "lp -n " . escapeshellarg($copies);

        if (!empty($pagesInput)) {
            $cmd .= " -P " . escapeshellarg($pagesInput);
        }

        $cmd .= $color === 'grayscale' ? " -o ColorModel=Gray " : " -o ColorModel=RGB ";
        $cmd .= " " . escapeshellarg($filePath);

        shell_exec($cmd);

        return redirect()->route('instructions');
    }

    public function printNow(Request $request)
    {
        $file = $request->input('file_name');
        $pages = $request->input('total_pages');
        $amount = $request->input('total_amount');

        return redirect()->route('usb.success')->with('message', 'Printing started!');
    }

    public function finalize(Request $request)
    {
        $file = $request->input('file');
        $copies = (int) $request->input('copies', 1);
        $pages = $request->input('pages');
        $color = $request->input('color');
        $paperSize = $request->input('paper_size');

        $pageCount = 0;
        if (!empty($pages)) {
            $parts = explode(',', $pages);
            foreach ($parts as $part) {
                if (strpos($part, '-') !== false) {
                    [$start, $end] = explode('-', $part);
                    $pageCount += max(0, (int)$end - (int)$start + 1);
                } else {
                    $pageCount += 1;
                }
            }
        }

        $rate = $this->getRatePerPage($paperSize, $color);
        if ($rate === null) {
            return back()->withErrors(['Invalid pricing data.']);
        }

        $total = $copies * $pageCount * $rate;
        $filePath = '/media/usb/' . $file;

        return view('USBFD.finalize', compact('file', 'copies', 'pages', 'color', 'total', 'filePath'));
    }

    public function preview(Request $request)
    {
        $path = $request->query('filepath');

        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function getRatePerPage($paperSize, $color)
    {
        $setting = PrintSetting::where('paper_size', $paperSize)
            ->where('color_option', strtolower($color))
            ->first();

        return $setting ? $setting->price : null;
    }
}
