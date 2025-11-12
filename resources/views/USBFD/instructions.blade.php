<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=800, height=480, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Printing Instructions - USB v2.0</title>
    
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
            flex-direction: column;
            height: 93vh;
            width: 100%;
            padding: 3vh 4vw;
            background: #000000;
        }

        .content-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 1.5vh;
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

        .gif-instructions {
            display: flex;
            gap: 3vw;
            margin-bottom: 1.5vh;
            justify-content: center;
        }
        .gif-instructions.hide {
            display: none;
        }

        .gif-step {
            flex: 1;
            max-width: 45%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1.5vh;
            padding: 1.5vh 1.5vw;
            border: 3px solid #fbbf24;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
        }

        .gif-step-title {
            font-size: 2.2vh;
            font-weight: 900;
            color: #1e40af;
            margin-bottom: 1vh;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .gif-step img {
            width: 100%;
            height: auto;
            border-radius: 0.8vh;
            background: white;
            border: 2px solid #ddd;
        }

        .instruction-box {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            padding: 3vh 3vw;
            border-radius: 2vh;
            text-align: center;
            font-size: 4vh;
            font-weight: 900;
            margin-bottom: 2vh;
            box-shadow: 0 8px 24px rgba(251, 191, 36, 0.6);
            animation: pulse 2s infinite;
            border: 4px solid #fff;
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
            border: 2px solid #10b981;
            border-radius: 1vh;
            padding: 1.2vh 1.5vw;
            box-shadow: 0 3px 10px rgba(34, 197, 94, 0.4);
            animation: pulse 2s infinite;
            margin-bottom: 1vh;
        }
        .voucher-card.hide {
            display: none;
        }

        .voucher-card h3 {
            font-size: 1.8vh;
            font-weight: 700;
            color: white;
            margin-bottom: 0.8vh;
            text-align: center;
        }

        .voucher-code-box {
            background: white;
            color: #0f172a;
            border-radius: 0.6vh;
            padding: 1vh 1.5vw;
            text-align: center;
            margin-bottom: 0.8vh;
        }

        .voucher-label {
            font-size: 1.2vh;
            color: #64748b;
            margin-bottom: 0.3vh;
        }

        .voucher-code {
            font-size: 2.2vh;
            font-weight: 900;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.2vw;
            color: #0f172a;
            margin-bottom: 0.5vh;
        }

        .voucher-amount {
            font-size: 2vh;
            font-weight: 700;
            color: #22c55e;
        }

        .voucher-expiry {
            border-top: 1px solid #e5e7eb;
            padding-top: 0.6vh;
            margin-top: 0.6vh;
        }

        .voucher-expiry-label {
            font-size: 1vh;
            color: #64748b;
            margin-bottom: 0.2vh;
        }

        .voucher-expiry-date {
            font-size: 1.3vh;
            font-weight: 700;
            color: #ef4444;
        }

        .voucher-note {
            font-size: 1.1vh;
            color: white;
            text-align: center;
            margin-bottom: 0.4vh;
        }

        .voucher-warning {
            font-size: 1vh;
            color: #fef3c7;
            text-align: center;
        }

        .print-button {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
            border: none;
            border-radius: 1vh;
            padding: 2vh 2vw;
            font-size: 2.5vh;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
            width: 100%;
        }
        .print-button.hide {
            display: none;
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
            margin-top: 1vh;
        }
        .progress-container.show {
            display: flex;
            gap: 3vw;
            align-items: stretch;
            flex: 1;
            height: 100%;
        }

        /* When no voucher, center and enlarge the progress section */
        .progress-container.show.no-voucher {
            justify-content: center;
            align-items: center;
        }

        .progress-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: linear-gradient(145deg, #1e293b, #0f172a);
            border: 3px solid #3b82f6;
            border-radius: 1.5vh;
            padding: 2vh 2vw;
            box-shadow: 0 8px 28px rgba(59, 130, 246, 0.5);
            gap: 2vh;
            justify-content: center;
            align-items: center;
        }

        /* Larger progress section when no voucher */
        .progress-container.no-voucher .progress-left {
            max-width: 70vw;
            padding: 4vh 4vw;
            gap: 4vh;
        }

        .progress-right {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Hide right side when no voucher */
        .progress-container.no-voucher .progress-right {
            display: none;
        }

        .progress-alert {
            background: rgba(59, 130, 246, 0.1);
            border: 2px solid #3b82f6;
            color: white;
            padding: 2vh 2vw;
            border-radius: 1vh;
            width: 100%;
        }

        /* Larger alert box when no voucher */
        .progress-container.no-voucher .progress-alert {
            padding: 4vh 4vw;
            border-radius: 1.5vh;
            border: 3px solid #3b82f6;
        }

        .progress-title {
            font-size: 1.8vh;
            font-weight: 700;
            margin-bottom: 0.5vh;
            color: #38bdf8;
            text-shadow: 0 0 1vh rgba(56, 189, 248, 0.6);
        }

        /* Larger text when no voucher */
        .progress-container.no-voucher .progress-title {
            font-size: 3.5vh;
            margin-bottom: 2vh;
        }

        .progress-message {
            font-size: 1.3vh;
            margin-bottom: 0.8vh;
            color: #94a3b8;
        }

        /* Larger message text when no voucher */
        .progress-container.no-voucher .progress-message {
            font-size: 2.5vh;
            margin-bottom: 1.5vh;
        }

        .progress-bar-container {
            background: rgba(0,0,0,0.2);
            border-radius: 0.6vh;
            height: 2.5vh;
            overflow: hidden;
        }

        /* Larger progress bar when no voucher */
        .progress-container.no-voucher .progress-bar-container {
            height: 5vh;
            border-radius: 1vh;
        }

        .progress-bar-fill {
            background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
            height: 100%;
            width: 0%;
            transition: width 0.5s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4vh;
            font-weight: 700;
        }

        /* Larger progress bar text when no voucher */
        .progress-container.no-voucher .progress-bar-fill {
            font-size: 3vh;
        }

        .continue-print-btn {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border: 2px solid #3b82f6;
            border-radius: 1vh;
            padding: 2.5vh 2vw;
            font-size: 2.5vh;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            text-shadow: 0 0 1vh rgba(34, 197, 94, 0.6);
        }

        /* Larger button when no voucher */
        .progress-container.no-voucher .continue-print-btn {
            padding: 4vh 4vw;
            font-size: 4vh;
            border-radius: 1.5vh;
        }
        .continue-print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.6);
            background: linear-gradient(145deg, #22c55e, #16a34a);
            color: white;
            border-color: #22c55e;
        }

        .voucher-card-progress {
            background: linear-gradient(145deg, #1e293b, #0f172a);
            border: 3px solid #3b82f6;
            border-radius: 1.5vh;
            padding: 2vh 2vw;
            box-shadow: 0 8px 28px rgba(59, 130, 246, 0.5);
            animation: pulse 2s infinite;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .voucher-card-progress h3 {
            font-size: 3.5vh;
            font-weight: 700;
            color: #fbbf24;
            margin-bottom: 3vh;
            text-align: center;
            text-shadow: 0 0 1vh rgba(251, 191, 36, 0.6);
        }

        .voucher-code-box-progress {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            border: 2px solid #3b82f6;
            border-radius: 1vh;
            padding: 4vh 3vw;
            text-align: center;
            margin-bottom: 3vh;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .voucher-code-box-progress .voucher-label {
            font-size: 2vh;
            color: #94a3b8;
            margin-bottom: 1.5vh;
        }

        .voucher-code-box-progress .voucher-code {
            font-size: 4.5vh;
            font-weight: 900;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.3vw;
            color: #38bdf8;
            margin-bottom: 2vh;
            text-shadow: 0 0 1vh rgba(56, 189, 248, 0.5);
        }

        .voucher-code-box-progress .voucher-amount {
            font-size: 4vh;
            font-weight: 700;
            color: #22c55e;
            text-shadow: 0 0 1vh rgba(34, 197, 94, 0.5);
        }

        .voucher-card-progress .voucher-note {
            font-size: 2vh;
            color: #94a3b8;
            text-align: center;
            margin-bottom: 1.5vh;
            font-weight: 600;
        }

        .voucher-card-progress .voucher-warning {
            font-size: 1.8vh;
            color: #94a3b8;
            text-align: center;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="top-bar">
        INSTAPRINT<br><span class="subtitle">Printing Vendo Machine</span>
    </div>

    <div class="main-container">
        <div class="content-section">
            <div class="gif-instructions">
                <div class="gif-step">
                    <div class="gif-step-title">STEP 1: Get Paper</div>
                    <img src="{{ asset('icons/Getpaper.gif') }}" alt="Get Paper">
                </div>
                <div class="gif-step">
                    <div class="gif-step-title">STEP 2: Put Paper</div>
                    <img src="{{ asset('icons/put paper.gif') }}" alt="Put Paper in Printer">
                </div>
            </div>

            <div>
                <button id="print-btn" class="print-button">
                    🖨️ START PRINTING
                </button>
            </div>

            {{-- Printing Progress Section --}}
            <div id="progress-container" class="progress-container">
                {{-- Left Side: Progress & Continue Button --}}
                <div class="progress-left">
                    <div class="progress-alert">
                        <div class="progress-title">⏳ Printing in Progress</div>
                        <div id="progress-message" class="progress-message">Preparing to print...</div>
                        <div id="job-info" class="progress-message" style="font-size: 1.4vh; color: #cbd5e1;"></div>
                        <div class="progress-bar-container">
                            <div id="progress-bar" class="progress-bar-fill">0%</div>
                        </div>
                    </div>

                    {{-- Continue Print Button --}}
                    <button id="continue-print-btn" class="continue-print-btn">
                        ▶️ CONTINUE PRINT
                    </button>
                </div>

                {{-- Right Side: Voucher Card --}}
                <div class="progress-right">
                    @if(!empty($order['voucher_code']))
                    <div class="voucher-card-progress">
                        <h3>🎟️ YOUR CHANGE VOUCHER</h3>
                        <div class="voucher-code-box-progress">
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
                    @endif
                </div>
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
        const hasVoucher = {{ !empty($order['voucher_code']) ? 'true' : 'false' }};

        document.getElementById('print-btn').addEventListener('click', function() {
            this.disabled = true;
            this.textContent = '⏳ PRINTING...';

            // Hide GIF instructions and button
            document.querySelector('.gif-instructions').classList.add('hide');
            this.classList.add('hide');

            // Show progress container
            const progressContainer = document.getElementById('progress-container');
            progressContainer.classList.add('show');

            // Add no-voucher class if there's no voucher
            if (!hasVoucher) {
                progressContainer.classList.add('no-voucher');
            }

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
            let checkCount = 0;
            let hasStartedPrinting = false;

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

        // Continue Print Button Handler
        document.getElementById('continue-print-btn').addEventListener('click', function() {
            this.disabled = true;
            this.textContent = '⏳ CONTINUING...';

            fetch('http://127.0.0.1:5006/emergency/clear-jam', {
                method: 'POST'
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                this.textContent = '▶️ CONTINUE PRINT';
            })
            .catch(err => {
                console.error(err);
                this.disabled = false;
                this.textContent = '▶️ CONTINUE PRINT';
            });
        });
    </script>

  @include('partials.emergency-check')
</body>
</html>