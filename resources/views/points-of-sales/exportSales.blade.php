<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #34495e;
            color: #ecf0f1;
            text-transform: uppercase;
            font-size: 12px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        td {
            font-size: 12px;
            color: #2c3e50;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #95a5a6;
        }
    </style>
</head>
<body>
    <h1>Reporte de Ventas del log de caja {{ $id }}</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Ventas Efectivo</th>
                <th>Ventas POS</th>
                <th>Descuento</th>
                <th>ID Cliente</th>
                <th>Total</th>
                <th>Notas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->date }}</td>
                    <td>{{ $sale->hour }}</td>
                    <td>{{ $sale->cash_sales }}</td>
                    <td>{{ $sale->pos_sales }}</td>
                    <td>{{ $sale->discount }}</td>
                    <td>{{ $sale->client_id }}</td>
                    <td>{{ $sale->total }}</td>
                    <td>{{ $sale->notes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado automáticamente &mdash; {{ \Carbon\Carbon::now()->format('d/m/Y h:i:s a') }}</p>
    </div>
</body>
</html>
