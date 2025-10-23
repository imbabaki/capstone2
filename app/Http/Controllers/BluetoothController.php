<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class BluetoothController extends Controller
{
    public function index()
    {
        $path = "/home/instaprint/uploads"; // unified folder

        $files = File::exists($path)
            ? collect(File::files($path))->map(fn($file) => $file->getFilename())
            : collect([]);

        return view('bluetooth.index', ['files' => $files]);
    }

    public function enable()
    {
        shell_exec('echo -e "power on\ndiscoverable on\npairable on\n" | sudo bluetoothctl');
        return back()->with('success', 'Bluetooth is now discoverable! Pair from your phone.');
    }

    public function print($filename)
    {
        $filePath = "/home/instaprint/uploads/" . $filename;

        // ✅ Step 1: File exists check
        if (!file_exists($filePath)) {
            return back()->with('error', 'File not found at ' . $filePath);
        }

        // ✅ Step 2: Build safe command with full path + output capture
        $cmd = "sudo /usr/bin/lp " . escapeshellarg($filePath) . " 2>&1";

        $output = shell_exec($cmd);

        // ✅ Step 3: Detect and show CUPS error output
        if (!$output) {
            return back()->with('error', "No response from lp command. Check CUPS or permissions.");
        }

        if (str_contains(strtolower($output), 'error') || str_contains(strtolower($output), 'failed')) {
            return back()->with('error', "Print failed: " . $output);
        }

        return back()->with('success', "Printing: $filename <br><small>$output</small>");
    }

    public function edit($filename)
    {
        $filePath = "/home/instaprint/uploads/" . $filename;

        if (file_exists($filePath)) {
            $cmd = "sudo /usr/bin/lp " . escapeshellarg($filePath) . " 2>&1";
            $output = shell_exec($cmd);
        }

        return redirect()->route('bluetooth.index')
            ->with('success', "Auto-printed: $filename <br><small>$output</small>");
    }

}
