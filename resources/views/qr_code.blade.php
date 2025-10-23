<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Instaprint - QR Upload</title>
  <meta name="viewport" content="width=1024, height=600, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://js.pusher.com/8.0/pusher.min.js"></script>

  <style>
    html, body {
      width: 100%;
      height: 100%;
      margin: 0;
      padding: 0;
      background: linear-gradient(to bottom right, #1f2937, #111827);
      color: white;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      overflow: hidden;
    }

    /* Top Bar */
    .top-bar {
      background: #0f172a;
      padding: 2vh 4vw;
      font-weight: bold;
      color: #67e8f9;
      border-bottom: 0.5vh solid #0ea5e9;
      text-shadow: 0 0 1vw rgba(103,232,249,0.6);
    }

    .top-bar span {
      display: block;
      color: #d1d5db;
      font-size: 2vh;
      margin-top: 0.5vh;
    }

    /* Main Container */
    .main-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      height: 100vh;
      text-align: center;
      padding-top: 5vh;
    }

    h1 {
      font-size: 6vh;
      font-weight: 900;
      letter-spacing: 0.3vw;
      color: #38bdf8;
      text-shadow: 0 0 2vw rgba(56,189,248,0.6);
      margin-bottom: 3vh;
      animation: fadeIn 1.2s ease forwards;
    }

    /* QR Section */
    .qr-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: rgba(31,41,55,0.9);
      padding: 4vh 6vw;
      border-radius: 3vh;
      box-shadow: 0 0 3vw rgba(0,0,0,0.5);
      width: clamp(55vw, 70vw, 80vw);
      margin-bottom: 4vh;
      animation: fadeIn 1.2s ease forwards;
    }

    .qr-container h2 {
      font-size: 3.5vh;
      font-weight: 800;
      color: #f1f5f9;
      margin-bottom: 2vh;
      text-shadow: 0 0 1vw rgba(56,189,248,0.6);
    }

    .qr-code-wrapper {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 3vh;
      width: 100%;
    }

    .qr-code-wrapper svg,
    .qr-code-wrapper img {
      width: clamp(25vh, 35vh, 40vh);
      height: auto;
      filter: drop-shadow(0 0 2vw rgba(6,182,212,0.8));
    }

    .qr-container p {
      font-size: 2.5vh;
      color: #e2e8f0;
    }

    .qr-container a {
      color: #38bdf8;
      text-decoration: none;
      font-weight: bold;
      transition: color 0.3s;
    }

    .qr-container a:hover {
      color: #dc2626;
    }

    /* Back Button */
    .back-button {
      padding: 2vh 6vw;
      font-size: 3vh;
      font-weight: 900;
      border-radius: 4vh;
      background: linear-gradient(145deg, #0ea5e9, #0284c7);
      color: white;
      text-decoration: none;
      box-shadow: 0 0 2vw rgba(14,165,233,0.9);
      transition: all 0.3s ease-in-out;
    }

    .back-button:hover {
      background: linear-gradient(145deg, #dc2626, #b91c1c);
      transform: scale(1.08);
      box-shadow: 0 0 3vw rgba(220,38,38,0.9);
    }

    footer {
      margin-top: 3vh;
      font-size: 2vh;
      color: #9ca3af;
      text-align: center;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(2vh); }
      to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 1024px) and (max-height: 600px) {
      h1 { font-size: 5vh; }
      .qr-container h2 { font-size: 3vh; }
      .qr-container p { font-size: 2vh; }
      .back-button { font-size: 2.5vh; }
    }
  </style>
</head>

<body>
  <div class="top-bar">
    INSTAPRINT<br>
    <span>PRINTING VENDO MACHINE</span>
  </div>

  <div class="main-container">
    <h1>QR UPLOAD</h1>

    <div class="qr-container">
      <h2>Scan to Upload Your File</h2>

      <!-- ✅ Centered QR Code -->
      <div class="qr-code-wrapper">
        {!! $qr !!}
      </div>

      <p>Or click here: <a href="{{ $uploadUrl }}">{{ $uploadUrl }}</a></p>
    </div>

    <a href="{{ route('options') }}" class="back-button">← BACK</a>
  </div>

  <footer>
    Powered by <strong>Instaprint</strong> @ 192.168.4.1
  </footer>

  <script>
    console.log("Initializing Pusher...");
    Pusher.logToConsole = true;

    const pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {
      wsHost: '{{ env("PUSHER_HOST", "192.168.4.1") }}',
      wsPort: Number("{{ env('PUSHER_PORT', 6001) }}"),
      cluster: 'mt1',
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

    // Polling fallback
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