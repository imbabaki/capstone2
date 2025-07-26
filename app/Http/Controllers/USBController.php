<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;


class USBController extends Controller
{
    public function index()
    {
        $usbPath = storage_path('app/usb_files'); // Adjust path if needed
        $files = collect(File::files($usbPath))
            ->filter(fn($file) => $file->getExtension() === 'pdf')
            ->map(fn($file) => [
                'name' => $file->getFilename(),
                'path' => $file->getRealPath(),
            ]);

        return view('USBFD', ['pdfFiles' => $files]);
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
        $copies = $request->input('copies');
        $pages = $request->input('pages');
        $color = $request->input('color');

        $usbPath = '/media/usb'; // Adjust path based on your OS and USB mount location
        $filePath = $usbPath . '/' . $file;

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        // Construct the print command
        $cmd = "lp ";

        if ($copies) {
            $cmd .= "-n " . escapeshellarg($copies) . " ";
        }

        if ($pages) {
            $cmd .= "-P " . escapeshellarg($pages) . " ";
        }

        $cmd .= ($color === 'grayscale')
            ? "-o ColorModel=Gray "
            : "-o ColorModel=RGB ";

        $cmd .= escapeshellarg($filePath);

        // Execute the command
        shell_exec($cmd);

        return redirect()->route('USBFD')->with('success', 'File sent to printer.');
    }
}
