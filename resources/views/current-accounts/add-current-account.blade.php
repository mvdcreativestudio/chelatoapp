@extends('layouts/layoutMaster')

@section('title', 'Agregar Nueva Cuenta Corriente')
<script>
  var currentAccountSettings = @json($currentAccountSettings);
</script>
@section('page-script')
@vite([
'resources/assets/js/current-accounts/app-current-account-add.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Contabilidad /</span> Cuentas Corrientes
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h5 class="card-title">Agregar Nueva Cuenta Corriente</h5>
    <a href="{{ route('current-accounts.index') }}" class="btn btn-secondary">Volver</a>
  </div>
  <div class="card-body">
    <form id="addNewCurrentAccountForm" method="POST">
      @csrf

      <!-- Combo para seleccionar el tipo de entidad -->
      <div class="form-group mb-3">
        <label for="entity_type">Tipo de Entidad</label>
        <select id="entity_type" class="form-control">
          <option value="" selected disabled>Seleccionar Tipo</option>
          <option value="client">Cliente</option>
          <option value="supplier">Proveedor</option>
        </select>
      </div>

      <!-- Select para Clientes (oculto por defecto) -->
      <div id="clientSelectWrapper" class="form-group d-none mb-3">
        <label for="client_id">Seleccionar Cliente</label>
        <select id="client_id" class="form-control">
          <option value="" selected disabled>Seleccionar Cliente</option>
          @foreach($clients as $client)
          <option value="{{ $client->id }}">{{ $client->name }}</option>
          @endforeach
        </select>
      </div>

      <!-- Select para Proveedores (oculto por defecto) -->
      <div id="supplierSelectWrapper" class="form-group d-none mb-3">
        <label for="supplier_id">Seleccionar Proveedor</label>
        <select id="supplier_id" class="form-control">
          <option value="" selected disabled>Seleccionar Proveedor</option>
          @foreach($suppliers as $supplier)
          <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
          @endforeach
        </select>
      </div>
      {{-- description --}}

      <div class="mb-3">
        <label for="description" class="form-label">Descripción</label>
        <input type="text" class="form-control" id="description" name="description" required
          placeholder="Ingrese una descripción de la cuenta" value="Crédito Inicial" disabled>
      </div>

      <div class="mb-3">
        <label for="total_debit" class="form-label">Monto Total</label>
        <input type="number" class="form-control" id="total_debit" name="total_debit" required
          placeholder="Ingrese el monto total de la cuenta">
      </div>

      <div class="mb-3">
        <label for="currency_id_current_account" class="form-label">Moneda</label>
        <select class="form-select" id="currency_id_current_account" name="currency_id_current_account" required>
          <option value="" selected disabled>Seleccione una moneda</option>
          @foreach($currencies as $currency)
          <option value="{{ $currency->id }}">{{ $currency->name }}</option>
          @endforeach
        </select>
      </div>

      {{-- Select para tipo de crédito de currentAccountSettings --}}
      <div class="mb-3">
        <label for="current_account_settings_id" class="form-label">Tipo de Crédito</label>
        <select class="form-select" id="current_account_settings_id" name="current_account_settings_id" required>
          <option value="" selected disabled>Seleccione el tipo de crédito</option>
        </select>
      </div>

      {{-- Checkbox para pagos parciales --}}
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="partial_payment" name="partial_payment">
        <label class="form-check-label" for="partial_payment">¿Pago Parcial?</label>
      </div>

      {{-- Campos adicionales de pago parcial que se muestran si el checkbox está seleccionado --}}
      <div id="partialPaymentFields" class="d-none">
        <div class="mb-3">
          <label for="amount_paid" class="form-label">Monto Pagado</label>
          <input type="number" class="form-control" id="amount_paid" name="amount_paid"
            placeholder="Ingrese el monto pagado">
        </div>
        <div class="mb-3">
          <label for="payment_method_id" class="form-label">Método de Pago</label>
          <select class="form-select" id="payment_method_id" name="payment_method_id">
            <option value="" selected disabled>Seleccione un método de pago</option>
            @foreach($paymentMethods as $method)
            <option value="{{ $method->id }}">{{ $method->description }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" id="submitCurrentAccountBtn"
          data-route="{{ route('current-accounts.store') }}">Guardar Cuenta Corriente</button>
      </div>
    </form>
  </div>
</div>

@endsection