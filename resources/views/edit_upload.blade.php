<!DOCTYPE html>
<html>
<head>
    <title>Edit & Print</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f5f5f5; }
        .container { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px; }
        .preview, .options { flex: 1; min-width: 300px; background: white; padding: 20px; border: 1px solid #ddd; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .preview iframe, .preview img { width: 100%; height: 700px; border: none; object-fit: contain; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        select, input { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 10px; width: 100%; background-color: #28a745; border: none; color: white; font-size: 16px; cursor: pointer; }
        button:hover { background-color: #218838; }
        @media (max-width:768px) { .container { flex-direction: column; } .preview iframe, .preview img { height: 400px; } }
    </style>
</head>
<body>
<h2>File Preview and Print Options</h2>

<div class="container">
    <div class="preview">
        @if(Str::endsWith($fileUrl, '.pdf'))
            <iframe src="{{ $fileUrl }}"></iframe>
        @else
            <img src="{{ $fileUrl }}" alt="Preview">
        @endif
    </div>

    <div class="options">
        <form action="{{ route('upload.payment') }}" method="POST">
            @csrf
            <input type="hidden" name="file_name" value="{{ $filename }}">

            <label for="copies">Copies</label>
            <input type="number" id="copies" name="copies" value="{{ $order['copies'] ?? 1 }}" min="1">

            <label for="pages">Pages (e.g., 1-3,5)</label>
            <input type="text" id="pages" name="pages" value="{{ $order['pages'] ?? 1}}">

            <label for="color_option">Color</label>
            <select id="color_option" name="color_option">
                <option value="color" {{ ($order['color'] ?? '')=='color' ? 'selected' : '' }}>Color</option>
                <option value="grayscale" {{ ($order['color'] ?? '')=='grayscale' ? 'selected' : '' }}>Grayscale</option>
            </select>

            <label for="paper_size">Paper Size</label>
            <select id="paper_size" name="paper_size">
                <option value="A4" {{ ($order['paper_size'] ?? '')=='A4' ? 'selected' : '' }}>A4</option>
                <option value="Letter" {{ ($order['paper_size'] ?? '')=='Letter' ? 'selected' : '' }}>Letter</option>
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

            <button type="submit">Proceed</button>
        </form>
    </div>
</div>

<script>
const prices = @json($pricing);
const paperSize = document.getElementById('paper_size');
const color = document.getElementById('color_option');
const copies = document.getElementById('copies');
const pages = document.getElementById('pages');
const duplex = document.getElementById('duplex');
const fit = document.getElementById('fit');
const totalAmount = document.getElementById('totalAmount');
const hiddenTotal = document.getElementById('calculated_total');

function calculateTotal() {
    const size = paperSize.value;
    const col = color.value;
    const numCopies = parseInt(copies.value) || 1;
    const numPages = parseInt(pages.value.split(',').length) || 1; // simple page count
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
        totalAmount.value = 'N/A';
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