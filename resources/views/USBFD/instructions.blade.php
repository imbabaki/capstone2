<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=800, height=480, initial-scale=1.0">
    <title>Printing Instructions - Instaprint</title>
    
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
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
            height: 93vh;
            width: 100%;
            gap: 0;
        }

        .left-section {
            flex: 0 0 45%;
            background: #1e293b;
            padding: 2.5vh 2.5vw;
            display: flex;
            flex-direction: column;
            border-right: 2px solid #334155;
        }

        .right-section {
            flex: 0 0 55%;
            background: #0f172a;
            padding: 2.5vh 2.5vw;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .section-title {
            font-size: 3.2vh;
            font-weight: 700;
            color: #38bdf8;
            margin-bottom: 2vh;
            display: flex;
            align-items: center;
            gap: 1vw;
            padding-bottom: 1.2vh;
            border-bottom: 2px solid #334155;
        }

        .instruction-box {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            padding: 2.5vh 2vw;
            border-radius: 1vh;
            text-align: center;
            font-size: 2.8vh;
            font-weight: 700;
            margin-bottom: 2vh;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .order-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1.8vh;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 2.2vh;
            padding: 1.5vh 2vw;
            background: #0f172a;
            border-radius: 0.8vh;
            border-left: 4px solid #0ea5e9;
        }

        .detail-label {
            color: #94a3b8;
            font-weight: 500;
        }

        .detail-value {
            color: #e2e8f0;
            font-weight: 700;
        }

        .voucher-card {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border: 3px solid #10b981;
            border-radius: 1.2vh;
            padding: 2vh 2vw;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
            animation: pulse 2s infinite;
            margin-bottom: 2vh;
        }

        .voucher-card h3 {
            font-size: 2.6vh;
            font-weight: 700;
            color: white;
            margin-bottom: 1.5vh;
            text-align: center;
        }

        .voucher-code-box {
            background: white;
            color: #0f172a;
            border-radius: 0.8vh;
            padding: 1.5vh 2vw;
            text-align: center;
            margin-bottom: 1.5vh;
        }

        .voucher-label {
            font-size: 1.6vh;
            color: #64748b;
            margin-bottom: 0.5vh;
        }

        .voucher-code {
            font-size: 3.2vh;
            font-weight: 900;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.2vw;
            color: #0f172a;
            margin-bottom: 1vh;
        }

        .voucher-amount {
            font-size: 2.8vh;
            font-weight: 700;
            color: #22c55e;
        }

        .voucher-expiry {
            border-top: 2px solid #e5e7eb;
            padding-top: 1vh;
            margin-top: 1vh;
        }

        .voucher-expiry-label {
            font-size: 1.4vh;
            color: #64748b;
            margin-bottom: 0.3vh;
        }

        .voucher-expiry-date {
            font-size: 1.8vh;
            font-weight: 700;
            color: #ef4444;
        }

        .voucher-note {
            font-size: 1.6vh;
            color: white;
            text-align: center;
            margin-bottom: 0.8vh;
        }

        .voucher-warning {
            font-size: 1.4vh;
            color: #fef3c7;
            text-align: center;
        }

        .print-button {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
            border: none;
            border-radius: 1vh;
            padding: 3vh 2vw;
            font-size: 3.5vh;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
            width: 100%;
        }

        .print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(34, 197, 94, 0.6);
        }

        .no-voucher-spacer {
            flex: 1;
        }

        .progress-container {
            display: none;
            margin-top: 2vh;
        }
        .progress-container.show {
            display: block;
        }

        .progress-alert {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 2vh 2vw;
            border-radius: 1vh;
            margin-bottom: 2vh;
        }

        .progress-title {
            font-size: 2.4vh;
            font-weight: 700;
            margin-bottom: 1vh;
        }

        .progress-message {
            font-size: 1.8vh;
            margin-bottom: 1.5vh;
        }

        .progress-bar-container {
            background: rgba(0,0,0,0.2);
            border-radius: 0.8vh;
            height: 3vh;
            overflow: hidden;
        }

        .progress-bar-fill {
            background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
            height: 100%;
            width: 0%;
            transition: width 0.5s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8vh;
            font-weight: 700;
        }

        .emergency-btn {
            display: none;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            border-radius: 1vh;
            padding: 2.5vh 2vw;
            font-size: 2.8vh;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            margin-top: 1vh;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        .emergency-btn.show {
            display: block;
        }
    </style>
</head>
<body>

    <div class="top-bar">
        INSTAPRINT<br><span class="subtitle">Printing Vendo Machine</span>
    </div>

    <div class="main-container">
        <!-- Left Section: Order Details -->
        <div class="left-section">
            <div class="section-title">
                📋 Print Details
            </div>
            
            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">📄 File Name</span>
                    <span class="detail-value">{{ $order['file_name'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">🔢 Copies</span>
                    <span class="detail-value">{{ $order['copies'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">📑 Pages</span>
                    <span class="detail-value">{{ $order['pages'] ?: 'All' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">🎨 Color</span>
                    <span class="detail-value">{{ ucfirst($order['color']) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">📏 Paper Size</span>
                    <span class="detail-value">{{ $order['paper_size'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">🔄 Duplex</span>
                    <span class="detail-value">{{ ucfirst(str_replace('-', ' ', $order['duplex'])) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">💰 Total Paid</span>
                    <span class="detail-value">₱{{ number_format($order['total'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Right Section: Instructions & Action -->
        <div class="right-section">
            <div>
                <div class="section-title">
                    🖨️ Ready to Print
                </div>

                <div class="instruction-box">
                    Please insert <strong>{{ $order['paper_size'] }}</strong> paper into the tray
                </div>

                @if(!empty($order['voucher_code']))
                <div class="voucher-card">
                    <h3>🎟️ YOUR CHANGE VOUCHER</h3>
                    <div class="voucher-code-box">
                        <div class="voucher-label">Voucher Code:</div>
                        <div class="voucher-code">{{ $order['voucher_code'] }}</div>
                        <div class="voucher-amount">₱{{ number_format($order['change_amount'], 2) }}</div>
                        
                        @if(!empty($order['voucher_expires_at']))
                        <div class="voucher-expiry">
                            <div class="voucher-expiry-label">⏰ Expires on:</div>
                            <div class="voucher-expiry-date">{{ $order['voucher_expires_at'] }}</div>
                        </div>
                        @endif
                    </div>
                    <div class="voucher-note">💡 Save this code! Use it on your next print.</div>
                    <div class="voucher-warning">⚠️ Voucher expires after date shown above</div>
                </div>
                @else
                <div class="no-voucher-spacer"></div>
                @endif
            </div>

            <button id="print-btn" class="print-button">
                🖨️ START PRINTING
            </button>

            {{-- Printing Progress Section --}}
            <div id="progress-container" class="progress-container">
                <div class="progress-alert">
                    <div class="progress-title">⏳ Printing in Progress</div>
                    <div id="progress-message" class="progress-message">Preparing to print...</div>
                    <div id="job-info" class="progress-message" style="font-size: 1.4vh; color: #cbd5e1;"></div>
                    <div class="progress-bar-container">
                        <div id="progress-bar" class="progress-bar-fill">0%</div>
                    </div>
                </div>

                {{-- Emergency Jam Button --}}
                <button id="emergency-jam-btn" class="emergency-btn">
                    ⚠️ EMERGENCY: CLEAR PAPER JAM
                </button>
            </div>
        </div>
    </div>

    <script>
        let printJobId = null;
        let progressInterval = null;
        let printComplete = false;
        const totalPages = {{ $order['page_count'] ?? 1 }};
        const copies = {{ $order['copies'] ?? 1 }};
        const totalSheets = totalPages * copies;

        document.getElementById('print-btn').addEventListener('click', function() {
            this.disabled = true;
            this.textContent = '⏳ PRINTING...';

            // Show progress container
            document.getElementById('progress-container').classList.add('show');

            // Send print request
            fetch("{{ route('usb.print') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Start monitoring CUPS print queue
                    startRealTimePrintMonitoring();
                } else {
                    alert('❌ Print failed: ' + data.message);
                    location.reload();
                }
            })
            .catch(err => {
                console.error(err);
                alert('❌ Print error. Please try again.');
                location.reload();
            });
        });

        function startRealTimePrintMonitoring() {
            const progressBar = document.getElementById('progress-bar');
            const progressMessage = document.getElementById('progress-message');
            const jobInfo = document.getElementById('job-info');
            const emergencyBtn = document.getElementById('emergency-jam-btn');
            let checkCount = 0;
            let hasStartedPrinting = false;

            // Show emergency button after 5 seconds
            setTimeout(() => {
                emergencyBtn.classList.add('show');
            }, 5000);

            // Poll CUPS status every second
            progressInterval = setInterval(() => {
                fetch('http://127.0.0.1:5007/print/check-complete' + (printJobId ? '/' + printJobId : ''))
                    .then(res => res.json())
                    .then(data => {
                        checkCount++;

                        if (data.completed && hasStartedPrinting) {
                            // Print job is complete - auto redirect
                            clearInterval(progressInterval);

                            progressBar.style.width = '100%';
                            progressBar.textContent = '100%';
                            progressBar.style.background = 'linear-gradient(90deg, #22c55e 0%, #16a34a 100%)';
                            progressMessage.innerHTML = '<strong>✅ PRINTING COMPLETE!</strong> Redirecting...';
                            jobInfo.textContent = 'All pages have been printed successfully.';

                            // Auto redirect after 2 seconds
                            setTimeout(() => {
                                window.location.href = "{{ route('usb.success') }}";
                            }, 2000);
                        } else {
                            // Still printing - update progress
                            hasStartedPrinting = true;

                            // Calculate estimated progress based on time
                            const estimatedTime = totalSheets * 5; // 5 seconds per page
                            const elapsed = checkCount;
                            const progress = Math.min(95, (elapsed / estimatedTime) * 100);

                            progressBar.style.width = progress + '%';
                            progressBar.textContent = Math.floor(progress) + '%';

                            if (checkCount < 3) {
                                progressMessage.textContent = 'Sending job to printer...';
                                jobInfo.textContent = 'Preparing print queue...';
                            } else {
                                progressMessage.textContent = `Printing... (${totalSheets} pages)`;
                                jobInfo.textContent = `Job ${printJobId || 'active'} - Est. ${Math.ceil(estimatedTime - elapsed)}s remaining`;
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Error checking print status:', err);
                        // Continue checking even if there's an error
                    });
            }, 1000); // Check every second
        }

        // Emergency Jam Button Handler
        document.getElementById('emergency-jam-btn').addEventListener('click', function() {
            this.disabled = true;
            this.textContent = '⏳ CLEARING JAM...';

            fetch('http://127.0.0.1:5006/emergency/clear-jam', {
                method: 'POST'
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                this.textContent = '⚠️ EMERGENCY: CLEAR PAPER JAM';
            })
            .catch(err => {
                console.error(err);
                this.disabled = false;
                this.textContent = '⚠️ EMERGENCY: CLEAR PAPER JAM';
            });
        });
    </script>

</body>
</html>