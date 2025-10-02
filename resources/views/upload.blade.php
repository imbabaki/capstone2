<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload File</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Pusher + Echo --}}
    <script src="https://js.pusher.com/8.0/pusher.min.js"></script>
    @vite(['resources/js/echo.js']) {{-- or mix if you’re still on Laravel Mix --}}

    <style>
        body {
            font-family: sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: #f8f8f8;
        }
        h2 { margin-bottom: 20px; }
        form { display: flex; flex-direction: column; gap: 12px; }
        button {
            padding: 10px 18px;
            cursor: pointer;
            border: none;
            background: #007bff;
            color: #fff;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.2s;
        }
        button:hover {
            background: #0056b3;
        }
        p.success { color: green; }
    </style>
</head>
<body>

<h2>Upload Your File</h2>

@if(session('success'))
    <p class="success">{{ session('success') }}</p>
@endif

<form id="uploadForm" method="POST" action="{{ route('upload.store') }}" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" required>
    <button type="submit">Upload</button>
</form>

<script>
    // Laravel Echo listener (already configured in echo.js)
    if (window.Echo) {
        window.Echo.channel('file-uploads')
            .listen('.FileUploaded', (e) => {
                console.log('Event received:', e);
                if (e.filename) {
                    window.location.href = `/upload/edit/${e.filename}`;
                }
            });
    } else {
        console.warn("Echo not loaded, fallback to polling only.");
    }

    // Fallback polling every 3 seconds
    async function checkForUpload() {
        try {
            const res = await fetch('{{ route("upload.check") }}');
            if (!res.ok) return;
            const data = await res.json();
            if (data.filename) {
                window.location.href = `/upload/edit/${data.filename}`;
            }
        } catch (err) {
            console.error('Polling error:', err);
        }
    }
    setInterval(checkForUpload, 3000);
</script>

</body>
</html>
