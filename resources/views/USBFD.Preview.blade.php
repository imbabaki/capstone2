<div class="container mt-4">
    <h2 class="mb-3">Preview: {{ $fileName }}</h2>

    {{-- File Preview --}}
    <div class="mb-4 border rounded shadow-sm p-2 bg-light">
        <iframe src="{{ $filePath }}" width="100%" height="600px" style="border: none;"></iframe>
    </div>

    {{-- Print Options --}}
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Print Options</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('USBFD.print') }}">
                @csrf
                <input type="hidden" name="file" value="{{ $fileName }}">

                <div class="mb-3">
                    <label for="paper_size" class="form-label">Paper Size</label>
                    <select name="paper_size" id="paper_size" class="form-select">
                        <option value="A4">A4</option>
                        <option value="Letter">Letter</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="copies" class="form-label">Number of Copies</label>
                    <input type="number" name="copies" id="copies" class="form-control" value="1" min="1" required>
                </div>

                <div class="mb-3">
                    <label for="pages" class="form-label">Pages to Print</label>
                    <input type="number" name="pages" id="pages" class="form-control" value="1" min="1" required>
                </div>

                <div class="mb-4">
                   <select name="color_option" id="color_option" class="form-select">
    <option value="color">Color</option>
    <option value="grayscale">Grayscale</option>
</select>

                </div>

                <button type="submit" class="btn btn-success w-100">Print File</button>
            </form>
        </div>
    </div>
</div>
<div class="mb-3">
    <label class="form-label fw-bold">Total Amount</label>
    <input type="text" id="totalAmount" class="form-control bg-light" readonly>
</div>

<script>
    // Sample price list fetched from the backend (you’ll replace this with actual DB data)
    const prices = @json($pricing); // You’ll pass $pricing from controller

    const paperSize = document.getElementById('paper_size');
    const color = document.getElementById('color_option');
    const copies = document.getElementById('copies');
    const pages = document.getElementById('pages');
    const totalAmountField = document.getElementById('totalAmount');

    function calculateTotal() {
        const size = paperSize.value;
        const col = color.value;
        const numCopies = parseInt(copies.value) || 1;
        const numPages = parseInt(pages.value) || 1;

        // Match the price from pricing settings
        const match = prices.find(p => p.paper_size === size && p.color_option === col);

        if (match) {
            const total = match.price * numCopies * numPages;
            totalAmountField.value = '₱' + total.toFixed(2);
        } else {
            totalAmountField.value = 'N/A';
        }
    }

    [paperSize, color, copies, pages].forEach(el => {
        el.addEventListener('change', calculateTotal);
        el.addEventListener('input', calculateTotal);
    });

    window.onload = calculateTotal;
</script>
