<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BluetoothController;
use App\Http\Controllers\USBController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\Admin\PrintSettingController;
use App\Http\Controllers\OptionsController;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


// Start screen
Route::get('/', function () {
    return view('start');
})->name('start');

// Options
Route::get('/options', [OptionsController::class, 'index'])->name('options');

// QR Code
Route::get('/qr-code', [QRCodeController::class, 'show'])->name('qr.code');

// Upload routes

Route::get('/upload', function () {
    return redirect()->route('upload.form');
});

Route::get('/upload/form', [FileUploadController::class, 'showForm'])->name('upload.form');
Route::post('/upload/form', [FileUploadController::class, 'store'])->name('upload.store');

// Edit settings
Route::get('/upload/edit/{filename}', [FileUploadController::class, 'edit'])->name('upload.edit');

// Payment summary (GET only)
Route::post('/upload/payment', [FileUploadController::class, 'paymentPage'])->name('upload.payment');

// Instructions page (GET only)
Route::get('/upload/instructions', [FileUploadController::class, 'instruction'])->name('upload.instructions');

// Print (POST only)
Route::post('/upload/print', [FileUploadController::class, 'doFinalPrint'])->name('upload.print');

// ✅ Check if a file has been uploaded (for kiosk auto-redirect)
Route::get('/check-upload', [FileUploadController::class, 'checkUpload'])->name('upload.check');
Route::get('/upload/success', [FileUploadController::class, 'success'])->name('upload.success');
Route::get('/upload/payments', [FileUploadController::class, 'handlePayment'])->name('upload.payments');


// Bluetooth functionality


Route::get('/bluetooth', [BluetoothController::class, 'index'])->name('bluetooth.index');
Route::post('/bluetooth/enable', [BluetoothController::class, 'enable'])->name('bluetooth.enable');
Route::get('/bluetooth/print/{filename}', [BluetoothController::class, 'print'])->name('bluetooth.print');

// This route handles Flask redirect and automatically prints
Route::get('/bluetooth/edit/{filename}', [BluetoothController::class, 'handleUploadRedirect'])->name('bluetooth.edit');


// USB Flash Drive flow
Route::get('/USBFD', [USBController::class, 'index'])->name('usbfd.index');
Route::post('/USBFD/review', [USBController::class, 'review'])->name('usbfd.review');
Route::get('/usbfd/payment', [USBController::class, 'paymentPage'])->name('usbfd.payment');
Route::post('/usbfd/process-payment', [USBController::class, 'processPayment'])->name('usbfd.process-payment');
Route::post('/usbfd/payment', [USBController::class, 'handlePayment'])->name('usbfd.payment.handle');
Route::get('/usbfd/instruction', [USBController::class, 'instruction'])->name('usbfd.instruction');
Route::post('/usb/print', [USBController::class, 'doFinalPrint'])->name('usb.print');
Route::get('/USBFD/success', [USBController::class, 'success'])->name('usb.success');
Route::get('/USBFD/preview/{filepath}', [USBController::class, 'preview'])
    ->where('filepath', '.*')
    ->name('USBFD.preview');
Route::get('/usb/status', [App\Http\Controllers\USBController::class, 'status'])->name('usb.status');

//  Coins Slot


Route::get('/coin/total', function () {
    try {
        $response = Http::timeout(2)->get('http://192.168.0.101:5000/coin/total');
        return response()->json(['total' => $response->json('total') ?? 0]);
    } catch (\Exception $e) {
        return response()->json(['total' => 0]);
    }
});




// Admin Pricing
Route::resource('admin/print-settings', PrintSettingController::class);
Route::get('/admin/print-settings', [PrintSettingController::class, 'index'])->name('admin.print-settings.index');

// Realtime USB Flash drive detection
Route::get('/usb-check', function () {
    $user = trim(shell_exec('whoami'));
    $possiblePaths = ["/media/$user", "/media/usb", "/mnt/usb"];

    $mountedUSBs = collect($possiblePaths)
        ->filter(fn($path) => File::exists($path))
        ->flatMap(fn($path) => File::directories($path))
        ->values();

    if ($mountedUSBs->isEmpty()) {
        return response()->json(['count' => 0]);
    }

    $usbPath = $mountedUSBs->first();
    $files = collect(File::files($usbPath))
        ->filter(fn($file) => strtolower($file->getExtension()) === 'pdf');

    return response()->json(['count' => $files->count()]);
});

Route::get('/trigger-dispenser', function() {
    $order = session('order'); // Retrieve saved order (you probably already store this)
    if (!$order) return response()->json(['error' => 'No order found'], 400);

    // Calculate total papers (copies × pages)
    $copies = intval($order['copies'] ?? 1);
    $pages = $order['pages'] ?? '1';
    $paperSize = $order['paper_size'] ?? 'A4';

    // Convert page ranges to count
    $pageCount = 0;
    foreach (explode(',', $pages) as $part) {
        $part = trim($part);
        if (strpos($part, '-') !== false) {
            [$start, $end] = explode('-', $part);
            $pageCount += (intval($end) - intval($start) + 1);
        } else {
            $pageCount += 1;
        }
    }

    $totalPapers = $copies * $pageCount;

    // Send trigger to Raspberry Pi Dispenser API (Flask or local Python)
    try {
        Http::post('http://194.168.4.1:5005/start', [
            'paper_size' => $paperSize,
            'count' => $totalPapers
        ]);
        return response()->json(['message' => 'Dispenser triggered successfully']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});