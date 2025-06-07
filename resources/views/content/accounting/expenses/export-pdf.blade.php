<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exportación de Gastos</title>
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
        .table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Asegura que la tabla no se salga */
            word-wrap: break-word; /* Permite que el texto largo se ajuste */
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
    </style>
</head>
<body>
    <div class="header">
        <h4>Listado de Gastos</h4>
        <p>Fecha: {{ now()->format('d/m/Y') }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Proveedor</th>
                <th>Categoría</th>
                <th>Moneda</th>
                <th>Tienda</th>
                <th>Concepto</th>
                <th>Monto Total</th>
                <th>Total Pagado</th>
                <th>Diferencia</th>
                <th>Estado</th>
                <th>Fecha de Vencimiento</th>
                <th>Estado Temporal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->supplier->name ?? 'N/A' }}</td>
                <td>{{ $expense->expenseCategory->name ?? 'N/A' }}</td>
                <td>{{ $expense->currency->name ?? 'N/A' }}</td>
                <td>{{ $expense->store->name ?? 'N/A' }}</td>
                <td>{{ $expense->concept ?? 'Sin concepto' }}</td>
                <td>{{ number_format($expense->amount, 2, ',', '.') }}</td>
                <td>{{ number_format($expense->total_payments, 2, ',', '.') }}</td>
                <td>{{ number_format($expense->difference_amount_paid, 2, ',', '.') }}</td>

                <!-- Traducir estado del gasto -->
                <td>{{ \App\Enums\Expense\ExpenseStatusEnum::getTranslateds()[$expense->status->value] ?? 'N/A' }}</td>

                <td>{{ $expense->due_date->format('d/m/Y') }}</td>

                <!-- Traducir estado temporal -->
                <td>{{ \App\Enums\Expense\ExpenseTemporalStatusEnum::getTranslateds()[$expense->calculateTemporalStatus()] ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
