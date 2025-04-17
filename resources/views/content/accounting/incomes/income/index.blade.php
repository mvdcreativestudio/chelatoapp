@extends('layouts/layoutMaster')

@section('title', 'Ventas Libres')

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
  window.detailUrl = "{{ route('incomes.show', ':id') }}";
</script>
@endsection

@section('page-script')
@vite([
'resources/assets/js/incomes/incomes/app-incomes-list.js',
'resources/assets/js/incomes/incomes/app-incomes-add.js',
'resources/assets/js/incomes/incomes/app-incomes-edit.js',
'resources/assets/js/incomes/incomes/app-incomes-delete.js',
])
@endsection

@section('content')
@include('content.accounting.incomes.income.edit-income')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-2">
    <span class="text-muted fw-light">Contabilidad /</span> Ventas Libres
  </h4>
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
    <i class="bx bx-plus me-1"></i>
    Agregar Venta Libre
  </button>
</div>

@if (Auth::user()->can('access_datacenter'))
<div class="row mb-4">
  <div class="col-lg-6 col-md-6 col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div class="card-info">
            <h5 class="card-title mb-0">Total Ventas Libres</h5>
            <h2 class="mb-2 mt-2">{{ $totalIncomes }}</h2>
            <small class="text-muted">Cantidad de ventas registradas</small>
          </div>
          <div class="card-icon">
            <span class="badge bg-label-primary rounded p-2">
              <i class="bx bx-dollar bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 col-md-6 col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div class="card-info">
            <h5 class="card-title mb-0">Monto Total</h5>
            <h2 class="mb-2 mt-2">{{ $settings->currency_symbol }} {{ $totalIncomeAmount }}</h2>
            <small class="text-muted">Total ventas realizadas</small>
          </div>
          <div class="card-icon">
            <span class="badge bg-label-success rounded p-2">
              <i class="bx bx-money bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filters Card -->
<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="card-title mb-0">Filtros</h5>
      <div>
        <button class="btn btn-outline-secondary btn-sm me-2" id="clear-filters">
          <i class="bx bx-reset me-1"></i>Limpiar
        </button>
        <div class="btn-group">
          <button class="btn btn-primary btn-sm" id="export-excel">
            <i class="bx bxs-file-export me-1"></i>Excel
          </button>
          <button class="btn btn-primary btn-sm" id="export-pdf">
            <i class="bx bxs-file-pdf me-1"></i>PDF
          </button>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label" for="entityType">Tipo de Entidad</label>
        <select id="entityType" class="form-select">
          <option value="">Todos</option>
          <option value="client">Cliente</option>
          <option value="supplier">Proveedor</option>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label" for="category">Categoría</label>
        <select id="category" class="form-select">
          <option value="">Todas</option>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label" for="startDate">Fecha Desde</label>
        <input type="date" class="form-control" id="startDate">
      </div>

      <div class="col-md-3">
        <label class="form-label" for="endDate">Fecha Hasta</label>
        <input type="date" class="form-control" id="endDate">
      </div>
    </div>
  </div>
</div>

<!-- DataTable -->
<div class="card">
  <div class="card-datatable table-responsive">
    @if($incomes->count() > 0)
    <table class="table datatables-incomes border-top" data-symbol="{{ $settings->currency_symbol }}">
      <thead class="table-light">
        <tr>
          <th>N°</th>
          <th>Fecha</th>
          <th>Entidad</th>
          <th>Descripción</th>
          <th>Método de Pago</th>
          <th>Importe</th>
          <th>Categoría</th>
          <th>Moneda</th>
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
      <h4>No hay Ventas Libres</h4>
      <p class="text-muted">Agrega una nueva venta libre para comenzar</p>
    </div>
    @endif
  </div>
</div>
@endif

@include('content.accounting.incomes.income.add-income')
@endsection