<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Models\PrintSetting;
use App\Events\FileUploaded;

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
        $file->move(public_path('uploads'), $filename);

        // ✅ Save latest upload for kiosk polling
        $latestPath = storage_path('app/latest_upload.json');
        file_put_contents($latestPath, json_encode([
            'filename'  => $filename,
            'timestamp' => now()->toDateTimeString(),
        ]));

        // 🔔 Broadcast event to WebSocket listeners
        event(new FileUploaded($filename));

        // Phone: show success page
        return response()->view('upload_success', [
            'filename' => $filename
        ]);
    }

    // Show edit page (kiosk after auto-redirect)
    public function edit($filename)
    {
        $fileUrl = asset('uploads/' . $filename);
        $pricing = PrintSetting::all();
        $order   = session('usb.order', []);

        return view('edit_upload', compact('fileUrl', 'filename', 'pricing', 'order'));
    }

    // Final print command
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

        return redirect()->route('USBFD.success')->with('success', 'Print job sent: ' . $output);
    }

    // Payment summary
public function paymentPage(Request $request)
{
    // Store order in session
    session(['usb.order' => $request->all()]);

    $order['color'] = $order['color'] ?? $order['color_option'] ?? null;
    
    return view('upload.payment', [
        'order' => $request->all()
    ]);
}


    // Instructions
    public function instruction()
    {
        $order = session('usb.order');
        if (!$order) {
            return redirect()->route('upload.form')->with('error', 'No order found.');
        }

        return view('upload.instructions', compact('order'));
    }

    // ✅ New: kiosk polling to check upload
    public function checkUpload()
    {
        $latestPath = storage_path('app/latest_upload.json');

        if (!File::exists($latestPath)) {
            return response()->json(['filename' => null]);
        }

        $data = json_decode(file_get_contents($latestPath), true);

        // Clear file after sending (so kiosk won’t loop forever)
        if (!empty($data['filename'])) {
            $filename = $data['filename'];

            // Reset JSON so next poll waits for new upload
            file_put_contents($latestPath, json_encode(['filename' => null]));

            return response()->json([
                'filename'  => $filename,
                'timestamp' => $data['timestamp'] ?? null,
            ]);
        }

        return response()->json(['filename' => null]);
    }
}
