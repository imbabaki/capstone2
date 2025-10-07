<h2>Printing Instructions</h2>
<p>Please insert <b>{{ $order['paper_size'] }}</b> paper into the tray.</p>
<ul>
  <li>File: {{ $order['file_name'] }}</li>
  <li>Copies: {{ $order['copies'] }}</li>
  <li>Pages: {{ $order['pages'] ?: 'All' }}</li>
  <li>Color: {{ ucfirst($order['color']) }}</li>
  <li>Paper: {{ $order['paper_size'] }}</li>
  <li>Duplex: {{ $order['duplex'] }}</li>
  <li>Total: ₱{{ number_format($order['total'], 2) }}</li>
</ul>
<form method="POST" action="{{ route('usb.print') }}">
  @csrf
  <button type="submit">🖨️ Start Printing</button>
</form>+