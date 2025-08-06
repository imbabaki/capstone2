<!DOCTYPE html>
<html>
<head>
    <title>Edit & Print</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            background: #f5f5f5;
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

        .preview iframe, .preview img {
            width: 100%;
            height: 700px;
            border: none;
            object-fit: contain;
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
            width: 100%;
            background-color: #28a745;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .preview iframe, .preview img {
                height: 400px;
            }
        }
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
            <form action="{{ route('upload.options.save') }}" method="POST">
                @csrf
                <input type="hidden" name="filename" value="{{ $filename }}">

                <label for="paper_size">Paper Size</label>
                <select name="paper_size" id="paper_size">
                    <option>A4</option>
                    <option>Letter</option>
                    <option>Legal</option>
                </select>

                <label for="copies">Copies</label>
                <input type="number" name="copies" id="copies" value="1" min="1">

                <label for="page_range">Page Range</label>
                <input type="number" name="page_range" id="pages" value="1" min="1">

                <label for="color_option">Color Mode</label>
                <select name="color_option" id="color_option">
                    <option value="color">Color</option>
                    <option value="grayscale">Black & White</option>
                </select>

                <label>Total Price</label>
                <input type="text" id="totalAmount" class="form-control" readonly style="background: #eee; font-weight: bold;">
                <input type="hidden" name="calculated_total" id="calculated_total">

                <button type="submit">Save Print Settings</button>
            </form>
        </div>
    </div>

    <script>
        const prices = @json($pricing);
        const paperSize = document.getElementById('paper_size');
        const color = document.getElementById('color_option');
        const copies = document.getElementById('copies');
        const pages = document.getElementById('pages');
        const totalAmount = document.getElementById('totalAmount');
        const hiddenTotal = document.getElementById('calculated_total');

        function calculateTotal() {
            const size = paperSize.value;
            const col = color.value;
            const numCopies = parseInt(copies.value) || 1;
            const numPages = parseInt(pages.value) || 1;

            const match = prices.find(p => p.paper_size === size && p.color_option === col);

            if (match) {
                const total = match.price * numCopies * numPages;
                totalAmount.value = '₱' + total.toFixed(2);
                hiddenTotal.value = total.toFixed(2);
            } else {
                totalAmount.value = 'N/A';
                hiddenTotal.value = '';
            }
        }

        [paperSize, color, copies, pages].forEach(el => {
            el.addEventListener('input', calculateTotal);
            el.addEventListener('change', calculateTotal);
        });

        window.onload = calculateTotal;
    </script>
</body>
</html>
