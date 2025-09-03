<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
{
    public function show()
    {
        $uploadUrl = 'http://192.168.1.26:8000/upload'; //change to actual to your ip address
        
        $qr = QrCode::size(300)->generate($uploadUrl);
        return view('qr_code', [
            'qr' => $qr,
            'uploadUrl' => $uploadUrl
        ]);
    }
}