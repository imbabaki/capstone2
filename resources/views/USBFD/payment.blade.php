<!DOCTYPE html>
<html>
<head>
    <title>Payment - USB Printing</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            background: #f5f5f5;
            text-align: center;
        }

        .container {
            display: inline-block;
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
            max-width: 500px;
            width: 100%;
        }

        h1 {
            margin-bottom: 20px;
            color: #333;
        }

        .amount {
            font-size: 2.5rem;
            font-weight: bold;
            color: #28a745;
            margin: 20px 0;
        }

        .inserted {
            font-size: 1.8rem;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 15px;
        }

        .timer {
            font-size: 1.2rem;
            margin-top: 20px;
            color: #dc3545;
        }

        form {
            margin-top: 30px;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .coin-button {
            margin: 5px;
            padding: 10px 15px;
            background-color: #ffc107;
            border: none;
            font-size: 16px;
            cursor: pointer;
        }

        .coin-button:hover {
            background-color: #e0a800;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Please Insert Coins</h1>

    <p>Total Amount: ₱{{ number_format($calculated_total ?? 0, 2) }}</p>

    <p>Inserted Amount</p>
    <div class="inserted">₱<span id="insertedAmount">0.00</span></div>

    <div class="timer">
        Time remaining: <span id="countdown">120</span> seconds
    </div>

    <div>
        <button class="coin-button" onclick="addCoin(1)">₱1</button>
        <button class="coin-button" onclick="addCoin(5)">₱5</button>
        <button class="coin-button" onclick="addCoin(10)">₱10</button>
    </div>

    <form id="paymentForm" action="{{ route('usb.instructions') }}" method="GET" style="display: none;">
        @csrf
        <input type="hidden" name="file" value="{{ $file }}">
        <input type="hidden" name="copies" value="{{ $copies }}">
        <input type="hidden" name="pages" value="{{ $pages }}">
        <input type="hidden" name="color" value="{{ $color }}">
        <input type="hidden" name="calculated_total" value="{{ $calculated_total }}">
        <input type="hidden" name="amount_paid" id="amountPaid" value="0">
        <button type="submit">Proceed</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let inserted = 0;
    let total = parseFloat(@json($calculated_total ?? 0));

    const insertedAmountEl = document.getElementById('insertedAmount');
    const paymentForm = document.getElementById('paymentForm');
    const amountPaidInput = document.getElementById('amountPaid');

    function addCoin(amount) {
        inserted += amount;
        insertedAmountEl.textContent = inserted.toFixed(2);
        amountPaidInput.value = inserted.toFixed(2);

        if (inserted >= total) {
            paymentForm.style.display = 'block';
        }
    }

    // Expose addCoin globally (so buttons can call it)
    window.addCoin = addCoin;

    // Countdown timer (2 minutes)
    let timeLeft = 120;
    const countdownEl = document.getElementById('countdown');

    const timer = setInterval(() => {
        timeLeft--;
        countdownEl.textContent = timeLeft;

        if (timeLeft <= 0) {
            clearInterval(timer);
            alert("Time expired. Returning to selection page.");
            window.location.href = "{{ route('start') }}";
        }
    }, 1000);
});
</script>


</body>
</html>
