<!-- resources/views/pdf/entry-list.blade.php -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asientos Contables</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h4 {
            margin: 0;
            font-size: 16px;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .footer {
            text-align: right;
            font-size: 12px;
            margin-top: 20px;
        }
        .totals-row {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .balanced {
            color: green;
            font-weight: bold;
        }
        .unbalanced {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h4>Listado de Asientos Contables</h4>
        <p>Fecha: {{ now()->format('d/m/Y') }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Tipo de Asiento</th>
                <th>Moneda</th>
                <th>Concepto</th>
                <th>Total Débito</th>
                <th>Total Crédito</th>
                <th>Balanceado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $entry)
            <tr>
                <td>{{ $entry->id }}</td>
                <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
                <td>{{ $entry->entryType->name }}</td>
                <td>{{ $entry->currency->name }}</td>
                <td>{{ $entry->concept ?? 'N/A' }}</td>
                <td>{{ number_format($entry->total_debit, 2, ',', '.') }}</td>
                <td>{{ number_format($entry->total_credit, 2, ',', '.') }}</td>
                <td>
                    <span class="{{ $entry->is_balanced ? 'balanced' : 'unbalanced' }}">
                        {{ $entry->is_balanced ? 'Sí' : 'No' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
