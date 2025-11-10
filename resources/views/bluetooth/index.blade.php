<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Bluetooth Print - Ready</title>

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.css') }}">

    <style>
        * {
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        ::-webkit-scrollbar { display: none; }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-card {
            animation: fadeInUp 0.6s ease-in-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .bluetooth-icon {
            font-size: 100px;
            color: #667eea;
            animation: pulse 2s infinite;
        }

        .waiting-spinner {
            display: none;
            animation: rotate 1s linear infinite;
        }

        .waiting-spinner.active {
            display: inline-block;
        }

        .step-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            color: white;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: white;
            color: #667eea;
        }

        #enable-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: all 0.3s;
            min-width: 250px;
        }

        #enable-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        #enable-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .status-message {
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            display: none;
        }

        .status-message.active {
            display: block;
            animation: fadeInUp 0.3s ease-in-out;
        }

        .progress-container {
            display: none;
            margin-top: 20px;
        }

        .progress-container.active {
            display: block;
            animation: fadeInUp 0.3s ease-in-out;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Main Card -->
            <div class="card shadow-lg border-0 main-card">
                <div class="card-body text-center p-5">
                    <!-- Bluetooth Icon -->
                    <div class="bluetooth-icon mb-4">
                        <i class="bi bi-bluetooth"></i>
                        <i class="bi bi-arrow-repeat waiting-spinner" id="spinner"></i>
                    </div>

                    <!-- Title -->
                    <h2 class="mb-3">Bluetooth Print Service</h2>
                    <p class="lead text-muted mb-4">Send your documents wirelessly via Bluetooth</p>

                    <!-- Discoverable Button -->
                    <div class="mb-4">
                        <form action="{{ route('bluetooth.enable') }}" method="POST" id="enable-form">
                            @csrf
                            <button type="submit" id="enable-btn" class="btn btn-primary btn-lg shadow">
                                <i class="bi bi-broadcast me-2"></i>Make Discoverable
                            </button>
                        </form>
                        <small class="text-muted d-block mt-2" id="discoverable-hint">
                            Click to make your device visible for Bluetooth pairing
                        </small>
                    </div>

                    <!-- Status Messages -->
                    <div id="status-idle" class="alert alert-info status-message active">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Ready to receive files</strong>
                        <p class="mb-0 mt-2">Pair your device and send a file to start printing</p>
                    </div>

                    <div id="status-discoverable" class="alert alert-success status-message">
                        <i class="bi bi-broadcast-pin me-2"></i>
                        <strong>Now Discoverable!</strong>
                        <p class="mb-0 mt-2">Your device is visible for 5 minutes. Pair and send your file.</p>
                    </div>

                    <div id="status-receiving" class="alert alert-primary status-message">
                        <i class="bi bi-download me-2"></i>
                        <strong>Receiving file...</strong>
                        <p class="mb-0 mt-2">Please wait while we receive your document</p>
                    </div>

                    <div id="status-processing" class="alert alert-success status-message">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>File received!</strong>
                        <p class="mb-0 mt-2">Please accept or reject the file below</p>
                    </div>

                    <!-- Progress Area -->
                    <div id="progress-container" class="progress-container">
                        <div id="progress-area"></div>
                    </div>

                    <!-- Accept/Reject File Dialog -->
                    <div id="file-dialog" class="alert alert-warning status-message" style="padding: 25px;">
                        <div class="text-center">
                            <i class="bi bi-file-earmark-pdf" style="font-size: 60px; color: #dc2626;"></i>
                            <h4 class="mt-3 mb-2">Incoming File</h4>
                            <p id="dialog-filename" class="fw-bold fs-5 mb-4" style="color: #0f172a;"></p>
                            <div class="d-flex gap-3 justify-content-center">
                                <button id="btn-reject" class="btn btn-danger btn-lg px-5">
                                    <i class="bi bi-x-circle me-2"></i>REJECT
                                </button>
                                <button id="btn-accept" class="btn btn-success btn-lg px-5">
                                    <i class="bi bi-check-circle me-2"></i>ACCEPT
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="mt-5">
                        <h5 class="mb-4 text-start">How to Print via Bluetooth:</h5>

                        <div class="step-card">
                            <div class="d-flex align-items-center">
                                <div class="step-number me-3">1</div>
                                <div class="text-start">
                                    <h6 class="mb-1">Pair Your Device</h6>
                                    <small class="text-muted">Connect your phone/laptop to the printer via Bluetooth</small>
                                </div>
                            </div>
                        </div>

                        <div class="step-card">
                            <div class="d-flex align-items-center">
                                <div class="step-number me-3">2</div>
                                <div class="text-start">
                                    <h6 class="mb-1">Send Your Document</h6>
                                    <small class="text-muted">Share your PDF or image file via Bluetooth</small>
                                </div>
                            </div>
                        </div>

                        <div class="step-card">
                            <div class="d-flex align-items-center">
                                <div class="step-number me-3">3</div>
                                <div class="text-start">
                                    <h6 class="mb-1">Configure & Pay</h6>
                                    <small class="text-muted">Select print options and insert payment</small>
                                </div>
                            </div>
                        </div>

                        <div class="step-card">
                            <div class="d-flex align-items-center">
                                <div class="step-number me-3">4</div>
                                <div class="text-start">
                                    <h6 class="mb-1">Get Your Prints</h6>
                                    <small class="text-muted">Collect your documents from the printer</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="mt-5">
                        <a href="{{ route('start') }}" class="btn btn-back btn-lg">
                            <i class="bi bi-arrow-left me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success mt-3 text-center shadow" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger mt-3 text-center shadow" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            </div>
            @endif
        </div>
    </div>
</div>

<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>

<script>
console.log("📡 Bluetooth print service initialized");

// Status elements
const statusIdle = document.getElementById('status-idle');
const statusDiscoverable = document.getElementById('status-discoverable');
const statusReceiving = document.getElementById('status-receiving');
const statusProcessing = document.getElementById('status-processing');
const progressContainer = document.getElementById('progress-container');
const progressArea = document.getElementById('progress-area');
const spinner = document.getElementById('spinner');
const enableBtn = document.getElementById('enable-btn');
const enableForm = document.getElementById('enable-form');
const discoverableHint = document.getElementById('discoverable-hint');

function setStatus(status) {
    // Hide all status messages
    statusIdle.classList.remove('active');
    statusDiscoverable.classList.remove('active');
    statusReceiving.classList.remove('active');
    statusProcessing.classList.remove('active');

    // Show the selected status
    if (status === 'idle') {
        statusIdle.classList.add('active');
        spinner.classList.remove('active');
        progressContainer.classList.remove('active');
        enableBtn.disabled = false;
        enableBtn.innerHTML = '<i class="bi bi-broadcast me-2"></i>Make Discoverable';
    } else if (status === 'discoverable') {
        statusDiscoverable.classList.add('active');
        spinner.classList.remove('active');
        progressContainer.classList.remove('active');
        enableBtn.disabled = true;
        enableBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Discoverable';
        discoverableHint.textContent = 'Waiting for file transfer...';
    } else if (status === 'receiving') {
        statusReceiving.classList.add('active');
        spinner.classList.add('active');
        progressContainer.classList.add('active');
        enableBtn.disabled = true;
    } else if (status === 'processing') {
        statusProcessing.classList.add('active');
        spinner.classList.add('active');
        enableBtn.disabled = true;
    }
}

// Handle enable button click
enableForm.addEventListener('submit', function(e) {
    e.preventDefault();

    enableBtn.disabled = true;
    enableBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enabling...';

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
    .then(res => {
        if (!res.ok) {
            throw new Error('Failed to disable Bluetooth');
        }
        return res.json();
    })
    .then(data => {
        console.log('✅ Bluetooth disabled:', data);
    })
    .catch(err => {
        console.error('❌ Failed to disable Bluetooth:', err);
    });
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
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-2">
                    <strong>${data.filename}</strong>
                    <span id="percent-${data.filename}">0%</span>
                </div>
                <div class="progress" style="height: 25px;">
                    <div id="progress-${data.filename}"
                         class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                         role="progressbar"
                         style="width:0%">
                    </div>
                </div>
            </div>`;
        bar = document.getElementById("progress-" + data.filename);
    }

    bar.style.width = data.percent + "%";
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

    setStatus('processing');

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
    btnAccept.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';

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

    // Optional: Send reject notification to server (if you want to delete the file)
    // fetch('/bluetooth/reject-file', { method: 'POST', ... });

    alert('File rejected. Ready for next file.');
});

// Initialize status
setStatus('idle');
</script>

</body>
</html>
