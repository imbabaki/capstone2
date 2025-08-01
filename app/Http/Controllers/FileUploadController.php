<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function showForm()
    {
        return view('upload_form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // max 10MB
        ]);

        // ✅ Store in 'public' disk so it’s accessible via browser
        $path = $request->file('file')->store('uploads', 'public');

        // ✅ Redirect to edit with filename
        return redirect()->route('upload.edit', ['filename' => basename($path)]);
    }

    public function edit($filename)
    {
        // ✅ Generate public URL for file preview
        $fileUrl = asset('storage/uploads/' . $filename);

        return view('edit_upload', compact('fileUrl', 'filename'));
    }

    // (Optional) Save options after edit
    public function saveOptions(Request $request)
    {
        // Handle print options here (e.g., save to session or DB)
        return back()->with('success', 'Options saved!');
    }
}
