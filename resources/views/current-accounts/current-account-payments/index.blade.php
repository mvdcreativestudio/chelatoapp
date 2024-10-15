@extends('layouts/layoutMaster')

@section('title', 'Detalle de Cuenta Corriente')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'
])
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js"></script>

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'
])
<script>
  window.baseUrl = "{{ url('/') }}";
</script>
@endsection

@section('page-script')
@vite([
'resources/assets/js/current-accounts/current-account-payments/app-current-account-payment-delete.js',
])
@endsection

@section('content')
@php
$statusClass = [
"Paid" => "text-success", // Green color for paid
"Unpaid" => "text-danger", // Red color for unpaid
"Partial" => "text-warning" // Yellow color for partial
];
$status = $currentAccount->status->value;
@endphp

<div class="row">
  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <h4>Pagos de Cuenta Corriente
        <span class="text-muted">/ #{{ $currentAccount->id ?? '' }}</span>
      </h4>
      <!-- Botones para agregar crédito inicial o pago -->
      <div>
        <a href="{{ route('current-account-payments.create', $currentAccount->id) }}" class="btn btn-secondary">Agregar Pago</a>
      </div>
    </div>

    <div class="card-body">
      <div class="d-flex justify-content-between">
        <div class="mb-3">
          <label for="formFile" class="form-label">Fecha de Creación</label>
          <input class="form-control" type="date" value="{{ $currentAccount->created_at->format('Y-m-d') ?? '' }}" id="formFile" disabled>
        </div>
        <div class="mb-3 col-4">
          <label for="formFile" class="form-label">{{ $typeEntity === 'client' ? 'Cliente' : 'Proveedor' }}</label>
          <input class="form-control" type="text" value="{{ $dataEntity->name ?? 'N/A' }}" disabled>
        </div>
        <div class="mb-3 col-2">
          <label for="formFile" class="form-label">Moneda</label>
          <input class="form-control" type="text" value="{{ $currentAccount->currency->name ?? 'N/A' }}" disabled>
        </div>
      </div>

      <div class="d-flex justify-content-end">
        <strong>Estado de la Cuenta: </strong>
        <span class="ms-2 {{ $statusClass[$status] ?? 'text-muted' }}">
          {{ $currentAccountStatus[$status] ?? 'N/A' }}
        </span>
      </div>
    </div>

    <!-- Contenedor para hacer scroll en la tabla -->
    <div class="card-datatable table-responsive" style="max-height: 400px; overflow-y: auto;">
      <table class="dt-responsive table border-top datatables-current-account-payments">
        <thead class="text-center table-dark" style="position: sticky; top: 0; z-index: 1;">
          <tr>
            <th class="font-white">Concepto</th>
            <th class="font-white">Debe</th>
            <th class="font-white">Haber</th>
            <th class="font-white">Moneda</th>
            <th class="font-white">Fecha</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody class="text-center">
          <!-- Mostrar Créditos Iniciales -->
          @foreach($currentAccount->initialCredits as $initialCredit)
          <tr>
            <th>{{ $initialCredit->description ?? 'Credito Inicial' }}</th>
            <th>{{ number_format($initialCredit->total_debit, 2) }}</th>
            <th class="bg-gray2"></th>
            <th>{{ $currentAccount->currency->name ?? 'N/A' }}</th>
            <th>{{ $initialCredit->created_at->format('d/m/Y') }}</th>
            <th></th>
          </tr>

          <!-- Mostrar Pagos relacionados con la cuenta corriente -->
          @foreach($currentAccount->payments as $payment)
          <tr>
            <th>{{ $payment->paymentMethod->description ?? 'N/A' }}</th>
            <th class="bg-gray2"></th>
            <th>{{ number_format($payment->payment_amount, 2) }}</th>
            <th>{{ $currentAccount->currency->name ?? 'N/A' }}</th>
            <th>{{ $payment->payment_date->format('d/m/Y') }}</th>
            <th>
              <a href="{{ route('current-account-payments.edit', $payment->id) }}" class="btn btn-sm btn-warning">Editar</a>
              <a class="btn btn-sm btn-danger delete-record text-white" data-id="{{ $payment->id }}">Eliminar</a>
            </th>
          </tr>
          @endforeach
          @endforeach
        </tbody>
        <tfoot class="text-center table-dark" style="position: sticky; bottom: 0; z-index: 1;">
          <tr>
            <th class="font-white">Totales</th>
            <th class="font-white">{{ number_format($totalDebit, 2) }}</th>
            <th class="font-white">{{ number_format($totalAmount, 2) }}</th>
            <th colspan="3"></th>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="d-flex justify-content-end m-2">
      <a href="{{ route('current-accounts.index') }}" class="btn btn-secondary">Volver</a>
    </div>
  </div>
</div>
@endsection
