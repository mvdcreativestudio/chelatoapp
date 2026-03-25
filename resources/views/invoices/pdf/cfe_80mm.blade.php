<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket - {{ $cfe->serie }}{{ $cfe->nro }}</title>
    <style>
        html, body {
            width: 204pt;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: white;
            page-break-after: avoid !important;
            page-break-before: avoid !important;
            page-break-inside: avoid !important;
            break-after: avoid !important;
            break-before: avoid !important;
            break-inside: avoid !important;
            overflow: visible !important;
        }

        .container {
            padding: 10px;
            page-break-after: avoid !important;
            page-break-before: avoid !important;
            page-break-inside: avoid !important;
            break-after: avoid !important;
            break-before: avoid !important;
            break-inside: avoid !important;
        }
        .header, .footer {
            text-align: center;
        }
        .footer p {
            font-size: 11px;
        }
        .header p {
            margin: 0;
            padding: 0;
            font-size: 14px;
        }
        .info-container {
            border: 1px solid #000;
            margin-bottom: 10px;
        }
        .info {
            text-align: center;
        }
        .info p {
            margin: 0;
            font-size: 15px;
        }
        .info h4 {
            margin: 0;
            padding: 4px;
            font-weight: bold;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table thead th {
            font-size: 13px;
            border-bottom: 1px solid #000;
            text-align: left;
        }
        .table td {
            font-size: 14px;
            text-align: left;
            padding: 2px;
        }
        .table td:first-child {
            text-align: left;
            width: 50%;
        }
        .table td:nth-child(2) { width: 10%; }
        .table td:nth-child(3) { width: 18%; }
        .table td:nth-child(4) { width: 22%; }
        .totals {
            text-align: right;
            font-size: 13px;
            margin-top: 5px;
        }
        .totals p {
            margin: 0;
            margin-top: 5px;
            padding: 1px 0;
        }

        img {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        table, tr, td, th, tbody, thead, tfoot {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    </style>
</head>

@php
    // Determinar tipo de CFE por código numérico
    $tipoCFEMap = [
        101 => 'e-Ticket',
        102 => 'NC e-Ticket',
        103 => 'ND e-Ticket',
        111 => 'e-Factura',
        112 => 'NC e-Factura',
        113 => 'ND e-Factura',
        121 => 'e-Factura Exp.',
        122 => 'NC e-Fact. Exp.',
        123 => 'ND e-Fact. Exp.',
    ];
    $cfeTypeInt = (int) $cfe->type;
    $esNotaCredito = in_array($cfeTypeInt, [102, 112, 122]);
    $esNotaDebito  = in_array($cfeTypeInt, [103, 113, 123]);
    $tipoLabel = $tipoCFEMap[$cfeTypeInt] ?? 'CFE ' . $cfe->type;

    // Productos de la orden (almacenados como JSON en orders.products)
    $products = [];
    if ($order && $order->products) {
        $products = is_string($order->products) ? json_decode($order->products, true) : $order->products;
        if (!is_array($products)) {
            $products = [];
        }
    }

    // Parsear caeRange
    $caeRange = $cfe->caeRange;
    $caeFrom = $caeRange['from'] ?? ($caeRange[0] ?? '');
    $caeTo   = $caeRange['to'] ?? ($caeRange[1] ?? '');
@endphp

<body>
<div class="container">
    {{-- HEADER: Logo + Datos Emisor --}}
    <div class="header">
        @if(isset($logo))
        <img src="{{ $logo }}" alt="Logo" style="max-height: 80px; max-width: 80%; margin-bottom: 10px;">
        @endif

        <h5>{{ $store->business_name ?? $store->name ?? '' }}</h5>
        <p>{{ $store->address ?? '' }}</p>
        @if($store->phone)
            <p>Tel: {{ $store->phone }}</p>
        @endif
        <p>RUC: {{ $store->rut ?? '' }}</p>

        <table style="width: 100%; margin-top: 10px; font-size: 14px; text-align: center;">
            <tr>
                <td style="width: 50%; padding: 4px;">
                    <div><strong>{{ $tipoLabel }}</strong></div>
                    <div>{{ $cfe->serie }}{{ $cfe->nro }}</div>
                </td>
                <td style="width: 50%; padding: 4px;">
                    <div><strong>{{ $order?->payment_method === 'cash' ? 'Contado' : 'Crédito' }}</strong></div>
                    <div>{{ $cfe->currency ?? 'UYU' }}</div>
                </td>
            </tr>
        </table>

        @if($cfe->emitionDate)
        <p style="margin: 0;">{{ $cfe->emitionDate->format('d/m/Y') }}</p>
        <p style="margin: 0;">{{ $cfe->emitionDate->format('H:i:s') }}</p>
        @endif
    </div>

    <hr>

    {{-- DATOS DEL CLIENTE --}}
    <div class="info-container">
        <div class="info">
            <h4>DATOS DEL CLIENTE</h4>
            @if($order && $order->client)
                @php $client = $order->client; @endphp
                <p>{{ $client->type === 'company' ? ($client->company_name ?? $client->name) : ($client->name . ' ' . ($client->lastname ?? '')) }}</p>
                @if($client->type === 'company' && $client->rut)
                    <p>RUT: {{ $client->rut }}</p>
                @elseif($client->ci)
                    <p>CI: {{ $client->ci }}</p>
                @elseif($client->document)
                    <p>Doc: {{ $client->document }}</p>
                @endif
                @if($client->address)
                    <p>{{ $client->address }}</p>
                @endif
                @if($client->city)
                    <p>{{ $client->city }}</p>
                @endif
            @else
                <p>Consumidor Final</p>
            @endif
        </div>
    </div>

    <hr>

    {{-- DETALLE DE PRODUCTOS --}}
    <table class="table">
        <thead>
          <tr>
            <th>Producto</th>
            <th>Cant</th>
            <th>Unitario</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          @if(count($products) > 0)
            @foreach($products as $product)
            <tr>
                <td>{{ $product['name'] ?? 'Producto' }}</td>
                <td>{{ $product['quantity'] ?? 1 }}</td>
                <td>{{ number_format($product['price'] ?? 0, 2) }}</td>
                <td>{{ number_format(($product['price'] ?? 0) * ($product['quantity'] ?? 1), 2) }}</td>
            </tr>
            @endforeach
          @else
            <tr>
                <td colspan="4" style="text-align: center; font-size: 12px;">Sin detalle de productos</td>
            </tr>
          @endif
        </tbody>
    </table>

    <hr>

    {{-- TOTALES --}}
    <div class="totals">
        @if($order && $order->subtotal)
            <p>Subtotal: {{ $cfe->currency ?? 'UYU' }} {{ number_format($order->subtotal, 2) }}</p>
        @endif
        @if($order && $order->discount > 0)
            <p>Descuento: -{{ $cfe->currency ?? 'UYU' }} {{ number_format($order->discount, 2) }}</p>
        @endif
        @if($order && $order->coupon_amount > 0)
            <p>Cupón: -{{ $cfe->currency ?? 'UYU' }} {{ number_format($order->coupon_amount, 2) }}</p>
        @endif
        <p><strong>TOTAL: {{ $cfe->currency ?? 'UYU' }} {{ number_format($cfe->total ?? $order->total ?? 0, 2) }}</strong></p>
    </div>

    {{-- REFERENCIA A CFE ORIGINAL (si es nota de crédito/débito) --}}
    @if(($esNotaCredito || $esNotaDebito) && $cfe->mainCfe)
    <div style="margin-top: 8px; padding: 6px; background: #eee; text-align: center; font-size: 12px; border: 1px solid #000;">
        Referencia: {{ $tipoCFEMap[(int)$cfe->mainCfe->type] ?? 'CFE '.$cfe->mainCfe->type }} {{ $cfe->mainCfe->serie }} {{ $cfe->mainCfe->nro }}
        @if($cfe->reason)
            <br>Motivo: {{ $cfe->reason }}
        @endif
    </div>
    @endif

    <hr>

    {{-- PIE: DATOS DE FACTURA ELECTRÓNICA --}}
    <div class="footer">
        <div style="border: 1px solid #000; padding: 8px; margin-top: 10px; text-align: center;">
            <p style="background-color: #eee; margin: 0; padding: 5px; font-weight: bold; text-align: center;">
                DATOS DE FACTURA ELECTRÓNICA
            </p>

            @if($cfe->qrUrl)
              <div style="text-align: center; margin-top: 15px;">
                <img
                  src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($cfe->qrUrl) }}"
                  alt="QR de verificación"
                  style="margin-top: 5px;"
                >
              </div>
            @endif

            <p style="margin: 2px 0;">Puede verificar el comprobante en:</p>
            <p style="font-size: 10px; word-wrap: break-word;">
                https://www.efactura.dgi.gub.uy/principal/verificacioncfe
            </p>
            <p style="margin: 2px 0;">IVA al día</p>

            <p style="margin: 2px 0;">
                Serie: {{ $cfe->serie ?? '' }} |
                Número: {{ $cfe->nro ?? '' }}
            </p>

            @if($cfe->caeNumber)
            <p style="margin: 2px 0;">Nro. CAE: {{ $cfe->caeNumber }}</p>
            @endif

            @if($caeFrom || $caeTo)
            <p style="margin: 2px 0;">
                Rango: Serie {{ $cfe->serie ?? '' }} del Nº {{ $caeFrom }} al {{ $caeTo }}
            </p>
            @endif

            @if($cfe->securityCode)
            <p style="margin: 2px 0;">Código de seguridad: {{ $cfe->securityCode }}</p>
            @endif

            @if($cfe->caeExpirationDate)
            <p style="margin: 2px 0;">
                Fecha de vencimiento CAE: {{ $cfe->caeExpirationDate->format('d/m/Y') }}
            </p>
            @endif
        </div>

        <div>
          <p>Venta generada a través de <strong>chelato.com.uy</strong></p>
        </div>
    </div>
</div>
<div style="height: 1px; width: 100%;"></div>

</body>
</html>
