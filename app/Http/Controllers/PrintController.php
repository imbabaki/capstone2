<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrintController extends Controller
{
     public function print(Request $request, $filename)
    {
        $filePath = "/home/instaprint/bluetooth_uploads/" . $filename;

        if (!file_exists($filePath)) {
            return back()->with('error', 'File not found.');
        }

        $printer = 'EPSON_L120_Series';
        $copies  = (int)($request->input('copies', 1));
        $pages   = trim($request->input('pages', '')); // empty = print all
        $color   = $request->input('color_option', 'color');
        $paper   = $request->input('paper_size');
        $duplex  = $request->input('duplex', 'one-sided');
        $fit     = $request->input('fit', 'none');

        // Build CUPS command
        $cmd = ['lp', '-d', $printer, '-n', (string) max(1, $copies)];

        // ✅ Only add page-ranges if user specified
        if (!empty($pages)) {
            $cmd[] = '-o';
            $cmd[] = 'page-ranges=' . $pages;
        }

        // Color option
        $cmd[] = '-o';
        $cmd[] = ($color === 'grayscale') ? 'ColorModel=Gray' : 'ColorModel=RGB';

        // Paper size
        if (!empty($paper)) {
            $cmd[] = '-o';
            $cmd[] = 'media=' . $paper;
        }

        // Duplex printing
        if (in_array($duplex, ['one-sided','two-sided-long-edge','two-sided-short-edge'], true)) {
            $cmd[] = '-o';
            $cmd[] = 'sides=' . $duplex;
        }

        // Fit to page
        if ($fit === 'fit-to-page') {
            $cmd[] = '-o';
            $cmd[] = 'fit-to-page';
        }

        $cmd[] = $filePath;

        // Escape arguments safely
        $escaped = array_map('escapeshellarg', $cmd);
        $final   = implode(' ', $escaped) . ' 2>&1';

        Log::info('Bluetooth CUPS print', ['cmd' => $final]);

        $output = shell_exec($final);

        Log::info('CUPS output', ['output' => $output]);

        return back()->with('success', "Printing all pages of: $filename | Output: " . $output);
    }
}

