<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Instaprint - QR Upload</title>
  <meta name="viewport" content="width=1024, height=600, initial-scale=1.0">
  <script src="/vendor/pusher/pusher.min.js"></script>

  <style>
    html, body {
      width: 100vw;
      height: 100vh;
      margin: 0;
      padding: 0;
      overflow: hidden;
      background: #0f172a;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #e2e8f0;
    }

    /* Top Bar */
    .top-bar {
      background: #0f172a;
      color: #67e8f9;
      padding: 0.8vh 1.5vw;
      height: 7vh;
      font-weight: bold;
      text-shadow: 0 0 1vw rgba(103,232,249,0.6);
      border-bottom: 0.2vh solid #0ea5e9;
      font-size: 2vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    }

    .top-bar span {
      font-size: 1.2vh;
      color: #d1d5db;
      display: block;
      margin-top: 0.2vh;
    }

    /* Main Container */
    .main-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: calc(100vh - 7vh);
      text-align: center;
      padding: 2vh;
    }

    h1 {
      font-size: 5vh;
      font-weight: 900;
      color: #38bdf8;
      text-shadow: 0 0 2vw rgba(56,189,248,0.6);
      margin-bottom: 2vh;
      animation: fadeIn 1.2s ease forwards;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* QR Section */
    .qr-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: linear-gradient(145deg, #1e293b, #0f172a);
      padding: 2.5vh 6vw;
      border-radius: 1.5vh;
      border: 3px solid #0ea5e9;
      box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4);
      width: clamp(55vw, 70vw, 80vw);
      margin-bottom: 2vh;
      animation: fadeIn 1.2s ease forwards;
    }

    .qr-container h2 {
      font-size: 3vh;
      font-weight: 700;
      color: #38bdf8;
      margin-bottom: 1.5vh;
      text-shadow: 0 0 1.5vh rgba(56, 189, 248, 0.8);
    }

    .qr-code-wrapper {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 2vh;
      width: 100%;
      background: white;
      padding: 1.5vh;
      border-radius: 1vh;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    .qr-code-wrapper svg,
    .qr-code-wrapper img {
      width: clamp(22vh, 28vh, 32vh);
      height: auto;
    }

    .qr-container p {
      font-size: 2vh;
      font-weight: 600;
      color: #e2e8f0;
    }

    .qr-container a {
      color: #38bdf8;
      text-decoration: none;
      font-weight: bold;
      transition: color 0.3s;
    }

    .qr-container a:hover {
      color: #0ea5e9;
    }

    /* Back Button */
    .back-button {
      margin-top: 1vh;
      padding: 1.5vh 6vw;
      font-size: 2.5vh;
      font-weight: 900;
      border-radius: 4vh;
      background: linear-gradient(145deg,#0ea5e9,#0284c7);
      color: white;
      text-decoration: none;
      box-shadow: 0 4px 12px rgba(14,165,233,0.4);
      transition: all 0.3s ease;
      display: inline-block;
    }

    .back-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(14,165,233,0.6);
    }
  </style>
</head>

<body>
  <div class="top-bar">
    INSTAPRINT<br><span>PRINTING VENDO MACHINE</span>
  </div>

  <div class="main-container">
    <h1>QR UPLOAD</h1>

    <div class="qr-container">
      <h2>Scan to Upload Your File</h2>

      <!-- ✅ Centered QR Code -->
      <div class="qr-code-wrapper">
        {!! $qr !!}
      </div>

      <p>Or visit: <a href="{{ $uploadUrl }}" target="_blank">{{ $uploadUrl }}</a></p>
    </div>

    <a href="{{ route('options') }}" class="back-button">← BACK</a>
  </div>

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
  @include('partials.emergency-check')
  @include('partials.hide-url')
</body>
</html>