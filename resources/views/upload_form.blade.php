<!DOCTYPE html>
<html>
<head>
    <title>Upload File</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        h2 {
            margin-bottom: 20px;
        }

        #preview-container {
            margin-top: 20px;
            max-width: 100%;
        }

        #preview img,
        #preview iframe {
            width: 100%;
            height: 600px;
            border: 2px solid #666;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        input[type="file"] {
            font-size: 16px;
            padding: 8px;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #004ea8;
        }
    </style>
</head>
<body>
    <h2>Upload a File</h2>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

<form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <label for="file">Upload PDF</label>
    <input type="file" name="file" id="file" accept="application/pdf" required>
    <button type="submit">Upload</button>
</form>

    <script>
        document.getElementById('fileInput').addEventListener('change', function (e) {
            const file = e.target.files[0];
            const preview = document.getElementById('preview');
            preview.innerHTML = ''; // Clear old preview

            if (!file) return;

            const fileType = file.type;

            if (fileType.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                preview.appendChild(img);
            } else if (fileType === 'application/pdf') {
                const iframe = document.createElement('iframe');
                iframe.src = URL.createObjectURL(file);
                preview.appendChild(iframe);
            } else {
                preview.innerHTML = '<p>Preview not supported for this file type.</p>';
            }
        });
    </script>
</body>
</html>