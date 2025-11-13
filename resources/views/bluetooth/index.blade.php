<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=800, height=480, initial-scale=1.0">
    <title>Bluetooth Print - Instaprint</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background: #0f172a;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #e2e8f0;
        }

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

        .top-bar .subtitle {
            font-size: 1.2vh;
            color: #d1d5db;
            display: block;
            margin-top: 0.2vh;
            font-weight: normal;
        }

        .main-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: calc(100vh - 7vh);
            text-align: center;
            padding: 2vh 3vw;
        }

        h1 {
            font-size: 6vh;
            font-weight: 900;
            color: #38bdf8;
            text-shadow: 0 0 2vw rgba(56,189,248,0.6);
            margin-bottom: 3vh;
            animation: fadeIn 1.2s ease forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .bluetooth-icon {
            font-size: 15vh;
            color: #38bdf8;
            margin-bottom: 3vh;
            animation: float 3s ease-in-out infinite;
            filter: drop-shadow(0 8px 16px rgba(56,189,248,0.5));
        }

        .main-card {
            background: linear-gradient(145deg, #1e293b, #0f172a);
            border: 3px solid #0ea5e9;
            border-radius: 1.5vh;
            padding: 3vh 4vw;
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4);
            width: 100%;
            max-width: 80vw;
            margin-bottom: 2vh;
            animation: fadeIn 1.2s ease forwards;
        }

        .section-title {
            font-size: 3.2vh;
            font-weight: 700;
            color: #38bdf8;
            margin-bottom: 2vh;
            text-shadow: 0 0 1.5vh rgba(56, 189, 248, 0.8);
        }

        .status-message {
            background: #1e293b;
            border: 2px solid #334155;
            border-radius: 1vh;
            padding: 2vh 2vw;
            margin-bottom: 2vh;
            font-size: 2.2vh;
            font-weight: 600;
            color: #e2e8f0;
            display: none;
        }

        .status-message.active {
            display: block;
        }

        .status-message.info {
            border-color: #3b82f6;
            background: linear-gradient(145deg, #1e3a8a, #1e293b);
        }

        .status-message.success {
            border-color: #22c55e;
            background: linear-gradient(145deg, #14532d, #1e293b);
        }

        .status-message.primary {
            border-color: #0ea5e9;
            background: linear-gradient(145deg, #0c4a6e, #1e293b);
        }

        .enable-btn {
            background: linear-gradient(145deg, #0ea5e9, #0284c7);
            color: white;
            border: none;
            border-radius: 1vh;
            padding: 2.5vh 6vw;
            font-size: 3vh;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
            margin-bottom: 2vh;
        }

        .enable-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(14, 165, 233, 0.6);
        }

        .enable-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .back-button {
            margin-top: 2vh;
            padding: 1.8vh 6vw;
            font-size: 2.8vh;
            font-weight: 900;
            border-radius: 4vh;
            background: linear-gradient(145deg, #0ea5e9, #0284c7);
            color: white;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
            transition: all 0.3s ease;
            display: inline-block;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(14, 165, 233, 0.6);
        }

        .file-dialog {
            background: linear-gradient(145deg, #1e293b, #0f172a);
            border: 3px solid #fbbf24;
            border-radius: 1.5vh;
            padding: 3vh 3vw;
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4);
            display: none;
        }

        .file-dialog.active {
            display: block;
        }

        .file-icon {
            font-size: 10vh;
            color: #dc2626;
            margin-bottom: 2vh;
        }

        .file-name {
            font-size: 3vh;
            font-weight: 700;
            color: #fbbf24;
            margin-bottom: 3vh;
        }

        .dialog-buttons {
            display: flex;
            gap: 2vw;
            justify-content: center;
        }

        .btn-reject {
            background: linear-gradient(145deg, #ef4444, #dc2626);
            color: white;
            border: none;
            border-radius: 1vh;
            padding: 2vh 4vw;
            font-size: 2.6vh;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .btn-reject:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.6);
        }

        .btn-accept {
            background: linear-gradient(145deg, #22c55e, #16a34a);
            color: white;
            border: none;
            border-radius: 1vh;
            padding: 2vh 4vw;
            font-size: 2.6vh;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
        }

        .btn-accept:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(34, 197, 94, 0.6);
        }

        .progress-area {
            margin-top: 2vh;
        }

        .progress-bar-bg {
            background: #0f172a;
            height: 4vh;
            border-radius: 1vh;
            overflow: hidden;
            border: 2px solid #334155;
            margin-top: 1vh;
        }

        .progress-bar-fill {
            background: linear-gradient(90deg, #22c55e, #16a34a);
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 2vh;
            color: white;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .spinner {
            display: inline-block;
            animation: spin 1s linear infinite;
        }
    </style>
</head>
<body>

    <div class="top-bar">
        INSTAPRINT<br><span class="subtitle">Printing Vendo Machine</span>
    </div>

    <div class="main-container">
        <h1>BLUETOOTH PRINT</h1>

        <div class="bluetooth-icon" id="main-icon">
            <img src="/icons/bluetooth.gif" alt="Bluetooth" style="width: 15vh; height: auto; filter: drop-shadow(0 8px 16px rgba(56,189,248,0.5));">
        </div>

        <div class="main-card">
            <!-- Status Messages -->
            <div id="status-idle" class="status-message info active">
                <strong>🔵 Ready to Receive Files</strong>
                <p style="margin-top: 1vh; font-size: 2vh;">Click below to make your device discoverable</p>
            </div>

            <div id="status-discoverable" class="status-message success">
                <strong>🟢 Now Discoverable!</strong>
                <p style="margin-top: 1vh; font-size: 2vh;">Pair your device and send your file</p>
            </div>

            <div id="status-receiving" class="status-message primary">
                <strong>📥 Receiving File...</strong>
                <p style="margin-top: 1vh; font-size: 2vh;">Please wait while we receive your document</p>
            </div>

            <!-- Discoverable Button -->
            <button id="enable-btn" class="enable-btn">
                🔊 MAKE DISCOVERABLE
            </button>

            <!-- Progress Area -->
            <div id="progress-container" style="display: none;">
                <div id="progress-area"></div>
            </div>
        </div>

        <!-- Accept/Reject File Dialog -->
        <div id="file-dialog" class="file-dialog">
            <div style="text-align: center;">
                <div class="file-icon">
                    <img src="/icons/bluetooth1.png" alt="Bluetooth Incoming" style="width: 10vh; height: auto;">
                </div>
                <div class="section-title">Incoming File</div>
                <div id="dialog-filename" class="file-name"></div>
                <div class="dialog-buttons">
                    <button id="btn-reject" class="btn-reject">
                        ✕ REJECT
                    </button>
                    <button id="btn-accept" class="btn-accept">
                        ✓ ACCEPT
                    </button>
                </div>
            </div>
        </div>

        <a href="{{ route('start') }}" class="back-button">← BACK</a>
    </div>

    <script src="/vendor/socketio/socket.io.min.js"></script>
    <script>
        console.log("📡 Bluetooth print service initialized");

        // Status elements
        const statusIdle = document.getElementById('status-idle');
        const statusDiscoverable = document.getElementById('status-discoverable');
        const statusReceiving = document.getElementById('status-receiving');
        const progressContainer = document.getElementById('progress-container');
        const progressArea = document.getElementById('progress-area');
        const enableBtn = document.getElementById('enable-btn');
        const mainIcon = document.getElementById('main-icon');

        function setStatus(status) {
            // Hide all status messages
            statusIdle.classList.remove('active');
            statusDiscoverable.classList.remove('active');
            statusReceiving.classList.remove('active');

            // Show the selected status
            if (status === 'idle') {
                statusIdle.classList.add('active');
                progressContainer.style.display = 'none';
                enableBtn.disabled = false;
                enableBtn.innerHTML = '🔊 MAKE DISCOVERABLE';
                // Change icon back to bluetooth
                mainIcon.innerHTML = '<img src="/icons/bluetooth.gif" alt="Bluetooth" style="width: 15vh; height: auto; filter: drop-shadow(0 8px 16px rgba(56,189,248,0.5));">';
            } else if (status === 'discoverable') {
                statusDiscoverable.classList.add('active');
                progressContainer.style.display = 'none';
                enableBtn.disabled = true;
                enableBtn.innerHTML = '✓ DISCOVERABLE';
                // Keep bluetooth icon
                mainIcon.innerHTML = '<img src="/icons/bluetooth.gif" alt="Bluetooth" style="width: 15vh; height: auto; filter: drop-shadow(0 8px 16px rgba(56,189,248,0.5));">';
            } else if (status === 'receiving') {
                statusReceiving.classList.add('active');
                progressContainer.style.display = 'block';
                enableBtn.disabled = true;
                // Change icon to files sent
                mainIcon.innerHTML = '<img src="/icons/files sent.gif" alt="Receiving File" style="width: 15vh; height: auto; filter: drop-shadow(0 8px 16px rgba(56,189,248,0.5));">';
            }
        }

        // Handle enable button click
        enableBtn.addEventListener('click', function() {
            enableBtn.disabled = true;
            enableBtn.innerHTML = '<span class="spinner">⏳</span> Enabling...';

            fetch("{{ route('bluetooth.enable') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Failed to enable Bluetooth');
                }
                return res.json();
            })
            .then(data => {
                console.log('✅ Bluetooth enabled:', data);
                if (data.success) {
                    setStatus('discoverable');
                } else {
                    throw new Error(data.message || 'Failed to enable Bluetooth');
                }
            })
            .catch(err => {
                console.error('❌ Failed to enable Bluetooth:', err);
                alert('Failed to enable Bluetooth. Please try again.');
                setStatus('idle');
            });
        });

        // Auto-disable after file received
        function autoDisableBluetooth() {
            console.log('🔒 Auto-disabling Bluetooth discoverability...');

            fetch("{{ route('bluetooth.disable') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => console.log('✅ Bluetooth disabled:', data))
            .catch(err => console.error('❌ Failed to disable Bluetooth:', err));
        }

        // Socket.IO connection
        console.log("Connecting to Socket.IO...");

        const socket = io("http://192.168.4.1:5001", {
            transports: ["websocket", "polling"]
        });

        socket.on("connect", () => {
            console.log("✅ Socket.IO Connected!");
        });

        socket.on("connect_error", (err) => {
            console.error("❌ Connection Error:", err);
        });

        socket.on("progress", (data) => {
            setStatus('receiving');

            let bar = document.getElementById("progress-" + data.filename);
            if (!bar) {
                progressArea.innerHTML += `
                    <div style="margin-bottom: 2vh;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1vh;">
                            <strong style="color: #e2e8f0; font-size: 2vh;">${data.filename}</strong>
                            <span id="percent-${data.filename}" style="color: #22c55e; font-size: 2vh;">0%</span>
                        </div>
                        <div class="progress-bar-bg">
                            <div id="progress-${data.filename}" class="progress-bar-fill"></div>
                        </div>
                    </div>`;
                bar = document.getElementById("progress-" + data.filename);
            }

            bar.style.width = data.percent + "%";
            bar.textContent = data.percent + "%";
            document.getElementById("percent-" + data.filename).innerText = data.percent + "%";
        });

        // Track the last redirected file
        let lastRedirected = null;
        let pendingFileData = null;

        // Accept/Reject Dialog Elements
        const fileDialog = document.getElementById('file-dialog');
        const dialogFilename = document.getElementById('dialog-filename');
        const btnAccept = document.getElementById('btn-accept');
        const btnReject = document.getElementById('btn-reject');

        socket.on("new_file", (data) => {
            console.log("📁 New file received:", data);

            setStatus('idle');

            // Auto-disable Bluetooth discoverability after file received
            autoDisableBluetooth();

            // Store file data and show accept/reject dialog
            pendingFileData = data;
            dialogFilename.textContent = data.filename;
            fileDialog.classList.add('active');

            console.log("⏳ Waiting for user to accept or reject file:", data.filename);
        });

        // Handle Accept Button
        btnAccept.addEventListener('click', function() {
            if (!pendingFileData) return;

            console.log("✅ User accepted file:", pendingFileData.filename);

            // Disable buttons
            btnAccept.disabled = true;
            btnReject.disabled = true;
            btnAccept.innerHTML = '<span class="spinner">⏳</span> Loading...';

            // Redirect to preview page
            if (pendingFileData.redirect && lastRedirected !== pendingFileData.filename) {
                lastRedirected = pendingFileData.filename;
                console.log("🔀 Redirecting to:", pendingFileData.redirect);

                setTimeout(() => {
                    window.location.href = pendingFileData.redirect;
                }, 500);
            }
        });

        // Handle Reject Button
        btnReject.addEventListener('click', function() {
            if (!pendingFileData) return;

            console.log("❌ User rejected file:", pendingFileData.filename);

            // Reset UI
            fileDialog.classList.remove('active');
            pendingFileData = null;
            setStatus('idle');

            alert('File rejected. Ready for next file.');
        });

        // Initialize status
        setStatus('idle');
    </script>

  @include('partials.emergency-check')
  @include('partials.hide-url')
</body>
</html>
