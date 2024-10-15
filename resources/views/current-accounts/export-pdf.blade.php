<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Cuentas Corrientes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-center {
            text-align: center;
        }

        .status-success {
            color: green;
            font-weight: bold;
        }

        .status-warning {
            color: orange;
            font-weight: bold;
        }

        .status-danger {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h1>Reporte de Cuentas Corrientes</h1>

    <table>
        <thead>
            <tr>
                <th>Entidad</th>
                <th>Tipo de Transacción</th>
                <th>Estado</th>
                <th>Total Débito</th>
                <th>Total Pagado</th>
                <th>Moneda</th>
                <th>Fecha de Vencimiento</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($currentAccounts as $account)
            <tr>
                <td>
                    {{ $account->client ? $account->client->name : ($account->supplier ? $account->supplier->name : 'Sin nombre') }}
                </td>
                <td>
                    {{ $account->transaction_type === 'Sale' ? 'Venta' : 'Compra' }}
                </td>
                <td class="text-center">
                    @if ($account->status->value === 'Paid')
                    <span class="status-success">Pagado</span>
                    @elseif ($account->status->value === 'Partial')
                    <span class="status-warning">Parcialmente Pago</span>
                    @else
                    <span class="status-danger">No Pagado</span>
                    @endif
                </td>
                <td>{{ $settings->currency_symbol }}{{ number_format($account->initialCredits->sum('total_debit'), 2) }}</td>
                <td>{{ $settings->currency_symbol }}{{ number_format($account->payments->sum('payment_amount'), 2) }}</td>
                <td>{{ $account->currency->code ?? 'N/A' }}</td>
                <td>{{ $account->initialCredits->first()->due_date ? \Carbon\Carbon::parse($account->initialCredits->first()->due_date)->format('d-m-Y') : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="text-align: center;">
        <p><strong>Total de Cuentas Corrientes: {{ $currentAccounts->count() }}</strong></p>
    </div>
</body>

</html>
