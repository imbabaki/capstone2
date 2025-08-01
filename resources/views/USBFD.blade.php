<!DOCTYPE html>
<html>
<head>
    <title>USB Flash Drive Mode</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }

        h1 {
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

        .preview iframe {
            width: 100%;
            height: 600px;
            border: none;
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

            .preview iframe {
                height: 400px;
            }
        }
    </style>
</head>
<body>

    <h1>USB Flash Drive Mode</h1>

    @if(count($pdfFiles) > 0)
        <ul>
            @foreach($pdfFiles as $file)
                <li>
                    📄 {{ $file['name'] }}
                    <button onclick="previewPDF('{{ route('USBFD.preview', ['filepath' => $file['path']]) }}')" class="btn-primary">Select</button>
                </li>
            @endforeach
        </ul>

        <div id="pdfPreview" style="display: none;" class="container">
            <!-- Left: PDF Preview -->
            <div class="preview">
                <h3>PDF Preview</h3>
                <iframe id="pdfViewer"></iframe>
            </div>

            <!-- Right: Print Settings -->
            <div class="options">
                <h3>Print Settings</h3>
                <form action="{{ route('USBFD.print') }}" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="filepath" id="selectedPDFPath">

                    <label for="paper_size">Paper Size</label>
                    <select name="paper_size" id="paper_size">
                        <option value="A4">A4</option>
                        <option value="Letter">Letter</option>
                        <option value="Legal">Legal</option>
                    </select>

                    <label for="copies">Number of Copies</label>
                    <input type="number" name="copies" id="copies" value="1" min="1" required>

                    <label for="pages">Pages to Print</label>
                    <input type="number" name="pages" id="pages" value="1" min="1" required>

                    <label for="color">Color Mode</label>
                    <select name="color" id="color">
                        <option value="color">Color</option>
                        <option value="bw">Black & White</option>
                    </select>

                    <button type="submit">Save Print Settings</button>
                </form>
            </div>
        </div>
    @else
        <p>No PDF files found in the USB drive.</p>
    @endif

    <script>
        function previewPDF(url) {
            document.getElementById('pdfViewer').src = url;
            document.getElementById('pdfPreview').style.display = 'flex';
            document.getElementById('selectedPDFPath').value = url;
        }
    </script>

</body>
</html>
