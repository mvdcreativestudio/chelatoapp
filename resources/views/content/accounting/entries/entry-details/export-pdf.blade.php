<!-- resources/views/pdf/entry-details.blade.php -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Asiento Contable - #{{ $entry->id }}</title>
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
        <h4>Detalle del Asiento Contable <span class="text-muted">/ #{{ $entry->id }}</span></h4>
        <p>Fecha: {{ $entry->entry_date ? $entry->entry_date->format('d/m/Y') : 'N/A' }}</p>
        <p>Serie: {{ $entry->entryType->name ?? 'N/A' }} | Moneda: {{ $entry->currency->name }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Cuenta contable</th>
                <th>IVA</th>
                <th>Concepto</th>
                <th>Debe</th>
                <th>Haber</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entry->details as $detail)
            <tr>
                <td>{{ $detail->entryAccount->code }} - {{ $detail->entryAccount->name }}</td>
                <td>{{ $detail->iva ? $detail->iva . '%' : 'Sin IVA' }}</td>
                <td>{{ $detail->concept ?? '-' }}</td>
                <td>{{ number_format($detail->amount_debit, 2, ',', '.') }}</td>
                <td>{{ number_format($detail->amount_credit, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="3">Totales</td>
                <td>{{ number_format($entry->details->sum('amount_debit'), 2, ',', '.') }}</td>
                <td>{{ number_format($entry->details->sum('amount_credit'), 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
