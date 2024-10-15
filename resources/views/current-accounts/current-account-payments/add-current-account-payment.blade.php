@extends('layouts/layoutMaster')

@section('title', 'Agregar Nuevo Pago a Cuenta Corriente')

@section('page-script')
@vite([
    'resources/assets/js/current-accounts/current-account-payments/app-current-account-payment-add.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Contabilidad /</span> Pagos de Cuentas Corrientes
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h5 class="card-title">Agregar Nuevo Pago</h5>
    <a href="{{ route('current-account-payments.show', $currentAccount->id) }}" class="btn btn-primary">Volver</a>
  </div>
  <div class="card-body">
    <form id="addNewPaymentForm" method="POST" action="{{ route('current-account-payments.store') }}">
      @csrf

      <!-- Campo oculto para la cuenta corriente -->
      <input type="hidden" name="current_account_id" value="{{ $currentAccount->id }}">

      <!-- Mostrar Cliente o Proveedor -->
      <div class="mb-3">
        @if ($currentAccount->client_id)
          <!-- Cliente -->
          <label for="client_id" class="form-label">Cliente</label>
          <input type="text" class="form-control" value="{{ $client->name }}" disabled>
          <input type="hidden" name="client_id" value="{{ $client->id }}">
        @elseif ($currentAccount->supplier_id)
          <!-- Proveedor -->
          <label for="supplier_id" class="form-label">Proveedor</label>
          <input type="text" class="form-control" value="{{ $supplier->name }}" disabled>
          <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
        @endif
      </div>

      <!-- Monto total de la cuenta corriente (Debe) -->
      <div class="mb-3">
        <label for="debe" class="form-label">Debe (Monto Total)</label>
        <input type="text" class="form-control" id="debe" name="debe" value="{{ number_format($currentAccount->payment_total_debit, 2) }}" disabled>
      </div>

      <!-- Total pagado hasta el momento (Haber) -->
      <div class="mb-3">
        <label for="haber" class="form-label">Haber (Total Pagado)</label>
        <input type="text" class="form-control" id="haber" name="haber" value="{{ number_format($currentAccount->payment_amount, 2) }}" disabled>
      </div>

      <!-- Monto Pagado -->
      <div class="mb-3">
        <label for="payment_amount" class="form-label">Monto a Pagar</label>
        <input type="number" class="form-control" id="payment_amount" name="payment_amount" required placeholder="Ingrese el monto pagado">
      </div>

      <!-- Método de Pago -->
      <div class="mb-3">
        <label for="payment_method_id" class="form-label">Método de Pago</label>
        <select class="form-select" id="payment_method_id" name="payment_method_id" required>
          <option value="" selected disabled>Seleccione un método de pago</option>
          @foreach($paymentMethods as $method)
          <option value="{{ $method->id }}">{{ $method->description }}</option>
          @endforeach
        </select>
      </div>

      <!-- Fecha de Pago -->
      <div class="mb-3">
        <label for="payment_date" class="form-label">Fecha de Pago</label>
        <input type="date" class="form-control" id="payment_date" name="payment_date" required value="{{ date('Y-m-d') }}">
      </div>

      <!-- Botón para enviar -->
      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" id="submitPaymentBtn">Guardar Pago</button>
      </div>
    </form>
  </div>
</div>

@endsection
