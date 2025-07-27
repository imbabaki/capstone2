<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QR Code Upload</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f8f8f8;
        }

        h2 {
            margin-bottom: 20px;
        }

        .qr-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        a {
            display: block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="qr-container">
        <h2>Scan this QR code to upload a file</h2>

        <div>{!! $qr !!}</div>

        <p>Or click: <a href="{{ $uploadUrl }}">{{ $uploadUrl }}</a></p>
    </div>

</body>
</html>
