<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Printing Instructions - QR Upload</title>

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
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .print-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
        }
    </style>
</head>
<body>

<div class="container my-5">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-success text-white">
            <h3 class="mb-0"><i class="bi bi-printer me-2"></i>Printing Instructions</h3>
        </div>

        <div class="card-body">
            <h5 class="mb-3">📋 Print Details</h5>
            <ul class="list-group mb-4">
                <li class="list-group-item">📄 File: <strong>{{ $order['file_name'] }}</strong></li>
                <li class="list-group-item">🔢 Copies: <strong>{{ $order['copies'] }}</strong></li>
                <li class="list-group-item">📑 Pages: <strong>{{ $order['pages'] ?: 'All' }}</strong></li>
                <li class="list-group-item">🎨 Color: <strong>{{ ucfirst($order['color'] ?? $order['color_option'] ?? 'N/A') }}</strong></li>
                <li class="list-group-item">📏 Paper: <strong>{{ $order['paper_size'] }}</strong></li>
                <li class="list-group-item">🔄 Duplex: <strong>{{ $order['duplex'] }}</strong></li>
                <li class="list-group-item">💰 Total: <strong>₱{{ number_format($order['calculated_total'], 2) }}</strong></li>
            </ul>

            {{-- Change Voucher Display --}}
            @if(!empty($order['change_voucher_code']))
            <div class="alert alert-success mb-4">
                <h5 class="alert-heading"><i class="bi bi-gift me-2"></i>Your Change Voucher</h5>
                <hr>
                <p class="mb-1"><strong>Code:</strong> <span class="h4 font-monospace">{{ $order['change_voucher_code'] }}</span></p>
                <p class="mb-1"><strong>Amount:</strong> ₱{{ number_format($order['change_amount'], 2) }}</p>
                <p class="mb-0"><strong>Expires:</strong> {{ $order['change_voucher_expires_at'] }}</p>
                <small class="text-muted">Save this code to use it on your next print!</small>
            </div>
            @endif

            <button id="print-btn" class="btn btn-primary btn-lg w-100">
                <i class="bi bi-printer-fill me-2"></i>Start Printing
            </button>
        </div>
    </div>
</div>

<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<script>
    document.getElementById('print-btn').addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Printing...';

        fetch("{{ route('upload.print') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Redirect to success page
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.href = "{{ route('upload.success') }}";
                }
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
</script>

</body>
</html>
