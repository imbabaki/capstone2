<form method="POST" action="{{ route('print-settings.store') }}">
    @csrf
    <label>Paper Size:</label>
    <select name="paper_size" required>
        <option value="">-- Select --</option>
        <option value="A4">A4</option>
        <option value="Letter">Letter</option>
        <option value="Legal">Legal</option>
    </select>

    <label>Color:</label>
    <select name="color_option" required>
        <option value="color">Color</option>
        <option value="grayscale">Grayscale</option>
    </select>

    <label>Price:</label>
    <input type="number" step="0.01" name="price" required>

    <button type="submit">Save</button>
</form>
