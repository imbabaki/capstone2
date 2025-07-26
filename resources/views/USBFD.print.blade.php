<form action="{{ route('USBFD.print') }}" method="POST">
    @csrf
    <input type="hidden" name="filename" value="{{ $file['name'] }}">
    
    <div class="form-group">
        <label for="paper_size">Paper Size</label>
        <select name="paper_size" id="paper_size" class="form-control">
            <option value="A4">A4</option>
            <option value="Letter">Letter</option>
        </select>
    </div>

    <div class="form-group">
        <label for="color">Color Option</label>
        <select name="color" id="color" class="form-control">
            <option value="color">Color</option>
            <option value="grayscale">Grayscale</option>
        </select>
    </div>

    <div class="form-group">
        <label for="copies">Copies</label>
        <input type="number" name="copies" id="copies" class="form-control" value="1" min="1">
    </div>

    <button type="submit" class="btn btn-success mt-2">Print</button>
</form>
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
