<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Print Success - Bluetooth</title>

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

        .success-icon {
            font-size: 80px;
            color: #28a745;
            animation: scaleIn 0.5s ease-in-out;
        }

        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
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

        .card {
            animation: fadeInUp 0.6s ease-in-out;
        }

        .print-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
        }

        .countdown {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
        }

        .btn-new-print {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: transform 0.2s;
        }

        .btn-new-print:hover {
            transform: scale(1.05);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow-lg border-0 text-center">
        <div class="card-body p-5">
            <!-- Success Icon -->
            <div class="success-icon mb-4">
                <i class="bi bi-check-circle-fill"></i>
            </div>

            <!-- Success Message -->
            <h2 class="text-success mb-3">Print Job Sent Successfully!</h2>
            <p class="lead text-muted mb-4">Your document is being printed.</p>

            <!-- Print Details -->
            @if(isset($order))
            <div class="print-details mb-4">
                <h5 class="mb-3"><i class="bi bi-file-earmark-text me-2"></i>Print Details</h5>
                <div class="row text-start">
                    <div class="col-6 mb-2">
                        <small class="text-muted">File:</small>
                        <div><strong>{{ $order['file_name'] ?? 'N/A' }}</strong></div>
                    </div>
                    <div class="col-6 mb-2">
                        <small class="text-muted">Copies:</small>
                        <div><strong>{{ $order['copies'] ?? 1 }}</strong></div>
                    </div>
                    <div class="col-6 mb-2">
                        <small class="text-muted">Pages:</small>
                        <div><strong>{{ $order['pages'] ?? 'All' }}</strong></div>
                    </div>
                    <div class="col-6 mb-2">
                        <small class="text-muted">Color:</small>
                        <div><strong>{{ ucfirst($order['color_option'] ?? 'N/A') }}</strong></div>
                    </div>
                    <div class="col-6 mb-2">
                        <small class="text-muted">Paper Size:</small>
                        <div><strong>{{ $order['paper_size'] ?? 'A4' }}</strong></div>
                    </div>
                    <div class="col-6 mb-2">
                        <small class="text-muted">Total Paid:</small>
                        <div class="text-success"><strong>₱{{ number_format($order['calculated_total'] ?? 0, 2) }}</strong></div>
                    </div>
                </div>
            </div>

            {{-- Change Voucher Display --}}
            @if(!empty($order['change_voucher_code']))
            <div class="alert alert-success mb-4">
                <h5 class="alert-heading"><i class="bi bi-gift me-2"></i>Your Change Voucher</h5>
                <hr>
                <p class="mb-1"><strong>Code:</strong> <span class="h4 font-monospace">{{ $order['change_voucher_code'] }}</span></p>
                <p class="mb-1"><strong>Amount:</strong> ₱{{ number_format($order['change_amount'] ?? 0, 2) }}</p>
                <p class="mb-0"><strong>Expires:</strong> {{ $order['change_voucher_expires_at'] ?? 'N/A' }}</p>
                <small class="text-muted">Save this code to use it on your next print!</small>
            </div>
            @endif
            @endif

            <!-- Auto Redirect Counter -->
            <div class="mb-4">
                <p class="mb-2">Redirecting to home in:</p>
                <div class="countdown" id="countdown">10</div>
            </div>

            <!-- Manual Navigation -->
            <div class="d-grid gap-2">
                <a href="{{ route('bluetooth.index') }}" class="btn btn-new-print btn-lg text-white">
                    <i class="bi bi-plus-circle me-2"></i>Print Another Document
                </a>
                <a href="{{ route('start') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-house me-2"></i>Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
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

</body>
</html>
