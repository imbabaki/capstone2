<h2>Review & Pay</h2>
<ul>
  <li>File: {{ $order['file_name'] }}</li>
  <li>Copies: {{ $order['copies'] }}</li>
  <li>Pages: {{ $order['pages'] ?: 'All' }}</li>
  <li>Color: {{ ucfirst($order['color'] ?? $order['color_option'] ?? 'N/A') }}</li>
  <li>Paper: {{ $order['paper_size'] }}</li>
  <li>Duplex: {{ $order['duplex'] }}</li>
  <li>Total: ₱{{ number_format($order['calculated_total'], 2) }}</li>
</ul>

<!-- ✅ Clean navigation without form -->
<a href="{{ route('upload.instructions') }}" style="text-decoration: none;">
    <button type="button" style="
        padding: 10px 20px;
        background: #28a745;
        border: none;
        color: white;
        font-size: 16px;
        cursor: pointer;
        border-radius: 5px;
    ">
        ✅ I have paid
    </button>
</a>
