<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function showUploadForm()
    {
        return view('upload'); // your blade file
    }

    public function handleUpload(Request $request)
    {
        $request->validate(['file' => 'required|file']);
        $path = $request->file('file')->store('uploads', 'public');
        return back()->with('success', 'File uploaded successfully!');
    }
}

