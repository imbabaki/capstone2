<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Choose Input Method</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        h2 {
            margin-bottom: 30px;
        }
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        button {
            width: 200px;
            padding: 15px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h2>Select Input Method</h2>
    <!-- resources/views/Options.blade.php -->



<div class="text-center mt-5">
    <h1>Select Upload Method</h1>
    <div class="mt-4 d-flex justify-content-center gap-3">
        <a href="{{ route('bluetooth') }}" class="btn btn-primary btn-lg">Bluetooth</a>
        <a href="{{ route('USBFD') }}" class="btn btn-primary btn-lg">USB Flash Drive</a>
        <a href="{{ route('qr.code') }}" class="btn btn-success btn-lg">QR Code</a>
    </div>
</div>  


</body>
</html>
