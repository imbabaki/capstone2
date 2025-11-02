<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Payment - QR Upload</title>

  <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.css') }}">

  <style>
    * {
      -webkit-overflow-scrolling: touch;
      scroll-behavior: smooth;
      scrollbar-width: none;
      -ms-overflow-style: none;
    }

    ::-webkit-scrollbar { display: none; }

    button, a, input, select {
      touch-action: manipulation;
      -webkit-tap-highlight-color: transparent;
    }

    body {
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
      min-height: 100vh;
      padding: 20px;
    }

    .voucher-badge {
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }

    #confirm-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    #apply-voucher-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
  </style>
</head>
<body>

<div class="container my-5">
  <div class="card shadow-lg border-0">
    <div class="card-header bg-success text-white d-flex align-items-center justify-content-between">
      <h3 class="mb-0"><i class="bi bi-receipt-cutoff me-2"></i> Review & Pay</h3>
      <span class="badge bg-light text-dark px-3 py-2"><i class="bi bi-qr-code me-1"></i>QR Upload</span>
    </div>

    <div class="card-body">
      <!-- Order Details -->
      <h5 class="mb-3 text-secondary">📝 Order Summary</h5>
      <ul class="list-group mb-4 shadow-sm">
        <li class="list-group-item"><strong>File:</strong> {{ $order['file_name'] }}</li>
        <li class="list-group-item"><strong>Copies:</strong> {{ $order['copies'] }}</li>
        <li class="list-group-item"><strong>Pages:</strong> {{ $order['pages'] ?: 'All' }}</li>
        <li class="list-group-item"><strong>Color:</strong> {{ ucfirst($order['color'] ?? $order['color_option'] ?? 'N/A') }}</li>
        <li class="list-group-item"><strong>Paper:</strong> {{ $order['paper_size'] }}</li>
        <li class="list-group-item"><strong>Duplex:</strong> {{ $order['duplex'] }}</li>
      </ul>

      {{-- Voucher Input Section --}}
      @if(empty($order['voucher_applied']))
      <div class="card mb-4 border-success shadow-sm">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0"><i class="bi bi-ticket-perforated me-2"></i>Have a Voucher?</h5>
        </div>
        <div class="card-body">
          <div class="input-group">
            <input
              type="text"
              id="voucherCode"
              class="form-control"
              placeholder="Enter voucher code (e.g., PRINT-ABC123)"
            >
            <button
              type="button"
              id="apply-voucher-btn"
              onclick="applyVoucher()"
              class="btn btn-success"
            >
              <i class="bi bi-check-circle me-1"></i>Apply
            </button>
          </div>
          <div id="voucherMessage" class="mt-3"></div>
        </div>
      </div>
      @else
      {{-- Show Applied Voucher --}}
      <div class="alert alert-success voucher-badge mb-4">
        <h5 class="alert-heading"><i class="bi bi-check-circle-fill me-2"></i>Voucher Applied!</h5>
        <hr>
        <p class="mb-1"><strong>Code:</strong> {{ $order['voucher_code'] }}</p>
        <p class="mb-1"><strong>Discount:</strong> <span class="text-danger">-₱{{ number_format($order['voucher_discount'], 2) }}</span></p>
        <p class="mb-0"><strong>Original Total:</strong> <del>₱{{ number_format($order['original_total'], 2) }}</del></p>
      </div>
      @endif

      <!-- Payment Status -->
      <h5 class="mb-3 text-secondary">💳 Payment Status</h5>
      <div class="mb-3">
        @if(!empty($order['voucher_applied']))
        <p class="mb-1">
          <strong>Original Total:</strong>
          <span class="text-muted"><del>₱{{ number_format($order['original_total'], 2) }}</del></span>
        </p>
        <p class="mb-1">
          <strong>Discount:</strong>
          <span class="text-success">-₱{{ number_format($order['voucher_discount'], 2) }}</span>
        </p>
        @endif
        <p class="mb-1">
          <strong>Total Due:</strong>
          <span class="text-danger fs-5">₱{{ number_format($order['calculated_total'], 2) }}</span>
        </p>
        <p class="mb-1">
          <strong>Inserted:</strong>
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
        💰 Please insert ₱{{ number_format($order['calculated_total'], 2) }}.
      </div>

      <!-- Payment Button -->
      <button id="confirm-btn" type="button" class="btn btn-lg btn-success w-100 shadow-sm" style="display:none;">
        ✅ Confirm Payment
      </button>
    </div>
  </div>
</div>

<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

<script>
console.log("🚀 Upload payment page loaded");

const required = {{ $order['calculated_total'] }};
const evtSource = new EventSource("http://127.0.0.1:5003/coin/stream");

