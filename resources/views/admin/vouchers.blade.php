@extends('admin.layout')

@section('title', 'Vouchers')

@section('content')
<!-- Header -->
<div class="page-header mb-2">
    <h1 class="page-title">
        <i class="fas fa-ticket-alt"></i> Vouchers
    </h1>
    <a href="{{ route('admin.vouchers.generate') }}" class="btn btn-success">
        <i class="fas fa-plus"></i> Generate
    </a>
</div>

<!-- Filter Tabs -->
<div class="card mb-2">
    <div style="display: flex; gap: 8px; overflow-x: auto; padding: 4px;">
        <a href="{{ route('admin.vouchers.index', ['filter' => 'all']) }}"
           class="btn {{ $filter == 'all' ? 'btn-primary' : 'btn-secondary' }}"
           style="white-space: nowrap; font-size: 13px; padding: 8px 16px;">
            <i class="fas fa-list"></i> All
        </a>
        <a href="{{ route('admin.vouchers.index', ['filter' => 'active']) }}"
           class="btn {{ $filter == 'active' ? 'btn-success' : 'btn-secondary' }}"
           style="white-space: nowrap; font-size: 13px; padding: 8px 16px;">
            <i class="fas fa-check-circle"></i> Active
        </a>
        <a href="{{ route('admin.vouchers.index', ['filter' => 'used']) }}"
           class="btn {{ $filter == 'used' ? 'btn-warning' : 'btn-secondary' }}"
           style="white-space: nowrap; font-size: 13px; padding: 8px 16px;">
            <i class="fas fa-times-circle"></i> Used
        </a>
        <a href="{{ route('admin.vouchers.index', ['filter' => 'expired']) }}"
           class="btn {{ $filter == 'expired' ? 'btn-danger' : 'btn-secondary' }}"
           style="white-space: nowrap; font-size: 13px; padding: 8px 16px;">
            <i class="fas fa-clock"></i> Expired
        </a>
    </div>
</div>

<!-- Voucher Cards -->
@forelse($vouchers as $voucher)
    <div class="card mb-2" style="border-left: 4px solid
        @if($voucher->is_used) #6b7280
        @elseif($voucher->isExpired()) #ef4444
        @else #10b981
        @endif
    ;">
        <div style="padding: 12px;">
            <!-- Voucher Header -->
            <div class="flex justify-between items-center mb-2">
                <div class="flex-1">
                    <div style="font-family: 'Courier New', monospace; font-weight: bold; font-size: 16px; color: #1f2937;">
                        {{ $voucher->code }}
                    </div>
                    <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">
                        <i class="fas fa-tag"></i> {{ $voucher->source }}
                    </div>
                </div>
                <div>
                    @if($voucher->is_used)
                        <span class="badge badge-gray">
                            <i class="fas fa-times-circle"></i> Used
                        </span>
                    @elseif($voucher->isExpired())
                        <span class="badge" style="background: #fee2e2; color: #991b1b;">
                            <i class="fas fa-clock"></i> Expired
                        </span>
                    @else
                        <span class="badge" style="background: #d1fae5; color: #065f46;">
                            <i class="fas fa-check-circle"></i> Active
                        </span>
                    @endif
                </div>
            </div>

            <!-- Amount -->
            <div style="background: #f9fafb; padding: 12px; border-radius: 8px; margin-bottom: 12px;">
                <div style="font-size: 28px; font-weight: bold; color: #10b981;">
                    ₱{{ number_format($voucher->amount, 2) }}
                </div>
                <div style="font-size: 11px; color: #6b7280;">Voucher Value</div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-2" style="gap: 8px;">
                <div style="background: #f3f4f6; padding: 8px; border-radius: 6px;">
                    <div style="font-size: 10px; color: #6b7280; margin-bottom: 2px;">Created</div>
                    <div style="font-size: 12px; font-weight: 600; color: #374151;">
                        {{ $voucher->created_at->format('M d, Y') }}
                    </div>
                </div>

                <div style="background: #f3f4f6; padding: 8px; border-radius: 6px;">
                    <div style="font-size: 10px; color: #6b7280; margin-bottom: 2px;">Expires</div>
                    <div style="font-size: 12px; font-weight: 600; color: #374151;">
                        @if($voucher->expires_at)
                            {{ $voucher->expires_at->format('M d, Y') }}
                            @if(!$voucher->is_used && !$voucher->isExpired())
                                <br><span style="font-size: 10px; color: #10b981;">{{ $voucher->daysRemaining() }}d left</span>
                            @endif
                        @else
                            No expiry
                        @endif
                    </div>
                </div>

                @if($voucher->is_used && $voucher->used_at)
                <div style="background: #fef3c7; padding: 8px; border-radius: 6px; grid-column: span 2;">
                    <div style="font-size: 10px; color: #78350f; margin-bottom: 2px;">
                        <i class="fas fa-check"></i> Used On
                    </div>
                    <div style="font-size: 12px; font-weight: 600; color: #92400e;">
                        {{ $voucher->used_at->format('M d, Y h:i A') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="fas fa-ticket-alt"></i>
        </div>
        <div class="empty-state-title">No vouchers found</div>
        <div class="empty-state-desc">
            @if($filter != 'all')
                Try changing the filter or generate new vouchers
            @else
                Generate your first voucher to get started
            @endif
        </div>
        <a href="{{ route('admin.vouchers.generate') }}" class="btn btn-success mt-2">
            <i class="fas fa-plus-circle"></i> Generate Vouchers
        </a>
    </div>
@endforelse

<!-- Pagination -->
@if($vouchers->hasPages())
<div class="card mt-2">
    <div style="padding: 12px;">
        <div class="flex justify-between items-center">
            @if($vouchers->onFirstPage())
                <button class="btn btn-secondary" disabled style="opacity: 0.5;">
                    <i class="fas fa-chevron-left"></i> Prev
                </button>
            @else
                <a href="{{ $vouchers->previousPageUrl() }}" class="btn btn-primary">
                    <i class="fas fa-chevron-left"></i> Prev
                </a>
            @endif

            <span style="font-size: 13px; color: #6b7280;">
                Page {{ $vouchers->currentPage() }} of {{ $vouchers->lastPage() }}
            </span>

            @if($vouchers->hasMorePages())
                <a href="{{ $vouchers->nextPageUrl() }}" class="btn btn-primary">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <button class="btn btn-secondary" disabled style="opacity: 0.5;">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Quick Actions -->
<div class="card mt-2">
    <div class="card-header">
        <i class="fas fa-bolt" style="color: #f59e0b;"></i> Quick Actions
    </div>
    <div class="grid grid-2">
        <a href="{{ route('admin.voucher.settings') }}" class="quick-action blue">
            <i class="fas fa-cog"></i>
            <div class="quick-action-title">Settings</div>
            <div class="quick-action-subtitle">Configure</div>
        </a>
        <a href="{{ route('admin.vouchers.generate') }}" class="quick-action green">
            <i class="fas fa-plus-circle"></i>
            <div class="quick-action-title">Generate</div>
            <div class="quick-action-subtitle">New Vouchers</div>
        </a>
    </div>
</div>

<!-- Info -->
<div class="alert alert-info mt-2">
    <strong><i class="fas fa-info-circle"></i> About Vouchers:</strong>
    <ul style="margin: 8px 0 0 20px; font-size: 13px;">
        <li><strong>Active:</strong> Valid vouchers ready to use</li>
        <li><strong>Used:</strong> Already redeemed by customers</li>
        <li><strong>Expired:</strong> Past expiration date</li>
    </ul>
</div>
@endsection
