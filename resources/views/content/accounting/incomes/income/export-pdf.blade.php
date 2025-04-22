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

        .currency-symbol {
            font-size: 9px;
            color: #666;
            margin-right: 2px;
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
                <th class="medium">Moneda</th>
                <th class="medium">Importe sin Imp.</th>
                <th class="medium">Impuesto</th>
                <th class="medium">Total Original</th>
                <th class="medium">Total Pesos</th>
                <th class="medium">Total Dólares</th>
            </tr>
                </thead>
                <tbody>
                    @php 
                        $grandSubTotal = 0;
                        $grandTaxTotal = 0;
                        $grandTotal = 0;
                        $grandTotalUYU = 0;
                        $grandTotalUSD = 0;
                    @endphp

                    @foreach($incomes as $income)
                        @php
                            // Cálculos de impuestos existentes
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

                            // Cálculos de moneda
                            $total = $income->income_amount;
                            $rate = $income->currency_rate;
                            
                            if ($income->currency === 'Dólar') {
                                $usdAmount = $total;
                                if (!$rate || $rate == 0) {
                                    $rate = app(\App\Repositories\IncomeRepository::class)->getHistoricalDollarRate($income->income_date);
                                }
                                $uyuAmount = $rate > 0 ? $total * $rate : 0;
                            } else {
                                $uyuAmount = $total;
                                if (!$rate || $rate == 0) {
                                    $rate = app(\App\Repositories\IncomeRepository::class)->getHistoricalDollarRate($income->income_date);
                                }
                                $usdAmount = $rate > 0 ? $total / $rate : 0;
                            }

                            // Acumuladores
                            $grandSubTotal += $subtotal;
                            $grandTaxTotal += $taxAmount;
                            $grandTotal += $total;
                            $grandTotalUYU += $uyuAmount;
                            $grandTotalUSD += $usdAmount;
                        @endphp

                        <tr>
                        <td class="text-center">{{ $income->id }}</td>
                        <td class="text-center">{{ $income->income_date ? $income->income_date->format('d/m/Y') : '-' }}</td>
                        <td>{{ $income->client ? $income->client->name : ($income->supplier ? $income->supplier->name : 'Ninguno') }}</td>
                        <td>{{ $income->paymentMethod ? $income->paymentMethod->description : '-' }}</td>
                        <td>{{ $income->incomeCategory ? $income->incomeCategory->income_name : '-' }}</td>
                        <td>{{ $income->currency ?: 'Sin Moneda' }}</td>
                        <td class="text-right">
                            <span class="currency-symbol">{{ $income->currency === 'Dólar' ? 'U$D' : '$' }}</span>
                            {{ number_format($subtotal, 2) }}
                        </td>
                        <td class="text-right">
                            @if($taxInfo)
                                <span class="tax-info">{{ $taxInfo }}</span>
                                <span class="currency-symbol">$</span>{{ number_format($taxAmount, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right">
                            <span class="currency-symbol">{{ $income->currency === 'Dólar' ? 'U$D' : '$' }}</span>
                            {{ number_format($total, 2) }}
                        </td>
                        <td class="text-right">
                            <span class="currency-symbol">$</span>
                            {{ number_format($uyuAmount, 2) }}
                        </td>
                        <td class="text-right">
                            <span class="currency-symbol">U$D</span>
                            {{ number_format($usdAmount, 2) }}
                        </td>
                    </tr>
                @endforeach

                <tr class="total-row">
                    <td colspan="6" class="text-right">Totales:</td>
                    <td class="text-right">
                        <span class="currency-symbol">$</span>
                        {{ number_format($grandSubTotal, 2) }}
                    </td>
                    <td class="text-right">
                        <span class="currency-symbol">$</span>
                        {{ number_format($grandTaxTotal, 2) }}
                    </td>
                    <td class="text-right">
                        <span class="currency-symbol">$</span>
                        {{ number_format($grandTotal, 2) }}
                    </td>
                    <td class="text-right">
                        <span class="currency-symbol">$</span>
                        {{ number_format($grandTotalUYU, 2) }}
                    </td>
                    <td class="text-right">
                        <span class="currency-symbol">U$D</span>
                        {{ number_format($grandTotalUSD, 2) }}
                    </td>
                </tr>
        </tbody>
    </table>

    <div style="font-size: 9px; margin-top: 20px;">
        <p><strong>Nota:</strong> Los montos incluyen impuestos según corresponda. Las conversiones de moneda se realizan según la cotización del día de la venta.</p>
    </div>
</body>
</html>