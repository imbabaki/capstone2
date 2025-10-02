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




// Bluetooth functionality


Route::get('/bluetooth', [BluetoothController::class, 'index'])->name('bluetooth.index');
Route::post('/bluetooth/enable', [BluetoothController::class, 'enable'])->name('bluetooth.enable');
Route::get('/bluetooth/print/{filename}', [BluetoothController::class, 'print'])->name('bluetooth.print');


// USB Flash Drive flow
Route::get('/USBFD', [USBController::class, 'index'])->name('usbfd.index');
Route::get('/USBFD/preview', [USBController::class, 'preview'])->name('usbfd.preview');
Route::post('/USBFD/review', [USBController::class, 'review'])->name('usbfd.review');
Route::post('/usbfd/process', [USBController::class, 'processPayment'])->name('usbfd.process');
Route::post('/usbfd/payment', [USBController::class, 'handlePayment'])->name('usbfd.payment.handle');
Route::get('/usbfd/instruction', [USBController::class, 'instruction'])->name('usbfd.instruction');
Route::post('/usb/print', [USBController::class, 'doFinalPrint'])->name('usb.print');
Route::get('/USBFD/success', [USBController::class, 'success'])->name('usb.success');
Route::post('/usbfd/process-payment', [USBController::class, 'processPayment'])->name('USBFD.processPayment');
Route::get('/usbfd/preview/{filepath}', [USBController::class, 'preview'])
    ->where('filepath', '.*')
    ->name('USBFD.preview');
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
