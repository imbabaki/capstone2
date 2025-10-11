<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class BluetoothController extends Controller
{
    public function index()
    {
        $path = "/home/instaprint/uploads"; // ✅ unified folder
        $files = File::exists($path)
            ? collect(File::files($path))->map(fn($file) => $file->getFilename())
            : collect([]);

        return view('bluetooth.index', ['files' => $files]);
    }

    public function enable()
    {
        // Make Bluetooth discoverable + pairable indefinitely
        shell_exec('echo -e "power on\ndiscoverable on\npairable on\ntimeout discoverable 0\n" | sudo bluetoothctl');

        return back()->with('success', 'Bluetooth is now discoverable! Pair from your phone.');
    }

    public function print($filename)
    {
        $filePath = "/home/instaprint/uploads/" . $filename; // ✅ unified folder

        if (!file_exists($filePath)) {
            return back()->with('error', 'File not found.');
        }

        shell_exec("lp " . escapeshellarg($filePath));

        return back()->with('success', "Printing: $filename");
    }

    // Optional: auto-print when Flask redirects
    public function edit($filename)
    {
        $filePath = "/home/instaprint/uploads/" . $filename;

        if (file_exists($filePath)) {
            shell_exec("lp " . escapeshellarg($filePath));
        }

        return redirect()->route('bluetooth.index')->with('success', "Auto-printed: $filename");
    }
}
