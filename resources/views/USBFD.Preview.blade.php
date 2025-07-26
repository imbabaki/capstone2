<h2>Preview: {{ $fileName }}</h2>

{{-- Basic preview depending on file type --}}
<iframe src="{{ $filePath }}" width="100%" height="600px"></iframe>

<h3>Print Options</h3>
<form method="POST" action="{{ route('USBFD.print') }}">
    @csrf
    <input type="hidden" name="file" value="{{ $fileName }}">
 <div class="form-group">
        <label for="paper_size">Paper Size</label>
        <select name="paper_size" id="paper_size" class="form-control">
            <option value="A4">A4</option>
            <option value="Letter">Letter</option>
        </select>
    </div>
    <label>Number of copies:</label>
    <input type="number" name="copies" value="1" min="1"><br>

    <label>Pages to print (e.g., 1-3):</label>
    <input type="text" name="pages"><br>

    <label>Color:</label>
    <select name="color">
        <option value="color">Color</option>
        <option value="grayscale">Grayscale</option>
    </select><br><br>

    <button type="submit">Print</button>
</form>
