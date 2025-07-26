<h1>USB Flash Drive Mode</h1>

@if(count($pdfFiles) > 0)
    <ul>
        @foreach($pdfFiles as $file)
            <li>
                📄 {{ $file['name'] }}
               <button onclick="previewPDF('{{ route('USBFD.preview', ['filepath' => $file['path']]) }}')">Select</button>

            </li>
        @endforeach
    </ul>

    <hr>

    <div id="pdfPreview" style="display: none;">
        <h2>PDF Preview</h2>
        <iframe id="pdfViewer" width="100%" height="500px" style="border: 1px solid #ccc;"></iframe>

        <h3>Print Settings</h3>
        <form action="{{ route('USBFD.print') }}" method="POST" target="_blank">
            @csrf
            <input type="hidden" name="filepath" id="selectedPDFPath">
 <div class="form-group">
        <label for="paper_size">Paper Size</label>
        <select name="paper_size" id="paper_size" class="form-control">
            <option value="A4">A4</option>
            <option value="Letter">Letter</option>
        </select>
    </div>
            <label for="copies">Number of copies:</label>
            <input type="number" name="copies" id="copies" value="1" min="1" required><br><br>
           
            <label for="pages">Pages to print (e.g., 1-3):</label>
            <input type="text" name="pages" id="pages" placeholder="All"><br><br>

            <label for="color">Color:</label>
            <select name="color" id="color">
                <option value="color">Color</option>
                <option value="bw">Black & White</option>
            </select><br><br>

            <button type="submit">Print</button>
        </form>
    </div>
@else
    <p>No PDF files found in the USB drive.</p>
@endif

<script>
    function previewPDF(url) {
        document.getElementById('pdfViewer').src = url;
        document.getElementById('pdfPreview').style.display = 'block';
        document.getElementById('selectedPDFPath').value = url;
    }
</script>
