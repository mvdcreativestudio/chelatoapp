@extends('layouts/layoutMaster')

@section('title', 'Editar Cuenta Corriente')
<script>
  var currentAccountSettings = @json($currentAccountSettings);
</script>
@section('page-script')
@vite([
'resources/assets/js/current-accounts/app-current-account-edit.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Contabilidad /</span> Editar Credito Inicial Cuenta Corriente
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h5 class="card-title">Editar Credito Inicial Cuenta Corriente</h5>
    <a href="{{ route('current-accounts.index') }}" class="btn btn-secondary">Volver</a>
  </div>
  <div class="card-body">
    <form id="editCurrentAccountForm" method="POST" action="{{ route('current-accounts.update', $currentAccount->id) }}">
      @csrf
      @method('PUT')

      {{-- hidden --}}
      <input type="hidden" name="current_account_id" id="current_account_id" value="{{ $currentAccount->id }}">
      <!-- Combo para seleccionar el tipo de entidad -->
      <div class="form-group mb-3">
        <label for="entity_type">Tipo de Entidad</label>
        <select id="entity_type" class="form-control" disabled>
          <option value="" disabled>Seleccionar Tipo</option>
          <option value="client" {{ $currentAccount->client_id ? 'selected' : '' }}>Cliente</option>
          <option value="supplier" {{ $currentAccount->supplier_id ? 'selected' : '' }}>Proveedor</option>
        </select>
      </div>

      <!-- Select para Clientes o Proveedores -->
      @if ($currentAccount->client_id)
      <div id="clientSelectWrapper" class="form-group mb-3">
        <label for="client_id">Seleccionar Cliente</label>
        <select id="client_id" class="form-control" disabled>
          <option value="" disabled>Seleccionar Cliente</option>
          @foreach($clients as $client)
          <option value="{{ $client->id }}" {{ $client->id == $currentAccount->client_id ? 'selected' : '' }}>{{ $client->name }}</option>
          @endforeach
        </select>
      </div>
      @else
      <div id="supplierSelectWrapper" class="form-group mb-3">
        <label for="supplier_id">Seleccionar Proveedor</label>
        <select id="supplier_id" class="form-control" disabled>
          <option value="" disabled>Seleccionar Proveedor</option>
          @foreach($suppliers as $supplier)
          <option value="{{ $supplier->id }}" {{ $supplier->id == $currentAccount->supplier_id ? 'selected' : '' }}>{{ $supplier->name }}</option>
          @endforeach
        </select>
      </div>
      @endif

      <!-- Descripción -->
      <div class="mb-3">
        <label for="description" class="form-label">Descripción</label>
        <input type="text" class="form-control" id="description" name="description" value="{{ $initialCredit->description ?? '' }}" required disabled>
      </div>

      <!-- Monto Total -->
      <div class="mb-3">
        <label for="total_debit" class="form-label">Monto Total</label>
        <input type="number" class="form-control" id="total_debit" name="total_debit" value="{{ $initialCredit?->total_debit ?? '0' }}" required>
      </div>

      <!-- Moneda -->
      <div class="mb-3">
        <label for="currency_id_current_account" class="form-label">Moneda</label>
        <select class="form-select" id="currency_id_current_account" name="currency_id_current_account" required>
          <option value="" selected disabled>Seleccione una moneda</option>
          @foreach($currencies as $currency)
          <option value="{{ $currency->id }}" {{ $currency->id == $currentAccount->currency_id ? 'selected' : '' }}>{{ $currency->name }}</option>
          @endforeach
        </select>
      </div>

      <!-- Tipo de Crédito -->
      <div class="mb-3">
        <label for="current_account_settings_id" class="form-label">Tipo de Crédito</label>
        <select class="form-select" id="current_account_settings_id" name="current_account_settings_id" required>
          <option value="" selected disabled>Seleccione el tipo de crédito</option>
          @foreach($currentAccountSettings as $setting)
          <option value="{{ $setting->id }}" {{ $setting->id == $initialCredit?->current_account_settings_id ? 'selected' : '' }}>{{ $setting->payment_terms }} Día/s</option>
          @endforeach
        </select>
      </div>
      <!-- Botón para guardar -->
      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" id="updateCurrentAccountBtn"
        data-route="{{ route('current-accounts.update', $currentAccount->id) }}">Actualizar Cuenta Corriente</button>
      </div>
    </form>
  </div>
</div>

@endsection
