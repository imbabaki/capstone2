<!DOCTYPE html>
<html>
<head>
    <title>Review & Pay</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        h2 {
            margin-bottom: 20px;
        }
        ul {
            list-style: none;
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            max-width: 400px;
        }
        li {
            margin-bottom: 10px;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        button:hover {
            background: #218838;
        }
        a {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h2>Review & Pay</h2>

    <ul>
        <li><strong>File:</strong> {{ $order['file_name'] }}</li>
        <li><strong>Copies:</strong> {{ $order['copies'] }}</li>
        <li><strong>Pages:</strong> {{ $order['pages'] ?: 'All' }}</li>
        <li><strong>Color:</strong> {{ ucfirst($order['color'] ?? $order['color_option'] ?? 'N/A') }}</li>
        <li><strong>Paper:</strong> {{ $order['paper_size'] }}</li>
        <li><strong>Duplex:</strong> {{ $order['duplex'] }}</li>
        <li><strong>Total:</strong> ₱{{ number_format($order['calculated_total'], 2) }}</li>
    </ul>

    <a href="{{ route('upload.instructions') }}">
        <button type="button">✅ I have paid</button>
    </a>
</body>
</html>