// --- Update Function ---
function updatePayment(total) {
  console.log("💰 Coin total updated:", total);

  const totalDisplay = document.getElementById("coinTotal");
  if (totalDisplay) {
    totalDisplay.innerText = "₱" + total.toFixed(2);
  }

  const progress = Math.min((total / required) * 100, 100);
  const progressBar = document.getElementById("payment-progress");
  if (progressBar) {
    progressBar.style.width = progress + "%";
    progressBar.innerText = Math.floor(progress) + "%";
    progressBar.setAttribute("aria-valuenow", progress);
  }

  const remainingMsg = document.getElementById("remaining-msg");
  const confirmBtn = document.getElementById("confirm-btn");

  if (total >= required) {
    console.log("✅ Sufficient payment received");
    if (remainingMsg) remainingMsg.style.display = "none";
    if (confirmBtn) confirmBtn.style.display = "block";
  } else {
    const remaining = (required - total).toFixed(2);
    if (remainingMsg) {
      remainingMsg.innerText = `💰 Please insert ₱${remaining} more.`;
      remainingMsg.style.display = "block";
    }
    if (confirmBtn) confirmBtn.style.display = "none";
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
    location.reload();
  }, 3000);
};

// --- Confirm Payment Button ---
const confirmBtn = document.getElementById("confirm-btn");
if (confirmBtn) {
  confirmBtn.addEventListener("click", function() {
    console.log("🔵 Confirm button clicked");

    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

    // Prepare dispenser payload
    const payload = {
      paper_size: "{{ $order['paper_size'] }}",
      copies: {{ $order['copies'] ?? 1 }},
      pages: "{{ $order['pages'] ?? '' }}"
    };

    console.log("📦 Dispenser payload:", payload);

    // Trigger dispenser motor with timeout
    const dispenserTimeout = setTimeout(() => {
      console.warn("⏱️ Dispenser timeout - proceeding anyway");
      processPayment();
    }, 5000);

    fetch("http://192.168.4.1:5005/start", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    })
    .then(res => {
      console.log("📡 Dispenser response status:", res.status);
      clearTimeout(dispenserTimeout);
      return res.json();
    })
    .then(data => {
      console.log("✅ Dispenser triggered:", data);
      processPayment();
    })
    .catch(err => {
      console.error("❌ Dispenser error:", err);
      clearTimeout(dispenserTimeout);
      setTimeout(processPayment, 1000);
    });
  });
}

// Process payment via handlePayment endpoint
function processPayment() {
  console.log("📝 Processing payment via server");

  fetch("{{ route('upload.handlePayment') }}", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": "{{ csrf_token() }}",
      "Accept": "application/json"
    }
  })
  .then(res => {
    if (!res.ok) {
      return res.json().then(data => {
        throw new Error(data.message || 'Payment failed');
      });
    }

    return res.json();
  })
  .then(data => {
    if (data.success) {
      console.log("✅ Payment processed successfully");

      setTimeout(() => {
        if (data.redirect) {
          window.location.href = data.redirect;
        } else {
          window.location.href = "{{ route('upload.instructions') }}";
        }
      }, 1000);
    } else {
      console.error("❌ Payment failed:", data.message);
      alert(data.message || 'Payment processing failed');
      location.reload();
    }
  })
  .catch(err => {
    console.error("❌ Payment processing error:", err);
    alert('Failed to process payment: ' + err.message);

    // Re-enable button on error
    const btn = document.getElementById('confirm-btn');
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '✅ Confirm Payment';
    }
  });
}

// --- Voucher Application Function ---
async function applyVoucher() {
  const voucherInput = document.getElementById('voucherCode');
  const msgEl = document.getElementById('voucherMessage');
  const applyBtn = document.getElementById('apply-voucher-btn');

  // Check if elements exist
  if (!voucherInput || !msgEl || !applyBtn) {
    console.log('Voucher elements not found - likely already applied');
    return;
  }

  const code = voucherInput.value.trim();

  if (!code) {
    msgEl.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Please enter a voucher code</div>';
    return;
  }

  // Disable button and show loading
  applyBtn.disabled = true;
  applyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Checking...';
  msgEl.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split me-2"></i>Verifying voucher code...</div>';

  const requestUrl = '/upload/apply-voucher';
  const requestData = {
    code: code,
    source: 'Upload'
  };

  try {
    const response = await fetch(requestUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: JSON.stringify(requestData)
    });

    const data = await response.json();

    if (data.success) {
      msgEl.innerHTML = `<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>${data.message}</div>`;
      setTimeout(() => window.location.href = "{{ route('upload.payment.view') }}", 1500);
    } else {
      msgEl.innerHTML = `<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>${data.message}</div>`;
      applyBtn.disabled = false;
      applyBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Apply';
    }
  } catch (error) {
    console.error('Voucher application error:', error);
    msgEl.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Failed to apply voucher. Please try again.</div>';
    applyBtn.disabled = false;
    applyBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Apply';
  }
}

// Allow pressing Enter to apply voucher (only if input exists)
const voucherInput = document.getElementById('voucherCode');
if (voucherInput) {
  voucherInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      applyVoucher();
    }
  });
}
</script>

</body>
</html>
