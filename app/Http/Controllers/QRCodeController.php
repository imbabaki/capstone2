<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
{
    public function show()
    {
        $uploadUrl = route('upload.form'); // This will be the target of the QR
        
        $qr = QrCode::size(300)->generate($uploadUrl);
        return view('qr_code', [
            'qr' => $qr,
            'uploadUrl' => $uploadUrl
        ]);
    }
}