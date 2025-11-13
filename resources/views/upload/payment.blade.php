<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=800, height=480, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Payment - Instaprint</title>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      width: 100vw;
      height: 100vh;
      overflow: hidden;
      background: #0f172a;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
      color: #e2e8f0;
    }

    .top-bar {
      background: #0f172a;
      color: #67e8f9;
      padding: 0.8vh 1.5vw;
      height: 7vh;
      font-weight: bold;
      text-shadow: 0 0 1vw rgba(103,232,249,0.6);
      border-bottom: 0.2vh solid #0ea5e9;
      font-size: 2vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    }

    .top-bar .subtitle {
      font-size: 1.2vh;
      color: #d1d5db;
      display: block;
      margin-top: 0.2vh;
      font-weight: normal;
      opacity: 1;
    }

    .main-container {
      display: flex;
      height: 93vh;
      width: 100%;
      gap: 0;
    }

    .order-summary {
      flex: 0 0 45%;
      background: #1e293b;
      padding: 2.5vh 2.5vw;
      display: flex;
      flex-direction: column;
      border-right: 2px solid #334155;
    }

    .section-title {
      font-size: 3.2vh;
      font-weight: 700;
      color: #38bdf8;
      margin-bottom: 2vh;
      display: flex;
      align-items: center;
      gap: 1vw;
      padding-bottom: 1.2vh;
      border-bottom: 2px solid #334155;
    }

    .order-details {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 1.5vh;
      justify-content: flex-start;
    }

    .detail-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 2.2vh;
      padding: 1.5vh 2vw;
      background: #0f172a;
      border-radius: 1vh;
      border-left: 4px solid #0ea5e9;
    }

    .detail-label {
      color: #94a3b8;
      font-weight: 500;
    }

    .detail-value {
      color: #e2e8f0;
      font-weight: 700;
    }

    .payment-section {
      flex: 0 0 55%;
      background: #0f172a;
      padding: 3vh 2.5vw;
      display: flex;
      flex-direction: column;
    }

    .voucher-box {
      background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
      border: 2px solid #10b981;
      border-radius: 1vh;
      padding: 2.2vh 2vw;
      margin-bottom: 2.5vh;
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .voucher-box.voucher-applied {
      background: linear-gradient(135deg, #065f46 0%, #047857 100%);
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.02); }
    }

    .voucher-box h3 {
      font-size: 2.4vh;
      color: #d1fae5;
      font-weight: 600;
      margin-bottom: 1.5vh;
    }

    .voucher-input-group {
      display: flex;
      gap: 1.5vw;
    }

    .voucher-input {
      flex: 1;
      padding: 1.5vh 1.5vw;
      font-size: 2.2vh;
      border: 2px solid #10b981;
      border-radius: 0.8vh;
      font-weight: 500;
      background: #0f172a;
      color: #e2e8f0;
      transition: all 0.2s;
    }

    .voucher-input:focus {
      outline: none;
      border-color: #22c55e;
      background: #1e293b;
    }

    .voucher-btn {
      padding: 1.5vh 3vw;
      font-size: 2.2vh;
      font-weight: 700;
      background: #0ea5e9;
      color: white;
      border: none;
      border-radius: 0.8vh;
      cursor: pointer;
      transition: all 0.2s;
    }

    .voucher-btn:hover:not(:disabled) {
      background: #0284c7;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
    }

    .voucher-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .voucher-applied-info {
      color: #d1fae5;
      font-size: 2vh;
      margin-top: 1.5vh;
    }

    .voucher-applied-info p {
      margin: 0.8vh 0;
    }

    .payment-status {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .amount-display {
      background: #1e293b;
      border: 2px solid #334155;
      border-radius: 1vh;
      padding: 2.5vh 2vw;
      margin-bottom: 2.5vh;
    }

    .amount-row {
      display: flex;
      justify-content: space-between;
      font-size: 2.4vh;
      font-weight: 600;
      margin-bottom: 1.8vh;
      padding-bottom: 1.8vh;
      border-bottom: 1px solid #334155;
    }

    .amount-row:last-child {
      margin-bottom: 0;
      padding-bottom: 0;
      border-bottom: none;
      font-size: 2.8vh;
    }

    .amount-label {
      color: #94a3b8;
    }

    .amount-value {
      color: #e2e8f0;
    }

    .amount-value.total {
      color: #ef4444;
      font-size: 3.5vh;
      font-weight: 700;
    }

    .amount-value.inserted {
      color: #22c55e;
      font-size: 3.5vh;
      font-weight: 700;
    }

    .amount-value.discount {
      color: #22c55e;
    }

    .amount-value.original {
      color: #94a3b8;
      text-decoration: line-through;
    }

    .progress-container {
      margin-bottom: 2.5vh;
    }

    .progress-label {
      font-size: 2vh;
      color: #94a3b8;
      font-weight: 500;
      margin-bottom: 1.2vh;
    }

    .progress-bar-bg {
      background: #1e293b;
      height: 4.5vh;
      border-radius: 1.2vh;
      overflow: hidden;
      border: 2px solid #334155;
    }

    .progress-bar-fill {
      background: linear-gradient(90deg, #22c55e, #16a34a);
      height: 100%;
      width: 0%;
      transition: width 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 2.2vh;
      color: white;
    }

    .remaining-message {
      background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
      color: white;
      padding: 2.2vh 2vw;
      border-radius: 1vh;
      text-align: center;
      font-size: 2.6vh;
      font-weight: 700;
      margin-bottom: 1.5vh;
      box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4);
    }

    .confirm-button {
      background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
      color: white;
      border: none;
      border-radius: 1vh;
      padding: 2.8vh 2vw;
      font-size: 3vh;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
      box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
      display: none;
    }

    .confirm-button:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(34, 197, 94, 0.6);
    }

    .confirm-button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .voucher-message {
      margin-top: 1.2vh;
      font-size: 2vh;
      font-weight: 500;
    }

    .alert {
      padding: 1.2vh 1.5vw;
      border-radius: 0.8vh;
      margin-bottom: 0;
      font-size: 2vh;
    }

    .alert-danger {
      background: #7f1d1d;
      color: #fecaca;
      border: 2px solid #991b1b;
    }

    .alert-success {
      background: #14532d;
      color: #bbf7d0;
      border: 2px solid #16a34a;
    }

    .alert-info {
      background: #1e3a8a;
      color: #bfdbfe;
      border: 2px solid #3b82f6;
    }

    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    /* Virtual Keyboard Styles */
    .keyboard-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.85);
      display: none;
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }

    .keyboard-overlay.show {
      display: flex;
    }

    .keyboard-container {
      background: #1e293b;
      border: 3px solid #0ea5e9;
      border-radius: 1.5vh;
      padding: 2vh;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6);
      max-width: 90vw;
    }

    .keyboard-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2vh;
      padding-bottom: 1.5vh;
      border-bottom: 2px solid #334155;
    }

    .keyboard-input-display {
      flex: 1;
      background: #0f172a;
      border: 2px solid #22c55e;
      border-radius: 0.8vh;
      padding: 1.5vh 2vw;
      font-size: 2.8vh;
      color: #22c55e;
      font-weight: 600;
      margin-right: 2vw;
      min-height: 5vh;
    }

    .keyboard-close {
      background: #ef4444;
      color: white;
      border: none;
      border-radius: 0.8vh;
      padding: 1.5vh 2.5vw;
      font-size: 2.4vh;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
    }

    .keyboard-close:hover {
      background: #dc2626;
      transform: scale(1.05);
    }

    .keyboard-keys {
      display: flex;
      flex-direction: column;
      gap: 1vh;
    }

    .keyboard-row {
      display: flex;
      gap: 1vh;
      justify-content: center;
    }

    .keyboard-key {
      background: #334155;
      color: #e2e8f0;
      border: 2px solid #475569;
      border-radius: 0.8vh;
      padding: 2vh 0;
      font-size: 2.6vh;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.15s;
      min-width: 6vw;
      text-align: center;
      user-select: none;
    }

    .keyboard-key:hover {
      background: #475569;
      border-color: #0ea5e9;
      transform: translateY(-2px);
    }

    .keyboard-key:active {
      transform: translateY(0);
      background: #0ea5e9;
    }

    .keyboard-key.wide {
      min-width: 12vw;
    }

    .keyboard-actions {
      display: flex;
      gap: 1.5vh;
      margin-top: 2vh;
      padding-top: 2vh;
      border-top: 2px solid #334155;
    }

    .keyboard-action-btn {
      flex: 1;
      padding: 2vh;
      font-size: 2.6vh;
      font-weight: 700;
      border: none;
      border-radius: 0.8vh;
      cursor: pointer;
      transition: all 0.2s;
    }

    .keyboard-clear {
      background: #f59e0b;
      color: white;
    }

    .keyboard-clear:hover {
      background: #d97706;
      transform: translateY(-2px);
    }

    .keyboard-done {
      background: #22c55e;
      color: white;
    }

    .keyboard-done:hover {
      background: #16a34a;
      transform: translateY(-2px);
    }
  </style>
