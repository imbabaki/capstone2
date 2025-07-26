<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrintController extends Controller
{
    public function print(Request $request)
    {
        $filename = $request->input('filename');
        $paperSize = $request->input('paper_size');
        $color = $request->input('color');
        $copies = (int) $request->input('copies');

        $filePath = storage_path('app/usb_files/' . $filename);

        if (!file_exists($filePath)) {
            return back()->with('error', 'File not found.');
        }

        // Build the command for printing
        $options = [];

        // Paper size
        $options[] = '-o media=' . $paperSize;

        // Color or Grayscale
        if ($color === 'grayscale') {
            $options[] = '-o ColorModel=Gray';
        } else {
            $options[] = '-o ColorModel=RGB';
        }

        // Number of copies
        $options[] = '-n ' . $copies;

        $command = 'lp ' . implode(' ', $options) . ' "' . $filePath . '"';

        // Execute the print command
        exec($command, $output, $status);

        if ($status === 0) {
            return back()->with('success', 'Printing started successfully!');
        } else {
            return back()->with('error', 'Failed to print. Command: ' . $command);
        }
    }
}

