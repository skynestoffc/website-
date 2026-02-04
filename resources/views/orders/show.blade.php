<h1>SkyNest Cloud</h1>

<h2>Order: {{ $order->order_code }}</h2>
<p>Status: {{ $order->status }}</p>

@if($order->status === 'fulfilled')
  <h3>Data Produk Kamu</h3>
  <pre style="padding:12px;border:1px solid #ddd;border-radius:8px;white-space:pre-wrap;">
{{ $order->delivery->delivered_payload_text }}
  </pre>
@else
  <p>Kalau sudah bayar, halaman ini akan ter-update otomatis setelah webhook masuk.</p>
@endif
