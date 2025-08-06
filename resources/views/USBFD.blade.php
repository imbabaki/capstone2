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

        h1 {
            margin-bottom: 20px;
        }

        ul {
            list-style: none;
            padding-left: 0;
        }

        li {
            margin-bottom: 10px;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        .preview, .options {
            flex: 1;
            min-width: 300px;
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .preview iframe {
            width: 100%;
            height: 600px;
            border: none;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        select, input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            box-sizing: border-box;
        }

        button {
            margin-top: 20px;
            padding: 10px;
            width: 20%;
            background-color: #28a745;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 6px;
            font-size: 14px;
            margin-left: 10px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .preview iframe {
                height: 400px;
            }

            button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <h1>USB Flash Drive Mode</h1>
@if(count($pdfFiles) > 0)


        <ul>
            @foreach($pdfFiles as $file)
                <li>
                    📄 {{ $file['name'] }}
                    <button onclick="previewPDF('{{ route('USBFD.preview', ['filepath' => $file['path']]) }}')" class="btn-primary">Select</button>
                </li>
            @endforeach
        </ul>

        <div id="pdfPreview" style="display: none;" class="container">
            <div class="preview">
                <h3>PDF Preview</h3>
                <iframe id="pdfViewer"></iframe>
            </div>     

            <div class="options">
                <h3>Print Settings</h3>
          <form action="{{ route('USBFD.processPayment') }}" method="POST">
                    @csrf
                    <input type="hidden" name="filepath" id="selectedPDFPath">

                    <label for="paper_size">Paper Size</label>
                    <select name="paper_size" id="paper_size">
                        <option value="A4">A4</option>
                        <option value="Letter">Letter</option>
                        <option value="Legal">Legal</option>
                    </select>

                    <label for="copies">Number of Copies</label>
                    <input type="number" name="copies" id="copies" value="1" min="1" required>

                    <label for="pages">Pages to Print (e.g., 1,2,5-7)</label>
                    <input type="text" name="pages" id="pages" value="1" required placeholder="e.g. 1,2,5-7">

                    <label for="color_option">Color Mode</label>
                    <select name="color_option" id="color_option">
                        <option value="color">Color</option>
                        <option value="grayscale">Grayscale</option>
                    </select>

                    <label>Total Price</label>
                    <input type="text" id="totalAmount" class="form-control" readonly style="background: #eee; font-weight: bold;">
                    <input type="hidden" name="calculated_total" id="calculated_total">

                    <button type="submit">Proceed</button>
                </form>
            </div>
        </div>
    @else
        <p>No PDF files found in the USB drive.</p>
    @endif

    <script>
        function previewPDF(url) {
            document.getElementById('pdfViewer').src = url;
            document.getElementById('pdfPreview').style.display = 'flex';
            document.getElementById('selectedPDFPath').value = url;
        }
    </script>

    <script>
        const prices = @json($pricing); // e.g. { "A4_color": 3, "A4_grayscale": 2, ... }

        const paperSize = document.getElementById('paper_size');
        const color = document.getElementById('color_option');
        const copies = document.getElementById('copies');
        const pages = document.getElementById('pages');
        const totalAmount = document.getElementById('totalAmount');
        const hiddenTotal = document.getElementById('calculated_total');

        function parsePageRange(range) {
            let total = 0;
            let parts = range.split(',');
            for (let part of parts) {
                if (part.includes('-')) {
                    let [start, end] = part.split('-').map(Number);
                    if (!isNaN(start) && !isNaN(end) && end >= start) {
                        total += (end - start + 1);
                    }
                } else {
                    let page = parseInt(part);
                    if (!isNaN(page)) {
                        total += 1;
                    }
                }
            }
            return total;
        }

        function calculateTotal() {
    const size = paperSize.value.toLowerCase();
    const col = color.value.toLowerCase();
    const key = `${size}_${col}`;
    const price = prices[key] ?? 0;

    const numCopies = parseInt(copies.value) || 1;
    const pageRange = pages.value;
    const numPages = parsePageRange(pageRange);
    const total = price * numCopies * numPages;

    totalAmount.value = price ? '₱' + total.toFixed(2) : 'N/A';
    hiddenTotal.value = price ? total.toFixed(2) : '';
}

        [paperSize, color, copies, pages].forEach(el => {
            el.addEventListener('input', calculateTotal);
            el.addEventListener('change', calculateTotal);
        });

        window.onload = calculateTotal;
    </script>

</body>
</html>
