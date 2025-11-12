@extends('admin.layout')

@section('title', 'Voucher Settings')

@section('content')
<!-- Header -->
<div class="page-header mb-2">
    <h1 class="page-title">
        <i class="fas fa-cog"></i> Settings
    </h1>
    <a href="{{ route('admin.vouchers.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<!-- Success Message -->
@if(session('success'))
<div class="alert alert-success mb-2">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<!-- Settings Form -->
<form method="POST" action="{{ route('admin.voucher.settings.update') }}">
    @csrf

    <!-- Current Setting Display -->
    <div class="card mb-2">
        <div class="card-header">
            <i class="fas fa-info-circle" style="color: #3b82f6;"></i> Current Setting
        </div>
        <div style="padding: 20px; text-align: center; background: #f9fafb;">
            <div style="font-size: 48px; font-weight: bold; color: #8b5cf6; margin-bottom: 8px;">
                {{ $expirationDays }}
            </div>
            <div style="font-size: 14px; color: #6b7280; font-weight: 600;">
                Days Until Expiration
            </div>
        </div>
    </div>

    <!-- Expiration Days Input -->
    <div class="card mb-2">
        <div class="card-header">
            <i class="fas fa-calendar-alt" style="color: #8b5cf6;"></i> Expiration Period
        </div>
        <div style="padding: 16px;">
            <label class="form-label">
                Days Until Voucher Expires
            </label>
            <div style="position: relative;">
                <input
                    type="number"
                    name="expiration_days"
                    value="{{ $expirationDays }}"
                    min="1"
                    max="365"
                    class="form-control"
                    style="font-size: 24px; font-weight: bold; padding-right: 60px;"
                    required
                >
                <span style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); font-size: 14px; font-weight: 600; color: #6b7280;">days</span>
            </div>
            <small style="font-size: 12px; color: #6b7280; margin-top: 8px; display: block;">
                <i class="fas fa-info-circle"></i> All newly generated vouchers will expire after this many days
            </small>
        </div>
    </div>

    <!-- Information -->
    <div class="alert alert-info mb-2">
        <strong><i class="fas fa-lightbulb"></i> How it works:</strong>
        <ul style="margin: 8px 0 0 20px; font-size: 13px;">
            <li>This sets the default expiration period</li>
            <li>Applies to all newly generated vouchers</li>
            <li>Does not affect existing vouchers</li>
            <li>Can be set between 1 and 365 days</li>
        </ul>
    </div>

    <!-- Save Button -->
    <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-save"></i> Save Settings
    </button>
</form>

<!-- Quick Actions -->
<div class="card mt-2">
    <div class="card-header">
        <i class="fas fa-bolt" style="color: #f59e0b;"></i> Quick Actions
    </div>
    <div class="grid grid-2">
        <a href="{{ route('admin.vouchers.index') }}" class="quick-action purple">
            <i class="fas fa-list"></i>
            <div class="quick-action-title">Vouchers</div>
            <div class="quick-action-subtitle">View All</div>
        </a>
        <a href="{{ route('admin.vouchers.generate') }}" class="quick-action green">
            <i class="fas fa-plus-circle"></i>
            <div class="quick-action-title">Generate</div>
            <div class="quick-action-subtitle">New Vouchers</div>
        </a>
    </div>
</div>

<!-- Example Timeline -->
<div class="card mt-2">
    <div class="card-header">
        <i class="fas fa-clock" style="color: #3b82f6;"></i> Example Timeline
    </div>
    <div style="padding: 16px;">
        <div style="font-size: 13px; color: #374151; line-height: 1.8;">
            <div style="display: flex; align-items: center; margin-bottom: 12px;">
                <div style="width: 32px; height: 32px; background: #d1fae5; color: #065f46; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 12px;">
                    1
                </div>
                <div>
                    <strong>Today:</strong> Generate voucher<br>
                    <span style="color: #6b7280; font-size: 12px;">Status: Active & Valid</span>
                </div>
            </div>

            <div style="display: flex; align-items: center; margin-bottom: 12px;">
                <div style="width: 32px; height: 32px; background: #fef3c7; color: #78350f; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 12px;">
                    2
                </div>
                <div>
                    <strong>Day {{ $expirationDays }}:</strong> Voucher expires<br>
                    <span style="color: #6b7280; font-size: 12px;">Status: Expired (unless used)</span>
                </div>
            </div>

            <div style="display: flex; align-items: center;">
                <div style="width: 32px; height: 32px; background: #e5e7eb; color: #374151; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 12px;">
                    ✓
                </div>
                <div>
                    <strong>When used:</strong> Becomes inactive<br>
                    <span style="color: #6b7280; font-size: 12px;">Status: Used (before expiration)</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
