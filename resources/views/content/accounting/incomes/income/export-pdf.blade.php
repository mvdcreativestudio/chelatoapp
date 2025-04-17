<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas Libres</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
            padding: 5px;
        }

        th {
            background-color: #f4f4f4;
            text-align: left;
            font-weight: bold;
            white-space: nowrap;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .narrow { width: 5%; }
        .medium { width: 10%; }
        .wide { width: 15%; }

        .tax-info {
            font-size: 9px;
            color: #666;
            display: block;
        }
    </style>
</head>

<body>
    <h2 class="text-center">Reporte de Ventas Libres</h2>

    <table>
        <thead>
            <tr>
                <th class="narrow">#</th>
                <th class="medium">Fecha</th>
                <th class="wide">Entidad</th>
                <th class="medium">Método Pago</th>
                <th class="wide">Categoría</th>
                <th class="wide">Producto</th>
                <th class="narrow">Cant.</th>
                <th class="medium">Precio Unit.</th>
                <th class="medium">Subtotal</th>
                <th class="medium">Impuesto</th>
                <th class="medium">Total</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $grandSubTotal = 0;
                $grandTaxTotal = 0;
                $grandTotal = 0;
            @endphp

            @foreach($incomes as $income)
                @php
                    $subtotal = collect($income->items)->reduce(function ($carry, $item) {
                        return $carry + ($item['price'] * $item['quantity']);
                    }, 0);

                    $taxAmount = 0;
                    $taxInfo = '';
                    
                    if ($income->client && $income->client->tax_rate_id) {
                        $clientTax = \App\Models\TaxRate::find($income->client->tax_rate_id);
                        if ($clientTax && $clientTax->rate == 0) {
                            $taxInfo = "Exento ({$clientTax->name})";
                        } elseif ($income->tax_rate_id && $income->taxRate) {
                            $taxAmount = $subtotal * ($income->taxRate->rate / 100);
                            $taxInfo = "{$income->taxRate->name} ({$income->taxRate->rate}%)";
                        }
                    } elseif ($income->tax_rate_id && $income->taxRate) {
                        $taxAmount = $subtotal * ($income->taxRate->rate / 100);
                        $taxInfo = "{$income->taxRate->name} ({$income->taxRate->rate}%)";
                    }

                    $total = $income->income_amount;
                    $grandSubTotal += $subtotal;
                    $grandTaxTotal += $taxAmount;
                    $grandTotal += $total;
                @endphp

                @if(!empty($income->items))
                    @foreach($income->items as $index => $item)
                    <tr>
                        @if($index === 0)
                        <td rowspan="{{ count($income->items) }}" class="text-center">{{ $income->id }}</td>
                        <td rowspan="{{ count($income->items) }}" class="text-center">{{ $income->income_date ? $income->income_date->format('d/m/Y') : '-' }}</td>
                        <td rowspan="{{ count($income->items) }}">
                            @if($income->client)
                                {{ $income->client->name }}
                                @if($income->client->tax_rate_id)
                                    <span class="tax-info">
                                        {{ \App\Models\TaxRate::find($income->client->tax_rate_id)->name }}
                                    </span>
                                @endif
                            @elseif($income->supplier)
                                {{ $income->supplier->name }}
                            @else
                                -
                            @endif
                        </td>
                        <td rowspan="{{ count($income->items) }}">{{ $income->paymentMethod ? $income->paymentMethod->description : '-' }}</td>
                        <td rowspan="{{ count($income->items) }}">{{ $income->incomeCategory ? $income->incomeCategory->income_name : '-' }}</td>
                        @endif
                        <td>{{ $item['name'] }}</td>
                        <td class="text-center">{{ $item['quantity'] }}</td>
                        <td class="text-right">${{ number_format($item['price'], 2) }}</td>
                        @if($index === 0)
                        <td rowspan="{{ count($income->items) }}" class="text-right">${{ number_format($subtotal, 2) }}</td>
                        <td rowspan="{{ count($income->items) }}" class="text-right">
                            @if($taxInfo)
                                <span class="tax-info">{{ $taxInfo }}</span>
                                ${{ number_format($taxAmount, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td rowspan="{{ count($income->items) }}" class="text-right">${{ number_format($total, 2) }}</td>
                        @endif
                    </tr>
                    @endforeach
                @endif
            @endforeach

            <tr class="total-row">
                <td colspan="8" class="text-right">Totales:</td>
                <td class="text-right">${{ number_format($grandSubTotal, 2) }}</td>
                <td class="text-right">${{ number_format($grandTaxTotal, 2) }}</td>
                <td class="text-right">${{ number_format($grandTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="font-size: 9px; margin-top: 20px;">
        <p><strong>Nota:</strong> Los montos de impuestos se calculan según la tasa aplicable a cada cliente o la tasa general del ingreso.</p>
    </div>
</body>
</html>