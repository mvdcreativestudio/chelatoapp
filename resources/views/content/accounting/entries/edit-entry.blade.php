@extends('layouts/layoutMaster')

@section('title', 'Editar Asiento Contable')

<script>
  var accounts = @json($accounts);
</script>

@section('page-script')
@vite([
    'resources/assets/js/entries/app-entries-edit.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Contabilidad /</span> Editar Asiento Contable
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h5 class="card-title">Editar Asiento Contable</h5>
    <a href="{{ route('entries.index') }}" class="btn btn-primary">Volver</a>
  </div>
  <div class="card-body">
    <form id="editEntryForm" method="POST" action="{{ route('entries.update', $entry->id) }}">
      @csrf
      @method('PUT')

      <!-- Fecha del Asiento -->
      <div class="mb-3">
        <label for="edit_entry_date" class="form-label">Fecha del Asiento</label>
        <input type="date" class="form-control" id="edit_entry_date" name="entry_date" value="{{ $entry->entry_date }}" required>
      </div>

      <!-- Tipo de Asiento -->
      <div class="mb-3">
        <label for="edit_entry_type_id" class="form-label">Tipo de Asiento</label>
        <select class="form-select" id="edit_entry_type_id" name="entry_type_id" required>
          <option value="" selected disabled>Seleccione un tipo de asiento</option>
          @foreach($entryTypes as $type)
            <option value="{{ $type->id }}" {{ $entry->entry_type_id == $type->id ? 'selected' : '' }}>
              {{ $type->name }}
            </option>
          @endforeach
        </select>
      </div>

      <!-- Concepto -->
      <div class="mb-3">
        <label for="edit_concept" class="form-label">Concepto</label>
        <input type="text" class="form-control" id="edit_concept" name="concept" value="{{ $entry->concept }}" required placeholder="Ingrese el concepto del asiento">
      </div>

      <!-- Moneda -->
      <div class="mb-3">
        <label for="edit_currency_id" class="form-label">Moneda</label>
        <select class="form-select" id="edit_currency_id" name="currency_id" required>
          <option value="" selected disabled>Seleccione una moneda</option>
          @foreach($currencies as $currency)
            <option value="{{ $currency->id }}" {{ $entry->currency_id == $currency->id ? 'selected' : '' }}>
              {{ $currency->name }}
            </option>
          @endforeach
        </select>
      </div>

      <!-- Detalles del Asiento -->
      <div class="mb-3">
        <label for="edit_details" class="form-label">Detalles del Asiento</label>
        <div id="editEntryDetails">
          @foreach($entry->details as $index => $detail)
          <div class="entry-detail">
            <input type="hidden" name="edit_details[{{ $index }}][entry_detail_id]" value="{{ $detail->id }}">
            <div class="row g-2 mb-2">
              <div class="col-md-5">
                <label for="edit_entry_account_id_{{ $index }}" class="form-label">Cuenta Contable</label>
                <select class="form-select" id="edit_entry_account_id_{{ $index }}" name="edit_details[{{ $index }}][entry_account_id]" required>
                  <option value="" selected disabled>Seleccione una cuenta contable</option>
                  @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ $detail->entry_account_id == $account->id ? 'selected' : '' }}>
                      {{ $account->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label for="edit_amount_debit_{{ $index }}" class="form-label">Debe</label>
                <input type="number" class="form-control" id="edit_amount_debit_{{ $index }}" name="edit_details[{{ $index }}][amount_debit]" value="{{ $detail->amount_debit }}" placeholder="0.00">
              </div>
              <div class="col-md-3">
                <label for="edit_amount_credit_{{ $index }}" class="form-label">Haber</label>
                <input type="number" class="form-control" id="edit_amount_credit_{{ $index }}" name="edit_details[{{ $index }}][amount_credit]" value="{{ $detail->amount_credit }}" placeholder="0.00">
              </div>
              <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm remove-entry-detail"><i class="bx bx-trash"></i></button>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        <button type="button" class="btn btn-secondary" id="addEditEntryDetail">Agregar Detalle</button>
      </div>

      <!-- Botón para enviar -->
      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" id="updateEntryBtn">Guardar Cambios</button>
      </div>
    </form>
  </div>
</div>

@endsection
