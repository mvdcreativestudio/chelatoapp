<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ingresos</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f4f4f4;
            text-align: left;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>

    <h2 class="text-center">Reporte de Ingresos</h2>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Fecha</th>
                <th>Entidad</th>
                <th>Descripción</th>
                <th>Método de Pago</th>
                <th>Importe</th>
                <th>Categoría</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incomes as $income)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $income->income_date ? $income->income_date->format('d/m/Y') : 'N/A' }}</td>
                    <td>
                        @if($income->client)
                            Cliente: {{ $income->client->name }}
                        @elseif($income->supplier)
                            Proveedor: {{ $income->supplier->name }}
                        @else
                            Ninguno
                        @endif
                    </td>
                    <td>{{ $income->income_description ?? 'N/A' }}</td>
                    <td>{{ $income->paymentMethod ? $income->paymentMethod->description : 'N/A' }}</td>
                    <td class="text-right">${{ number_format($income->income_amount, 2) }}</td>
                    <td>{{ $income->incomeCategory ? $income->incomeCategory->income_name : 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
