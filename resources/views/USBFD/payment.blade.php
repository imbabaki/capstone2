<div class="container my-5">
  <div class="card shadow-lg border-0">
    <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
      <h3 class="mb-0"><i class="bi bi-receipt-cutoff me-2"></i> Review & Pay</h3>
      <span class="badge bg-light text-dark px-3 py-2">Instaprint</span>
    </div>

    <div class="card-body">
      <!-- Order Details -->
      <h5 class="mb-3 text-secondary">📝 Order Summary</h5>
      <ul class="list-group mb-4 shadow-sm">
        <li class="list-group-item"><strong>File:</strong> {{ $order['file_name'] }}</li>
        <li class="list-group-item"><strong>Copies:</strong> {{ $order['copies'] }}</li>
        <li class="list-group-item"><strong>Pages:</strong> {{ $order['pages'] ?: 'All' }}</li>
        <li class="list-group-item"><strong>Color:</strong> {{ ucfirst($order['color']) }}</li>
        <li class="list-group-item"><strong>Paper:</strong> {{ $order['paper_size'] }}</li>
        <li class="list-group-item"><strong>Duplex:</strong> {{ $order['duplex'] }}</li>
      </ul>

      <!-- Payment Status -->
      <h5 class="mb-3 text-secondary">💳 Payment Status</h5>
      <div class="mb-3">
        <p class="mb-1"><strong>Total Due:</strong> 
          <span class="text-danger fs-5">₱{{ number_format($order['total'], 2) }}</span>
        </p>
        <p class="mb-1"><strong>Inserted:</strong> 
          <span id="coinTotal" class="text-success fs-5">₱0.00</span>
        </p>
      </div>

      <!-- Progress Bar -->
      <div class="progress mb-3" style="height: 25px;">
        <div id="payment-progress"
             class="progress-bar bg-success fw-bold"
             role="progressbar"
             style="width: 0%;"
             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
          0%
        </div>
      </div>

      <!-- Remaining Message -->
      <div id="remaining-msg" class="alert alert-warning text-center fw-bold">
        💰 Please insert ₱{{ number_format($order['total'], 2) }}.
      </div>

      <!-- Payment Button -->
      <form id="payment-form" method="POST" 
            action="{{ route('usbfd.payment.handle') }}" 
            style="display:none;">
        @csrf
        <button type="submit" class="btn btn-lg btn-success w-100 shadow-sm">
          ✅ Confirm Payment
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Live Coin Update -->
<script>
const required = {{ $order['total'] }};
const evtSource = new EventSource("http://127.168.0.101:5003/coin/stream");

// --- Update Function ---
function updatePayment(total) {
  // Update inserted text
  const totalDisplay = document.getElementById("coinTotal");
  totalDisplay.innerText = "₱" + total.toFixed(2);

  // Update progress bar
  const progress = Math.min((total / required) * 100, 100);
  const progressBar = document.getElementById("payment-progress");
  progressBar.style.width = progress + "%";
  progressBar.innerText = Math.floor(progress) + "%";
  progressBar.setAttribute("aria-valuenow", progress);

  // Update remaining or confirm UI
  const remainingMsg = document.getElementById("remaining-msg");
  const paymentForm = document.getElementById("payment-form");

  if (total >= required) {
    remainingMsg.style.display = "none";
    paymentForm.style.display = "block";
  } else {
    const remaining = (required - total).toFixed(2);
    remainingMsg.innerText = `💰 Please insert ₱${remaining} more.`;
    remainingMsg.style.display = "block";
    paymentForm.style.display = "none";
  }
}

// --- Handle SSE Messages ---
evtSource.onmessage = function(event) {
  try {
    const data = JSON.parse(event.data);
    const total = parseFloat(data.total || 0);
    updatePayment(total);
  } catch (err) {
    console.error("⚠️ JSON Parse Error:", err);
  }
};

// --- Reconnect on Error ---
evtSource.onerror = function(err) {
  console.error("❌ SSE error or disconnected:", err);
  evtSource.close();
  setTimeout(() => {
    console.log("🔄 Reconnecting to coin stream...");
    location.reload(); // reload to reconnect if needed
  }, 3000);
};
</script>
