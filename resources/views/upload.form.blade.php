<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * Display the file upload form.
     */
    public function showUploadForm()
    {
        return view('upload'); // Make sure this Blade file exists: resources/views/upload.blade.php
    }

    /**
     * Handle the uploaded file.
     */
    public function handleUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB limit example
        ]);

        // Store in public disk, inside uploads/
        $path = $request->file('file')->store('uploads', 'public');

        return redirect()->route('upload.form')->with('success', 'File uploaded successfully!');
    }
}
