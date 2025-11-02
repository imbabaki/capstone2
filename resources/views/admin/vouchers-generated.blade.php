<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vouchers Generated</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .voucher-card {
            border: 2px dashed #28a745;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }
        .voucher-code {
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 2px;
        }
        @media print {
            .no-print { display: none; }
            .voucher-card { page-break-inside: avoid; margin-bottom: 20px; }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="card shadow-lg border-0 mb-4 no-print">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0"><i class="bi bi-check-circle me-2"></i>Vouchers Generated Successfully!</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <h4 class="alert-heading"><i class="bi bi-trophy me-2"></i>Success!</h4>
                    <p class="mb-0">
                        Generated <strong>{{ $quantity }}</strong> voucher(s) worth 
                        <strong>₱{{ number_format($amount, 2) }}</strong> each.
                    </p>
                </div>

                <div class="d-flex gap-2 mb-3">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer me-2"></i>Print Vouchers
                    </button>
                    <button onclick="copyAllCodes()" class="btn btn-info">
                        <i class="bi bi-clipboard me-2"></i>Copy All Codes
                    </button>
                    <a href="{{ route('admin.vouchers.generate') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i>Generate More
                    </a>
                    <a href="{{ route('admin.vouchers.index') }}" class="btn btn-secondary">
                        <i class="bi bi-list me-2"></i>View All Vouchers
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($vouchers as $voucher)
            <div class="col-md-6 mb-4">
                <div class="voucher-card card shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-gift-fill text-success me-2"></i>INSTAPRINT VOUCHER
                        </h5>
                        <div class="voucher-code text-success mb-3">
                            {{ $voucher->code }}
                        </div>
                        <h2 class="text-success mb-3">₱{{ number_format($voucher->amount, 2) }}</h2>
                        <hr>
                        <p class="mb-1"><small><strong>Source:</strong> {{ $voucher->source }}</small></p>
                        <p class="mb-1"><small><strong>Generated:</strong> {{ $voucher->created_at->format('M d, Y h:i A') }}</small></p>
                        <p class="mb-1"><small><strong>Expires:</strong> {{ $voucher->expires_at->format('M d, Y') }}</small></p>
                        <p class="mb-0"><small class="text-danger"><strong>{{ $voucher->daysRemaining() }} days remaining</strong></small></p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="no-print">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-code-square me-2"></i>All Voucher Codes</h5>
                </div>
                <div class="card-body">
                    <textarea id="allCodes" class="form-control" rows="10" readonly>@foreach($vouchers as $voucher){{ $voucher->code }}
@endforeach</textarea>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyAllCodes() {
            const textarea = document.getElementById('allCodes');
            textarea.select();
            document.execCommand('copy');
            
            alert('✅ All voucher codes copied to clipboard!');
        }
    </script>
</body>
</html>