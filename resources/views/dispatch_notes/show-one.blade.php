<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remito #{{ $dispatchNote->id }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            background-color: #f8f9fa;
        }

        .header,
        .footer {
            text-align: center;
            margin-bottom: 20px;
            font-family: 'Helvetica', sans-serif;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 0;
        }

        .information-title {
            background-color: #007bff;
            color: #dee2e6;
            padding: 8px;
            text-align: center;
            margin-bottom: 10px;
            border-radius: 0.25rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th {
            background-color: #e9ecef;
            padding: 12px;
            border: 1px solid #dee2e6;
        }

        .table td {
            padding: 8px;
            border: 1px solid #dee2e6;
        }

        .dispatch-note {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }

        tbody {
            font-size: 12px;
            margin: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Remito #{{ $dispatchNote->id }}</h1>
        </div>

        <div class="information">
            <div class="information-title">Información General</div>
            <table class="table">
                <tbody>
                    <tr>
                        <th>Cliente</th>
                        <td>{{ $order->client->name ?? 'No especificado' }} {{ $order->client->lastname ?? ' ' }}</td>
                        </tr>
                    <tr>
                        <th>Obra</th>
                        <td>{{ $order->construction_site ?? 'No especificada' }}</td>
                    </tr>
                    <tr>
                        <th>Fecha</th>
                        <td>{{ \Carbon\Carbon::parse($dispatchNote->date)->format('d/m/Y') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="dispatch-note">
            <div class="information-title">Detalles del Remito</div>
            <table class="table">
                <tbody>
                    <tr>
                        <th>Producto</th>
                        <td>{{ $dispatchNote->product->name }}</td>
                    </tr>
                    <tr>
                        <th>Cantidad</th>
                        <td>{{ $dispatchNote->quantity }} metros</td>
                    </tr>
                    <tr>
                        <th>Tipo de Bombeo</th>
                        <td>
                            @switch($dispatchNote->bombing_type)
                            @case('Drag')
                            Arrastre
                            @break
                            @case('Throw')
                            Lanza
                            @break
                            @default
                            {{ $dispatchNote->bombing_type }}
                            @endswitch
                        </td>
                    </tr>
                    <tr>
                        <th>Método de Entrega</th>
                        <td>
                            @switch($dispatchNote->delivery_method)
                            @case('Dumped')
                            Volcado
                            @break
                            @case('Pumped')
                            Lanzamiento
                            @break
                            @default
                            {{ $dispatchNote->delivery_method }}
                            @endswitch
                        </td>
                    </tr>
                </tbody>
            </table>

            @if($dispatchNote->noteDelivery && count($dispatchNote->noteDelivery) > 0)
            <div class="information-title">Información de Entrega</div>
            <table class="table">
                <tbody>
                    <tr>
                        <th>Vehículo</th>
                        <td>{{ $dispatchNote->noteDelivery->first()->vehicle->number ?? 'No especificado' }} - {{ $dispatchNote->noteDelivery->first()->vehicle->plate ?? 'No especificada' }}</td>
                    </tr>
                    <tr>
                        <th>Conductor</th>
                        <td>{{ $dispatchNote->noteDelivery->first()->driver->name ?? 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <th>Planta</th>
                        <td>{{ $dispatchNote->noteDelivery->first()->store->name ?? 'No especificada' }}</td>
                    </tr>
                    <tr>
                        <th>Salida del Sitio</th>
                        <td>{{ $dispatchNote->noteDelivery->first()->departuring ? \Carbon\Carbon::parse($dispatchNote->noteDelivery->first()->departuring)->format('d-m-y H:i') : 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <th>Llegada</th>
                        <td>{{ $dispatchNote->noteDelivery->first()->arriving ? \Carbon\Carbon::parse($dispatchNote->noteDelivery->first()->arriving)->format('d-m-y H:i') : 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <th>Inicio de Descarga</th>
                        <td>{{ $dispatchNote->noteDelivery->first()->unload_starting ? \Carbon\Carbon::parse($dispatchNote->noteDelivery->first()->unload_starting)->format('d-m-y H:i') : 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <th>Fin de Descarga</th>
                        <td>{{ $dispatchNote->noteDelivery->first()->unload_finishing ? \Carbon\Carbon::parse($dispatchNote->noteDelivery->first()->unload_finishing)->format('d-m-y H:i') : 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <th>Salida del Sitio</th>
                        <td>{{ $dispatchNote->noteDelivery->first()->departure_from_site ? \Carbon\Carbon::parse($dispatchNote->noteDelivery->first()->departure_from_site)->format('d-m-y H:i') : 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <th>Regreso a la Planta</th>
                        <td>{{ $dispatchNote->noteDelivery->first()->return_to_plant ? \Carbon\Carbon::parse($dispatchNote->noteDelivery->first()->return_to_plant)->format('d-m-y H:i') : 'No especificado' }}</td>
                    </tr>
                </tbody>
            </table>
            @else
            <div class="information-title">Información de Entrega</div>
            <table class="table">
                <tbody>
                    <tr>
                        <th>Vehículo</th>
                        <td>
                            <div style="border: 1px solid #f8f9fa; width: 250px; height: 20px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <th>Conductor</th>
                        <td>
                            <div style="border: 1px solid #f8f9fa; width: 250px; height: 20px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <th>Planta</th>
                        <td>
                            <div style="border: 1px solid #f8f9fa; width: 250px; height: 20px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <th>Salida del Sitio</th>
                        <td>
                            <div style="border: 1px solid #f8f9fa; width: 250px; height: 20px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <th>Llegada</th>
                        <td>
                            <div style="border: 1px solid #f8f9fa; width: 250px; height: 20px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <th>Inicio de Descarga</th>
                        <td>
                            <div style="border: 1px solid #f8f9fa; width: 250px; height: 20px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <th>Fin de Descarga</th>
                        <td>
                            <div style="border: 1px solid #f8f9fa; width: 250px; height: 20px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <th>Salida del Sitio</th>
                        <td>
                            <div style="border: 1px solid #f8f9fa; width: 250px; height: 20px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <th>Regreso a la Planta</th>
                        <td>
                            <div style="border: 1px solid #f8f9fa; width: 250px; height: 20px;"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
            @endif
        </div>

        <div class="footer">
            <p>Este es un documento generado automáticamente el {{ now()->format('d/m/Y H:i:s') }}.<br>
                Si tienes alguna pregunta, comunícate con nuestro equipo de soporte.</p>
        </div>
    </div>
</body>

</html>