<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\File;


class BluetoothController extends Controller
{
    public function index()
    {
        return view('bluetooth');
    }

    public function enableBluetooth(Request $request)
    {
        // Example: run a shell command to enable Bluetooth or start file receive
        // Replace with your actual command/script
        $process = new Process(['bash', '-c', 'echo "Simulating Bluetooth receive"']); 

        try {
            $process->mustRun();
            return response()->json(['status' => 'Bluetooth enabled and ready to receive PDF.']);
        } catch (ProcessFailedException $exception) {
            return response()->json(['status' => 'Failed to enable Bluetooth.']);
        }
    }
    public function listPDFs()
{
    $path = '/home/instaprint/bluetooth_uploads'; // make sure this folder exists
    if (!File::exists($path)) {
        File::makeDirectory($path, 0777, true);
    }

    $files = collect(File::files($path))
        ->filter(fn($file) => strtolower($file->getExtension()) === 'pdf')
        ->map(fn($file) => ['name' => $file->getFilename()])
        ->values();

    return response()->json(['pdfFiles' => $files]);
}
}
