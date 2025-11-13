@php
use Illuminate\Support\Str;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=800, height=480, initial-scale=1.0">
  <title>Bluetooth Preview - Instaprint</title>

  <style>
    html, body {
      width: 100vw;
      height: 100vh;
      margin: 0;
      padding: 0;
      overflow: hidden;
      background: #0f172a;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

    .top-bar span {
      font-size: 1.2vh;
      color: #d1d5db;
      display: block;
      margin-top: 0.2vh;
    }

    .main-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      width: 100%;
      height: calc(100vh - 7vh);
      padding-top: 1vh;
    }

    h1 {
      font-size: 3.5vh;
      font-weight: 900;
      color: #38bdf8;
      text-shadow: 0 0 2vw rgba(56,189,248,0.6);
      margin-bottom: 1vh;
      margin-top: 0;
      animation: fadeIn 1.2s ease forwards;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .container {
      display: flex;
      flex: 1;
      width: 100%;
      height: 100%;
      overflow: hidden;
    }

    .preview {
      flex: 1.2;
      padding: 2vh 2vw;
      height: 100%;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      background: #1e293b;
      color: #e2e8f0;
      border-right: 2px solid #334155;
    }

    .preview h3 {
      font-size: 2.8vh;
      font-weight: 700;
      color: #38bdf8;
      margin-bottom: 1vh;
    }

    .pdf-wrapper {
      flex: 1;
      width: 100%;
      overflow: auto;
      overflow-x: hidden;
      border: 2px solid #334155;
      border-radius: 1vh;
      background: white;
      position: relative;
      -webkit-overflow-scrolling: touch;
      touch-action: pan-y;
      overflow-y: scroll;
    }

    .pdf-wrapper::-webkit-scrollbar {
      display: none;
      width: 0 !important;
    }

    .pdf-wrapper {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    /* Remove overlay - not needed with pointer-events: none on iframe */

    .preview iframe, .preview img {
      width: 100%;
      border: none;
      background: white;
      display: block;
      user-select: none;
      object-fit: contain;
      pointer-events: none;
    }

    .preview iframe {
      height: 100%;
      min-height: 500px;
    }

    .preview img {
      height: 100%;
      min-height: 100%;
      pointer-events: auto;
    }

    .options {
      flex: 1.1;
      background: #0f172a;
      color: #e2e8f0;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      padding: 2vh 3vw 3vh 3vw;
      box-sizing: border-box;
      border-left: 2px solid #334155;
    }

    .options h3 {
      font-size: 2.5vh;
      font-weight: 700;
      color: #38bdf8;
      margin-bottom: 0.5vh;
      margin-top: 0;
    }

    .options form {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: 0.3vh;
    }

    label {
      font-size: 1.8vh;
      font-weight: 600;
      color: #94a3b8;
      margin-bottom: 0.3vh;
      margin-top: 0.3vh;
    }

    select, input[type="text"], input[type="number"] {
      width: 100%;
      border-radius: 1vh;
      border: 2px solid #334155;
      padding: 1.5vh 2vw;
      font-size: 2.5vh;
      font-weight: 700;
      background: #1e293b;
      color: #e2e8f0;
      transition: 0.3s ease;
      cursor: pointer;
      min-height: 6vh;
      box-sizing: border-box;
      -webkit-appearance: none;
      -moz-appearance: none;
      appearance: none;
    }

    select {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 2vw center;
      background-size: 3vh;
      padding-right: 6vw;
    }

    /* Make pages input match select width exactly */
    input[type="text"]#pages {
      padding: 1.5vh 6vw 1.5vh 2vw;
      width: 100%;
    }

    select:focus, input:focus {
      outline: none;
      border-color: #0ea5e9;
      border-width: 2px;
      box-shadow: 0 0 0 3px rgba(14,165,233,0.4);
    }

    .number-input-wrapper {
      display: flex;
      align-items: center;
      gap: 1.5vw;
    }

    .number-input-wrapper input {
      flex: 1;
      text-align: center;
    }

    .number-btn {
      width: 10vw;
      height: 6vh;
      min-width: 50px;
      background: linear-gradient(145deg,#334155,#1e293b);
      border: 2px solid #475569;
      border-radius: 1vh;
      color: #e2e8f0;
      font-size: 3vh;
      cursor: pointer;
      font-weight: 900;
      transition: all 0.2s;
      flex-shrink: 0;
    }

    .number-btn:hover {
      background: linear-gradient(145deg,#475569,#334155);
      border-color: #0ea5e9;
    }

    select option {
      font-size: 2.5vh;
      padding: 1.5vh;
      background: #1e293b;
      color: #e2e8f0;
    }

    #totalAmount {
      background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
      font-weight: 900;
      color: white;
      font-size: 2.8vh;
      padding: 1.5vh;
      text-align: center;
      border: 2px solid #38bdf8;
      border-radius: 1vh;
      min-height: 6vh;
      box-shadow: 0 0 15px rgba(56, 189, 248, 0.6), 0 0 25px rgba(56, 189, 248, 0.4);
      text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
    }

    .proceed-button {
      background: linear-gradient(145deg,#22c55e,#16a34a);
      border: none;
      border-radius: 1vh;
      color: white;
      font-size: 3vh;
      font-weight: 900;
      padding: 1.5vh;
      margin-top: 0.5vh;
      margin-bottom: 2vh;
      cursor: pointer;
      transition: 0.3s ease;
      box-shadow: 0 4px 12px rgba(34,197,94,0.4);
      min-height: 6vh;
    }

    .proceed-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(34,197,94,0.6);
    }

    .back-button {
      margin-top: 0.5vh;
      padding: 1.5vh 4vw;
      font-size: 2.5vh;
      font-weight: 700;
      border-radius: 1vh;
      background: linear-gradient(145deg,#0ea5e9,#0284c7);
      color: white;
      text-decoration: none;
      box-shadow: 0 4px 12px rgba(14,165,233,0.4);
      transition: all 0.3s ease;
      display: block;
      text-align: center;
    }

    .back-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(14,165,233,0.6);
    }

    /* Virtual Keyboard Styles */
    .keyboard-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.9);
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
      gap: 1.5vh;
    }

    .keyboard-row {
      display: flex;
      gap: 1.5vh;
      justify-content: center;
    }

    .keyboard-key {
      background: #334155;
      color: #e2e8f0;
      border: 2px solid #475569;
      border-radius: 0.8vh;
      padding: 2vh 2.5vw;
      font-size: 2.8vh;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
      min-width: 6vw;
    }

    .keyboard-key:hover {
      background: #475569;
      border-color: #0ea5e9;
      transform: translateY(-2px);
    }

    .keyboard-key:active {
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

    .back-button {
      background: linear-gradient(145deg, #06b6d4, #0891b2);
      color: white;
      padding: 1vh 3vw;
      border-radius: 0.8vh;
      font-size: 1.8vh;
      font-weight: 700;
      text-decoration: none;
      display: inline-block;
      align-items: center;
      white-space: nowrap;
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
  </style>
</head>
<body>

  <!-- Virtual Keyboard Overlay -->
  <div id="keyboardOverlay" class="keyboard-overlay">
    <div class="keyboard-container">
      <div class="keyboard-header">
        <div id="keyboardDisplay" class="keyboard-input-display">Enter pages (e.g., 1-5, 8, 10)</div>
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
          <button class="keyboard-key" onclick="addChar('-')">-</button>
          <button class="keyboard-key" onclick="addChar(',')">,</button>
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
    INSTAPRINT<br><span>PRINTING VENDO MACHINE</span>
  </div>

  <div class="main-container">
    <h1>BLUETOOTH FILE PREVIEW & SETTINGS</h1>

    <div class="container">
      <!-- Left: File Preview -->
      <div class="preview">
        <h3>PDF Preview</h3>
        <div class="pdf-wrapper" id="pdfWrapper">
          @if(Str::endsWith($fileUrl, '.pdf'))
            <iframe id="pdfViewer" src="{{ $fileUrl }}#view=FitH&toolbar=0&navpanes=0" frameborder="0"></iframe>
          @else
            <img src="{{ $fileUrl }}" alt="Preview">
          @endif
        </div>
      </div>

      <!-- Right: Print Options -->
      <div class="options">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1vh; gap: 2vw;">
          <h3 style="margin: 0; flex-shrink: 0;">Print Settings</h3>
          <a href="{{ route('start') }}" class="back-button" id="backButton" style="flex-shrink: 0;">
            ← BACK
          </a>
        </div>
        <form action="{{ route('bluetooth.payment') }}" method="POST" id="printForm">
          @csrf
          <input type="hidden" name="file_name" value="{{ $filename }}">
          <input type="hidden" name="duplex" value="one-sided">
          <input type="hidden" name="fit" value="none">

          <label for="paper_size">Paper Size</label>
          <select id="paper_size" name="paper_size">
            <option value="A4">A4</option>
            <option value="Letter">Short</option>
            <option value="Legal">Long</option>
          </select>

          <label for="copies">Number of Copies</label>
          <div class="number-input-wrapper">
            <input type="number" id="copies" name="copies" value="1" min="1" readonly>
            <button type="button" class="number-btn" onclick="incrementCopies()">+</button>
            <button type="button" class="number-btn" onclick="decrementCopies()">-</button>
          </div>

          <label for="pages">Pages to Print</label>
          <input type="text" id="pages" name="pages" placeholder="All pages" readonly>

          <label for="color_option">Color Mode</label>
          <select id="color_option" name="color_option">
            <option value="color">W/Color</option>
            <option value="grayscale">Black&White</option>
          </select>

          <label>Total Price</label>
          <input type="text" id="totalAmount" readonly>
          <input type="hidden" id="calculated_total" name="calculated_total">

          <button type="submit" class="proceed-button">Proceed</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    const prices = @json($pricing ?? []);
    const paperSize = document.getElementById('paper_size');
    const color = document.getElementById('color_option');
    const copies = document.getElementById('copies');
    const pages = document.getElementById('pages');
    const totalAmount = document.getElementById('totalAmount');
    const hiddenTotal = document.getElementById('calculated_total');

    const totalPdfPages = {{ $totalPages ?? 1 }};
    let keyboardValue = '';

    function parsePageRange(range) {
      if (!range || range.trim() === '' || range.trim().toLowerCase() === 'all') {
        return totalPdfPages;
      }

      let total = 0;
      const parts = range.split(',').map(s => s.trim());
      for (const part of parts) {
        if (part.includes('-')) {
          const [start, end] = part.split('-').map(Number);
          if (!isNaN(start) && !isNaN(end) && end >= start) total += (end - start + 1);
        } else if (!isNaN(Number(part))) {
          total += 1;
        }
      }
      return total || totalPdfPages;
    }

    function calculateTotal() {
      const size = paperSize.value;
      const col = color.value;
      const numCopies = parseInt(copies.value) || 1;
      const numPages = parsePageRange(pages.value);

      const match = prices.find(p =>
        p.paper_size === size &&
        p.color_option === col
      );

      if (match) {
        const total = match.price * numCopies * numPages;
        totalAmount.value = '₱' + total.toFixed(2);
        hiddenTotal.value = total.toFixed(2);
      } else {
        totalAmount.value = 'No price configured';
        hiddenTotal.value = '';
      }
    }

    function incrementCopies() {
      copies.value = parseInt(copies.value || 1) + 1;
      calculateTotal();
    }

    function decrementCopies() {
      const cur = parseInt(copies.value || 1);
      if (cur > 1) copies.value = cur - 1;
      calculateTotal();
    }

    [paperSize, color, copies].forEach(el => {
      el.addEventListener('input', calculateTotal);
      el.addEventListener('change', calculateTotal);
    });

    // ⌨️ Virtual Keyboard Functions
    function showKeyboard() {
      const overlay = document.getElementById('keyboardOverlay');
      const display = document.getElementById('keyboardDisplay');
      keyboardValue = pages.value;
      display.textContent = keyboardValue || 'Enter pages (e.g., 1-5, 8, 10)';
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
      document.getElementById('keyboardDisplay').textContent =
        keyboardValue || 'Enter pages (e.g., 1-5, 8, 10)';
    }

    function clearInput() {
      keyboardValue = '';
      document.getElementById('keyboardDisplay').textContent =
        'Enter pages (e.g., 1-5, 8, 10)';
    }

    function doneTyping() {
      pages.value = keyboardValue;
      hideKeyboard();
      calculateTotal();
    }

    // 🖱️ Show keyboard on click/touch for pages field
    pages.addEventListener('click', (e) => { e.preventDefault(); showKeyboard(); });
    pages.addEventListener('touchstart', (e) => { e.preventDefault(); showKeyboard(); });

    window.onload = calculateTotal;

    // ===== SET PDF IFRAME HEIGHT FOR SCROLLING =====
    const pdfViewer = document.getElementById('pdfViewer');
    if (pdfViewer) {
      // Set iframe height based on page count (approximate 11 inches per page at 96 DPI)
      const estimatedHeight = totalPdfPages * 1056; // 11 inches * 96 DPI
      pdfViewer.style.height = estimatedHeight + 'px';
      console.log('PDF iframe height set to:', estimatedHeight, 'px for', totalPdfPages, 'pages');
    }

    // ===== ENABLE TOUCH SCROLLING (USB-STYLE) =====
    const pdfWrapper = document.getElementById('pdfWrapper');

    if (pdfWrapper) {
      let isDragging = false;
      let startY = 0;
      let startScrollTop = 0;

      // Handle both touch and mouse events for compatibility
      const startDrag = (clientY) => {
        isDragging = true;
        startY = clientY;
        startScrollTop = pdfWrapper.scrollTop;
      };

      const doDrag = (clientY) => {
        if (!isDragging) return;
        const deltaY = startY - clientY;
        pdfWrapper.scrollTop = startScrollTop + deltaY;
      };

      const endDrag = () => {
        isDragging = false;
      };

      // Touch events for touchscreen
      pdfWrapper.addEventListener('touchstart', (e) => {
        e.preventDefault();
        startDrag(e.touches[0].clientY);
      }, { passive: false });

      pdfWrapper.addEventListener('touchmove', (e) => {
        e.preventDefault();
        if (e.touches.length > 0) {
          doDrag(e.touches[0].clientY);
        }
      }, { passive: false });

      pdfWrapper.addEventListener('touchend', (e) => {
        e.preventDefault();
        endDrag();
      }, { passive: false });

      // Mouse events for testing on desktop
      pdfWrapper.addEventListener('mousedown', (e) => {
        e.preventDefault();
        startDrag(e.clientY);
      });

      pdfWrapper.addEventListener('mousemove', (e) => {
        if (isDragging) {
          e.preventDefault();
          doDrag(e.clientY);
        }
      });

      pdfWrapper.addEventListener('mouseup', (e) => {
        e.preventDefault();
        endDrag();
      });

      pdfWrapper.addEventListener('mouseleave', () => {
        endDrag();
      });

      console.log('Touch scrolling enabled on PDF wrapper');
    } else {
      console.error('PDF wrapper not found!');
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
    // Clicks on buttons, dropdowns, form elements
    document.getElementById('paper_size').addEventListener('click', resetTimer);
    document.getElementById('paper_size').addEventListener('change', resetTimer);
    document.getElementById('color_option').addEventListener('click', resetTimer);
    document.getElementById('color_option').addEventListener('change', resetTimer);
    document.getElementById('pages').addEventListener('click', resetTimer);

    // Number input buttons
    document.querySelectorAll('.number-btn').forEach(btn => {
        btn.addEventListener('click', resetTimer);
    });

    // Keyboard interactions
    document.querySelectorAll('.keyboard-key').forEach(key => {
        key.addEventListener('click', resetTimer);
    });
    document.querySelectorAll('.keyboard-action-btn').forEach(btn => {
        btn.addEventListener('click', resetTimer);
    });

    // Form submission button
    document.querySelector('.proceed-button').addEventListener('click', resetTimer);

    // Back button
    const backButton = document.getElementById('backButton');
    if (backButton) {
        backButton.addEventListener('click', resetTimer);
    }

    // Initialize timer on page load
    resetTimer();

    console.log('3-minute inactivity timer initialized (resets only on button/input interactions)');
  </script>

  @include('partials.emergency-check')
  @include('partials.hide-url')
</body>
</html>
