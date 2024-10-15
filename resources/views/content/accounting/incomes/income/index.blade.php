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
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Contabilidad /</span> Ingresos
</h4>

@if (Auth::user()->can('access_datacenter'))
<div class="card mb-4">
  <div class="card-body card-widget-separator">
    <div class="row gy-4 gy-sm-1">
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
          <div>
            <h6 class="mb-2">Cantidad Total Ingreso</h6>
            <h4 class="mb-2">{{ $totalIncomes }}</h4>
          </div>
          <div class="avatar me-lg-4">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-dollar bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h6 class="mb-2">Ingresos Totales Clientes</h6>
            <h4 class="mb-2">{{ $totalIncomeAmount }}</h4>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-check bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- Incomes List Table -->
<div class="card">
  <div class="card pb-3">
    <h5 class="card-header pb-0">
      Ingresos
      <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
        Agregar Ingreso
      </button>
      <div class="d-flex">
        <p class="text-muted small">
          <a href="" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches"
            aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
        </p>
      </div>
      <div class="collapse" id="columnSwitches">
        <div class="mt-0 d-flex flex-wrap">
          <!-- Column Switches -->
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="2" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Fecha</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="3" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Cliente</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="4" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Método de Pago</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="5" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Importe</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="6" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Categoría</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="7" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Acciones</span>
            </label>
          </div>
        </div>
        <div class="dropdown d-inline float-end mx-2">
          <button class="btn btn-primary dropdown-toggle d-none" type="button" id="dropdownMenuButton"
            data-bs-toggle="dropdown" aria-expanded="false">
            Acciones
          </button>
          <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <li><a class="dropdown-item" href="#" id="deleteSelected">Eliminar seleccionados</a></li>
          </ul>
        </div>
      </div>

      <!-- Filter for incomes -->
      <div class="d-flex justify-content-start align-items-center row py-3 gap-3 mb-0 pb-0 gap-md-0">
        <div class="col-md-2 entity_type">
          <label for="entityType">Entidad</label>
        </div>

        <div class="col-md-2 category_filter">
          <label for="category">Categoría</label>
        </div>
        {{-- filter for date --}}
        <div class="col-md-2">
          <label for="startDate">Fecha Desde</label>
          <input type="date" class="form-control date-range-filter" id="startDate" placeholder="Fecha de inicio">
        </div>
        <div class="col-md-2">
          <label for="endDate">Fecha Hasta</label>
          <input type="date" class="form-control date-range-filter" id="endDate" placeholder="Fecha de fin">
        </div>
        {{-- button for clear filters --}}
        <div class="col-md-2 d-flex flex-column mt-2">
          <button class="btn btn-outline-danger btn-sm clear-filters w-100 mb-2" id="clear-filters">
            <i class="fas fa-eraser"></i> Limpiar Filtros
          </button>
          <button class="btn btn-outline-success btn-sm export-excel w-100" id="export-excel">
            <i class="fas fa-file-excel"></i> Exportar a Excel
          </button>
          <button class="btn btn-outline-primary btn-sm export-pdf w-100 mt-2" id="export-pdf">
            <i class="fas fa-file-pdf"></i> Exportar a PDF
          </button>
        </div>
      </div>
    </h5>
  </div>

  <div class="card-datatable table-responsive pt-0">
    @if($incomes->count() > 0)
    <table class="table datatables-incomes" data-symbol="{{ $settings->currency_symbol }}">
      <thead>
        <tr>
          <th>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="checkAll">
            </div>
          </th>
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
    <div class="text-center py-5">
      <h4>No hay ingresos</h4>
      <p class="text-muted">Agrega un nuevo ingreso para comenzar</p>
    </div>
    @endif
  </div>
</div>

@include('content.accounting.incomes.income.add-income')
@include('content.accounting.incomes.income.edit-income')
@endsection
