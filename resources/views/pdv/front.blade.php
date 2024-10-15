@extends('layouts.layoutMaster')

@section('title', 'PDV - MVD')

@section('vendor-style')
@vite([

'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/toastr/toastr.scss',
'resources/assets/vendor/libs/animate-css/animate.scss'
// 'resources/assets/vendor/libs/bootstrap/bootstrap.min.css',
// 'resources/assets/vendor/libs/fontawesome/fontawesome.min.css'
])

@endsection

@section('vendor-script')
@vite([

// 'resources/assets/vendor/libs/select2/select2.min.js',
// 'resources/assets/vendor/libs/bootstrap/bootstrap.bundle.min.js',
// 'resources/assets/vendor/libs/fontawesome/fontawesome.min.js',

'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/toastr/toastr.js',
'resources/assets/js/pdv.js'
])

@php
$openCashRegister = Session::get('open_cash_register_id');
$currencySymbol = $settings->currency_symbol;
@endphp

<script>
  window.cashRegisterId = "{{ Session::get('open_cash_register_id') }}";
    window.baseUrl = "{{ url('') }}/";
  window.currencySymbol = '{{ $currencySymbol }}';
</script>


@if ($openCashRegister !== null)


@section('content')
<div class="container-fluid">
  <div id="errorContainer" class="alert alert-danger d-none" role="alert"></div>
  <div class="row">
    <div class="col-12 mb-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
      <h2 class="text-center text-md-start mb-2 mb-md-0">Punto de Venta</h2>
      {{-- Botón para cerrar caja --}}
      <button type="button" id="submit-cerrar-caja" class="btn btn-outline-danger btn-sm d-flex align-items-center">
        <i class="bx bx-lock-alt me-2"></i> Cerrar Caja
      </button>
    </div>

    <div class="col-12">
      <div class="row align-items-center p-3 mb-4 card sticky-top">

        {{-- Buscador de productos y botón de cambio de vista --}}
        <div class="col-12 mb-3">
          <div class="d-flex">
            <input class="form-control" type="search" placeholder="Buscar por nombre o código" id="html5-search-input" />
            <div class="ms-2">
              {{-- Botón de cambio de vista --}}
              <button id="toggle-view-btn" class="btn btn-outline-secondary d-flex align-items-center" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="left" data-bs-html="true" title="Ver productos en lista">
                <i class="bx bx-list-ul fs-5"></i>
              </button>
            </div>
          </div>
        </div>

        {{-- Botones de acciones --}}
        <div class="col-12 d-flex flex-column flex-md-row justify-content-md-end text-end align-items-center">
          {{-- Botón de categorías --}}
          {{-- <div class="btn-group mb-2 mb-md-0 ms-md-2">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-category"></i> Categorías
            </button>
            <div class="dropdown-menu dropdown-menu-end w-auto" style="min-width: 300px;">
              <form class="p-3" onsubmit="return false">
                <div class="mb-2">
                  <h6 class="mb-1">Filtrar por categoría</h6>
                </div>
                <div class="mb-2" id="category-container">
                  {{-- Aquí se cargarán las categorías dinámicamente --}}
                {{-- </div>
              </form>
            </div>
          </div> --}}

          {{-- Botón para ver carrito --}}
          <button id="view-cart-btn" class="btn btn-lg btn-success d-flex align-items-center mb-2 mb-md-5 ms-md-2 position-fixed bottom-0 end-0 m-4 mb-5" data-bs-toggle="modal" data-bs-target="#cartModal">
            <i class="bx bx-cart fs-5 me-2"></i>
            <a class="">Continuar</a>
            <span id="cart-count" class="badge bg-danger position-absolute top-0 start-100 translate-middle">0</span>
          </button>
        </div>
      </div>


      {{-- Contenedor de productos --}}
      <div class="row d-flex flex-wrap" id="products-container">
        {{-- Aquí se cargarán los productos --}}
      </div>
    </div>
  </div>
</div>


<!-- Modal para ver el carrito -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title" id="cartModalLabel">
          <i class="bx bx-cart me-2"></i> Resumen de la venta
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Contenedor dinámico de productos del carrito -->
        <div id="cart-items" class="row gy-3">
          <!-- Aquí se agregarán los productos del carrito en formato de tarjeta -->
        </div>

        <!-- Totales -->
        <div class="totals-container mt-4 p-3 shadow-sm rounded bg-light d-flex flex-column align-items-end" style="max-width: 350px; margin-left: auto;">
          <div class="totals-item d-flex justify-content-between align-items-center w-100 mb-2">
            <h6 class="text-muted">Subtotal:</h6>
            <h6 class="subtotal text-primary fw-bold">$770</h6>
          </div>
          {{-- <div class="totals-item d-flex justify-content-between align-items-center w-100 mb-2">
            <small class="text-muted"><i class="bx bx-package"></i> Envío:</small>
            <small class="text-dark">$0</small>
          </div> --}}
          <div class="totals-item d-flex justify-content-between align-items-center w-100 border-top pt-2">
            <h5 class="text-dark">Total:</h5>
            <h4 class="total text-dark fw-bold">$770</h4>
          </div>
        </div>

        <!-- Botón de acciones -->
        <div class="d-flex justify-content-end mt-3">
          <button class="btn btn-outline-danger me-2" type="button" data-bs-dismiss="modal">Cerrar</button>
          <a href="{{ route('pdv.front2') }}" class="btn btn-primary disabled" id="finalizarVentaBtn" aria-disabled="true" tabindex="-1">Finalizar Venta</a>
        </div>

      </div>
    </div>
  </div>
</div>


<!-- Offcanvas Crear Cliente -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="crearClienteOffcanvas"
  aria-labelledby="crearClienteOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 id="crearClienteOffcanvasLabel" class="offcanvas-title">Crear Cliente</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form id="formCrearCliente">
      <div class="mb-3">
        <label for="nombreCliente" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="nombreCliente" required>
      </div>
      <div class="mb-3">
        <label for="apellidoCliente" class="form-label">Apellido</label>
        <input type="text" class="form-control" id="apellidoCliente" required>
      </div>
      <div class="mb-3">
        <label for="tipoCliente" class="form-label">Tipo de Cliente</label>
        <select class="form-select" id="tipoCliente" required>
          <option value="individual">Individual</option>
          <option value="company">Compañía</option>
        </select>
      </div>
      <div class="mb-3" id="ciField">
        <label for="ciCliente" class="form-label">CI</label>
        <input type="text" class="form-control" id="ciCliente">
      </div>
      <div class="mb-3" id="rutField" style="display: none;">
        <label for="rutCliente" class="form-label">RUT</label>
        <input type="text" class="form-control" id="rutCliente">
      </div>
      <div class="mb-3">
        <label for="emailCliente" class="form-label">Correo Electrónico</label>
        <input type="email" class="form-control" id="emailCliente" required>
      </div>
      <button type="button" class="btn btn-primary" id="guardarCliente">Guardar</button>
    </form>
  </div>
</div>

<!-- Modal para seleccionar variaciones -->
<div class="modal fade" id="flavorModal" tabindex="-1" aria-labelledby="flavorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="flavorModalLabel">Seleccionar Variaciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="flavorsContainer" class="mb-3 col-12">
          <label class="form-label">Variaciones disponibles</label>
          <select id="flavorsSelect" class="select2 form-select variationOptions" multiple="multiple" name="flavors[]">
            <!-- Opciones de variaciones serán añadidas dinámicamente -->
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" id="saveFlavors" class="btn btn-primary">Guardar</button>
      </div>
    </div>
  </div>
</div>
@endsection
@else

@section('content')

<div class="alert alert-success mt-3 mb-3">
  <h4 class="alert-heading">¡Caja cerrada!</h4>
  <p>Para abrir una nueva caja, haga clic en el botón de abajo.</p>
  <a href="/admin/points-of-sales" class="btn btn-primary">Abrir caja</a>
</div>

@endsection

@endif

