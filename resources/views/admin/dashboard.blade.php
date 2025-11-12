@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<!-- Header -->
<div class="page-header mb-2">
    <h1 class="page-title">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </h1>
</div>

<!-- Success Message -->
@if(session('success'))
<div class="alert alert-success mb-2">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<!-- Emergency Shutdown Card -->
<div class="card mb-2" style="border: 3px solid {{ $emergencyShutdown ? '#ef4444' : '#22c55e' }};">
    <div class="card-header" style="background: {{ $emergencyShutdown ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'linear-gradient(135deg, #22c55e, #16a34a)' }}; color: white;">
        <i class="fas {{ $emergencyShutdown ? 'fa-exclamation-triangle' : 'fa-power-off' }}"></i>
        Emergency Shutdown Control
    </div>
    <div style="padding: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 1.5rem;">
            <div style="flex: 1;">
                <h3 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 0.5rem; color: {{ $emergencyShutdown ? '#ef4444' : '#22c55e' }};">
                    System Status: {{ $emergencyShutdown ? 'DISABLED' : 'OPERATIONAL' }}
                </h3>
                <p style="color: #64748b; font-size: 0.95rem; line-height: 1.5;">
                    @if($emergencyShutdown)
                        <strong style="color: #ef4444;">⛔ EMERGENCY SHUTDOWN ACTIVE</strong><br>
                        All customer routes are currently disabled. Only admin panel is accessible.
                    @else
                        <strong style="color: #22c55e;">✅ MACHINE IS OPERATIONAL</strong><br>
                        All customer routes are accessible. The machine is functioning normally.
                    @endif
                </p>
            </div>
            <div>
                <form action="{{ route('admin.emergency.toggle') }}" method="POST" onsubmit="return confirm('Are you sure you want to {{ $emergencyShutdown ? 'ENABLE' : 'DISABLE' }} the machine?');">
                    @csrf
                    <button type="submit" class="btn" style="
                        background: {{ $emergencyShutdown ? 'linear-gradient(135deg, #22c55e, #16a34a)' : 'linear-gradient(135deg, #ef4444, #dc2626)' }};
                        color: white;
                        padding: 1rem 2rem;
                        font-size: 1.1rem;
                        font-weight: 900;
                        border: none;
                        border-radius: 0.5rem;
                        cursor: pointer;
                        box-shadow: 0 4px 12px {{ $emergencyShutdown ? 'rgba(34, 197, 94, 0.4)' : 'rgba(239, 68, 68, 0.4)' }};
                        transition: all 0.2s;
                    ">
                        <i class="fas {{ $emergencyShutdown ? 'fa-play' : 'fa-stop' }}"></i>
                        {{ $emergencyShutdown ? 'ENABLE MACHINE' : 'DISABLE MACHINE' }}
                    </button>
                </form>
            </div>
        </div>
        <div style="background: {{ $emergencyShutdown ? 'rgba(239, 68, 68, 0.1)' : 'rgba(34, 197, 94, 0.1)' }}; border: 2px solid {{ $emergencyShutdown ? '#ef4444' : '#22c55e' }}; border-radius: 0.5rem; padding: 1rem; font-size: 0.9rem; color: #475569;">
            <strong>ℹ️ Note:</strong> Use this emergency shutdown feature to immediately disable all customer-facing routes in case of maintenance, emergency, or hardware issues. The admin panel will remain accessible at all times.
        </div>
    </div>
</div>

