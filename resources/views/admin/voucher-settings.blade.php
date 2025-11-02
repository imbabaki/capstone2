<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold mb-6">🎟️ Voucher Settings</h1>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('admin.voucher.settings.update') }}">
            @csrf
            
            <div class="mb-6">
                <label class="block text-gray-700 text-lg font-bold mb-2">
                    Voucher Expiration Period
                </label>
                <div class="flex items-center gap-4">
                    <input 
                        type="number" 
                        name="expiration_days" 
                        value="{{ $expirationDays }}"
                        min="1"
                        max="365"
                        class="shadow appearance-none border rounded w-32 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-xl font-bold"
                        required
                    >
                    <span class="text-gray-700 text-lg">days</span>
                </div>
                <p class="text-gray-600 text-sm mt-2">
                    All newly generated vouchers will expire after this many days.
                </p>
            </div>

            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg">
                💾 Save Settings
            </button>
        </form>

        <div class="mt-8 flex gap-4">
            <a href="{{ route('admin.vouchers.index') }}" class="text-blue-500 hover:underline">
                📋 View All Vouchers
            </a>
            <a href="{{ route('start') }}" class="text-gray-500 hover:underline">
                ← Back to Home
            </a>
        </div>
    </div>
</body>
</html>