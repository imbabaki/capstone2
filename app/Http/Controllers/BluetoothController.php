<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BluetoothController extends Controller
{
    public function index()
    {
        return view('bluetooth');
    }
}