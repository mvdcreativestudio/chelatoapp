@extends('layouts/layoutMaster')

@section('title', 'Categorías de Ingresos')

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
  window.detailUrl = "{{ route('income-categories.show', ':id') }}";
</script>
@endsection

@section('page-script')
@vite([
'resources/assets/js/incomes/incomes-categories/app-incomes-categories-list.js',
'resources/assets/js/incomes/incomes-categories/app-incomes-categories-add.js',
'resources/assets/js/incomes/incomes-categories/app-incomes-categories-edit.js',
// 'resources/assets/js/incomes/incomes-categories/app-incomes-categories-detail.js',
'resources/assets/js/incomes/incomes-categories/app-incomes-categories-delete.js',
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Contabilidad /</span> Categorías de Ingresos
</h4>

@if (Auth::user()->can('access_datacenter'))
<div class="card mb-4">
  <div class="card-body card-widget-separator">
    <div class="row gy-4 gy-sm-1">
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
          <div>
            <h6 class="mb-2">Total Categorías</h6>
            <h4 class="mb-2">{{ $totalIncomeCategories }}</h4>
          </div>
          <div class="avatar me-lg-4">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-category bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- Income Categories List Table -->
<div class="card">
  <div class="card pb-3">
    <h5 class="card-header pb-0">
      Categorías de Ingresos
      <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addIncomeCategoryModal">
        Agregar Categoría
      </button>
      <div class="d-flex">
        <p class="text-muted small">
          <a href="" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches"
            aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
        </p>
      </div>
      <div class="collapse" id="columnSwitches">
        <div class="mt-0 d-flex flex-wrap">
          <!-- Selectores de columnas -->
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="2" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Nombre</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="3" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Descripción</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="4" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Fecha de Creación</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="5" checked>
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
    </h5>
  </div>

  <div class="card-datatable table-responsive pt-0">
    @if($incomeCategories->count() > 0)
    <table class="table datatables-income-categories">
      <thead>
        <tr>
          <th>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="checkAll">
            </div>
          </th>
          <th>N°</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Fecha de Creación</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        <!-- Datos llenados por DataTables -->
      </tbody>
    </table>
    @else
    <div class="text-center py-5">
      <h4>No hay categorías de ingresos</h4>
      <p class="text-muted">Agrega una nueva categoría para comenzar</p>
    </div>
    @endif
  </div>
</div>

@include('content.accounting.incomes.incomes-categories.add-income-categories')
@include('content.accounting.incomes.incomes-categories.edit-income-categories')

@endsection
