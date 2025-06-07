@extends('layouts/layoutMaster')

@section('title', 'Monedas')

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
  window.detailUrl = "{{ route('currencies.show', ':id') }}";
</script>
@endsection

@section('page-script')
@vite([
'resources/assets/js/currencies/app-currencies-list.js',
'resources/assets/js/currencies/app-currencies-add.js',
'resources/assets/js/currencies/app-currencies-edit.js',
'resources/assets/js/currencies/app-currencies-delete.js',
])
@endsection

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-2">
      <span class="text-muted fw-light">Contabilidad /</span> Monedas
    </h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCurrencyModal">
      <i class="bx bx-plus me-1"></i>
      Agregar Moneda
    </button>
  </div>

  <!-- DataTable -->
  <div class="card">
    <div class="card-datatable table-responsive">
      @if($currencies->count() > 0)
      <table class="table datatables-currencies">
        <thead>
          <tr>
            <th>N°</th>
            <th>Código</th>
            <th>Símbolo</th>
            <th>Nombre</th>
            <th>Tipo de Cambio</th>
            <th>Fecha de Creación</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          <!-- Datos llenados por DataTables -->
        </tbody>
      </table>
      @else
      <div class="text-center p-5">
        <img src="{{ asset('assets/img/illustrations/empty.svg') }}" class="mb-3" width="150">
        <h4>No hay monedas</h4>
        <p class="text-muted">Comienza agregando una nueva moneda</p>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCurrencyModal">
          <i class="bx bx-plus me-1"></i>
          Agregar Moneda
        </button>
      </div>
      @endif
    </div>
  </div>

@include('content.accounting.currencies.add-currencies')
@include('content.accounting.currencies.edit-currencies')
@endsection