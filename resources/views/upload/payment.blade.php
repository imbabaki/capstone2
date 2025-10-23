<!DOCTYPE html>
<html>
<head>
    <title>Review & Pay</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        h2 {
            margin-bottom: 20px;
        }
        ul {
            list-style: none;
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            max-width: 400px;
        }
        li {
            margin-bottom: 10px;
        }
        .total-box {
            margin-top: 15px;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            max-width: 400px;
        }
        .progress {
            height: 25px;
            background: #ddd;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 10px;
        }
        .progress-bar {
            height: 100%;
            background: #28a745;
            color: white;
            text-align: center;
            font-weight: bold;
            transition: width 0.3s ease;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            display: none;
        }
        button:hover {
            background: #218838;
        }
        a {
            text-decoration: none;
        }
        .alert {
            margin-top: 15px;
            padding: 10px;
            background: #fff3cd;
            color: #856404;
            border-radius: 6px;
            border: 1px solid #ffeeba;
            max-width: 400px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>Review & Pay</h2>

    <ul>
        <li><strong>File:</strong> {{ $order['file_name'] }}</li>
        <li><strong>Copies:</strong> {{ $order['copies'] }}</li>
        <li><strong>Pages:</strong> {{ $order['pages'] ?: 'All' }}</li>
        <li><strong>Color:</strong> {{ ucfirst($order['color'] ?? $order['color_option'] ?? 'N/A') }}</li>
        <li><strong>Paper:</strong> {{ $order['paper_size'] }}</li>
        <li><strong>Duplex:</strong> {{ $order['duplex'] }}</li>
        <li><strong>Total Due:</strong> ₱{{ number_format($order['calculated_total'], 2) }}</li>
    </ul>

    <div class="total-box">
        <p><strong>Inserted:</strong> <span id="coinTotal">₱0.00</span></p>

        <div class="progress">
            <div id="payment-progress" class="progress-bar" style="width: 0%;">0%</div>
        </div>

        <div id="remaining-msg" class="alert">
            💰 Please insert ₱{{ number_format($order['calculated_total'], 2) }}.
        </div>
    </div>

    <button id="confirm-btn" type="button">✅ I have paid</button>

    <script>
    document.getElementById("confirm-btn").addEventListener("click", () => {
        const payload = {
            paper_size: "{{ $order['paper_size'] }}",
            copies: {{ $order['copies'] ?? 1 }},
            pages: "{{ $order['pages'] ?? '1' }}"
        };

        // Replace with your Pi hotspot IP if using 192.168.4.1
        fetch("http://192.168.4.1:5005/start", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            console.log("✅ Dispenser triggered:", data);

            // Redirect immediately while motor is running
            window.location.href = "{{ route('upload.payments') }}";
        })
        .catch(err => console.error("❌ Error triggering dispenser:", err));
    });
    </script>







    <script>
        // --- CONFIGURATION ---
        const required = {{ $order['calculated_total'] }};
        const evtSource = new EventSource("http://127.168.0.101:5003/coin/stream");

        // --- UPDATE FUNCTION ---
        function updatePayment(total) {
            const totalDisplay = document.getElementById("coinTotal");
            totalDisplay.innerText = "₱" + total.toFixed(2);

            const progress = Math.min((total / required) * 100, 100);
            const progressBar = document.getElementById("payment-progress");
            progressBar.style.width = progress + "%";
            progressBar.innerText = Math.floor(progress) + "%";
            progressBar.setAttribute("aria-valuenow", progress);

            const remainingMsg = document.getElementById("remaining-msg");
            const confirmBtn = document.getElementById("confirm-btn");

            if (total >= required) {
                remainingMsg.style.display = "none";
                confirmBtn.style.display = "inline-block";
            } else {
                const remaining = (required - total).toFixed(2);
                remainingMsg.innerText = `💰 Please insert ₱${remaining} more.`;
                remainingMsg.style.display = "block";
                confirmBtn.style.display = "none";
            }
        }

        // --- HANDLE SSE MESSAGES ---
        evtSource.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);
                const total = parseFloat(data.total || 0);
                updatePayment(total);
            } catch (err) {
                console.error("⚠️ JSON Parse Error:", err);
            }
        };

        // --- RECONNECT ON ERROR ---
        evtSource.onerror = function(err) {
            console.error("❌ SSE error or disconnected:", err);
            evtSource.close();
            setTimeout(() => {
                console.log("🔄 Reconnecting to coin stream...");
                location.reload();
            }, 3000);
        };
    </script>
</body>
</html>
