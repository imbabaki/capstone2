<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Print Success - QR Upload</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
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
            height: 93vh;
            width: 100%;
            padding: 2vh 2vw;
            gap: 2vw;
        }

        .left-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2vh;
        }

        .right-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2vh;
        }

        .success-card {
            background: linear-gradient(145deg, #1e293b, #0f172a);
            border: 3px solid #3b82f6;
            border-radius: 1.5vh;
            padding: 3vh 2vw;
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .success-icon {
            width: 12vh;
            height: 12vh;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 6vh;
            color: white;
            margin-bottom: 2vh;
            box-shadow: 0 4px 16px rgba(34, 197, 94, 0.5);
            animation: scaleIn 0.6s ease-in-out;
        }

        @keyframes scaleIn {
            0% { transform: scale(0) rotate(0deg); }
            50% { transform: scale(1.1) rotate(180deg); }
            100% { transform: scale(1) rotate(360deg); }
        }

        .success-title {
            font-size: 4vh;
            font-weight: 900;
            color: #38bdf8;
            text-shadow: 0 0 1.5vh rgba(56, 189, 248, 0.6);
            margin-bottom: 1vh;
        }

        .success-message {
            font-size: 2.2vh;
            color: #94a3b8;
            font-weight: 600;
        }

        .receipt-card {
            flex: 1;
            background: linear-gradient(145deg, #1e293b, #0f172a);
            border: 3px solid #0ea5e9;
            border-radius: 1.5vh;
            padding: 2.5vh 2vw;
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4);
            overflow-y: auto;
        }

        .receipt-card::-webkit-scrollbar {
            width: 0.5vw;
        }

        .receipt-card::-webkit-scrollbar-track {
            background: #0f172a;
        }

        .receipt-card::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 1vh;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 2vh;
            padding-bottom: 1.5vh;
            border-bottom: 2px solid #334155;
        }

        .receipt-title {
            font-size: 3.2vh;
            font-weight: 900;
            color: #38bdf8;
            text-shadow: 0 0 1vh rgba(56, 189, 248, 0.6);
            margin-bottom: 0.5vh;
        }

        .receipt-subtitle {
            font-size: 1.6vh;
            color: #64748b;
            font-weight: 600;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2vh 0;
            border-bottom: 1px solid #1e293b;
        }

        .receipt-label {
            font-size: 2vh;
            color: #94a3b8;
            font-weight: 600;
        }

        .receipt-value {
            font-size: 2vh;
            color: #e2e8f0;
            font-weight: 700;
            text-align: right;
        }

        .receipt-total {
            margin-top: 2vh;
            padding-top: 2vh;
            border-top: 3px solid #3b82f6;
        }

        .receipt-total .receipt-label {
            font-size: 2.8vh;
            color: #38bdf8;
        }

        .receipt-total .receipt-value {
            font-size: 3.5vh;
            color: #22c55e;
            text-shadow: 0 0 1vh rgba(34, 197, 94, 0.5);
        }

        .voucher-card {
            background: linear-gradient(145deg, #1e293b, #0f172a);
            border: 3px solid #fbbf24;
            border-radius: 1.5vh;
            padding: 2.5vh 2vw;
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4);
            text-align: center;
        }

        .voucher-title {
            font-size: 2.8vh;
            font-weight: 900;
            color: #fbbf24;
            text-shadow: 0 0 1vh rgba(251, 191, 36, 0.6);
            margin-bottom: 2vh;
        }

        .voucher-code-box {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            border: 2px solid #fbbf24;
            border-radius: 1vh;
            padding: 2vh 2vw;
            margin-bottom: 1.5vh;
        }

        .voucher-code {
            font-size: 4vh;
            font-weight: 900;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.4vw;
            color: #fbbf24;
            text-shadow: 0 0 1vh rgba(251, 191, 36, 0.5);
        }

        .voucher-amount {
            font-size: 3vh;
            font-weight: 900;
            color: #22c55e;
            margin-top: 1vh;
        }

        .voucher-note {
            font-size: 1.6vh;
            color: #94a3b8;
            margin-top: 1vh;
            font-weight: 600;
        }

        .countdown-card {
            background: linear-gradient(145deg, #1e293b, #0f172a);
            border: 3px solid #3b82f6;
            border-radius: 1.5vh;
            padding: 2.5vh 2vw;
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            text-align: center;
        }

        .countdown-text {
            font-size: 2.2vh;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 1.5vh;
        }

        .countdown {
            font-size: 8vh;
            font-weight: 900;
            color: #3b82f6;
            text-shadow: 0 0 2vh rgba(59, 130, 246, 0.8);
            line-height: 1;
        }

        .thank-you {
            font-size: 2.4vh;
            font-weight: 700;
            color: #38bdf8;
            text-shadow: 0 0 1vh rgba(56, 189, 248, 0.6);
            margin-top: 2vh;
        }

        /* Mobile responsive adjustments */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                overflow-y: auto;
                height: 93vh;
            }

            .left-section, .right-section {
                flex: none;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="top-bar">
        INSTAPRINT<br><span class="subtitle">Printing Vendo Machine</span>
    </div>

    <div class="main-container">
        <!-- Left Section -->
        <div class="left-section">
            <!-- Success Message -->
            <div class="success-card">
                <div class="success-icon">✓</div>
                <div class="success-title">PRINT SUCCESSFUL!</div>
                <div class="success-message">Your document has been printed</div>
            </div>

            <!-- Countdown -->
            <div class="countdown-card">
                <div class="countdown-text">Redirecting to home in:</div>
                <div class="countdown" id="countdown">10</div>
                <div class="thank-you">THANK YOU!</div>
            </div>
        </div>

        <!-- Right Section -->
        <div class="right-section">
            <!-- Receipt Details -->
            <div class="receipt-card">
                <div class="receipt-header">
                    <div class="receipt-title">📋 RECEIPT</div>
                    <div class="receipt-subtitle">Print Job Details</div>
                </div>

                @if(isset($order))
                <div class="receipt-row">
                    <span class="receipt-label">File Name:</span>
                    <span class="receipt-value">{{ $order['file_name'] ?? 'N/A' }}</span>
                </div>

                <div class="receipt-row">
                    <span class="receipt-label">Copies:</span>
                    <span class="receipt-value">{{ $order['copies'] ?? 1 }}</span>
                </div>

                <div class="receipt-row">
                    <span class="receipt-label">Pages:</span>
                    <span class="receipt-value">{{ $order['pages'] ?? 'All' }}</span>
                </div>

                <div class="receipt-row">
                    <span class="receipt-label">Color Mode:</span>
                    <span class="receipt-value">{{ ucfirst($order['color'] ?? $order['color_option'] ?? 'N/A') }}</span>
                </div>

                <div class="receipt-row">
                    <span class="receipt-label">Paper Size:</span>
                    <span class="receipt-value">{{ $order['paper_size'] ?? 'A4' }}</span>
                </div>

                <div class="receipt-row receipt-total">
                    <span class="receipt-label">TOTAL PAID:</span>
                    <span class="receipt-value">₱{{ number_format($order['calculated_total'] ?? 0, 2) }}</span>
                </div>
                @endif
            </div>

            <!-- Change Voucher (if exists) -->
            @if(isset($order) && !empty($order['change_voucher_code']))
            <div class="voucher-card">
                <div class="voucher-title">🎟️ YOUR CHANGE VOUCHER</div>
                <div class="voucher-code-box">
                    <div class="voucher-code">{{ $order['change_voucher_code'] }}</div>
                    <div class="voucher-amount">₱{{ number_format($order['change_amount'] ?? 0, 2) }}</div>
                </div>
                @if(!empty($order['change_voucher_expires_at']))
                <div class="voucher-note">
                    Expires: {{ $order['change_voucher_expires_at'] }}
                </div>
                @endif
                <div class="voucher-note">💡 Save this code for your next print!</div>
            </div>
            @endif
        </div>
    </div>

    <script>
        // Auto-redirect countdown
        let seconds = 10;
        const countdownEl = document.getElementById('countdown');

        const interval = setInterval(() => {
            seconds--;
            if (countdownEl) {
                countdownEl.textContent = seconds;
            }

            if (seconds <= 0) {
                clearInterval(interval);
                window.location.href = "{{ route('start') }}";
            }
        }, 1000);

        console.log('✅ Print job completed successfully');
    </script>

  @include('partials.emergency-check')
</body>
</html>
