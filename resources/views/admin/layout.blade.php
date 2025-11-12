<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>@yield('title', 'Admin') - InstaPrint</title>
    <link rel="stylesheet" href="{{ asset('css/admin-mobile.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @auth
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="{{ route('admin.dashboard') }}" class="navbar-brand">
                <i class="fas fa-print"></i>
                <span>InstaPrint</span>
            </a>

            <button class="navbar-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>

            <div class="navbar-menu" id="navbarMenu">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="{{ route('admin.print-settings.index') }}" class="{{ request()->routeIs('admin.print-settings.*') ? 'active' : '' }}">
                    <i class="fas fa-dollar-sign"></i> Prices
                </a>
                <a href="{{ route('admin.sales.report') }}" class="{{ request()->routeIs('admin.sales.report') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Sales
                </a>
                <a href="{{ route('admin.vouchers.index') }}" class="{{ request()->routeIs('admin.vouchers.*') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt"></i> Vouchers
                </a>

                <div class="navbar-user">
                    <div style="padding: 8px 16px; font-size: 13px; opacity: 0.8;">
                        <i class="fas fa-user"></i> {{ auth()->user()->name }}
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" style="width: 100%; text-align: left; background: rgba(255,255,255,0.1); border: none; color: white; padding: 12px 16px; border-radius: 8px; cursor: pointer; font-size: 14px;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Bottom Navigation (Mobile) -->
    <div class="bottom-nav">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('admin.print-settings.index') }}" class="{{ request()->routeIs('admin.print-settings.*') ? 'active' : '' }}">
            <i class="fas fa-dollar-sign"></i>
            <span>Prices</span>
        </a>
        <a href="{{ route('admin.sales.report') }}" class="{{ request()->routeIs('admin.sales.report') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            <span>Sales</span>
        </a>
        <a href="{{ route('admin.vouchers.index') }}" class="{{ request()->routeIs('admin.vouchers.*') ? 'active' : '' }}">
            <i class="fas fa-ticket-alt"></i>
            <span>Vouchers</span>
        </a>
    </div>
    @endauth

    <!-- Main Content -->
    <main class="content container">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Mobile Menu Script -->
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const navbarMenu = document.getElementById('navbarMenu');

        if (menuToggle && navbarMenu) {
            menuToggle.addEventListener('click', function() {
                navbarMenu.classList.toggle('active');
                const icon = this.querySelector('i');
                if (navbarMenu.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });

            // Close menu when clicking a link
            navbarMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    navbarMenu.classList.remove('active');
                    const icon = menuToggle.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                });
            });
        }

        // Prevent double-tap zoom
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);

        // Touch feedback
        document.querySelectorAll('.btn, .quick-action, .price-item-actions button').forEach(element => {
            element.addEventListener('touchstart', function() {
                this.style.opacity = '0.7';
            });
            element.addEventListener('touchend', function() {
                this.style.opacity = '1';
            });
        });
    </script>
</body>
</html>
