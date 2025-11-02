<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold mb-6">📊 Sales Report</h1>

        <div class="grid grid-cols-3 gap-6 mb-8">
            <!-- Paper Size Stats -->
            <div class="bg-blue-50 p-6 rounded-lg">
                <h3 class="font-bold text-lg mb-3">📄 By Paper Size</h3>
                <p>A4: <strong>{{ $A4_total_print }}</strong> pages</p>
                <p>Short: <strong>{{ $Short_total_print }}</strong> pages</p>
                <p>Legal: <strong>{{ $Legal_total_print }}</strong> pages</p>
            </div>

            <!-- Color Stats -->
            <div class="bg-green-50 p-6 rounded-lg">
                <h3 class="font-bold text-lg mb-3">🎨 By Color Mode</h3>
                <p>Color: <strong>{{ $color_total_print }}</strong> pages</p>
                <p>Grayscale: <strong>{{ $grayscale_total_print }}</strong> pages</p>
            </div>

            <!-- Source Stats -->
            <div class="bg-purple-50 p-6 rounded-lg">
                <h3 class="font-bold text-lg mb-3">📡 By Source</h3>
                <p>USB: <strong>{{ $USB_total_print }}</strong> jobs</p>
                <p>Bluetooth: <strong>{{ $Bluetooth_total_print }}</strong> jobs</p>
                <p>QR Code: <strong>{{ $QR_total_print }}</strong> jobs</p>
            </div>
        </div>

        <!-- Totals -->
        <div class="bg-yellow-50 p-6 rounded-lg">
            <h3 class="font-bold text-2xl mb-3">💰 Summary</h3>
            <p class="text-xl">Total Pages Printed: <strong>{{ $total_pages_print }}</strong></p>
            <p class="text-xl">Total Revenue: <strong>₱{{ number_format($total_amount, 2) }}</strong></p>
        </div>

        <a href="{{ route('start') }}" class="mt-6 inline-block bg-blue-500 text-white px-6 py-3 rounded-lg">← Back</a>
    </div>
</body>
</html>