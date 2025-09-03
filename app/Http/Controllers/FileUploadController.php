<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Models\PrintSetting;

class FileUploadController extends Controller
{
    // Show upload form
    public function showForm()
    {
        return view('upload_form');
    }

    // Handle file upload
    public function store(Request $request)
    {
        if (!$request->hasFile('file')) {
            return back()->with('info', 'No file uploaded.');
        }

        $file = $request->file('file');
        if (!$file->isValid()) {
            return back()->with('error', 'Uploaded file is not valid.');
        }

        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads'), $filename);

        // Store session consistently with full file path
        session(['usb.order' => [
            'file_name'  => $filename,
            'file_path'  => public_path('uploads/' . $filename),
            'copies'     => 1,
            'pages'      => 1, // default all pages
            'color'      => 'color',
            'paper_size' => 'A4',
            'duplex'     => 'one-sided',
            'fit'        => 'none',
            'total'      => 0,
            'paid'       => false,
        ]]);

        return redirect()->route('upload.edit', ['filename' => $filename])->with('success', 'File uploaded successfully: ' . $filename);
    }

    // Show edit page with preview and options
    public function edit($filename)
    {
        $fileUrl = asset('uploads/' . $filename);
        $pricing = PrintSetting::all();
        $order = session('usb.order', []);

        return view('edit_upload', compact('fileUrl', 'filename', 'pricing', 'order'));
    }

   public function doFinalPrint(Request $request)
    {
    $order = session('usb.order');

    if (!$order) {
        return back()->with('error', 'No order in session.');
    }

    $filePath = public_path('uploads/' . $order['file_name']);

    if (!File::exists($filePath)) {
        Log::error('Print failed: File not found', ['path' => $filePath]);
        return back()->with('error', 'File not found: ' . $filePath);
    }

    $printer = $order['printer'] ?? 'EPSON_L120_Series';
    $copies  = (int)($order['copies'] ?? 1);
    $pages   = $order['pages'] ?? 1;
    $color   = $order['color'] ?? 'color';
    $paper   = $order['paper_size'] ?? null;
    $duplex  = $order['duplex'] ?? 'one-sided';
    $fit     = $order['fit'] ?? 'none';

    $cmd = ['lp', '-d', $printer, '-n', (string) max(1, $copies)];

    if (!empty($pages)) {
        $cmd[] = '-o';
        $cmd[] = 'page-ranges=' . $pages;
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

    // ❌ don’t clear here
    // session()->forget('usb.order');

    return redirect()->route('USBFD.success')->with('success', 'Print job sent: ' . $output);
    }




    public function paymentPage()
    {
    $order = session('usb.order');
    if (!$order) {
        return redirect()->route('upload.form')->with('error', 'No order found.');
    }

    return view('upload.payment', compact('order'));
    }

    public function instruction()
    {
    $order = session('usb.order');
    if (!$order) {
        return redirect()->route('upload.form')->with('error', 'No order found.');
    }

    return view('upload.instructions', compact('order'));
    }
}