<script>
// Real-time status monitor for admin dashboard
(function() {
    const emergencyCard = document.querySelector('.card[style*="border: 3px solid"]');
    const statusHeader = emergencyCard.querySelector('.card-header');
    const statusTitle = emergencyCard.querySelector('h3');
    const statusText = emergencyCard.querySelector('p');
    const toggleButton = emergencyCard.querySelector('button[type="submit"]');
    const noteDiv = emergencyCard.querySelector('div[style*="rgba"]');

    function updateDashboardStatus() {
        fetch('/api/emergency-status')
            .then(response => response.json())
            .then(data => {
                const isShutdown = data.emergency_shutdown;
                const currentIsShutdown = statusTitle.textContent.includes('DISABLED');

                // Only update if status changed
                if (isShutdown !== currentIsShutdown) {
                    // Update card border
                    emergencyCard.style.border = isShutdown ? '3px solid #ef4444' : '3px solid #22c55e';

                    // Update header
                    statusHeader.style.background = isShutdown
                        ? 'linear-gradient(135deg, #ef4444, #dc2626)'
                        : 'linear-gradient(135deg, #22c55e, #16a34a)';
                    statusHeader.innerHTML = isShutdown
                        ? '<i class="fas fa-exclamation-triangle"></i> Emergency Shutdown Control'
                        : '<i class="fas fa-power-off"></i> Emergency Shutdown Control';

                    // Update status text
                    statusTitle.style.color = isShutdown ? '#ef4444' : '#22c55e';
                    statusTitle.textContent = 'System Status: ' + (isShutdown ? 'DISABLED' : 'OPERATIONAL');

                    statusText.innerHTML = isShutdown
                        ? '<strong style="color: #ef4444;">⛔ EMERGENCY SHUTDOWN ACTIVE</strong><br>All customer routes are currently disabled. Only admin panel is accessible.'
                        : '<strong style="color: #22c55e;">✅ MACHINE IS OPERATIONAL</strong><br>All customer routes are accessible. The machine is functioning normally.';

                    // Update button
                    toggleButton.style.background = isShutdown
                        ? 'linear-gradient(135deg, #22c55e, #16a34a)'
                        : 'linear-gradient(135deg, #ef4444, #dc2626)';
                    toggleButton.style.boxShadow = isShutdown
                        ? '0 4px 12px rgba(34, 197, 94, 0.4)'
                        : '0 4px 12px rgba(239, 68, 68, 0.4)';
                    toggleButton.innerHTML = isShutdown
                        ? '<i class="fas fa-play"></i> ENABLE MACHINE'
                        : '<i class="fas fa-stop"></i> DISABLE MACHINE';

                    // Update note box
                    noteDiv.style.background = isShutdown ? 'rgba(239, 68, 68, 0.1)' : 'rgba(34, 197, 94, 0.1)';
                    noteDiv.style.borderColor = isShutdown ? '#ef4444' : '#22c55e';
                }
            })
            .catch(error => {
                console.error('Error checking emergency status:', error);
            });
    }

    // Check every 2 seconds
    setInterval(updateDashboardStatus, 2000);
})();
</script>

<!-- Statistics Cards -->
<div class="grid grid-2 mb-2">
    <div class="stat-card green">
        <div class="stat-card-header">
            <i class="stat-card-icon fas fa-peso-sign"></i>
            <span class="stat-card-label">Sales</span>
        </div>
        <div class="stat-card-value">₱{{ number_format($totalSales, 0) }}</div>
        <div class="stat-card-desc">Total Revenue</div>
    </div>

    <div class="stat-card blue">
        <div class="stat-card-header">
            <i class="stat-card-icon fas fa-print"></i>
            <span class="stat-card-label">Prints</span>
        </div>
        <div class="stat-card-value">{{ number_format($totalPrints) }}</div>
        <div class="stat-card-desc">Print Jobs</div>
    </div>

    <div class="stat-card purple">
        <div class="stat-card-header">
            <i class="stat-card-icon fas fa-ticket-alt"></i>
            <span class="stat-card-label">Vouchers</span>
        </div>
        <div class="stat-card-value">{{ number_format($totalVouchers) }}</div>
        <div class="stat-card-desc">Total Created</div>
    </div>

    <div class="stat-card orange">
        <div class="stat-card-header">
            <i class="stat-card-icon fas fa-check-circle"></i>
            <span class="stat-card-label">Active</span>
        </div>
        <div class="stat-card-value">{{ number_format($activeVouchers) }}</div>
        <div class="stat-card-desc">Valid Vouchers</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-2">
    <div class="card-header">
        <i class="fas fa-bolt" style="color: #f59e0b;"></i> Quick Actions
    </div>
    <div class="grid grid-2">
        <a href="{{ route('admin.print-settings.index') }}" class="quick-action blue">
            <i class="fas fa-dollar-sign"></i>
            <div class="quick-action-title">Prices</div>
            <div class="quick-action-subtitle">{{ $priceSettings }} settings</div>
        </a>

        <a href="{{ route('admin.sales.report') }}" class="quick-action green">
            <i class="fas fa-chart-line"></i>
            <div class="quick-action-title">Sales</div>
            <div class="quick-action-subtitle">Reports</div>
        </a>

        <a href="{{ route('admin.vouchers.generate') }}" class="quick-action purple">
            <i class="fas fa-plus-circle"></i>
            <div class="quick-action-title">Generate</div>
            <div class="quick-action-subtitle">Vouchers</div>
        </a>

        <a href="{{ route('admin.vouchers.index') }}" class="quick-action orange">
            <i class="fas fa-list"></i>
            <div class="quick-action-title">Manage</div>
            <div class="quick-action-subtitle">Vouchers</div>
        </a>
    </div>
</div>

<!-- System Info -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-info-circle" style="color: #3b82f6;"></i> System Info
    </div>
    <div class="sys-info-item">
        <div class="sys-info-label">
            <i class="fas fa-server"></i>
            <span>Laravel</span>
        </div>
        <div class="sys-info-value">{{ app()->version() }}</div>
    </div>
    <div class="sys-info-item">
        <div class="sys-info-label">
            <i class="fas fa-clock"></i>
            <span>Time</span>
        </div>
        <div class="sys-info-value">{{ now()->format('M d, h:i A') }}</div>
    </div>
</div>
@endsection
