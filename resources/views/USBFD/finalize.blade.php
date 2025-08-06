<!DOCTYPE html>
<html>
<head>
    <title>Finalize Print</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }

        h1 {
            margin-bottom: 20px;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        .preview, .summary {
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
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }

        .readonly-field {
            background: #eee;
            padding: 8px;
            border: 1px solid #ccc;
            margin-top: 5px;
            width: 100%;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
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

    <h1>Finalize Your Print Job</h1>

    <div class="container">
        <div class="preview">
            <h3>PDF Preview</h3>
            <iframe src="{{ route('USBFD.preview', ['filepath' => $filePath]) }}"></iframe>
        </div>

        <div class="summary">
            <h3>Print Summary</h3>

            <label>File Name</label>
            <div class="readonly-field">{{ $file }}</div>

            <label>Copies</label>
            <div class="readonly-field">{{ $copies }}</div>

            <label>Pages</label>
            <div class="readonly-field">{{ $pages }}</div>

            <label>Color Mode</label>
            <div class="readonly-field">{{ ucfirst($color) }}</div>

            <label>Total Price</label>
            <div class="readonly-field">₱{{ number_format($total, 2) }}</div>

            <form action="{{ route('print.finalize') }}" method="POST">
                @csrf
                <input type="hidden" name="file" value="{{ $file }}">
                <input type="hidden" name="copies" value="{{ $copies }}">
                <input type="hidden" name="pages" value="{{ $pages }}">
                <input type="hidden" name="color" value="{{ $color }}">
                <button type="submit">Print</button>
            </form>
        </div>
    </div>

</body>
</html>
