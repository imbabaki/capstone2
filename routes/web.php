<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BluetoothController;
use App\Http\Controllers\USBController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\QRCodeController;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Controllers\FileUploadController;


// Start screen
Route::get('/start', function () {
    return view('start');
})->name('start');

// Options after clicking Start
Route::get('/options', function () {
    return view('options');
})->name('options');

Route::get('/qr-code', [QrCodeController::class, 'show'])->name('qr.code');
Route::get('/upload', function () {
    return view('upload'); // or your actual upload logic/view
})->name('upload.file');

Route::get('/upload', [FileUploadController::class, 'showForm'])->name('upload.form');
Route::post('/upload', [FileUploadController::class, 'store'])->name('upload.store');
Route::get('/upload/edit/{filename}', [FileUploadController::class, 'edit'])->name('upload.edit');
Route::post('/upload/options/save', [FileUploadController::class, 'saveOptions'])->name('upload.options.save');

// Bluetooth functionality
Route::get('/bluetooth', [BluetoothController::class, 'index'])->name('bluetooth');

// USB Flash Drive functionality
Route::get('/USBFD', [USBController::class, 'index'])->name('USBFD');
Route::post('/USBFD/download', [USBController::class, 'download'])->name('USBFD.download');
Route::get('/USBFD/preview', [USBController::class, 'preview'])->name('USBFD_preview');
Route::post('/USBFD/print', [USBController::class, 'print'])->name('USBFD.print');
Route::get('/USBFD/preview', [USBController::class, 'preview'])->name('USBFD.preview');




