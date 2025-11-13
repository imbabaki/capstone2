<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Admin Login - InstaPrint</title>
    <link rel="stylesheet" href="{{ asset('css/admin-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/all.min.css') }}">
</head>
<body class="login-page">
    <div class="login-container">
        <!-- Logo -->
        <div class="login-logo">
            <div class="login-logo-icon">
                <i class="fas fa-print"></i>
            </div>
            <h1 class="login-title">InstaPrint</h1>
            <p class="login-subtitle">Admin Login</p>
        </div>

        <!-- Login Card -->
        <div class="login-card">
            @if($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 16px;">
                    @foreach($errors->all() as $error)
                        <div>• {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success" style="margin-bottom: 16px;">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="admin@instaprint.com"
                        class="form-control"
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input
                        type="password"
                        name="password"
                        required
                        placeholder="Enter password"
                        class="form-control"
                    >
                </div>

                <!-- Remember Me -->
                <div class="form-group">
                    <label style="display: flex; align-items: center; font-weight: normal; font-size: 14px;">
                        <input type="checkbox" name="remember" style="margin-right: 8px;">
                        Remember me
                    </label>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <!-- Back Link -->
            <div class="text-center mt-3">
                <a href="{{ route('start') }}" style="color: #6b7280; text-decoration: none; font-size: 14px;">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="login-footer">
            &copy; {{ date('Y') }} InstaPrint. All rights reserved.
        </div>
    </div>
</body>
</html>
