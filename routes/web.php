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
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VoucherManagementController;
use App\Http\Controllers\Admin\VoucherSettingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Admin Authentication Routes (No middleware)
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Emergency Status Check API (No middleware - needs to be accessible during shutdown)
Route::get('/api/emergency-status', [DashboardController::class, 'checkEmergencyStatus'])->name('api.emergency.status');

// Protected Admin Routes (Requires admin middleware)
Route::middleware(['admin'])->group(function () {
    // Dashboard
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.alt');

    // Emergency Shutdown
    Route::post('/admin/emergency-shutdown/toggle', [DashboardController::class, 'toggleEmergencyShutdown'])->name('admin.emergency.toggle');

    // Sales Report
    Route::get('/admin/sales-report', [SalesReportController::class, 'index'])->name('admin.sales.report');

    // Print Settings
    Route::resource('admin/print-settings', PrintSettingController::class);
    Route::get('/admin/print-settings', [PrintSettingController::class, 'index'])->name('admin.print-settings.index');

    // Voucher Management
    Route::get('/admin/vouchers', [VoucherManagementController::class, 'index'])->name('admin.vouchers.index');
    Route::get('/admin/vouchers/generate', [VoucherManagementController::class, 'showGenerateForm'])->name('admin.vouchers.generate');
    Route::post('/admin/vouchers/generate', [VoucherManagementController::class, 'generateManual'])->name('admin.vouchers.generate.store');

    // Voucher Settings
    Route::get('/admin/voucher-settings', [VoucherSettingController::class, 'index'])->name('admin.voucher.settings');
    Route::post('/admin/voucher-settings', [VoucherSettingController::class, 'update'])->name('admin.voucher.settings.update');
});

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

// Payment routes (both GET and POST)
Route::post('/upload/payment', [FileUploadController::class, 'paymentPage'])->name('upload.payment');
Route::get('/upload/payment', [FileUploadController::class, 'paymentView'])->name('upload.payment.view');

// Voucher and payment handling
Route::post('/upload/apply-voucher', [FileUploadController::class, 'applyVoucher'])->name('upload.applyVoucher');
Route::post('/upload/handle-payment', [FileUploadController::class, 'handlePayment'])->name('upload.handlePayment');

// Instructions and printing
Route::get('/upload/instructions', [FileUploadController::class, 'instruction'])->name('upload.instructions');
Route::post('/upload/print', [FileUploadController::class, 'doFinalPrint'])->name('upload.print');
Route::post('/upload/mark-completed', [FileUploadController::class, 'markCompleted'])->name('upload.markCompleted');

// Success and utilities
Route::get('/upload/success', [FileUploadController::class, 'success'])->name('upload.success');
Route::get('/check-upload', [FileUploadController::class, 'checkUpload'])->name('upload.check');

// Legacy route (kept for compatibility)
Route::get('/upload/payments', [FileUploadController::class, 'handlePayment'])->name('upload.payments');


// Bluetooth functionality


// Bluetooth routes
// Bluetooth Routes - Organized and cleaned up
Route::get('/bluetooth', [BluetoothController::class, 'index'])->name('bluetooth.index');
Route::post('/bluetooth/enable', [BluetoothController::class, 'enable'])->name('bluetooth.enable');
Route::post('/bluetooth/disable', [BluetoothController::class, 'disable'])->name('bluetooth.disable');
Route::get('/bluetooth/preview/{filename}', [BluetoothController::class, 'preview'])->name('bluetooth.preview');

// Payment routes (both GET and POST)
Route::post('/bluetooth/payment', [BluetoothController::class, 'payment'])->name('bluetooth.payment');
Route::get('/bluetooth/payment', [BluetoothController::class, 'paymentView'])->name('bluetooth.payment.view');

// Voucher and payment handling
Route::post('/bluetooth/apply-voucher', [BluetoothController::class, 'applyVoucher'])->name('bluetooth.applyVoucher');
Route::post('/bluetooth/handle-payment', [BluetoothController::class, 'handlePayment'])->name('bluetooth.handlePayment');
Route::post('/usbfd/apply-voucher', [USBController::class, 'applyVoucher'])->name('usbfd.applyVoucher');

// Instruction and printing
Route::get('/bluetooth/instruction', [BluetoothController::class, 'instruction'])->name('bluetooth.instruction');
Route::post('/bluetooth/print-job', [BluetoothController::class, 'printJob'])->name('bluetooth.printJob');
Route::post('/bluetooth/mark-completed', [BluetoothController::class, 'markCompleted'])->name('bluetooth.markCompleted');
Route::get('/bluetooth/success', [BluetoothController::class, 'success'])->name('bluetooth.success');
Route::get('/bluetooth/complete', [BluetoothController::class, 'complete'])->name('bluetooth.complete');


// USB Flash Drive flow
Route::get('/USBFD', [USBController::class, 'index'])->name('usbfd.index');
Route::post('/USBFD/review', [USBController::class, 'review'])->name('usbfd.review');
Route::get('/usbfd/payment', [USBController::class, 'paymentPage'])->name('usbfd.payment');
Route::post('/usbfd/process-payment', [USBController::class, 'processPayment'])->name('usbfd.process-payment');
Route::post('/usbfd/payment', [USBController::class, 'handlePayment'])->name('usbfd.payment.handle');
Route::get('/usbfd/instruction', [USBController::class, 'instruction'])->name('usbfd.instruction');
Route::post('/usb/print', [USBController::class, 'doFinalPrint'])->name('usb.print');
Route::post('/usb/mark-completed', [USBController::class, 'markCompleted'])->name('usb.markCompleted');
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

// Voucher routes (public)
Route::get('/voucher/check', [App\Http\Controllers\VoucherController::class, 'check'])->name('voucher.check');
Route::post('/voucher/apply', [App\Http\Controllers\VoucherController::class, 'apply'])->name('voucher.apply');

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