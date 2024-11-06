@extends('layouts/layoutMaster')

@section('title', 'Productos Compuestos')

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
  window.detailUrl = "{{ route('composite-products.show', ':id') }}";
</script>
@endsection

@section('page-script')
@vite([
'resources/assets/js/composite-products/app-composite-product-list.js',
// 'resources/assets/js/composite-products/app-composite-product-add.js',
// 'resources/assets/js/composite-products/app-composite-product-edit.js',
'resources/assets/js/composite-products/app-composite-product-delete.js',
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light"></span> Productos Compuestos
</h4>

{{-- @if (Auth::user()->can('access_composite-products'))
<div class="card mb-4">
  <div class="card-body card-widget-separator">
    <div class="row gy-4 gy-sm-1">
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
          <div>
            <h6 class="mb-2">Productos Compuestos Totales</h6>
            <h4 class="mb-2">{{ $totalProducts }}</h4>
          </div>
          <div class="avatar me-lg-4">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-box bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h6 class="mb-2">Precio Total</h6>
            <h4 class="mb-2">{{ $settings->currency_symbol }} {{ number_format($totalPrice, 2) }}</h4>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-dollar bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start border-end pb-3 pb-sm-0 card-widget-3">
          <div>
            <h6 class="mb-2">Costo Total</h6>
            <h4 class="mb-2">{{ $settings->currency_symbol }} {{ number_format($totalRecommendedPrice, 2) }}</h4>
          </div>
          <div class="avatar me-sm-4">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-calculator bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif --}}

<!-- Composite Products List Table -->
<div class="card">
  <div class="card pb-3">
    <h5 class="card-header pb-0">
      Productos Compuestos
      {{-- <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addCompositeProductModal">
        Agregar Producto Compuesto
      </button> --}}
      <a href="{{ route('composite-products.create') }}" class="btn btn-primary float-end">Crear Nuevo</a>
      <div class="d-flex">
        <p class="text-muted small">
          <a href="" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches"
            aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
        </p>
      </div>

      <div class="collapse" id="columnSwitches">
        <div class="mt-0 d-flex justify-content-between align-items-start flex-wrap">
            <div class="d-flex flex-wrap">
                <!-- Selectores de columnas -->
                <div class="ml-1">
                    <label class="switch switch-square">
                        <input type="checkbox" class="toggle-column switch-input" data-column="2" checked>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"><i class="bx bx-check"></i></span>
                            <span class="switch-off"><i class="bx bx-x"></i></span>
                        </span>
                        <span class="switch-label">Título</span>
                    </label>
                </div>
                <div class="ml-1">
                    <label class="switch switch-square">
                        <input type="checkbox" class="toggle-column switch-input" data-column="3" checked>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"><i class="bx bx-check"></i></span>
                            <span class="switch-off"><i class="bx bx-x"></i></span>
                        </span>
                        <span class="switch-label">Empresa</span>
                    </label>
                </div>
                <div class="ml-1">
                    <label class="switch switch-square">
                        <input type="checkbox" class="toggle-column switch-input" data-column="4" checked>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"><i class="bx bx-check"></i></span>
                            <span class="switch-off"><i class="bx bx-x"></i></span>
                        </span>
                        <span class="switch-label">Precio</span>
                    </label>
                </div>
                <div class="ml-1">
                    <label class="switch switch-square">
                        <input type="checkbox" class="toggle-column switch-input" data-column="5" checked>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"><i class="bx bx-check"></i></span>
                            <span class="switch-off"><i class="bx bx-x"></i></span>
                        </span>
                        <span class="switch-label">Costo Total</span>
                    </label>
                </div>
                <div class="ml-1">
                    <label class="switch switch-square">
                        <input type="checkbox" class="toggle-column switch-input" data-column="6" checked>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"><i class="bx bx-check"></i></span>
                            <span class="switch-off"><i class="bx bx-x"></i></span>
                        </span>
                        <span class="switch-label">Acciones</span>
                    </label>
                </div>
            </div>

            <!-- Dropdown de acciones -->
            <div class="dropdown d-inline mx-2">
                <button class="btn btn-primary dropdown-toggle d-none" type="button" id="dropdownMenuButton"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Acciones
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="#" id="deleteSelected">Eliminar seleccionados</a></li>
                </ul>
            </div>
        </div>
    </div>
      <!-- Filtros para los productos compuestos -->
      <div class="d-flex justify-content-start align-items-center row py-3 gap-3 mb-0 pb-0 gap-md-0">
        <div class="col-md-2 store_filter">
          <label for="store">Empresa</label>
        </div>
        <div class="col-md-2">
          <label for="startDate">Fecha Desde</label>
          <input type="date" class="form-control date-range-filter" id="startDate" placeholder="Fecha de inicio">
        </div>
        <div class="col-md-2">
          <label for="endDate">Fecha Hasta</label>
          <input type="date" class="form-control date-range-filter" id="endDate" placeholder="Fecha de fin">
        </div>
        <div class="col-md-2 mt-2">
          <button class="btn btn-outline-danger btn-sm clear-filters" id="clear-filters">
            <i class="fas fa-eraser"></i> Limpiar Filtros
          </button>
        </div>
      </div>
    </h5>
  </div>
  <div class="card-datatable table-responsive pt-0">
    @if($compositeProducts->count() > 0)
    <table class="table datatables-composite-products" data-symbol="{{ $settings->currency_symbol }}">
      <thead>
        <tr>
          <th>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="checkAll">
            </div>
          </th>
          <th>N°</th>
          <th>Título</th>
          <th>Empresa</th>
          <th>Precio</th>
          <th>Costo Total</th>
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
      <h4>No hay productos compuestos</h4>
      <p class="text-muted">Agrega un nuevo producto compuesto para comenzar</p>
    </div>
    @endif
  </div>
</div>


{{-- @include('content.e-commerce.backoffice.composite-products.add-composite-product')
@include('content.e-commerce.backoffice.composite-products.edit-composite-product')
@include('content.e-commerce.backoffice.composite-products.details-composite-product') --}}

@endsection
