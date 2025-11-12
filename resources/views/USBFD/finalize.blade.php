<!DOCTYPE html>
<html>
<head>
    <title>Finalize Print</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }

        h1 {
            margin-bottom: 20px;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        .preview, .summary {
            flex: 1;
            min-width: 300px;
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .preview iframe {
            width: 100%;
            height: 600px;
            border: none;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }

        .readonly-field {
            background: #eee;
            padding: 8px;
            border: 1px solid #ccc;
            margin-top: 5px;
            width: 100%;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .back-button {
            background: linear-gradient(145deg, #06b6d4, #0891b2);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(6, 182, 212, 0.4);
            border: 2px solid #0e7490;
            text-shadow: 0 0 10px rgba(6, 182, 212, 0.8);
        }

        .back-button:hover {
            background: linear-gradient(145deg, #0891b2, #0e7490);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.8), 0 0 20px rgba(6, 182, 212, 0.5);
        }

        .back-button:active {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .preview iframe {
                height: 400px;
            }

            button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <h1>Finalize Your Print Job</h1>

    <div class="container">
        <div class="preview">
            <h3>PDF Preview</h3>
            <iframe src="{{ route('USBFD.preview', ['filepath' => $filePath]) }}"></iframe>
        </div>

        <div class="summary">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0;">Print Summary</h3>
                <a href="{{ route('start') }}" class="back-button" id="backButton">
                    ← BACK
                </a>
            </div>

            <label>File Name</label>
            <div class="readonly-field">{{ $file }}</div>

            <label>Copies</label>
            <div class="readonly-field">{{ $copies }}</div>

            <label>Pages</label>
            <div class="readonly-field">{{ $pages }}</div>

            <label>Color Mode</label>
            <div class="readonly-field">{{ ucfirst($color) }}</div>

            <label>Total Price</label>
            <div class="readonly-field">₱{{ number_format($total, 2) }}</div>

            <form action="{{ route('print.finalize') }}" method="POST">
                @csrf
                <input type="hidden" name="file" value="{{ $file }}">
                <input type="hidden" name="copies" value="{{ $copies }}">
                <input type="hidden" name="pages" value="{{ $pages }}">
                <input type="hidden" name="color" value="{{ $color }}">
                <button type="submit">Print</button>
            </form>
        </div>
    </div>

    <script>
        // 3-minute inactivity timeout - redirect to start
        let inactivityTimer;
        const TIMEOUT_DURATION = 3 * 60 * 1000; // 3 minutes in milliseconds

        function resetTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                console.log('3-minute inactivity timeout reached, redirecting to start...');
                window.location.href = "{{ route('start') }}";
            }, TIMEOUT_DURATION);
            console.log('Timer reset - will redirect in 3 minutes');
        }

        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('USB Finalize page loaded - initializing 3-minute inactivity timer');

            // Reset timer ONLY on meaningful user interactions (not passive scrolling/movement)
            // This page has a Print button as the main interaction
            const printButton = document.querySelector('button[type="submit"]');
            if (printButton) {
                printButton.addEventListener('click', function() {
                    console.log('Print button clicked - resetting timer');
                    resetTimer();
                });
                console.log('Print button listener attached');
            } else {
                console.error('Print button not found!');
            }

            // Back button
            const backButton = document.getElementById('backButton');
            if (backButton) {
                backButton.addEventListener('click', function() {
                    console.log('Back button clicked - resetting timer');
                    resetTimer();
                });
                console.log('Back button listener attached');
            }

            // Initialize timer on page load
            resetTimer();
            console.log('3-minute inactivity timer initialized (resets only on button interactions)');
        });
    </script>

  @include('partials.emergency-check')
</body>
</html>
