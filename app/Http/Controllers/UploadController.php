<?php
namespace App\Http\Controllers;

use App\Models\UploadToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UploadController extends Controller
{
    public function generateQr()
    {
        $token = Str::uuid(); // unique token
        UploadToken::create(['token' => $token]);

        $uploadUrl = route('upload.form', ['token' => $token]);

        $qr = QrCode::size(250)->generate($uploadUrl);

        return view('qr_code', compact('qr', 'uploadUrl'));
    }

    public function showForm($token)
    {
        if (!UploadToken::where('token', $token)->exists()) {
            abort(404);
        }

        return view('upload_form', compact('token'));
    }

    public function uploadFile(Request $request, $token)
    {
        if (!UploadToken::where('token', $token)->exists()) {
            abort(403, 'Invalid or expired token');
        }

        $request->validate([
            'file' => 'required|file|max:10240', // max 10MB
        ]);

        $path = $request->file('file')->store('uploads');

        return back()->with('success', 'File uploaded successfully!');
    }

    public function show()
    {
        return view('upload');
    }
    
    public function create()
    {
        return view('upload'); // create this view in resources/views/upload.blade.php
    }


    public function store(Request $request)
    {
        $request->validate([

            'file' => 'required|file|max:10240', // 10MB limit
        ]);

        $path = $request->file('file')->store('uploads', 'public');

        return back()->with('success', 'File uploaded successfully! Path: ' . $path);

    }
}