</head>
<body>

  <!-- Virtual Keyboard Overlay -->
  <div id="keyboardOverlay" class="keyboard-overlay">
    <div class="keyboard-container">
      <div class="keyboard-header">
        <div id="keyboardDisplay" class="keyboard-input-display">Enter voucher code...</div>
        <button class="keyboard-close" onclick="hideKeyboard()">✕ CLOSE</button>
      </div>

      <div class="keyboard-keys">
        <div class="keyboard-row">
          <button class="keyboard-key" onclick="addChar('1')">1</button>
          <button class="keyboard-key" onclick="addChar('2')">2</button>
          <button class="keyboard-key" onclick="addChar('3')">3</button>
          <button class="keyboard-key" onclick="addChar('4')">4</button>
          <button class="keyboard-key" onclick="addChar('5')">5</button>
          <button class="keyboard-key" onclick="addChar('6')">6</button>
          <button class="keyboard-key" onclick="addChar('7')">7</button>
          <button class="keyboard-key" onclick="addChar('8')">8</button>
          <button class="keyboard-key" onclick="addChar('9')">9</button>
          <button class="keyboard-key" onclick="addChar('0')">0</button>
        </div>

        <div class="keyboard-row">
          <button class="keyboard-key" onclick="addChar('Q')">Q</button>
          <button class="keyboard-key" onclick="addChar('W')">W</button>
          <button class="keyboard-key" onclick="addChar('E')">E</button>
          <button class="keyboard-key" onclick="addChar('R')">R</button>
          <button class="keyboard-key" onclick="addChar('T')">T</button>
          <button class="keyboard-key" onclick="addChar('Y')">Y</button>
          <button class="keyboard-key" onclick="addChar('U')">U</button>
          <button class="keyboard-key" onclick="addChar('I')">I</button>
          <button class="keyboard-key" onclick="addChar('O')">O</button>
          <button class="keyboard-key" onclick="addChar('P')">P</button>
        </div>

        <div class="keyboard-row">
          <button class="keyboard-key" onclick="addChar('A')">A</button>
          <button class="keyboard-key" onclick="addChar('S')">S</button>
          <button class="keyboard-key" onclick="addChar('D')">D</button>
          <button class="keyboard-key" onclick="addChar('F')">F</button>
          <button class="keyboard-key" onclick="addChar('G')">G</button>
          <button class="keyboard-key" onclick="addChar('H')">H</button>
          <button class="keyboard-key" onclick="addChar('J')">J</button>
          <button class="keyboard-key" onclick="addChar('K')">K</button>
          <button class="keyboard-key" onclick="addChar('L')">L</button>
        </div>

        <div class="keyboard-row">
          <button class="keyboard-key" onclick="addChar('Z')">Z</button>
          <button class="keyboard-key" onclick="addChar('X')">X</button>
          <button class="keyboard-key" onclick="addChar('C')">C</button>
          <button class="keyboard-key" onclick="addChar('V')">V</button>
          <button class="keyboard-key" onclick="addChar('B')">B</button>
          <button class="keyboard-key" onclick="addChar('N')">N</button>
          <button class="keyboard-key" onclick="addChar('M')">M</button>
          <button class="keyboard-key" onclick="addChar('-')">-</button>
          <button class="keyboard-key wide" onclick="backspace()">⌫ DEL</button>
        </div>
      </div>

      <div class="keyboard-actions">
        <button class="keyboard-action-btn keyboard-clear" onclick="clearInput()">🗑️ CLEAR</button>
        <button class="keyboard-action-btn keyboard-done" onclick="doneTyping()">✓ DONE</button>
      </div>
    </div>
  </div>

  <div class="top-bar">
    INSTAPRINT<br><span class="subtitle">Printing Vendo Machine</span>
  </div>

  <div class="main-container">
    <!-- Left Side: Order Summary -->
    <div class="order-summary">
      <div class="section-title">
        📋 Order Summary
      </div>

      <div class="order-details">
        <div class="detail-row">
          <span class="detail-label">File</span>
          <span class="detail-value">{{ $order['file_name'] }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Copies</span>
          <span class="detail-value">{{ $order['copies'] }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Pages</span>
          <span class="detail-value">{{ $order['pages'] ?: 'All' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Color</span>
          <span class="detail-value">{{ ucfirst($order['color'] ?? $order['color_option'] ?? 'N/A') }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Paper Size</span>
          <span class="detail-value">{{ $order['paper_size'] }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Duplex</span>
          <span class="detail-value">{{ $order['duplex'] }}</span>
        </div>
      </div>
    </div>

    <!-- Right Side: Payment -->
    <div class="payment-section">
      <div class="section-title">
        💳 Payment Details
      </div>

      <!-- Voucher Section -->
      @if(empty($order['voucher_applied']))
      <div class="voucher-box">
        <h3>🎟️ Have a Voucher Code?</h3>
        <div class="voucher-input-group">
          <input
            type="text"
            id="voucherCode"
            class="voucher-input"
            placeholder="Enter voucher code (e.g., PRINT-ABC123)"
            readonly
          >
          <button
            type="button"
            id="apply-voucher-btn"
            onclick="applyVoucher()"
            class="voucher-btn"
          >
            APPLY
          </button>
        </div>
        <div id="voucherMessage" class="voucher-message"></div>
      </div>
      @else
      <!-- Applied Voucher Display -->
      <div class="voucher-box voucher-applied">
        <h3>✅ Voucher Applied!</h3>
        <div class="voucher-applied-info">
          <p><strong>Code:</strong> {{ $order['voucher_code'] }}</p>
          <p><strong>Discount:</strong> -₱{{ number_format($order['voucher_discount'], 2) }}</p>
          <p><strong>Original Total:</strong> ₱{{ number_format($order['original_total'], 2) }}</p>
        </div>
      </div>
      @endif

      <!-- Payment Status -->
      <div class="payment-status">
        <div class="amount-display">
          @if(!empty($order['voucher_applied']))
          <div class="amount-row">
            <span class="amount-label">Original Total</span>
            <span class="amount-value original">₱{{ number_format($order['original_total'], 2) }}</span>
          </div>
          <div class="amount-row">
            <span class="amount-label">Discount</span>
            <span class="amount-value discount">-₱{{ number_format($order['voucher_discount'], 2) }}</span>
          </div>
          @endif
          <div class="amount-row">
            <span class="amount-label">Total Due</span>
            <span class="amount-value total">₱{{ number_format($order['calculated_total'], 2) }}</span>
          </div>
          <div class="amount-row">
            <span class="amount-label">Amount Inserted</span>
            <span id="coinTotal" class="amount-value inserted">₱0.00</span>
          </div>
        </div>

        <div class="progress-container">
          <div class="progress-label">Payment Progress</div>
          <div class="progress-bar-bg">
            <div id="payment-progress" class="progress-bar-fill">0%</div>
          </div>
        </div>

        <div id="remaining-msg" class="remaining-message">
          💰 Please insert ₱{{ number_format($order['calculated_total'], 2) }}
        </div>

        <button id="confirm-btn" type="button" class="confirm-button">
          ✅ Confirm Payment
        </button>
      </div>
    </div>
  </div>

  <script>
    console.log("🚀 Upload payment page loaded");

    // Keyboard functionality
    let keyboardValue = '';

    function showKeyboard() {
      const overlay = document.getElementById('keyboardOverlay');
      const display = document.getElementById('keyboardDisplay');
      const input = document.getElementById('voucherCode');
      keyboardValue = input.value;
      display.textContent = keyboardValue || 'Enter voucher code...';
      overlay.classList.add('show');
    }

    function hideKeyboard() {
      document.getElementById('keyboardOverlay').classList.remove('show');
    }

    function addChar(char) {
      keyboardValue += char;
      document.getElementById('keyboardDisplay').textContent = keyboardValue;
    }

    function backspace() {
      keyboardValue = keyboardValue.slice(0, -1);
      document.getElementById('keyboardDisplay').textContent = keyboardValue || 'Enter voucher code...';
    }

    function clearInput() {
      keyboardValue = '';
      document.getElementById('keyboardDisplay').textContent = 'Enter voucher code...';
    }

    function doneTyping() {
      document.getElementById('voucherCode').value = keyboardValue;
      hideKeyboard();
    }

    // Show keyboard when input is clicked/touched
    document.addEventListener('DOMContentLoaded', function() {
      const voucherInput = document.getElementById('voucherCode');
      if (voucherInput) {
        voucherInput.addEventListener('click', function(e) {
          e.preventDefault();
          showKeyboard();
        });
        voucherInput.addEventListener('touchstart', function(e) {
          e.preventDefault();
          showKeyboard();
        });
      }
    });

    let required = {{ $order['calculated_total'] }};
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
          remainingMsg.innerText = `💰 Please insert ₱${remaining} more`;
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
        btn.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite;">⏳</span> Processing...';

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
        msgEl.innerHTML = '<div class="alert alert-danger">Please enter a voucher code</div>';
        return;
      }

      // Disable button and show loading
      applyBtn.disabled = true;
      applyBtn.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite;">⏳</span> Checking...';
      msgEl.innerHTML = '<div class="alert alert-info">Verifying voucher code...</div>';

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
          msgEl.innerHTML = `<div class="alert alert-success">${data.message}</div>`;

          // Update the total amount display without reloading
          if (data.new_total !== undefined) {
            const totalElements = document.querySelectorAll('.amount-value.total');
            totalElements.forEach(el => {
              el.textContent = '₱' + parseFloat(data.new_total).toFixed(2);
            });

            // Update the required amount variable
            required = parseFloat(data.new_total);
            console.log('✅ Updated required amount to:', required);

            // Trigger updatePayment to check if confirm button should appear
            const currentCoinTotal = parseFloat(document.getElementById('coinTotal').textContent.replace('₱', '')) || 0;
            updatePayment(currentCoinTotal);
          }

          // Hide voucher input section after successful application
          setTimeout(() => {
            const voucherBox = document.getElementById('voucherBox');
            if (voucherBox) {
              voucherBox.style.display = 'none';
            }
          }, 2000);
        } else {
          msgEl.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
          applyBtn.disabled = false;
          applyBtn.innerHTML = 'APPLY';
        }
      } catch (error) {
        console.error('Voucher application error:', error);
        msgEl.innerHTML = '<div class="alert alert-danger">Failed to apply voucher. Please try again.</div>';
        applyBtn.disabled = false;
        applyBtn.innerHTML = 'APPLY';
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

    // 3-minute inactivity timeout - redirect to start
    let inactivityTimer;
    const TIMEOUT_DURATION = 3 * 60 * 1000; // 3 minutes in milliseconds

    function resetTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(() => {
            console.log('3-minute inactivity timeout reached, redirecting to start...');
            window.location.href = "{{ route('start') }}";
        }, TIMEOUT_DURATION);
    }

    // Reset timer ONLY on meaningful user interactions (not passive scrolling/movement)
    // Voucher input interaction
    const voucherCodeInput = document.getElementById('voucherCode');
    if (voucherCodeInput) {
        voucherCodeInput.addEventListener('click', resetTimer);
    }

    // Apply voucher button
    const applyVoucherBtn = document.getElementById('apply-voucher-btn');
    if (applyVoucherBtn) {
        applyVoucherBtn.addEventListener('click', resetTimer);
    }

    // Keyboard interactions
    document.querySelectorAll('.keyboard-key').forEach(key => {
        key.addEventListener('click', resetTimer);
    });
    document.querySelectorAll('.keyboard-action-btn').forEach(btn => {
        btn.addEventListener('click', resetTimer);
    });

    // Confirm payment button
    const confirmButton = document.getElementById('confirm-btn');
    if (confirmButton) {
        confirmButton.addEventListener('click', resetTimer);
    }

    // Initialize timer on page load
    resetTimer();

    console.log('3-minute inactivity timer initialized (resets only on button/input interactions)');
  </script>

  @include('partials.emergency-check')
  @include('partials.hide-url')
</body>
</html>
