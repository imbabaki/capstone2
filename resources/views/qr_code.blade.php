<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InstaPrint - QR Upload</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Pusher JS -->
    <script src="https://js.pusher.com/8.0/pusher.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #f8f8f8, #eaeaea);
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .qr-container {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.12);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .qr-container img {
            max-width: 100%;
            height: auto;
        }
        a {
            display: inline-block;
            margin-top: 18px;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        footer {
            margin-top: 40px;
            font-size: 0.9rem;
            color: #777;
        }
    </style>
</head>
<body>

<div class="qr-container">
    <h2>Scan to Upload Your File</h2>
    <div>{!! $qr !!}</div>
    <p>Or click here: <a href="{{ $uploadUrl }}">{{ $uploadUrl }}</a></p>
</div>

<footer>
    Powered by <strong>InstaPrint</strong> @ 192.168.4.1
</footer>

<script>
    console.log("Initializing Pusher...");

    // ✅ Enable debug logs (optional, remove after testing)
    Pusher.logToConsole = true;
    const pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {
    wsHost: '{{ env("PUSHER_HOST", "192.168.4.1") }}',
    wsPort: Number("{{ env('PUSHER_PORT', 6001) }}"),
    cluster: 'mt1',          // ✅ REQUIRED even if not using real Pusher
    forceTLS: false,
    encrypted: false,
    disableStats: true,
    enabledTransports: ['ws'], 
    });


    pusher.connection.bind('connected', () => {
        console.log('%c✅ Connected to Pusher WebSocket!', 'color: green');
    });

    pusher.connection.bind('error', (err) => {
        console.error('❌ Pusher Connection Error:', err);
    });

    console.log("Subscribing to channel: file-uploads");
    const channel = pusher.subscribe('file-uploads');

    channel.bind('pusher:subscription_succeeded', () => {
        console.log('%c✅ Subscribed to channel: file-uploads', 'color: green');
    });

    channel.bind('FileUploaded', (e) => {
        console.log("%c📩 Event Received:", "color: blue", e);
        if (e.filename) {
            window.location.href = `/upload/edit/${e.filename}`;
        }
    });

    // ✅ Polling fallback
    setInterval(async () => {
        try {
            const res = await fetch('{{ route("upload.check") }}');
            const data = await res.json();
            if (data.filename) {
                window.location.href = `/upload/edit/${data.filename}`;
            }
        } catch (err) {
            console.error('Polling error:', err);
        }
    }, 3000);
</script>


</body>
</html>
