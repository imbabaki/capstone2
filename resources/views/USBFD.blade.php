<!DOCTYPE html>
<html>
<head>
    <title>USB Flash Drive Mode</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 { margin-bottom: 20px; }
        ul { list-style: none; padding-left: 0; }
        li { margin-bottom: 10px; }
        .container { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px; }
        .preview, .options {
            flex: 1; min-width: 300px;
            background: white; padding: 20px;
            border: 1px solid #ddd; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .preview iframe { width: 100%; height: 600px; border: none; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        select, input { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button {
            margin-top: 20px; padding: 10px; width: 20%;
            background-color: #28a745; border: none; color: white; font-size: 16px; cursor: pointer;
        }
        button:hover { background-color: #218838; }
        .btn-primary {
            background-color: #007bff; color: white; border: none;
            padding: 6px; font-size: 14px; margin-left: 10px; cursor: pointer;
        }
        @media (max-width: 768px) {
            .container { flex-direction: column; }
            .preview iframe { height: 400px; }
            button { width: 100%; }
        }
    </style>
</head>
<body>

<h1>USB Flash Drive Mode</h1>

@if(!empty($pdfFiles) && count($pdfFiles) > 0)
    <ul>
        @foreach($pdfFiles as $file)
            <li>
                📄 {{ $file['name'] }}
                <button onclick="previewPDF(
                    '{{ route('USBFD.preview', ['filepath' => $file['path']]) }}',
                    '{{ $file['name'] }}',
                    '{{ $file['path'] }}',
                    {{ $file['pages'] ?? 1 }}
 )" class="btn-primary">Select</button>
            </li>
        @endforeach
    </ul>

    <div id="pdfPreview" style="display:none;" class="container">
        <div class="preview">
            <h3>PDF Preview</h3>
            <iframe id="pdfViewer"></iframe>
        </div>

        <div class="options">
            <h3>Print Settings</h3>
            <form action="{{ route('USBFD.processPayment') }}" method="POST">
                @csrf
                <input type="hidden" name="file" id="selectedFileName">
                <input type="hidden" name="file_path" id="selectedPDFPath">
                <input type="hidden" name="total_pages" id="total_pages">
                <input type="hidden" name="total" id="calculated_total">

                <label for="paper_size">Paper Size</label>
                <select name="paper_size" id="paper_size">
                    <option value="A4">A4</option>
                    <option value="Letter">Letter</option>
                    <option value="Legal">Legal</option>
                </select>

                <label for="copies">Number of Copies</label>
                <input type="number" name="copies" id="copies" value="1" min="1" required>

                <label for="pages">Pages to Print (e.g., 1,2,5-7)</label>
                <input type="text" name="pages" id="pages" placeholder="Print all pages">

                <label for="color_option">Color Mode</label>
                <select name="color" id="color_option">
                    <option value="color">Color</option>
                    <option value="grayscale">Grayscale</option>
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
                <input type="text" id="totalAmount" readonly style="background:#eee; font-weight:bold;">
                
                <button type="submit">Proceed</button>
            </form>
        </div>
    </div>

@else
    <p>{{ $error ?? 'Insert Flash Drive' }}</p>
@endif

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
const totalPagesH = document.getElementById('total_pages');

function previewPDF(previewUrl, fileName, realPath, pdfPages = 1) {
    const pdfList = document.querySelector('ul');
    if (pdfList) pdfList.style.display = 'none';

    const previewContainer = document.getElementById('pdfPreview');
    previewContainer.style.display = 'flex';

    document.getElementById('pdfViewer').src = previewUrl;
    fileNameH.value = fileName;
    filePathH.value = realPath;
    totalPagesH.value = pdfPages;

    // Set pages input to empty but placeholder shows all pages
    pagesEl.value = '';
    pagesEl.placeholder = `Print all (${pdfPages} pages)`;

    // Recalculate total (use pdfPages as default if pages input empty)
    calculateTotal(pdfPages);
}

function parsePageRange(range, defaultPages = 1) {
    if (!range || range.trim() === "") return defaultPages; // <-- default to all pages
    let total = 0;
    const parts = range.split(',').map(s => s.trim()).filter(Boolean);
    for (const part of parts) {
        if (part.includes('-')) {
            const [s, e] = part.split('-').map(Number);
            if (!isNaN(s) && !isNaN(e) && e >= s) total += (e - s + 1);
        } else {
            const p = Number(part);
            if (!isNaN(p)) total += 1;
        }
    }
    return total;
}

function calculateTotal(defaultPages = null) {
    const sizeKey = (paperSize.value || '').toLowerCase();
    const colorKey = (colorSel.value || '').toLowerCase();
    const pricePerPage = Number(prices[`${sizeKey}_${colorKey}`] ?? 0);

    let copies = parseInt(copiesEl.value, 10);
    if (isNaN(copies) || copies <= 0) copies = 1;

    const pagesCount = parsePageRange(pagesEl.value || '', defaultPages || parseInt(totalPagesH.value || 1));
    const total = pricePerPage * copies * pagesCount;

    totalAmount.value = pricePerPage ? `₱${total.toFixed(2)}` : 'No price configured';
    hiddenTotal.value = pricePerPage ? total.toFixed(2) : '';
}

// Event listeners
[paperSize, colorSel, copiesEl, pagesEl].forEach(el => {
    el.addEventListener('input', () => calculateTotal());
    el.addEventListener('change', () => calculateTotal());
});

window.addEventListener('DOMContentLoaded', () => calculateTotal(parseInt(totalPagesH.value || 1)));
</script>

</body>
</html>
