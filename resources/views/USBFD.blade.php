@php
  // existing blade variables: $pdfFiles (initial), $pricing
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=800, height=480, initial-scale=1.0">
  <title>USB Flash Drive Mode</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
  html, body {
    margin: 0;
    padding: 0;
    width: 100vw;
    height: 100vh;
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
    height: calc(100vh - 9vh);
    text-align: center;
  }

  #defaultUI {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
  }

  h1 {
    font-size: 6vh;
    font-weight: 900;
    color: #38bdf8;
    text-shadow: 0 0 2vw rgba(56,189,248,0.6);
    animation: fadeIn 1.2s ease forwards;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .usb-image {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 4vh 0;
    animation: float 3s ease-in-out infinite;
  }

  @keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
  }

  .usb-image img {
    width: clamp(20vh,25vw,35vh);
    user-select: none;
    -webkit-user-drag: none;
    filter: drop-shadow(0 8px 16px rgba(0,0,0,0.5));
  }

  .insert-text {
    font-size: 5vh;
    font-weight: bold;
    color: #e2e8f0;
    margin-top: 2vh;
  }

  .back-button {
    margin-top: 3vh;
    padding: 1.8vh 6vw;
    font-size: 2.8vh;
    font-weight: 900;
    border-radius: 4vh;
    background: linear-gradient(145deg,#0ea5e9,#0284c7);
    color: white;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(14,165,233,0.4);
    transition: all 0.3s ease;
  }

  .back-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(14,165,233,0.6);
  }

  #usbUI {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
  }

  #chooseFileHeader {
    font-size: 4vh;
    margin: 1vh 0;
  }

  .pdf-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-content: flex-start;
    gap: 2vh 2vw;
    flex: 1;
    width: 100%;
    padding: 2vh 3vw;
    overflow: hidden;
  }

  .pdf-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    transition: transform 0.3s ease;
  }

  .pdf-item:hover .pdf-icon {
    transform: scale(1.1);
    filter: drop-shadow(0 4px 8px rgba(220,38,38,0.6));
  }

  .pdf-icon {
    width: 70px;
    height: 90px;
    background: linear-gradient(145deg,#dc2626,#991b1b);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
  }

  .pdf-icon::before {
    content: 'PDF';
    color: white;
    font-weight: 900;
    font-size: 18px;
  }

  .pdf-icon::after {
    content: '';
    position: absolute;
    top: -2px;
    right: -2px;
    width: 18px;
    height: 18px;
    background: white;
    clip-path: polygon(100% 0, 0 0, 100% 100%);
  }

  .pdf-name {
    max-width: 90px;
    text-align: center;
    font-size: 13px;
    font-weight: 700;
    color: #e2e8f0;
    margin-top: 0.6vh;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
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
    font-size: 3.2vh;
    font-weight: 700;
    color: #38bdf8;
    margin-bottom: 1.5vh;
  }

  /* Wrapper for iframe with hidden scrollbar */
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
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
  }

  /* Hide scrollbar in wrapper */
  .pdf-wrapper::-webkit-scrollbar {
    display: none;
    width: 0 !important;
    height: 0 !important;
  }

  .pdf-wrapper {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }

  .preview iframe {
    width: 100%;
    height: 100%;
    min-height: 100%;
    border: none;
    background: white;
    display: block;
    pointer-events: none;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    touch-action: none;
  }

  .options {
    flex: 1.1;
    background: #0f172a;
    color: #e2e8f0;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding: 2vh 3vw;
    box-sizing: border-box;
    border-left: 2px solid #334155;
  }

  .options h3 {
    font-size: 3.2vh;
    font-weight: 700;
    color: #38bdf8;
    margin-bottom: 2vh;
  }

  .options form {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 0.5vh;
  }

  label {
    font-size: 1.8vh;
    font-weight: 600;
    color: #94a3b8;
    margin-bottom: 0.2vh;
    margin-top: 0.2vh;
  }

  select, input[type="text"], input[type="number"] {
    width: 100%;
    border-radius: 1vh;
    border: 3px solid #334155;
    padding: 2vh 2vw;
    font-size: 3.5vh;
    font-weight: 700;
    background: #1e293b;
    color: #e2e8f0;
    transition: 0.3s ease;
    cursor: pointer;
    min-height: 7vh;
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

  select:focus, input:focus {
    outline: none;
    border-color: #0ea5e9;
    border-width: 3px;
    box-shadow: 0 0 0 4px rgba(14,165,233,0.4);
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
    height: 7vh;
    min-width: 55px;
    background: linear-gradient(145deg,#334155,#1e293b);
    border: 3px solid #475569;
    border-radius: 1vh;
    color: #e2e8f0;
    font-size: 5vh;
    cursor: pointer;
    font-weight: 900;
    transition: all 0.2s;
    flex-shrink: 0;
  }

  .number-btn:hover {
    background: linear-gradient(145deg,#475569,#334155);
    border-color: #0ea5e9;
  }

  /* Make select option text bigger */
  select option {
    font-size: 3.5vh;
    padding: 2vh;
    background: #1e293b;
    color: #e2e8f0;
  }

  #totalAmount {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    font-weight: 900;
    color: white;
    font-size: 3vh;
    padding: 1.8vh 2vw;
    text-align: center;
    border: 3px solid #ea580c;
    min-height: 7vh;
  }

  .proceed-button {
    background: linear-gradient(145deg,#22c55e,#16a34a);
    border: none;
    border-radius: 1vh;
    color: white;
    font-size: 3.8vh;
    font-weight: 900;
    padding: 1.8vh;
    margin-top: 0.5vh;
    cursor: pointer;
    transition: 0.3s ease;
    box-shadow: 0 4px 12px rgba(34,197,94,0.4);
    min-height: 7.5vh;
  }

  .proceed-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(34,197,94,0.6);
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
    <div id="defaultUI">
      <h1>USB FLASHDRIVE</h1>
      <div class="usb-image"><img src="/icons/usb.gif" alt="USB Animation"></div>
      <div class="insert-text">INSERT USB FLASHDRIVE</div>
      <a href="{{ route('options') }}" class="back-button">← BACK</a>
    </div>

    <div id="usbUI" style="display:none;">
      <h1 id="chooseFileHeader">CHOOSE YOUR FILE</h1>
      <ul id="pdfList" class="pdf-grid">
        @if(!empty($pdfFiles))
          @foreach($pdfFiles as $file)
            <li class="pdf-item" data-path="{{ $file['path'] }}"
              onclick="previewPDF('{{ route('USBFD.preview', ['filepath' => $file['path']]) }}','{{ $file['name'] }}','{{ $file['path'] }}', {{ $file['pages'] ?? 1 }})">
              <div class="pdf-icon"></div>
              <div class="pdf-name" title="{{ $file['name'] }}">{{ $file['name'] }}</div>
            </li>
          @endforeach
        @endif
      </ul>

      <div id="pdfPreview" style="display:none;" class="container">
        <div class="preview">
          <h3>PDF Preview</h3>
          <div class="pdf-wrapper" id="pdfWrapper">
            <iframe id="pdfViewer" src="" scrolling="no" frameborder="0"></iframe>
          </div>
        </div>

        <div class="options">
          <h3>Print Settings</h3>
          <form action="{{ route('usbfd.process-payment') }}" method="POST" id="printForm">
            @csrf
            <input type="hidden" name="file" id="selectedFileName">
            <input type="hidden" name="file_path" id="selectedPDFPath">
            <input type="hidden" name="pages" id="hidden_pages">
            <input type="hidden" name="pdf_total_pages" id="hidden_pdf_total_pages" value="1">
            <input type="hidden" name="copies" id="hidden_copies" value="1">
            <input type="hidden" name="paper_size" id="hidden_paper_size" value="A4">
            <input type="hidden" name="color" id="hidden_color" value="grayscale">
            <input type="hidden" name="duplex" id="hidden_duplex" value="one-sided">
            <input type="hidden" name="fit" id="hidden_fit" value="none">
            <input type="hidden" name="total" id="calculated_total">

            <label for="paper_size">Paper Size</label>
            <select id="paper_size">
              <option value="A4">A4</option>
              <option value="Letter">Short</option>
              <option value="Legal">Long</option>
            </select>

            <label for="copies">Number of Copies</label>
            <div class="number-input-wrapper">
              <input type="number" id="copies" value="1" min="1" readonly>
              <button type="button" class="number-btn" onclick="incrementCopies()">+</button>
              <button type="button" class="number-btn" onclick="decrementCopies()">-</button>
            </div>

            <label for="pages">Pages to Print</label>
            <input type="text" id="pages" placeholder="All pages" readonly>

            <label for="color_option">Color Mode</label>
            <select id="color_option">
              <option value="color">W/Color</option>
              <option value="grayscale">Black&White</option>
            </select>

            <label>Total Price</label>
            <input type="text" id="totalAmount" readonly>

            <button type="submit" class="proceed-button">Proceed</button>
          </form>
        </div>
      </div>
    </div>
  </div>

 <script>
  const prices = @json($pricing ?? []);
  console.log('💰 Pricing data loaded:', prices);

  const paperSize = document.getElementById('paper_size');
  const colorSel = document.getElementById('color_option');
  const copiesEl = document.getElementById('copies');
  const pagesEl = document.getElementById('pages');
  const totalAmount = document.getElementById('totalAmount');
  const hiddenTotal = document.getElementById('calculated_total');
  const fileNameH = document.getElementById('selectedFileName');
  const filePathH = document.getElementById('selectedPDFPath');
  const pdfListEl = document.getElementById('pdfList');
  const defaultUI = document.getElementById('defaultUI');
  const usbUI = document.getElementById('usbUI');
  const pdfPreview = document.getElementById('pdfPreview');
  const chooseFileHeader = document.getElementById('chooseFileHeader');

  let keyboardValue = '';
  let currentPdfTotalPages = 1;

  console.log('📱 USB Page initialized');

  // 🧮 Parse page ranges (returns number of pages to print)
  function parsePageRange(range, defaultPages) {
    // If empty or whitespace, use defaultPages
    if (!range || range.trim() === '') {
      return defaultPages;
    }

    let total = 0;
    const parts = range.split(',').map(s => s.trim()).filter(Boolean);

    for (const part of parts) {
      if (part.includes('-')) {
        const [s, e] = part.split('-').map(Number);
        if (!isNaN(s) && !isNaN(e) && e >= s && s > 0) {
          total += (e - s + 1);
        }
      } else {
        const p = Number(part);
        if (!isNaN(p) && p > 0) {
          total += 1;
        }
      }
    }
    return total || defaultPages;
  }

  // 💰 Calculate total price
  function calculateTotal(defaultPages = 1) {
    const sizeKey = (paperSize?.value || '').toLowerCase();
    const colorKey = (colorSel?.value || '').toLowerCase();
    const priceKey = `${sizeKey}_${colorKey}`;
    const pricePerPage = Number(prices[priceKey] ?? 0);
    let copies = parseInt(copiesEl.value, 10);
    if (isNaN(copies) || copies <= 0) copies = 1;
    const pagesCount = parsePageRange(pagesEl.value, defaultPages);
    const total = pricePerPage * copies * pagesCount;

    console.log('USB Calculation Debug:');
    console.log('  Default pages:', defaultPages);
    console.log('  Pages input value:', pagesEl.value);
    console.log('  Parsed page count:', pagesCount);
    console.log('  Paper:', sizeKey, '| Color:', colorKey, '| Price key:', priceKey);
    console.log('  Price per page:', pricePerPage);
    console.log('  Copies:', copies);
    console.log('  Formula:', pricePerPage, '×', copies, '×', pagesCount, '=', total);

    totalAmount.value = pricePerPage ? `₱${total.toFixed(2)}` : 'No price configured';
    hiddenTotal.value = total.toFixed(2);
  }

  // 📄 When previewing PDF
  function previewPDF(previewUrl, fileName, realPath, pdfPages = 1) {
    currentPdfTotalPages = pdfPages;
    chooseFileHeader.style.display = 'none';
    pdfListEl.style.display = 'none';
    usbUI.style.display = 'block';
    pdfPreview.style.display = 'flex';

    // Set the hidden field for total pages
    document.getElementById('hidden_pdf_total_pages').value = pdfPages;

    const iframe = document.getElementById('pdfViewer');
    const wrapper = document.getElementById('pdfWrapper');

    // Set iframe height based on page count (approximate 11 inches per page at 96 DPI)
    const estimatedHeight = pdfPages * 1056; // 11 inches * 96 DPI
    iframe.style.height = estimatedHeight + 'px';

    // Load PDF without toolbar and with fit to width
    iframe.src = previewUrl + '#view=FitH&toolbar=0&navpanes=0&scrollbar=0';

    // Enable simple touch/mouse scrolling for single-touch touchscreens
    let isDragging = false;
    let startY = 0;
    let startScrollTop = 0;

    // Handle both touch and mouse events for compatibility
    const startDrag = (clientY) => {
      isDragging = true;
      startY = clientY;
      startScrollTop = wrapper.scrollTop;
    };

    const doDrag = (clientY) => {
      if (!isDragging) return;
      const deltaY = startY - clientY;
      wrapper.scrollTop = startScrollTop + deltaY;
    };

    const endDrag = () => {
      isDragging = false;
    };

    // Touch events for touchscreen
    wrapper.addEventListener('touchstart', (e) => {
      e.preventDefault();
      startDrag(e.touches[0].clientY);
    }, { passive: false });

    wrapper.addEventListener('touchmove', (e) => {
      e.preventDefault();
      if (e.touches.length > 0) {
        doDrag(e.touches[0].clientY);
      }
    }, { passive: false });

    wrapper.addEventListener('touchend', (e) => {
      e.preventDefault();
      endDrag();
    }, { passive: false });

    // Mouse events for testing on desktop
    wrapper.addEventListener('mousedown', (e) => {
      e.preventDefault();
      startDrag(e.clientY);
    });

    wrapper.addEventListener('mousemove', (e) => {
      if (isDragging) {
        e.preventDefault();
        doDrag(e.clientY);
      }
    });

    wrapper.addEventListener('mouseup', (e) => {
      e.preventDefault();
      endDrag();
    });

    wrapper.addEventListener('mouseleave', () => {
      endDrag();
    });

    fileNameH.value = fileName;
    filePathH.value = realPath;
    pagesEl.value = ''; // blank = auto total pages
    pagesEl.placeholder = `All pages (${pdfPages} ${pdfPages === 1 ? 'page' : 'pages'})`;
    calculateTotal(pdfPages);
  }

  // 🔢 Number input (copies)
  function incrementCopies() {
    copiesEl.value = parseInt(copiesEl.value || 1) + 1;
    calculateTotal(currentPdfTotalPages);
  }
  function decrementCopies() {
    const cur = parseInt(copiesEl.value || 1);
    if (cur > 1) copiesEl.value = cur - 1;
    calculateTotal(currentPdfTotalPages);
  }

  // ⌨️ Virtual Keyboard
  function showKeyboard() {
    const overlay = document.getElementById('keyboardOverlay');
    const display = document.getElementById('keyboardDisplay');
    keyboardValue = pagesEl.value;
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
    pagesEl.value = keyboardValue;
    hideKeyboard();
    calculateTotal(currentPdfTotalPages);
  }

  // 🧾 Automatically recalculate when user types pages
  pagesEl.addEventListener('input', () => calculateTotal(currentPdfTotalPages));

  // 🖱️ Show keyboard on click/touch
  pagesEl.addEventListener('click', (e) => { e.preventDefault(); showKeyboard(); });
  pagesEl.addEventListener('touchstart', (e) => { e.preventDefault(); showKeyboard(); });

  // 🔁 Recalculate when paper size or color changes
  [paperSize, colorSel].forEach(el => {
    el?.addEventListener('change', () => calculateTotal(currentPdfTotalPages));
  });

  // 🧾 On submit, include final data
  document.getElementById('printForm').addEventListener('submit', () => {
    document.getElementById('hidden_pages').value = pagesEl.value || '';
    document.getElementById('hidden_copies').value = copiesEl.value || '1';
    document.getElementById('hidden_paper_size').value = paperSize.value || 'A4';
    document.getElementById('hidden_color').value = colorSel.value || 'grayscale';
    document.getElementById('hidden_duplex').value = 'one-sided';
    document.getElementById('hidden_fit').value = 'none'; // Always actual size
  });

  // 🖥️ Initial setup
  window.addEventListener('DOMContentLoaded', () => {
    const hasFiles = @json(!empty($pdfFiles) && count($pdfFiles) > 0);
    if (hasFiles) {
      defaultUI.style.display = 'none';
      usbUI.style.display = 'block';
    }
    calculateTotal(currentPdfTotalPages);
  });


    const STREAM_URLS = [
      "http://127.0.0.1:5004/usb/stream",
      "http://192.168.1.18:5004/usb/stream"
    ];

    let evtSource = null;
    let currentStreamIndex = 0;

    function connectStream() {
      if (evtSource) try { evtSource.close(); } catch(_) {}
      const url = STREAM_URLS[currentStreamIndex];
      console.log('🔌 Connecting USB stream to', url);
      evtSource = new EventSource(url);

      evtSource.onopen = () => console.log('✅ USB SSE connected to', url);
      evtSource.onmessage = (e) => {
        try {
          const data = JSON.parse(e.data);
          console.log('📡 USB SSE message received:', data);
          if (data.status === 'inserted') {
            const files = data.files || [];
            console.log(`📂 Found ${files.length} PDF files`);
            renderFileList(files);
          } else if (data.status === 'removed') {
            console.log('❌ USB removed');
            showDefaultUI();
          }
        } catch(err) {
          console.error('❌ Failed to parse SSE data:', err);
        }
      };
      evtSource.onerror = (err) => {
        console.error('❌ USB SSE error:', err);
        console.log(`🔄 Retrying with URL index ${(currentStreamIndex + 1) % STREAM_URLS.length}`);
        currentStreamIndex = (currentStreamIndex + 1) % STREAM_URLS.length;
        setTimeout(connectStream, 2000);
      };
    }

    function renderFileList(files) {
      console.log('🖼️ Rendering file list with', files.length, 'files');

      if (chooseFileHeader) {
        chooseFileHeader.style.display = 'block';
      }

      pdfListEl.innerHTML = '';
      if (files.length === 0) {
        console.log('⚠️ No PDF files found');
        pdfListEl.innerHTML = '<li style="grid-column: 1/-1; text-align:center;">No PDF files found on USB drive.</li>';
        defaultUI.style.display = 'none';
        usbUI.style.display = 'block';
        return;
      }
      files.forEach((f) => {
        console.log(`📄 Adding file: ${f.name} (${f.pages} pages)`);
        const li = document.createElement('li');
        li.className = 'pdf-item';
        li.setAttribute('data-path', f.path || '');
        li.setAttribute('title', f.name || '');

        const icon = document.createElement('div');
        icon.className = 'pdf-icon';

        const nameDiv = document.createElement('div');
        nameDiv.className = 'pdf-name';
        nameDiv.textContent = f.name || '';

        const previewUrl = "{{ url('/') }}" + "/USBFD/preview/" + encodeURIComponent(f.path || '');
        li.onclick = () => previewPDF(previewUrl, f.name, f.path, f.pages || 1);

        li.appendChild(icon);
        li.appendChild(nameDiv);
        pdfListEl.appendChild(li);
      });
      defaultUI.style.display = 'none';
      usbUI.style.display = 'block';
      pdfPreview.style.display = 'none';
      console.log('✅ File list rendered successfully');
    }

    function showDefaultUI() {
      pdfPreview.style.display = 'none';
      pdfListEl.innerHTML = '';
      usbUI.style.display = 'none';
      defaultUI.style.display = 'block';

      if (chooseFileHeader) {
        chooseFileHeader.style.display = 'block';
      }
    }

    connectStream();
    window.addEventListener('beforeunload', () => { try { evtSource.close(); } catch(_) {} });
  </script>
</body>
</html>
