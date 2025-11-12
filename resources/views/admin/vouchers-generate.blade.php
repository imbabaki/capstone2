@extends('admin.layout')

@section('title', 'Generate Vouchers')

@section('content')
<!-- Header -->
<div class="page-header mb-2">
    <h1 class="page-title">
        <i class="fas fa-ticket-alt"></i> Generate
    </h1>
    <a href="{{ route('admin.vouchers.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<!-- Error Display -->
@if($errors->any())
<div class="alert alert-danger mb-2">
    <strong><i class="fas fa-exclamation-circle"></i> Errors:</strong>
    <ul style="margin: 8px 0 0 20px; font-size: 13px;">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- Generate Form -->
<form method="POST" action="{{ route('admin.vouchers.generate.store') }}">
    @csrf

    <!-- Amount -->
    <div class="card mb-2">
        <div class="card-header">
            <i class="fas fa-peso-sign" style="color: #10b981;"></i> Voucher Amount
        </div>
        <div style="padding: 16px;">
            <div style="position: relative;">
                <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 24px; font-weight: bold; color: #10b981;">₱</span>
                <input
                    type="number"
                    name="amount"
                    class="form-control"
                    style="font-size: 24px; font-weight: bold; padding-left: 40px; text-align: left;"
                    step="0.01"
                    min="0.01"
                    max="10000"
                    value="{{ old('amount', '10.00') }}"
                    required
                    id="amountInput"
                >
            </div>
            <small style="font-size: 12px; color: #6b7280; margin-top: 8px; display: block;">
                <i class="fas fa-info-circle"></i> Enter value per voucher (e.g., 10.00)
            </small>
        </div>
    </div>

    <!-- Quantity -->
    <div class="card mb-2">
        <div class="card-header">
            <i class="fas fa-layer-group" style="color: #3b82f6;"></i> Quantity
        </div>
        <div style="padding: 16px;">
            <input
                type="number"
                name="quantity"
                class="form-control"
                style="font-size: 20px; font-weight: 600;"
                min="1"
                max="100"
                value="{{ old('quantity', '1') }}"
                required
                id="quantityInput"
            >
            <small style="font-size: 12px; color: #6b7280; margin-top: 8px; display: block;">
                <i class="fas fa-info-circle"></i> How many vouchers to generate (max 100)
            </small>
        </div>
    </div>

    <!-- Expiration Days -->
    <div class="card mb-2">
        <div class="card-header">
            <i class="fas fa-calendar-alt" style="color: #8b5cf6;"></i> Expiration Days
        </div>
        <div style="padding: 16px;">
            <div style="position: relative;">
                <input
                    type="number"
                    name="expiration_days"
                    class="form-control"
                    style="font-size: 20px; font-weight: 600; padding-right: 60px;"
                    min="1"
                    max="365"
                    value="{{ old('expiration_days', $defaultExpirationDays) }}"
                    required
                    id="expirationInput"
                >
                <span style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); font-size: 14px; font-weight: 600; color: #6b7280;">days</span>
            </div>
            <small style="font-size: 12px; color: #6b7280; margin-top: 8px; display: block;">
                <i class="fas fa-info-circle"></i> Vouchers expire after this many days
            </small>
        </div>
    </div>

    <!-- Source/Label -->
    <div class="card mb-2">
        <div class="card-header">
            <i class="fas fa-tag" style="color: #f59e0b;"></i> Source/Label (Optional)
        </div>
        <div style="padding: 16px;">
            <input
                type="text"
                name="source"
                class="form-control"
                style="font-size: 16px;"
                maxlength="50"
                value="{{ old('source', 'Manual') }}"
                placeholder="e.g., Promo, Giveaway, Manual"
            >
            <small style="font-size: 12px; color: #6b7280; margin-top: 8px; display: block;">
                <i class="fas fa-info-circle"></i> Label to identify where these vouchers came from
            </small>
        </div>
    </div>

    <!-- Preview -->
    <div class="alert alert-info mb-2">
        <strong><i class="fas fa-eye"></i> Preview:</strong>
        <div style="margin-top: 8px; font-size: 14px; line-height: 1.6;">
            You will generate <strong><span id="previewQty">1</span> voucher(s)</strong><br>
            Worth <strong style="color: #10b981;">₱<span id="previewAmount">10.00</span></strong> each<br>
            Expiring in <strong><span id="previewDays">{{ $defaultExpirationDays }}</span> days</strong>
        </div>
    </div>

    <!-- Submit Buttons -->
    <div class="grid grid-2" style="gap: 8px;">
        <a href="{{ route('admin.vouchers.index') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancel
        </a>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-check"></i> Generate
        </button>
    </div>
</form>

<!-- Help Info -->
<div class="alert alert-info mt-2">
    <strong><i class="fas fa-lightbulb"></i> Tips:</strong>
    <ul style="margin: 8px 0 0 20px; font-size: 13px;">
        <li>Generated vouchers will be valid immediately</li>
        <li>Expiration starts from creation date</li>
        <li>Use source labels to organize vouchers</li>
        <li>Maximum 100 vouchers per generation</li>
    </ul>
</div>

<script>
    // Live preview update
    function updatePreview() {
        const amount = document.getElementById('amountInput').value || '0.00';
        const quantity = document.getElementById('quantityInput').value || '0';
        const days = document.getElementById('expirationInput').value || '0';

        document.getElementById('previewAmount').textContent = parseFloat(amount).toFixed(2);
        document.getElementById('previewQty').textContent = quantity;
        document.getElementById('previewDays').textContent = days;
    }

    document.getElementById('amountInput').addEventListener('input', updatePreview);
    document.getElementById('quantityInput').addEventListener('input', updatePreview);
    document.getElementById('expirationInput').addEventListener('input', updatePreview);

    // Initialize preview
    updatePreview();
</script>
@endsection
