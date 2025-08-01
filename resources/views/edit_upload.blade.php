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

        h1, h2 {
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

        .preview iframe,
        .preview img {
            width: 100%;
            height: 600px;
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

            .preview iframe,
            .preview img {
                height: 400px;
            }

            button {
                width: 100%;
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

                <label>Paper Size</label>
                <select name="paper_size">
                    <option>A4</option>
                    <option>Letter</option>
                    <option>Legal</option>
                </select>

                <label>Copies</label>
                <input type="number" name="copies" value="1" min="1">

                <label>Page Range</label>
                <input type="number" name="page_range" value="1" min="1">

                <label>Color Mode</label>
                <select name="color_mode">
                    <option value="color">Color</option>
                    <option value="bw">Black & White</option>
                </select>

                <button type="submit">Save Print Settings</button>
            </form>
        </div>
    </div>
</body>
</html>
