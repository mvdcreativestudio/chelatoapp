<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Facturas</title>
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
    <h1>Listado de Facturas</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Tipo</th>
                <th>Serie</th>
                <th>Nro</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoicesData as $invoice)
            <tr>
                <td>{{ $invoice['id'] }}</td>
                <td>
                    @if(!empty($invoice['date']))
                        {{ \Carbon\Carbon::parse($invoice['date'])->format('d-m-Y H:i') }}
                    @endif
                </td>
                <td>{{ $invoice['client_name'] }}</td>
                <td>{{ $invoice['total'] }}</td>
                <td>{{ $invoice['type'] }}</td>
                <td>{{ $invoice['serie'] }}</td>
                <td>{{ $invoice['nro'] }}</td>
                <td>{{ \App\Models\CFE::getTranslatedStatus($invoice['status']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
