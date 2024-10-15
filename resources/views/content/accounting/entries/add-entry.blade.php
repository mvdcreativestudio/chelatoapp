@extends('layouts/layoutMaster')

@section('title', 'Agregar Nuevo Asiento Contable')
<script>
  var accounts = @json($accounts);
</script>
@section('page-script')
@vite([
    'resources/assets/js/entries/app-entries-add.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Contabilidad /</span> Asientos Contables
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h5 class="card-title">Agregar Nuevo Asiento Contable</h5>
    <a href="{{ route('entries.index') }}" class="btn btn-primary">Volver</a>
  </div>
  <div class="card-body">
    <form id="addNewEntryForm" method="POST" action="{{ route('entries.store') }}">
      @csrf

      <!-- Fecha del Asiento -->
      <div class="mb-3">
        <label for="entry_date" class="form-label">Fecha del Asiento</label>
        <input type="date" class="form-control" id="entry_date" name="entry_date" required>
      </div>

      <!-- Tipo de Asiento -->
      <div class="mb-3">
        <label for="entry_type_id" class="form-label">Tipo de Asiento</label>
        <select class="form-select" id="entry_type_id" name="entry_type_id" required>
          <option value="" selected disabled>Seleccione un tipo de asiento</option>
          @foreach($entryTypes as $type)
            <option value="{{ $type->id }}">{{ $type->name }}</option>
          @endforeach
        </select>
      </div>

      <!-- Concepto -->
      <div class="mb-3">
        <label for="concept" class="form-label">Concepto</label>
        <input type="text" class="form-control" id="concept" name="concept" required placeholder="Ingrese el concepto del asiento">
      </div>

      <!-- Moneda -->
      <div class="mb-3">
        <label for="currency_id" class="form-label">Moneda</label>
        <select class="form-select" id="currency_id" name="currency_id" required>
          <option value="" selected disabled>Seleccione una moneda2</option>
          @foreach($currencies as $currency)
            <option value="{{ $currency->id }}">{{ $currency->name }}</option>
          @endforeach
        </select>
      </div>

      <!-- Detalles del Asiento -->
      <div class="mb-3">
        <label for="details" class="form-label">Detalles del Asiento</label>
        <div id="entryDetails">
          <div class="entry-detail">
            <div class="row g-2 mb-2">
              <div class="col-md-5">
                <label for="entry_account_id" class="form-label">Cuenta Contable</label>
                <select class="form-select" id="entry_account_id" name="details[0][entry_account_id]" required>
                  <option value="" selected disabled>Seleccione una cuenta contable</option>
                  @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label for="amount_debit" class="form-label">Debe</label>
                <input type="number" class="form-control" id="amount_debit" name="details[0][amount_debit]" placeholder="0.00">
              </div>
              <div class="col-md-3">
                <label for="amount_credit" class="form-label">Haber</label>
                <input type="number" class="form-control" id="amount_credit" name="details[0][amount_credit]" placeholder="0.00">
              </div>
              <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm remove-entry-detail"><i class="bx bx-trash"></i></button>
              </div>
            </div>
          </div>
        </div>
        <button type="button" class="btn btn-secondary" id="addEntryDetail">Agregar Detalle</button>
      </div>

      <!-- Botón para enviar -->
      <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-primary" id="submitEntryBtn" data-route="{{ route('entries.store') }}">Guardar Asiento</button>
      </div>
    </form>
  </div>
</div>

@endsection
