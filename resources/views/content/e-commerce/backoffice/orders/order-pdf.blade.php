<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del pedido</title>
    <style>
        body {
            font-family: 'Lato', sans-serif;
            background-color: #F5F5F5;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 650px;
            margin: 0 auto;
            background-color: #FFFFFF;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        p {
          font-size: 12px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
        }
        .header img {
            max-width: 200px;
            height: auto;
        }
        .title {
            text-align: center;
            color: #333;
        }
        .order-meta, .order-summary, .shipping-details {
            margin-bottom: 20px;
        }
        .order-meta p, .order-summary p, .shipping-details p {
            margin: 5px 0;
        }
        .order-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-details th, .order-details td {
            padding: 10px;
            border: 1px solid #D6E7F0;
            text-align: left;
        }
        .order-details th {
            background-color: #D6E7F0;
            color: #333;
        }
        .order-summary p {
            text-align: right;
        }
        .order-summary p span {
            font-weight: bold;
        }
        .order-meta, .shipping-details {
            background-color: #F9F9F9;
            padding: 10px;
            border: 1px solid #EEE;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
          @if($companySettings->logo_black != null)
            <img src="{{ $companySettings->logo_black }}" alt="{{ $companySettings->name }}">
          @endif
        </div>
        <h1 class="title">Nuevo pedido - #{{ $order->id }}</h1>
        <div class="order-meta">
            <p><strong>Fecha del pedido:</strong> {{ $order->date }}</p>
            <p><strong>Hora del pedido:</strong> {{ $order->time }}</p>
            <p><strong>Empresa:</strong> {{ $order->store->name }}</p>
            <p><strong>Método de pago:</strong>
              @if($order->payment_method === 'card')
              <span class="badge bg-label-primary me-2 ms-2">MercadoPago</span>
              @elseif($order->payment_method === 'efectivo')
                <span class="me-2 ms-2">Efectivo</span>
              @elseif($order->payment_method === 'debit')
                <span class="me-2 ms-2">Débito</span>
              @elseif($order->payment_method === 'credit')
                <span class="me-2 ms-2">Crédito</span>
              @elseif($order->payment_method === 'internalCredit')
                <span class="me-2 ms-2">Crédito Interno</span>
              @endif
            </p>
            {{-- <p><strong>Método de envío:</strong>
              @if($order->shipping_method === 'peya')
                  Pedidos Ya
              @elseif($order->shipping_method === 'pickup')
                  Retira en el local
              @endif
          </p> --}}
            <p><strong>Estado de pago:</strong>
                @if($order->payment_status === 'paid')
                    Pagado
                @elseif($order->payment_status === 'pending' && $order->payment_method === 'efectivo' && $order->shipping_method === 'peya')
                Paga al recibir
                @elseif($order->payment_status === 'pending')
                    Pago pendiente

                @elseif($order->payment_status === 'failed')
                    Pago fallido
                @endif
            </p>
            <p><strong>Estado de envío:</strong>
                @if($order->shipping_status === 'pending')
                    No enviado
                @elseif($order->shipping_status === 'shipped')
                    Enviado
                @elseif($order->shipping_status === 'delivered')
                    Entregado
                @endif
            </p>
        </div>
        <div class="order-details">
            <h2>Detalles del pedido</h2>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>{{ $product['name'] }}</td>
                            <td>{{ $product['quantity'] }}</td>
                            <td>${{ $product['price'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="order-summary">
            <p><span>Subtotal:</span> ${{ $order->subtotal }}</p>
            <p><span>Envío:</span> ${{ $order->shipping }}</p>
            <p><span>Total:</span> ${{ $order->total }}</p>
        </div>
        <div class="shipping-details">
            <h2>Datos del cliente</h2>
            <p><strong>Nombre:</strong> {{ $order->client->name }} {{ $order->client->lastname }}</p>
            <p><strong>Dirección:</strong> {{ $order->client->address }}</p>
            <p><strong>Teléfono:</strong> {{ $order->client->phone }}</p>
        </div>
    </div>
</body>
</html>
