<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Orden Interna #{{ $order->id }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
    h1 { margin-bottom: 20px; }
  </style>
</head>
<body>
  <h1>Orden Interna #{{ $order->id }}</h1>
  <p><strong>Desde:</strong> {{ $order->fromStore->name }}</p>
  <p><strong>Hacia:</strong> {{ $order->toStore->name }}</p>
  <p><strong>Estado:</strong> {{ $order->statusLabel }}</p>
  <p><strong>Fecha de Entrega:</strong> {{ $order->delivery_date?->format('d/m/Y') ?? '-' }}</p>

  <h3>Productos</h3>
  <table>
    <thead>
      <tr>
        <th>Producto</th>
        <th>Solicitado</th>
        <th>A entregar</th>
      </tr>
    </thead>
    <tbody>
      @foreach($order->items as $item)
        <tr>
          <td>{{ $item->product->name }}</td>
          <td>{{ $item->quantity }}</td>
          <td>{{ $item->deliver_quantity ?? '-' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
