<div class="tab-pane fade" id="navs-pills-expenses-suppliers" role="tabpanel">
    <!-- Tabla de gastos por proveedor -->
    <div class="table-responsive text-start text-nowrap">
        <table class="table table-borderless">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Proveedor</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($suppliersExpensesData as $index => $supplierData)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $supplierData['supplier'] }}</td>
                    <td>{{ $settings->currency_symbol }}{{ number_format((float) $supplierData['total'], 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>