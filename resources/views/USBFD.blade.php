@php
  // existing blade variables: $pdfFiles (initial), $pricing
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=1024, height=600, initial-scale=1.0">
  <title>USB Flash Drive Mode</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html, body {
      width: 100%;
      height: 100%;
      margin: 0;
      padding: 0;
      overflow: hidden;
      background: linear-gradient(to bottom right, #1f2937, #111827);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: white;
    }
    .top-bar { background: #0f172a; padding: 2vh 4vw; font-weight: bold; color: #67e8f9; text-shadow: 0 0 1vw rgba(103,232,249,0.6); border-bottom: 0.5vh solid #0ea5e9; }
    .top-bar span { font-size: 2vh; color: #d1d5db; display:block; margin-top:0.5vh; }
    .main-container { display:flex; flex-direction:column; align-items:center; justify-content:flex-start; text-align:center; width:100vw; height:100vh; padding-top:4vh; }
    h1 { font-size:6vh; font-weight:900; color:#38bdf8; text-shadow:0 0 2vw rgba(56,189,248,0.6); animation: fadeIn 1.2s ease forwards; }
    @keyframes fadeIn { from { opacity:0; transform:translateY(20px)} to {opacity:1; transform:translateY(0)} }
    .usb-image { display:flex; justify-content:center; align-items:center; margin:5vh 0; animation: float 3s ease-in-out infinite; }
    .usb-image img { width: clamp(20vh,25vw,35vh); user-select:none; -webkit-user-drag:none; }
    .insert-text { font-size:10vh; font-weight:bold; color:#f1f5f9; animation: fadeIn 1.5s ease forwards; }
    .back-button { margin-top:5vh; padding:2vh 6vw; font-size:3vh; font-weight:900; border-radius:4vh; background: linear-gradient(145deg,#0ea5e9,#0284c7); color:white; text-decoration:none; box-shadow:0 0 2vw rgba(14,165,233,0.9); transition:all 0.3s; display:inline-block;}
    .container { display:flex; flex-wrap:wrap; gap:3vw; justify-content:center; margin-top:4vh; }
    ul { list-style:none; padding-left:0; margin-top:4vh; font-size:2vh; max-width:900px; width:90%; }
    li { margin-bottom:1.5vh; background: rgba(253,250,250,0.05); padding:1vh 2vw; border-radius:1vh; box-shadow: inset 0 0 1vh rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:space-between; }
    .preview, .options { flex:1; min-width:40vw; background: rgba(116,168,240,0.8); padding:2vh 2vw; border-radius:2vh; color:black; box-shadow:0 0 2vw rgba(0,0,0,0.5); }
    .preview iframe { width:100%; height:80vh; border:none; background:white; border-radius:1vh; }
    label { display:block; margin-top:1vh; font-size:2vh; font-weight:bold; }
    select, input { width:100%; padding:1vh; margin-top:0.5vh; border-radius:1vh; border:none; font-size:2vh; color:black; }
    #totalAmount { background:#eee; font-weight:bold; color:black; font-size:2.5vh; }
    .proceed-button { margin-top:3vh; padding:2vh; width:100%; background:linear-gradient(145deg,#22c55e,#15803d); border:none; color:white; font-size:2.3vh; font-weight:900; border-radius:2vh; cursor:pointer; }
    .btn-primary { background: linear-gradient(145deg,#3b82f6,#1d4ed8); padding:1vh 2vw; border-radius:2vh; font-size:1.8vh; font-weight:bold; transition:0.3s; color:white; border:none; }
    .btn-primary:hover { transform:scale(1.02); }
    @media (max-width: 1024px) and (max-height:600px) { h1 { font-size:4.5vh } .insert-text { font-size:2.5vh } .back-button { font-size:2.5vh } }
  </style>
</head>

<body>
  <div class="top-bar">
    INSTAPRINT<br>
    <span>PRINTING VENDO MACHINE</span>
  </div>

  <div class="main-container">

    {{-- Default UI: shown when no USB detected --}}
    <div id="defaultUI">
      <h1>USB FLASHDRIVE</h1>
      <div class="usb-image">
        <img src="/icons/usb.gif" alt="USB Animation">
      </div>
      <div class="insert-text">INSERT USB FLASHDRIVE</div>
      <a href="{{ route('options') }}" class="back-button">← BACK</a>
    </div>

    {{-- USB UI: when files exist --}}
    <div id="usbUI" style="display:none;">
      <ul id="pdfList">
        @if(!empty($pdfFiles) && count($pdfFiles) > 0)
          @foreach($pdfFiles as $file)
            <li data-path="{{ $file['path'] }}">
              <span>📄 {{ $file['name'] }}</span>
              <div>
                <button class="btn-primary" onclick="previewPDF('{{ route('USBFD.preview', ['filepath' => $file['path']]) }}','{{ $file['name'] }}','{{ $file['path'] }}', {{ $file['pages'] ?? 1 }})">Select</button>
              </div>
            </li>
          @endforeach
        @endif
      </ul>

      <div id="pdfPreview" style="display:none;" class="container">
        <div class="preview">
          <h3>PDF Preview</h3>
          <iframe id="pdfViewer" src=""></iframe>
        </div>

        <div class="options">
          <h3>Print Settings</h3>
          <form action="{{ route('usbfd.process-payment') }}" method="POST" id="printForm">
            @csrf
            <!-- Hidden synced fields -->
            <input type="hidden" name="file" id="selectedFileName">
            <input type="hidden" name="file_path" id="selectedPDFPath">
            <input type="hidden" name="pages" id="hidden_pages">
            <input type="hidden" name="copies" id="hidden_copies" value="1">
            <input type="hidden" name="paper_size" id="hidden_paper_size" value="A4">
            <input type="hidden" name="color" id="hidden_color" value="grayscale">
            <input type="hidden" name="total" id="calculated_total">

            <label for="paper_size">Paper Size</label>
            <select id="paper_size">
              <option value="A4">A4</option>
              <option value="Letter">Short</option>
              <option value="Legal">Long</option>
            </select>

            <label for="copies">Number of Copies</label>
            <input type="number" id="copies" value="1" min="1" required>

            <label for="pages">Pages to Print (e.g., 1,2,5-7)</label>
            <input type="text" id="pages" placeholder="Print all pages">

            <label for="color_option">Color Mode</label>
            <select id="color_option">
              <option value="color">W/Color</option>
              <option value="grayscale">Black&White</option>
            </select>

            <label for="duplex">Duplex</label>
            <select name="duplex" id="duplex">
              <option value="one-sided">One-sided</option>
              <option value="two-sided-long-edge">Two-sided (long edge)</option>
              <option value="two-sided-short-edge">Two-sided (short edge)</option>
            </select>

            <label for="fit">Scale</label>
            <select name="fit" id="fit">
              <option value="none">Actual size</option>
              <option value="fit-to-page">Fit to page</option>
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

    function previewPDF(previewUrl, fileName, realPath, pdfPages = 1) {
      pdfListEl.style.display = 'none';
      usbUI.style.display = 'block';
      pdfPreview.style.display = 'flex';
      document.getElementById('pdfViewer').src = previewUrl;
      fileNameH.value = fileName;
      filePathH.value = realPath;
      pagesEl.value = '';
      calculateTotal(pdfPages);
    }

    function parsePageRange(range, defaultPages = 1) {
      if (!range || range.trim() === "") return defaultPages;
      let total = 0;
      const parts = range.split(',').map(s => s.trim()).filter(Boolean);
      for (const part of parts) {
        if (part.includes('-')) {
          const [s,e] = part.split('-').map(Number);
          if (!isNaN(s) && !isNaN(e) && e>=s) total += (e-s+1);
        } else {
          const p = Number(part);
          if (!isNaN(p)) total += 1;
        }
      }
      return total;
    }

    function calculateTotal(defaultPages = 1) {
      const sizeKey = (paperSize?.value || '').toLowerCase();
      const colorKey = (colorSel?.value || '').toLowerCase();
      const pricePerPage = Number(prices[`${sizeKey}_${colorKey}`] ?? 0);
      let copies = parseInt(copiesEl.value, 10);
      if (isNaN(copies) || copies <= 0) copies = 1;
      const pagesCount = parsePageRange(pagesEl.value, defaultPages);
      const total = pricePerPage * copies * pagesCount;
      totalAmount.value = pricePerPage ? `₱${total.toFixed(2)}` : 'No price configured';
      hiddenTotal.value = total.toFixed(2);
    }

    // ✅ Sync visible fields to hidden ones before submitting
    document.getElementById('printForm').addEventListener('submit', () => {
      document.getElementById('hidden_pages').value = pagesEl.value || '';
      document.getElementById('hidden_copies').value = copiesEl.value || '1';
      document.getElementById('hidden_paper_size').value = paperSize.value || 'A4';
      document.getElementById('hidden_color').value = colorSel.value || 'grayscale';
    });

    [paperSize, colorSel, copiesEl, pagesEl].forEach(el => {
      el?.addEventListener('input', () => calculateTotal());
      el?.addEventListener('change', () => calculateTotal());
    });

    window.addEventListener('DOMContentLoaded', () => {
      const hasInitialFiles = @json(!empty($pdfFiles) && count($pdfFiles) > 0);
      if (hasInitialFiles) {
        defaultUI.style.display = 'none';
        usbUI.style.display = 'block';
      }
      calculateTotal(1);
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
      console.log('Connecting USB stream to', url);
      evtSource = new EventSource(url);

      evtSource.onopen = () => console.log('USB SSE connected');
      evtSource.onmessage = (e) => {
        try {
          const data = JSON.parse(e.data);
          if (data.status === 'inserted') {
            const files = data.files || [];
            renderFileList(files);
          } else if (data.status === 'removed') {
            showDefaultUI();
          }
        } catch {}
      };
      evtSource.onerror = () => {
        currentStreamIndex = (currentStreamIndex + 1) % STREAM_URLS.length;
        setTimeout(connectStream, 2000);
      };
    }

    function renderFileList(files) {
      pdfListEl.innerHTML = '';
      if (files.length === 0) {
        pdfListEl.innerHTML = '<li>No PDF files found on USB.</li>';
        defaultUI.style.display = 'none';
        usbUI.style.display = 'block';
        return;
      }
      files.forEach((f) => {
        const li = document.createElement('li');
        li.setAttribute('data-path', f.path || '');
        const nameSpan = document.createElement('span');
        nameSpan.textContent = '📄 ' + (f.name || '');
        const btnWrap = document.createElement('div');
        const btn = document.createElement('button');
        btn.className = 'btn-primary';
        const previewUrl = "{{ url('/') }}" + "/USBFD/preview/" + encodeURIComponent(f.path || '');
        btn.textContent = 'Select';
        btn.onclick = () => previewPDF(previewUrl, f.name, f.path, f.pages || 1);
        btnWrap.appendChild(btn);
        li.appendChild(nameSpan);
        li.appendChild(btnWrap);
        pdfListEl.appendChild(li);
      });
      defaultUI.style.display = 'none';
      usbUI.style.display = 'block';
    }

    function showDefaultUI() {
      pdfPreview.style.display = 'none';
      pdfListEl.innerHTML = '';
      usbUI.style.display = 'none';
      defaultUI.style.display = 'block';
    }

    connectStream();
    window.addEventListener('beforeunload', () => { try { evtSource.close(); } catch(_) {} });
  </script>
</body>
</html>
