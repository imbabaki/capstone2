<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'A4_total_print',
        'Short_total_print',
        'Legal_total_print',
        'color_total_print',
        'grayscale_total_print',
        'USB_total_print',
        'Bluetooth_total_print',
        'QR_total_print',
        'total_pages_print',
        'total_amount',
    ];
}
