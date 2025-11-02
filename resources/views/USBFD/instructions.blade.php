<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printing Instructions</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white p-8">
    <div class="max-w-2xl mx-auto bg-gray-800 rounded-lg shadow-2xl p-8">
        <h2 class="text-3xl font-bold mb-6 text-cyan-400">🖨️ Printing Instructions</h2>
        
        <p class="text-xl mb-6">Please insert <b class="text-yellow-400">{{ $order['paper_size'] }}</b> paper into the tray.</p>
        
        <div class="bg-gray-700 rounded-lg p-6 mb-6">
            <h3 class="font-bold text-lg mb-3">📋 Print Details</h3>
            <ul class="space-y-2">
                <li>📄 File: <strong>{{ $order['file_name'] }}</strong></li>
                <li>🔢 Copies: <strong>{{ $order['copies'] }}</strong></li>
                <li>📑 Pages: <strong>{{ $order['pages'] ?: 'All' }}</strong></li>
                <li>🎨 Color: <strong>{{ ucfirst($order['color']) }}</strong></li>
                <li>📏 Paper: <strong>{{ $order['paper_size'] }}</strong></li>
                <li>🔄 Duplex: <strong>{{ $order['duplex'] }}</strong></li>
                <li>💰 Total: <strong>₱{{ number_format($order['total'], 2) }}</strong></li>
            </ul>
        </div>

        {{-- ✅ Display voucher if change was given --}}
        @if(!empty($order['voucher_code']))
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg p-6 mb-6 border-4 border-green-400 animate-pulse">
            <h3 class="font-bold text-2xl mb-3 text-center">🎟️ YOUR CHANGE VOUCHER</h3>
            <div class="bg-white text-gray-900 rounded-lg p-4 text-center">
                <p class="text-sm mb-2">Voucher Code:</p>
                <p class="text-3xl font-mono font-bold tracking-wider mb-2">{{ $order['voucher_code'] }}</p>
                <p class="text-xl font-bold text-green-600">₱{{ number_format($order['change_amount'], 2) }}</p>
                
                {{-- ✅ Show expiration date --}}
                @if(!empty($order['voucher_expires_at']))
                <div class="mt-3 pt-3 border-t border-gray-300">
                    <p class="text-sm text-gray-600">⏰ Expires on:</p>
                    <p class="text-lg font-bold text-red-600">{{ $order['voucher_expires_at'] }}</p>
                </div>
                @endif
            </div>
            <p class="text-center mt-4 text-sm">💡 Save this code! Use it on your next print before it expires.</p>
            <p class="text-center mt-2 text-xs text-yellow-200">⚠️ This voucher will expire and become invalid after the date shown above.</p>
        </div>
        @endif

        <form method="POST" action="{{ route('usb.print') }}" class="mt-6">
            @csrf
            <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white font-bold py-4 px-6 rounded-lg text-xl transition-all transform hover:scale-105">
                🖨️ Start Printing
            </button>
        </form>
    </div>
</body>
</html>