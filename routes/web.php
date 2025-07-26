<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BluetoothController;
use App\Http\Controllers\USBController;



// Start screen
Route::get('/start', function () {
    return view('start');
})->name('start');

// Options after clicking Start
Route::get('/options', function () {
    return view('options');
})->name('options');

// Bluetooth functionality
Route::get('/bluetooth', [BluetoothController::class, 'index'])->name('bluetooth');

// USB Flash Drive functionality
Route::get('/USBFD', [USBController::class, 'index'])->name('USBFD');
Route::post('/USBFD/download', [USBController::class, 'download'])->name('USBFD.download');
Route::get('/USBFD/preview', [USBController::class, 'preview'])->name('USBFD_preview');
Route::post('/USBFD/print', [USBController::class, 'print'])->name('USBFD.print');
Route::get('/USBFD/preview', [USBController::class, 'preview'])->name('USBFD.preview');




