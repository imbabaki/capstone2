<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Vouchers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0"><i class="bi bi-ticket-perforated me-2"></i>Generate Vouchers Manually</h3>
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('admin.vouchers.generate.store') }}">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-cash-coin me-2"></i>Voucher Amount (₱)
                                </label>
                                <input 
                                    type="number" 
                                    name="amount" 
                                    class="form-control form-control-lg" 
                                    step="0.01" 
                                    min="0.01" 
                                    max="10000"
                                    value="{{ old('amount', '10.00') }}"
                                    required
                                >
                                <small class="text-muted">Enter the value for each voucher (e.g., 10.00)</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-layers me-2"></i>Quantity
                                </label>
                                <input 
                                    type="number" 
                                    name="quantity" 
                                    class="form-control form-control-lg" 
                                    min="1" 
                                    max="100"
                                    value="{{ old('quantity', '1') }}"
                                    required
                                >
                                <small class="text-muted">How many vouchers to generate (max 100)</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-calendar-event me-2"></i>Expiration Days
                                </label>
                                <input 
                                    type="number" 
                                    name="expiration_days" 
                                    class="form-control form-control-lg" 
                                    min="1" 
                                    max="365"
                                    value="{{ old('expiration_days', $defaultExpirationDays) }}"
                                    required
                                >
                                <small class="text-muted">Vouchers will expire after this many days</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-tag me-2"></i>Source/Label (Optional)
                                </label>
                                <input 
                                    type="text" 
                                    name="source" 
                                    class="form-control form-control-lg" 
                                    maxlength="50"
                                    value="{{ old('source', 'Manual') }}"
                                    placeholder="e.g., Promo, Giveaway, Manual"
                                >
                                <small class="text-muted">Label to identify where these vouchers came from</small>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Preview:</strong> You will generate 
                                <span id="previewQty">1</span> voucher(s) worth 
                                <strong>₱<span id="previewAmount">10.00</span></strong> each, 
                                expiring in <span id="previewDays">{{ $defaultExpirationDays }}</span> days.
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-plus-circle me-2"></i>Generate Vouchers
                                </button>
                                <a href="{{ route('admin.vouchers.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Voucher List
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Live preview update
        function updatePreview() {
            const amount = document.querySelector('input[name="amount"]').value || '0.00';
            const quantity = document.querySelector('input[name="quantity"]').value || '0';
            const days = document.querySelector('input[name="expiration_days"]').value || '0';
            
            document.getElementById('previewAmount').textContent = parseFloat(amount).toFixed(2);
            document.getElementById('previewQty').textContent = quantity;
            document.getElementById('previewDays').textContent = days;
        }

        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', updatePreview);
        });

        // Initialize preview
        updatePreview();
    </script>
</body>
</html>