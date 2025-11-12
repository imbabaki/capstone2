@extends('admin.layout')

@section('title', 'Sales Report')

@section('content')
<!-- Header -->
<div class="page-header mb-2">
    <h1 class="page-title">
        <i class="fas fa-chart-line"></i> Sales Report
    </h1>
</div>

<!-- Total Summary Cards -->
<div class="grid grid-2 mb-2">
    <div class="stat-card green">
        <div class="stat-card-header">
            <i class="stat-card-icon fas fa-peso-sign"></i>
            <span class="stat-card-label">Revenue</span>
        </div>
        <div class="stat-card-value">₱{{ number_format($total_amount, 2) }}</div>
        <div class="stat-card-desc">Total Sales</div>
    </div>

    <div class="stat-card blue">
        <div class="stat-card-header">
            <i class="stat-card-icon fas fa-print"></i>
            <span class="stat-card-label">Pages</span>
        </div>
        <div class="stat-card-value">{{ number_format($total_pages_print) }}</div>
        <div class="stat-card-desc">Total Printed</div>
    </div>
</div>

<!-- By Paper Size -->
<div class="card mb-2">
    <div class="card-header">
        <i class="fas fa-file-alt" style="color: #3b82f6;"></i> By Paper Size
    </div>
    <div style="padding: 16px;">
        <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center;">
                    <div style="width: 8px; height: 8px; background: #3b82f6; border-radius: 50%; margin-right: 8px;"></div>
                    <span style="font-size: 14px; color: #374151;">A4</span>
                </div>
                <strong style="font-size: 16px; color: #1f2937;">{{ number_format($A4_total_print) }}</strong>
            </div>
        </div>
        <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center;">
                    <div style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; margin-right: 8px;"></div>
                    <span style="font-size: 14px; color: #374151;">Short</span>
                </div>
                <strong style="font-size: 16px; color: #1f2937;">{{ number_format($Short_total_print) }}</strong>
            </div>
        </div>
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center;">
                    <div style="width: 8px; height: 8px; background: #f59e0b; border-radius: 50%; margin-right: 8px;"></div>
                    <span style="font-size: 14px; color: #374151;">Legal</span>
                </div>
                <strong style="font-size: 16px; color: #1f2937;">{{ number_format($Legal_total_print) }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- By Color Mode -->
<div class="card mb-2">
    <div class="card-header">
        <i class="fas fa-palette" style="color: #ec4899;"></i> By Color Mode
    </div>
    <div style="padding: 16px;">
        <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center;">
                    <div style="width: 8px; height: 8px; background: #ec4899; border-radius: 50%; margin-right: 8px;"></div>
                    <span style="font-size: 14px; color: #374151;">Color</span>
                </div>
                <strong style="font-size: 16px; color: #1f2937;">{{ number_format($color_total_print) }}</strong>
            </div>
        </div>
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center;">
                    <div style="width: 8px; height: 8px; background: #6b7280; border-radius: 50%; margin-right: 8px;"></div>
                    <span style="font-size: 14px; color: #374151;">Grayscale</span>
                </div>
                <strong style="font-size: 16px; color: #1f2937;">{{ number_format($grayscale_total_print) }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- By Source -->
<div class="card mb-2">
    <div class="card-header">
        <i class="fas fa-wifi" style="color: #8b5cf6;"></i> By Source
    </div>
    <div style="padding: 16px;">
        <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center;">
                    <div style="width: 32px; height: 32px; background: #dbeafe; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                        <i class="fas fa-usb" style="color: #3b82f6; font-size: 16px;"></i>
                    </div>
                    <span style="font-size: 14px; color: #374151;">USB</span>
                </div>
                <strong style="font-size: 16px; color: #1f2937;">{{ number_format($USB_total_print) }}</strong>
            </div>
        </div>
        <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center;">
                    <div style="width: 32px; height: 32px; background: #dbeafe; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                        <i class="fas fa-bluetooth" style="color: #3b82f6; font-size: 16px;"></i>
                    </div>
                    <span style="font-size: 14px; color: #374151;">Bluetooth</span>
                </div>
                <strong style="font-size: 16px; color: #1f2937;">{{ number_format($Bluetooth_total_print) }}</strong>
            </div>
        </div>
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center;">
                    <div style="width: 32px; height: 32px; background: #dbeafe; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                        <i class="fas fa-qrcode" style="color: #3b82f6; font-size: 16px;"></i>
                    </div>
                    <span style="font-size: 14px; color: #374151;">QR Code</span>
                </div>
                <strong style="font-size: 16px; color: #1f2937;">{{ number_format($QR_total_print) }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Breakdown -->
<div class="grid grid-3 mb-2">
    <div class="stat-mini blue">
        <div class="stat-mini-value">{{ number_format($A4_total_print + $Short_total_print + $Legal_total_print) }}</div>
        <div class="stat-mini-label">Total Pages</div>
    </div>
    <div class="stat-mini green">
        <div class="stat-mini-value">{{ number_format($USB_total_print + $Bluetooth_total_print + $QR_total_print) }}</div>
        <div class="stat-mini-label">Total Jobs</div>
    </div>
    <div class="stat-mini purple">
        @php
            $totalJobs = $USB_total_print + $Bluetooth_total_print + $QR_total_print;
            $avgPages = $totalJobs > 0 ? round(($A4_total_print + $Short_total_print + $Legal_total_print) / $totalJobs, 1) : 0;
        @endphp
        <div class="stat-mini-value">{{ $avgPages }}</div>
        <div class="stat-mini-label">Avg Pages/Job</div>
    </div>
</div>

<!-- Info -->
<div class="alert alert-info">
    <strong><i class="fas fa-info-circle"></i> About This Report:</strong>
    <ul style="margin: 8px 0 0 20px; font-size: 13px;">
        <li><strong>Pages:</strong> Total number of pages printed</li>
        <li><strong>Jobs:</strong> Individual print requests</li>
        <li><strong>Revenue:</strong> Total amount earned from prints</li>
        <li><strong>Sources:</strong> USB, Bluetooth, or QR code</li>
    </ul>
</div>
@endsection
