<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OptionsController extends Controller
{
    public function index()
    {
        return view('options'); // This still needs options.blade.php to exist
    }
}
