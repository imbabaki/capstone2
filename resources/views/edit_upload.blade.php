@php
use Illuminate\Support\Str;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=1024, height=600, initial-scale=1.0">
  <title>File Preview & Print</title>
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

    /* Top bar */
    .top-bar {
      background: #0f172a;
      padding: 2vh 4vw;
      font-weight: bold;
      color: #67e8f9;
      text-shadow: 0 0 1vw rgba(103,232,249,0.6);
      border-bottom: 0.5vh solid #0ea5e9;
    }

    .top-bar span {
      font-size: 2vh;
      color: #d1d5db;
      display: block;
      margin-top: 0.5vh;
    }

    .main-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      text-align: center;
      width: 100vw;
      height: 100vh;
      padding-top: 4vh;
    }

    h1 {
      font-size: 6vh;
      font-weight: 900;
      letter-spacing: 0.3vw;
      margin-bottom: 3vh;
      color: #38bdf8;
      text-shadow: 0 0 2vw rgba(56,189,248,0.6);
      animation: fadeIn 1.2s ease forwards;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Container for preview and options */
    .container {
      display: flex;
      flex-wrap: wrap;
      gap: 3vw;
      justify-content: center;
      margin-top: 2vh;
      width: 90vw;
    }

    .preview, .options {
      flex: 1;
      min-width: 40vw;
      background: rgba(116, 168, 240, 0.85);
      padding: 2vh 2vw;
      border-radius: 2vh;
      color: black;
      box-shadow: 0 0 2vw rgba(0,0,0,0.5);
      animation: fadeIn 1.2s ease forwards;
    }

    .preview iframe, .preview img {
      width: 100%;
      height: 80vh;
      border: none;
      background: white;
      border-radius: 1vh;
      object-fit: contain;
    }

    label {
      display: block;
      margin-top: 1vh;
      font-size: 2vh;
      font-weight: bold;
    }

    select, input {
      width: 100%;
      padding: 1vh;
      margin-top: 0.5vh;
      border-radius: 1vh;
      border: none;
      font-size: 2vh;
      color: black;
    }

    #totalAmount {
      background: #eee;
      font-weight: bold;
      color: black;
      font-size: 2.5vh;
    }

    .proceed-button {
      margin-top: 3vh;
      padding: 2vh;
      width: 100%;
      background: linear-gradient(145deg, #22c55e, #15803d);
      border: none;
      color: white;
      font-size: 2.3vh;
      font-weight: 900;
      border-radius: 2vh;
      cursor: pointer;
      transition: 0.3s;
      display: block;
    }

    .proceed-button:hover {
      background: linear-gradient(145deg, #dc2626, #b91c1c);
      transform: scale(1.05);
    }

    .back-button {
      margin-top: 3vh;
      padding: 1.5vh 4vw;
      font-size: 2.5vh;
      font-weight: 900;
      border-radius: 4vh;
      background: linear-gradient(145deg, #0ea5e9, #0284c7);
      color: white;
      text-decoration: none;
      box-shadow: 0 0 2vw rgba(14,165,233,0.9);
      transition: all 0.3s ease-in-out;
      display: inline-block;
    }

    .back-button:hover {
      background: linear-gradient(145deg, #dc2626, #b91c1c);
      transform: scale(1.08);
      box-shadow: 0 0 3vw rgba(220,38,38,0.9);
    }

  </style>
</head>
<body>

  <div class="top-bar">
    INSTAPRINT<br>
    <span>PRINTING VENDO MACHINE</span>
  </div>

  <div class="main-container">
    <h1>File Preview & Print Settings</h1>

    <div class="container">

      <!-- Left: File Preview -->
      <div class="preview">
        @if(Str::endsWith($fileUrl, '.pdf'))
          <iframe src="{{ $fileUrl }}#toolbar=0"></iframe>
        @else
          <img src="{{ $fileUrl }}" alt="Preview">
        @endif
      </div>

      <!-- Right: Print Options -->
      <div class="options">
        <form action="{{ route('upload.payment') }}" method="POST">
          @csrf
          <input type="hidden" name="file_name" value="{{ $filename }}">

          <label for="copies">Copies</label>
          <input type="number" id="copies" name="copies" value="{{ $order['copies'] ?? 1 }}" min="1">

          <label for="pages">Pages (leave empty for all pages)</label>
          <input type="text" id="pages" name="pages" value="{{ $order['pages'] ?? '' }}" placeholder="All pages">

          <label for="color_option">Color</label>
          <select id="color_option" name="color_option">
              <option value="color" {{ ($order['color'] ?? '')=='color' ? 'selected' : '' }}>Color</option>
              <option value="grayscale" {{ ($order['color'] ?? '')=='grayscale' ? 'selected' : '' }}>Grayscale</option>
          </select>

          <label for="paper_size">Paper Size</label>
          <select id="paper_size" name="paper_size">
              <option value="A4" {{ ($order['paper_size'] ?? '')=='A4' ? 'selected' : '' }}>A4</option>
              <option value="Letter" {{ ($order['paper_size'] ?? '')=='Letter' ? 'selected' : '' }}>Letter</option>
              <option value="Legal" {{ ($order['paper_size'] ?? '')=='Legal' ? 'selected' : '' }}>Legal</option>
          </select>

          <label for="duplex">Duplex</label>
          <select id="duplex" name="duplex">
              <option value="one-sided" {{ ($order['duplex'] ?? '')=='one-sided' ? 'selected' : '' }}>One-sided</option>
              <option value="two-sided-long-edge" {{ ($order['duplex'] ?? '')=='two-sided-long-edge' ? 'selected' : '' }}>Two-sided Long Edge</option>
              <option value="two-sided-short-edge" {{ ($order['duplex'] ?? '')=='two-sided-short-edge' ? 'selected' : '' }}>Two-sided Short Edge</option>
          </select>

          <label for="fit">Fit</label>
          <select id="fit" name="fit">
              <option value="none" {{ ($order['fit'] ?? '')=='none' ? 'selected' : '' }}>None</option>
              <option value="fit-to-page" {{ ($order['fit'] ?? '')=='fit-to-page' ? 'selected' : '' }}>Fit to Page</option>
          </select>

          <label for="totalAmount">Total</label>
          <input type="text" id="totalAmount" readonly>
          <input type="hidden" id="calculated_total" name="calculated_total">

          <button type="submit" class="proceed-button">Proceed</button>
        </form>

        <a href="{{ route('options') }}" class="back-button">← BACK</a>
      </div>
    </div>
  </div>

  <script>
    const prices = @json($pricing ?? []);
    const paperSize = document.getElementById('paper_size');
    const color = document.getElementById('color_option');
    const copies = document.getElementById('copies');
    const pages = document.getElementById('pages');
    const duplex = document.getElementById('duplex');
    const fit = document.getElementById('fit');
    const totalAmount = document.getElementById('totalAmount');
    const hiddenTotal = document.getElementById('calculated_total');

    // Total number of pages in the PDF (from controller)
    const totalPdfPages = {{ $totalPages ?? 1 }};

    // Parses custom page ranges (e.g., 1-3,5)
    function parsePageRange(range) {
      if (!range || range.trim() === '') {
        // ✅ If user left it blank → print all pages
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

    // Calculates total cost dynamically
    function calculateTotal() {
      const size = paperSize.value;
      const col = color.value;
      const numCopies = parseInt(copies.value) || 1;
      const numPages = parsePageRange(pages.value);
      const side = duplex.value;

      const match = prices.find(p =>
          p.paper_size === size &&
          p.color_option === col &&
          (p.duplex ?? 'one-sided') === side
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

    [paperSize, color, copies, pages, duplex, fit].forEach(el => {
      el.addEventListener('input', calculateTotal);
      el.addEventListener('change', calculateTotal);
    });

    window.onload = calculateTotal;
  </script>

</body>
</html>
