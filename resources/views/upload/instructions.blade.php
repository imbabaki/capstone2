<!DOCTYPE html>
<html>
<head>
    <title>Print Instructions</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        h2 { margin-bottom: 20px; }
        ul { background: white; padding: 20px; border: 1px solid #ddd; }
        li { margin-bottom: 10px; }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover { background: #218838; }
    </style>
</head>
<body>
    <h2>Print Instructions</h2>
    <ul>
        <li>File: {{ $order['file_name'] }}</li>
        <li>Copies: {{ $order['copies'] }}</li>
        <li>Pages: {{ $order['pages'] ?: 'All' }}</li>
        <li>Color: {{ ucfirst($order['color'] ?? $order['color_option'] ?? 'N/A') }}</li>
        <li>Paper: {{ $order['paper_size'] }}</li>
        <li>Duplex: {{ $order['duplex'] }}</li>
    </ul>

    <form method="POST" action="{{ route('upload.print') }}">
    @csrf
    <button type="submit">Start Printing</button>
</form>
</body>
</html>
