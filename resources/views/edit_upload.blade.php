<!DOCTYPE html>
<html>
<head>
    <title>Edit & Print</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .container { display: flex; gap: 20px; }
        .preview { flex: 1; border: 1px solid #ccc; height: 600px; overflow: auto; }
        .preview iframe, .preview img { width: 100%; height: 100%; object-fit: contain; }
        .options { flex: 1; }
        label { display: block; margin-top: 10px; }
        select, input { width: 100%; padding: 8px; }
        button { margin-top: 20px; padding: 10px 20px; }
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

                <label>Paper Size</label>
                <select name="paper_size">
                    <option>A4</option>
                    <option>Letter</option>
                    <option>Legal</option>
                </select>

                <label>Copies</label>
                <input type="number" name="copies" value="1" min="1">

                <label>Page Range</label>
                <input type="text" name="page_range" placeholder="e.g. 1-3,5">

                <label>Color Mode</label>
                <select name="color_mode">
                    <option value="color">Color</option>
                    <option value="grayscale">Grayscale</option>
                </select>

                <button type="submit">Save Print Settings</button>
            </form>
        </div>
    </div>
</body>
</html>
