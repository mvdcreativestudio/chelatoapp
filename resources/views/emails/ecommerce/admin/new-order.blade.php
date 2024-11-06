<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Orden</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #dddddd;
        }
        .header img {
            max-width: 150px;
        }
        .order-details {
            padding: 20px 0;
            text-align: center;
        }
        .order-details h2 {
            margin: 0 0 20px 0;
            font-size: 24px;
            color: #333333;
        }
        .order-details p {
            margin: 5px 0;
            color: #666666;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .product-table th, .product-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dddddd;
        }
        .product-table th {
            background-color: #f4f4f4;
        }
        .totals {
            text-align: right;
        }
        .totals p {
            margin: 5px 0;
            font-size: 16px;
            color: #333333;
        }
        .totals .total {
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
        }
        .footer {
            padding-top: 20px;
            border-top: 1px solid #dddddd;
            text-align: center;
            color: #888888;
        }
        .footer p {
            margin: 5px 0;
            font-size: 14px;
        }
        .footer a {
            color: #fc7318;
            text-decoration: none;
        }

        .subheader {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #dddddd;
        }

        .client-details {
            padding: 20px 0;
            text-align: center;
        }

        .client-details h2 {
            margin: 0 0 20px 0;
            font-size: 24px;
            color: #333333;
        }
        .client-details p {
            margin: 5px 0;
            color: #666666;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #181818;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
        }
        .buttons {
            text-align: center;
            margin-bottom: 20px;
        }

    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="https://657041b07b.imgdist.com/pub/bfra/j2m7xe6w/nvl/6a4/y4a/dali-Black-Logo.png" alt="Logo">
        </div>
        <div class="subheader">
          <h4 style="margin-bottom: 8px;">¡Has recibido un nuevo pedido!</h4><br><h6 style="margin-top: 0px;">Local: <strong>{{ $variables['store_name'] }}</strong></h6>
        </div>
        <div class="order-details">
            <h2>Detalles del pedido</h2>
            <p><strong>Número de Pedido:</strong> #{{ $variables['order_id'] }}</p>
            <p><strong>Fecha de Pedido:</strong> {{ $variables['order_date'] }}</p>
            <p><strong>Método de Pago:</strong> {{ $variables['order_payment_method'] }}</p>
            <p><strong>Método de Envío:</strong> {{ $variables['order_shipping_method'] }}</p>
        </div>
        <div class="client-details">
          <h2>Detalles del cliente</h2>
          <p>{{ $variables['client_name'] }} {{ $variables['client_lastname'] }}</p>
          <p>{{ $variables['client_email'] }}</p>
          <p>{{ $variables['client_phone'] }}</p>
          <p>{{ $variables['client_address'] }}, {{ $variables['client_city'] }}, {{ $variables['client_state'] }}</p>
        </div>
        <div class="buttons">
            <a href="" class="btn">Ver Pedido</a>
        </div>
        <table class="product-table">
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                {!! $variables['order_items'] !!}
            </tbody>
        </table>
        <div class="totals">
            <p>Subtotal: <strong>${{ $variables['order_subtotal'] }}</strong></p>
            <p>Envío: <strong>${{ $variables['order_shipping'] }}</strong></p>
            <p>Descuentos: <strong>-${{ $variables['coupon_amount'] }}</strong></p>
            <p class="total"><strong>Total:</strong> ${{ $variables['order_total'] }}</p>
        </div>
        <div class="footer">
            <p>Si tienes algún problema con tu pedido, por favor contacta a <a href="mailto:soporte@mvdstudio.com.uy">soporte@mvdstudio.com.uy</a></p>
            <p>&copy; 2024 MVD Studio. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
