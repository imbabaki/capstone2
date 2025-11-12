@extends('admin.layout')

@section('title', 'Vouchers Generated')

@section('content')
<!-- Success Header -->
<div class="card mb-2" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
    <div style="padding: 20px; text-align: center;">
        <div style="font-size: 48px; margin-bottom: 8px;">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2 style="font-size: 20px; font-weight: bold; margin-bottom: 8px;">
            Successfully Generated!
        </h2>
        <div style="font-size: 14px; opacity: 0.9;">
            {{ $quantity }} voucher(s) worth ₱{{ number_format($amount, 2) }} each
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="grid grid-2 mb-2" style="gap: 8px;">
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fas fa-print"></i> Print
    </button>
    <button onclick="copyAllCodes()" class="btn btn-info">
        <i class="fas fa-copy"></i> Copy All
    </button>
    <a href="{{ route('admin.vouchers.generate') }}" class="btn btn-success">
        <i class="fas fa-plus"></i> Generate More
    </a>
    <a href="{{ route('admin.vouchers.index') }}" class="btn btn-secondary">
        <i class="fas fa-list"></i> View All
    </a>
</div>

<!-- Generated Vouchers -->
@foreach($vouchers as $voucher)
    <div class="card mb-2 voucher-print-card" style="border: 2px dashed #10b981; background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);">
        <div style="padding: 20px; text-align: center;">
            <!-- Logo/Title -->
            <div style="margin-bottom: 16px;">
                <i class="fas fa-gift" style="font-size: 32px; color: #059669;"></i>
            </div>
            <div style="font-size: 16px; font-weight: bold; color: #065f46; margin-bottom: 16px; letter-spacing: 1px;">
                INSTAPRINT VOUCHER
            </div>

            <!-- Voucher Code -->
            <div style="font-family: 'Courier New', monospace; font-size: 24px; font-weight: bold; color: #059669; letter-spacing: 3px; margin-bottom: 16px; padding: 12px; background: white; border-radius: 8px; border: 2px solid #10b981;">
                {{ $voucher->code }}
            </div>

            <!-- Amount -->
            <div style="font-size: 36px; font-weight: bold; color: #059669; margin-bottom: 16px;">
                ₱{{ number_format($voucher->amount, 2) }}
            </div>

            <hr style="border: none; border-top: 1px dashed #10b981; margin: 16px 0;">

            <!-- Details -->
            <div style="font-size: 12px; color: #065f46; text-align: left;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <strong>Source:</strong>
                    <span>{{ $voucher->source }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <strong>Generated:</strong>
                    <span>{{ $voucher->created_at->format('M d, Y') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <strong>Expires:</strong>
                    <span>{{ $voucher->expires_at->format('M d, Y') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <strong>Days Left:</strong>
                    <span style="color: #dc2626; font-weight: bold;">{{ $voucher->daysRemaining() }} days</span>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- All Codes (for copying) -->
<div class="card mt-2 no-print">
    <div class="card-header">
        <i class="fas fa-code"></i> All Voucher Codes
    </div>
    <div style="padding: 16px;">
        <textarea
            id="allCodes"
            class="form-control"
            rows="8"
            readonly
            style="font-family: 'Courier New', monospace; font-size: 12px; resize: none;"
        >@foreach($vouchers as $voucher){{ $voucher->code }}
@endforeach</textarea>
        <button onclick="copyAllCodes()" class="btn btn-info btn-block mt-2">
            <i class="fas fa-copy"></i> Copy All Codes
        </button>
    </div>
</div>

<!-- Info -->
<div class="alert alert-info mt-2 no-print">
    <strong><i class="fas fa-info-circle"></i> Next Steps:</strong>
    <ul style="margin: 8px 0 0 20px; font-size: 13px;">
        <li>Print vouchers for physical distribution</li>
        <li>Copy codes for digital sharing</li>
        <li>Vouchers are immediately active</li>
        <li>Monitor usage in Voucher List</li>
    </ul>
</div>

<style>
    /* Print styles */
    @media print {
        .no-print {
            display: none !important;
        }
        .page-header,
        .navbar,
        .bottom-nav {
            display: none !important;
        }
        .voucher-print-card {
            page-break-inside: avoid;
            margin-bottom: 20px;
        }
        body {
            background: white;
        }
        .content-wrapper {
            padding: 0 !important;
        }
    }
</style>

<script>
    function copyAllCodes() {
        const textarea = document.getElementById('allCodes');
        textarea.select();
        textarea.setSelectionRange(0, 99999); // For mobile devices

        try {
            document.execCommand('copy');
            alert('✅ All voucher codes copied to clipboard!');
        } catch (err) {
            alert('❌ Failed to copy codes. Please select and copy manually.');
        }
    }
</script>
@endsection
