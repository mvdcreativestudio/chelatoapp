@php
    if (!is_object($currentAccount)) {
        dd('currentAccount no es objeto:', $currentAccount);
    }

    // Calcular el saldo actual
    $balance = $totalDebit - $totalAmount;
@endphp

<table>
  <!-- Encabezado con información del cliente/proveedor -->
  <thead>
    <tr>
      <th colspan="6" style="font-size: 14px; font-weight: bold; background-color: #f0f0f0; text-align: center;">INFORMACIÓN DEL {{ $typeEntity === 'client' ? 'CLIENTE' : 'PROVEEDOR' }}</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td colspan="2" style="font-weight: bold; text-align: center;">Nombre:</td>
      <td colspan="4" style="text-align: center;">
        @if ($typeEntity === 'client')
          @if ($dataEntity->name)
            {{ $dataEntity->name }} {{ $dataEntity->lastname }}
          @else
            {{ $dataEntity->company_name }}
          @endif
        @else
          {{ $dataEntity->name }}
        @endif
      </td>
    </tr>
    @if ($typeEntity === 'client' && $dataEntity->company_name)
    <tr>
      <td colspan="2" style="font-weight: bold; text-align: center;">Empresa:</td>
      <td colspan="4" style="text-align: center;">{{ $dataEntity->company_name }}</td>
    </tr>
    @endif
    @if ($typeEntity === 'client')
    <tr>
      <td colspan="2" style="font-weight: bold; text-align: center;">Sucursal:</td>
      <td colspan="4" style="text-align: center;">{{ $dataEntity->branch ?: '-' }}</td>
    </tr>
    @endif
    <tr>
      <td colspan="2" style="font-weight: bold; text-align: center;">Fecha de reporte:</td>
      <td colspan="4" style="text-align: center;">{{ now()->format('d/m/Y') }}</td>
    </tr>
    <tr>
      <td colspan="2" style="font-weight: bold; text-align: center;">Moneda:</td>
      <td colspan="4" style="text-align: center;">{{ $currentAccount->currency->name ?? 'N/A' }}</td>
    </tr>
    <tr><td colspan="6"></td></tr> <!-- Espacio en blanco separador -->
  </tbody>

  <!-- Tabla de movimientos -->
  <thead>
    <tr>
      <th style="font-weight: bold; background-color: #d6ff4d; color: #181818; text-align: center;">Concepto</th>
      <th style="font-weight: bold; background-color: #d6ff4d; color: #181818; text-align: center;">Ventas</th>
      <th style="font-weight: bold; background-color: #d6ff4d; color: #181818; text-align: center;">Pagos</th>
      <th style="font-weight: bold; background-color: #d6ff4d; color: #181818; text-align: center;">Saldo</th>
      <th style="font-weight: bold; background-color: #d6ff4d; color: #181818; text-align: center;">Moneda</th>
      <th style="font-weight: bold; background-color: #d6ff4d; color: #181818; text-align: center;">Fecha</th>
    </tr>
  </thead>
  <tbody>
    @php $runningBalance = 0; @endphp
    @foreach($combinedEntries as $entry)
      @if($entry['type'] === 'credit')
        @php
          $runningBalance += $entry['entry']->total_debit;
          $description = strip_tags($entry['entry']->description ?? 'Crédito Inicial');
          $order = $entry['entry']->order ?? null;
          $cfeSuffix = '';

          if ($order && $order->is_billed) {
              $cfe = $order->invoices->first();
              if ($cfe) {
                  $cfeSuffix = ' - ' . $cfe->serie . '-' . $cfe->nro;
              }
          }
        @endphp
        <tr>
          <td style="text-align: center;">{{ $description . $cfeSuffix }}</td>
          <td style="text-align: center;">${{ number_format($entry['entry']->total_debit, 2) }}</td>
          <td style="text-align: center;"></td>
          <td style="text-align: center;">${{ number_format($runningBalance, 2) }}</td>
          <td style="text-align: center;">{{ $currentAccount->currency->name ?? 'N/A' }}</td>
          <td style="text-align: center;">{{ $entry['entry']->created_at->format('d/m/Y') }}</td>
        </tr>
      @elseif($entry['type'] === 'payment')
        @php $runningBalance -= $entry['entry']->payment_amount; @endphp
        <tr>
          <td style="text-align: center;">{{ strip_tags($entry['entry']->paymentMethod->description ?? 'N/A') }}</td>
          <td style="text-align: center;"></td>
          <td style="text-align: center;">${{ number_format($entry['entry']->payment_amount, 2) }}</td>
          <td style="text-align: center;">${{ number_format($runningBalance, 2) }}</td>
          <td style="text-align: center;">{{ $currentAccount->currency->name ?? 'N/A' }}</td>
          <td style="text-align: center;">{{ $entry['entry']->payment_date->format('d/m/Y') }}</td>
        </tr>
      @endif

    @endforeach

    <!-- Totales -->
    <tr>
      <td style="font-weight: bold; background-color: #f0f0f0; text-align: center;">Totales</td>
      <td style="font-weight: bold; background-color: #f0f0f0; text-align: center;">${{ number_format($totalDebit, 2) }}</td>
      <td style="font-weight: bold; background-color: #f0f0f0; text-align: center;">${{ number_format($totalAmount, 2) }}</td>
      <td style="font-weight: bold; background-color: #f0f0f0; text-align: center;">${{ number_format($runningBalance, 2) }}</td>
      <td colspan="2" style="background-color: #f0f0f0; text-align: center;"></td>
    </tr>
    <tr><td colspan="6"></td></tr> <!-- Espacio en blanco separador -->

    <!-- Saldo a la fecha -->
    <tr>
      <td colspan="3" style="font-weight: bold; background-color: #d6ff4d; color: #181818; text-align: center;">SALDO A LA FECHA {{ now()->format('d/m/Y') }}:</td>
      <td style="font-weight: bold; background-color: #d6ff4d; color: #181818; text-align: center;">${{ number_format($runningBalance, 2) }}</td>
      <td style="font-weight: bold; background-color: #d6ff4d; color: #181818; text-align: center;">{{ $currentAccount->currency->name ?? 'N/A' }}</td>
      <td style="background-color: #d6ff4d; text-align: center;"></td>
    </tr>
  </tbody>
</table>
