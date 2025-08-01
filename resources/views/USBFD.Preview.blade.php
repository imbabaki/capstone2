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
                    <label for="color" class="form-label">Color Option</label>
                    <select name="color" id="color" class="form-select">
                        <option value="color">Color</option>
                        <option value="grayscale">Grayscale</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success w-100">Print File</button>
            </form>
        </div>
    </div>
</div>
