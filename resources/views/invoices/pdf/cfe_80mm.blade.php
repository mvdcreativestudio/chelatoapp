<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura Electrónica</title>
    <style>



        body {
            font-family: Arial, sans-serif;
            width: 80mm;
            margin: auto;
        }
        .container {
            padding: 10px;
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
            width: 46%;
        }
        .table td:nth-child(2) { width: 9%; }
        .table td:nth-child(3) { width: 15%; }
        .table td:nth-child(4) { width: 9%; }
        .table td:nth-child(5) { width: 21%; }
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

    </style>
</head>

@php
    $tipo = isset($cfe['cfe']['eTck']) ? 'eTck' : 'eFact';
    $datos = $cfe['cfe'][$tipo];
    $tipoCFE = $datos['Encabezado']['IdDoc']['TipoCFE'] ?? null;
    $esNotaCredito = $tipoCFE === '112';
    $esNotaDebito = $tipoCFE === '113';
    $referencias = $datos['Referencia']['Referencia'] ?? [];
    $ref = $referencias[0] ?? null;
    $tipoCFEMap = ['111' => 'eFactura', '101' => 'eTicket', '112' => 'Nota de Crédito', '113' => 'Nota de Débito'];
@endphp

<body>
<div class="container">
    <div class="header">
        @if(isset($logo))
        <img src="{{ $logo }}" alt="Logo Empresa" style="max-height: 80px; margin-bottom: 10px;">
        @endif

        <h3>{{ $datos['Encabezado']['Emisor']['RznSoc'] ?? '' }}</h3>
        <p>{{ $datos['Encabezado']['Emisor']['DomFiscal'] ?? '' }}</p>
        <p>Tel: {{ $datos['Encabezado']['Emisor']['Telefono'] ?? '' }}</p>
        <p>RUC: {{ $datos['Encabezado']['Emisor']['RUCEmisor'] ?? '' }}</p>

        <table style="width: 100%; margin-top: 10px; font-size: 14px; text-align: center;">
            <tr>
                <td style="width: 50%; padding: 4px;">
                    <div><strong>
                        @if($esNotaCredito)
                            Nota de Crédito
                        @elseif($esNotaDebito)
                            Nota de Débito
                        @else
                            {{ $tipo === 'eTck' ? 'e-Ticket' : 'e-Factura' }}
                        @endif
                    </strong></div>
                    <div>{{ $datos['Encabezado']['IdDoc']['Serie'] ?? '' }}{{ $cfe['nro'] ?? '' }}</div>
                </td>
                <td style="width: 50%; padding: 4px;">
                    <div><strong>Contado</strong></div>
                    <div>{{ $datos['Encabezado']['Totales']['TpoMoneda'] ?? 'UYU' }}</div>
                </td>
            </tr>
        </table>

        <p style="margin: 0;">
            {{ \Carbon\Carbon::parse($cfe['cfeDate'])->format('d/m/Y') }}
        </p>
        <p style="margin: 0;">
            {{ \Carbon\Carbon::parse($cfe['cfeDate'])->format('H:i:s') }}
        </p>
    </div>

    <hr>
    <div class="info-container">
        <div class="info">
            <h4>DATOS DEL CLIENTE</h4>
            @if(isset($datos['Encabezado']['Receptor']))
                <p>{{ $datos['Encabezado']['Receptor']['RznSocRecep'] ?? '' }}</p>
                <p>{{ $datos['Encabezado']['Receptor']['DocRecep'] ?? '' }}</p>
                <p>{{ $datos['Encabezado']['Receptor']['DirRecep'] ?? '' }}</p>
                <p>{{ $datos['Encabezado']['Receptor']['CiudadRecep'] ?? '' }}</p>
            @else
                <p>{{ $datos['Encabezado']['IdDoc']['RUCRec'] ?? 'Consumidor Final' }}</p>
            @endif
        </div>
    </div>

    <hr>
    <table class="table">
        <thead>
        <tr>
            <th>Producto</th>
            <th>Cant</th>
            <th>Unitario</th>
            <th>IVA</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($datos['Detalle']['Item'] as $item)
            @php
                $descripcion = $item['NomItem'] ?? '';
                if ($ref) {
                    $tipoRefNombre = $tipoCFEMap[$ref['TpoDocRef']] ?? 'CFE';
                    $descripcion .= " - Afecta $tipoRefNombre (Código {$ref['TpoDocRef']}) - {$ref['Serie']}{$ref['NroCFERef']} - línea {$ref['NroLinRef']}";
                }

                $iva = match ($item['IndFact'] ?? null) {
                    '1' => '0',
                    '2' => $datos['Encabezado']['Totales']['IVATasaMin'] ?? '10',
                    '3' => $datos['Encabezado']['Totales']['IVATasaBasica'] ?? '22',
                    default => '0'
                };
            @endphp
            <tr>
                <td>{{ $descripcion }}</td>
                <td>{{ number_format($item['Cantidad'] ?? 0, 2) }}</td>
                <td>{{ number_format($item['PrecioUnitario'] ?? 0, 2) }}</td>
                <td>{{ $iva }}</td>
                <td>{{ number_format($item['MontoItem'] ?? 0, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <hr>
    <div class="totals">
        @if(!$esNotaCredito && !$esNotaDebito)
            <p>Subtotal: UYU {{ number_format(
                $datos['Encabezado']['Totales']['MntTotal']
                - ($datos['Encabezado']['Totales']['MntIVATasaBasica'] ?? 0)
                - ($datos['Encabezado']['Totales']['MntIVATasaMin'] ?? 0), 2) }}</p>
        @endif

        @if(($datos['Encabezado']['Totales']['MntNetoIVATasaBasica'] ?? 0) > 0)
            <p>Gravado IVA Tasa Básica: UYU {{ number_format($datos['Encabezado']['Totales']['MntNetoIVATasaBasica'], 2) }}</p>
            <p>IVA Tasa Básica: UYU {{ number_format($datos['Encabezado']['Totales']['MntIVATasaBasica'], 2) }}</p>
        @endif
        @if(($datos['Encabezado']['Totales']['MntNetoIvaTasaMin'] ?? 0) > 0)
            <p>Gravado IVA Tasa Mínima: UYU {{ number_format($datos['Encabezado']['Totales']['MntNetoIvaTasaMin'], 2) }}</p>
            <p>IVA Tasa Mínima: UYU {{ number_format($datos['Encabezado']['Totales']['MntIVATasaMin'], 2) }}</p>
        @endif
        <p><strong>TOTAL: UYU {{ number_format($datos['Encabezado']['Totales']['MntPagar'], 2) }}</strong></p>
    </div>

    <hr>
    <div class="footer">
        <div style="border: 1px solid #000; padding: 8px; margin-top: 10px; text-align: center;">
            <p style="background-color: #eee; margin: 0; padding: 5px; font-weight: bold; text-align: center;">
                DATOS DE FACTURA ELECTRÓNICA
            </p>
            @if(isset($qrUrl))
              <div style="text-align: center; margin-top: 15px;">
                <img
                  src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($qrUrl) }}"
                  alt="QR de verificación"
                  style="margin-top: 5px;"
                >
              </div>
            @endif

            <p style="margin: 2px 0;">Res. Nro.: 001/2018</p>
            <p style="margin: 2px 0;">Puede verificar el comprobante en:</p>
            <p style="font-size: 11px; word-wrap: break-word;">
                https://www.efactura.dgi.gub.uy/principal/verificacioncfe
            </p>
            <p style="margin: 2px 0;">IVA al día</p>

            <p style="margin: 2px 0;">
                Serie: {{ $datos['Encabezado']['IdDoc']['Serie'] ?? '' }} |
                Número: {{ $datos['Encabezado']['IdDoc']['Nro'] ?? '' }}
            </p>

            <p style="margin: 2px 0;">Nro. CAE: {{ $datos['CAEData']['CAE_ID'] ?? '' }}</p>
            <p style="margin: 2px 0;">
                Rango: Serie {{ $datos['Encabezado']['IdDoc']['Serie'] ?? '' }} del Nº {{ $datos['CAEData']['DNro'] ?? '' }}
                al {{ $datos['CAEData']['HNro'] ?? '' }}
            </p>
            <p style="margin: 2px 0;">Código de seguridad: {{ substr(md5($cfe['_id']), 0, 6) }}</p>
            <p style="margin: 2px 0;">
                Fecha de vencimiento CAE:
                {{ \Carbon\Carbon::parse($datos['CAEData']['FecVenc'])->format('d/m/Y') }}
            </p>
        </div>

        <div style="border: 1px solid #000; margin-top: 10px;">
            <p style="background-color: #eee; margin: 0; padding: 5px; font-weight: bold; text-align: center;">
                ADENDA
            </p>
            <p style="margin: 0; padding: 5px; font-size: 12px; text-align: center;">
                {{ $cfe['adenda'] ?? '—' }}
            </p>
        </div>

        <div>
          <p>Venta generada a través de <strong>sumeria.com.uy</strong></p>
        </div>
    </div>
</div>
</body>
</html>

<script>
  window.onload = function () {
      window.print();
  }
</script>

