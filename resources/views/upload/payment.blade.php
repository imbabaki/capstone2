h2>Review & Pay</h2>
<ul>
  <li>File: {{ $order['file_name'] }}</li>
  <li>Copies: {{ $order['copies'] }}</li>
  <li>Pages: {{ $order['pages'] ?: 'All' }}</li>
  <li>Color: {{ ucfirst($order['color']) }}</li>
  <li>Paper: {{ $order['paper_size'] }}</li>
  <li>Duplex: {{ $order['duplex'] }}</li>
  <li>Total: ₱{{ number_format($order['total'], 2) }}</li>
</ul>

<form method= "GET" action="{{ route('upload.instructions') }}">
  @csrf
  <button type="submit">✅ I have paid</button>
</form>