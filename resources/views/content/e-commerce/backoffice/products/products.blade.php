@extends('layouts/layoutMaster')

@section('title', 'Productos')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
@vite([
  'resources/assets/js/app-ecommerce-product-list.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">E-Commerce /</span> Productos
</h4>

@if(session('success'))
  <div class="alert alert-success d-flex" role="alert">
    <span class="badge badge-center rounded-pill bg-success border-label-success p-3 me-2"><i class="bx bx-user fs-6"></i></span>
    <div class="d-flex flex-column ps-1">
      <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Correcto!</h6>
      <span>{{ session('success') }}</span>
    </div>
  </div>
@elseif(session('error'))
  <div class="alert alert-danger d-flex" role="alert">
    <span class="badge badge-center rounded-pill bg-danger border-label-danger p-3 me-2"><i class="bx bx-user fs-6"></i></span>
    <div class="d-flex flex-column ps-1">
      <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Error!</h6>
      <span>{{ session('error') }}</span>
  </div>
@endif

<!-- Product List Widget -->

<div class="card mb-4">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gy-4 gy-sm-1">
        <div class="col-sm-6 col-lg-3">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Ventas en tienda</h6>
              <h4 class="mb-2">$67.485</h4>
              <p class="mb-0"><span class="text-muted me-2">134 ventas</span><span class="badge bg-label-success">+5.7%</span></p>
            </div>
            <div class="avatar me-sm-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-store-alt bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none me-4">
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Ventas E-Commerce</h6>
              <h4 class="mb-2">$32.312</h4>
              <p class="mb-0"><span class="text-muted me-2">74 ventas</span><span class="badge bg-label-success">+12.4%</span></p>
            </div>
            <div class="avatar me-lg-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-laptop bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none">
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="d-flex justify-content-between align-items-start border-end pb-3 pb-sm-0 card-widget-3">
            <div>
              <h6 class="mb-2">Cupones</h6>
              <h4 class="mb-2">$1.484</h4>
              <p class="mb-0 text-muted">14 pedidos</p>
            </div>
            <div class="avatar me-sm-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-gift bx-sm"></i>
              </span>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-2">Total ventas</h6>
              <h4 class="mb-2">$99.797</h4>
              <p class="mb-0"><span class="text-muted me-2">208 ventas</span><span class="badge bg-label-danger">-3.5%</span></p>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-wallet bx-sm"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



<!-- Product List Table -->
<div class="card">
  <div class="card-header">
    <div class="d-flex col-12 justify-content-between align-items-center">

      <!-- Ver / Ocultar columnas de la tabla -->
      <div class="d-flex">
        <p class="text-muted small">
          <a href="" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches" aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
        </p>
      </div>

    </div>

    <!-- Columnas de la tabla -->
    <div class="collapse" id="columnSwitches">
      <div class="mt-0 d-flex flex-wrap">
        <div class="mx-0">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="0" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">Imagen</span>
          </label>
        </div>
        <div class="mx-3">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="1" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">Nombre</span>
          </label>
        </div>
        <!-- Agrega los switches para las demás columnas aquí -->
        <div class="mx-3">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="2" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">SKU</span>
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
      </div>
    </div>

         <!-- Información adicional -->
         <div class="d-flex justify-content-start align-items-center row py-3 gap-3 gap-md-0">
          <div class="col-md-2 product_type"></div>
          <div class="col-md-2 product_category"></div>
          <div class="col-md-2 product_store"></div>
        </div>
  </div>


  <div class="card-datatable table-responsive">
    <table class="datatables-products table border-top" data-ajax-url="{{ route('products.datatable') }}">
      <thead>
        <tr>
          <th></th>
          <th>Nombre</th>
          <th>SKU</th>
          <th>Descripción</th>
          <th>Tipo</th>
          <th>Precio</th>
          <th>Precio rebajado</th>
          <th>Categoría</th>
          <th>Local</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

@endsection
