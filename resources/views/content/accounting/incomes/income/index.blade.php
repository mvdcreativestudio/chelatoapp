@extends('layouts/layoutMaster')

@section('title', 'Ingresos')

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
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-2">
        <span class="text-muted fw-light">Contabilidad /</span> Ingresos
    </h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
        <i class="bx bx-plus me-1"></i>
        Agregar Ingreso
    </button>
</div>

@if (Auth::user()->can('access_datacenter'))
  <div class="row mb-4">
      <!-- Total Ingresos Card -->
      <div class="col-xl-3 col-md-6 col-12 mb-4 mb-xl-0">
      <div class="card stats-card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div class="card-info">
                <h5 class="card-title">Total Ingresos</h5>
                <h2>{{ $totalIncomes }}</h2>
                <small class="text-muted">Cantidad de transacciones</small>
            </div>
            <div class="card-icon">
                <span class="badge bg-label-primary rounded">
                    <i class="bx bx-trending-up"></i>
                </span>
            </div>
        </div>
    </div>
</div>
      </div>

      <!-- Ingresos Totales Card -->
      <div class="col-xl-3 col-md-6 col-12 mb-4 mb-xl-0">
          <div class="card stats-card">
              <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start">
                      <div class="card-info">
                          <h5 class="card-title mb-0">Ingresos Totales</h5>
                          <h2 class="mb-2 mt-2">{{ $settings->currency_symbol }} {{ $totalIncomeAmount }}</h2>
                          <small class="text-muted">Monto total recibido</small>
                      </div>
                      <div class="card-icon">
                          <span class="badge bg-label-success rounded p-2">
                              <i class="bx bx-dollar bx-sm"></i>
                          </span>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Promedio de Ingresos Card -->
      <div class="col-xl-3 col-md-6 col-12 mb-4 mb-xl-0">
          <div class="card stats-card">
              <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start">
                      <div class="card-info">
                          <h5 class="card-title mb-0">Promedio de Ingresos</h5>
                          <h2 class="mb-2 mt-2">{{ $settings->currency_symbol }} {{ number_format($averageIncome, 2) }}</h2>
                          <small class="text-muted">Promedio por transacción</small>
                      </div>
                      <div class="card-icon">
                          <span class="badge bg-label-info rounded p-2">
                              <i class="bx bx-chart bx-sm"></i>
                          </span>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Ingresos Mensuales Card -->
      <div class="col-xl-3 col-md-6 col-12">
          <div class="card stats-card">
              <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start">
                      <div class="card-info">
                          <h5 class="card-title mb-0">Ingresos Mensuales</h5>
                          <h2 class="mb-2 mt-2">{{ $settings->currency_symbol }} {{ number_format($monthlyIncome, 2) }}</h2>
                          <small class="text-muted">Total del mes actual</small>
                      </div>
                      <div class="card-icon">
                          <span class="badge bg-label-warning rounded p-2">
                              <i class="bx bx-calendar bx-sm"></i>
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
          <div class="col-md-3 entity_type">
            <label for="entityType">Entidad</label>
          </div>
            <div class="col-md-3">
              <label class="form-label">Categoría</label>
              <div class="category_filter"></div>
          </div>

            <div class="col-md-3">
                <label class="form-label">Fecha Desde</label>
                <input type="date" id="startDate" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" id="endDate" class="form-control">
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
        </table>
        @else
        <div class="text-center p-5">
            <img src="{{ asset('assets/img/illustrations/empty.svg') }}" class="mb-3" width="150">
            <h4>No hay ingresos registrados</h4>
            <p class="text-muted">Comienza agregando un nuevo ingreso</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                <i class="bx bx-plus me-1"></i>
                Agregar Ingreso
            </button>
        </div>
        @endif
    </div>
</div>
@endif
<style>
  .stats-card {
    height: 100%;
    min-height: 120px; 
  }
  .stats-card .card-body {
    padding: 1.25rem; 
  }
  .card-info h5 {
    font-size: 0.9375rem;
    margin-bottom: 0.5rem !important; 
  }
  .card-info h2 {
    font-size: 1.5rem;
    line-height: 1.2;
    margin: 0.25rem 0 !important;
  }
  .card-info small {
    font-size: 0.75rem;
  }
  .badge {
    padding: 0.5rem !important;
    height: 2.25rem; 
    width: 2.25rem;
  }
  .badge i {
    font-size: 1.125rem;
  }
</style>

@include('content.accounting.incomes.income.add-income')
@include('content.accounting.incomes.income.edit-income')
@endsection