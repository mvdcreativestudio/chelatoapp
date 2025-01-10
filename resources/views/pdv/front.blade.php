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
])
@endsection

@section('vendor-script')
@vite([
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
  window.csrfToken = "{{ csrf_token() }}";
</script>

@if ($openCashRegister !== null)

@section('content')

<div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light animate__animated animate__fadeIn">

  <!-- Título del formulario alineado a la izquierda -->
  <div class="d-flex flex-column justify-content-center">
    <h4 class="mb-0 page-title animate__animated animate__fadeIn">
      <i class="bx bx-box me-2"></i> Punto de Venta
    </h4>
  </div>

  <!-- Barra de búsqueda y botón de filtros, con espacio intermedio -->
  <div class="d-flex align-items-center justify-content-center flex-grow-1 gap-3 animate__animated animate__fadeIn">
    <div class="input-group w-50 shadow-sm">
      <span class="input-group-text bg-white">
        <i class="bx bx-search"></i>
      </span>
      <input class="form-control" type="search" placeholder="Buscar por nombre o código" id="html5-search-input" />
    </div>
  </div>

  <!-- Botones alineados a la derecha, ahora responsive -->
  <div class="text-end d-flex gap-2 align-items-center animate__animated animate__fadeIn">
      
      <!-- Toggle tipo de visualización -->
      <button id="toggle-view-btn" class="btn btn-sm btn-outline-secondary d-flex align-items-center animate__animated animate__pulse" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="left" data-bs-html="true" title="Lista / Cuadrícula">
        <i class="bx bx-list-ul fs-5"></i>
      </button>

      <!-- Grupo de botones para Seleccionar Cliente y Lista de Precios -->
      <div class="btn-group">
        @if(!session('client'))
          <button id="seleccionar-cliente-btn" class="btn btn-outline-primary btn-sm d-flex align-items-center animate__animated animate__fadeIn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd">
            <i class="bx bx-user-plus me-1"></i>
            <span>Seleccionar Cliente</span>
          </button>
        @endif

        <!-- Botón de lista de precios con menú desplegable -->
        <button id="price-list-dropdown" class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bx bx-list-ul me-1"></i>Lista de Precios
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="price-list-dropdown">
          <li><a class="dropdown-item" href="#" onclick="selectPriceList(0, 'Precios originales')" style="color: white; background-color: #ff7e7e;" title="Se utilizan los precios por defecto en la aplicación, ignorando la lista del cliente">Precios Originales</a></li>
          @foreach($priceLists as $priceList)
            <li><a class="dropdown-item" href="#" onclick="selectPriceList({{ $priceList->id }}, '{{ $priceList->name }}')">{{ $priceList->name }}</a></li>
          @endforeach
        </ul>
      </div>

      <button type="button" id="submit-cerrar-caja" class="btn btn-outline-danger btn-sm d-flex align-items-center">
        <i class="bx bx-lock-alt me-2"></i> Cerrar Caja
      </button>
  </div>
</div>

<div class="price-list-section mt-3 mb-3 animate__animated animate__fadeIn">
  <input type="hidden" id="manual_price_list_id">
</div>

<script>
  function selectPriceList(priceListId, priceListName) {
    $('#manual_price_list_id').val(priceListId).change();
    $('#price-list-dropdown').html(`<i class="bx bx-list-ul me-1"></i> Lista Cargada: ${priceListName}`);
  }
</script>

<!-- Selección de Cliente -->
<div class="col-12 mb-3">
  <div id="client-info" class="card shadow-sm p-4 mb-3 rounded-lg border-0 client-info-card animate__animated animate__fadeIn" style="display: block;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="m-0">Información del Cliente</h5>
      <button id="deselect-client" class="btn btn-outline-danger btn-sm animate__animated animate__fadeIn">
        <span class="d-none d-md-inline">Deseleccionar</span>
        <i class="bx bx-x d-inline d-md-none"></i>
      </button>
    </div>
    <div class="client-details">
      <div class="row">
        <div class="col-md-6 mb-3 animate__animated animate__fadeIn">
          <p class="mb-1"><strong class="text-muted">ID:</strong> <span id="client-id" class="text-body fw-bold">-</span></p>
        </div>
        <div class="col-md-6 mb-3 animate__animated animate__fadeIn">
          <p class="mb-1"><strong class="text-muted">Nombre:</strong> <span id="client-name" class="text-body fw-bold">-</span></p>
        </div>
        <div class="col-md-6 mb-3 animate__animated animate__fadeIn">
          <p class="mb-1"><strong class="text-muted">Tipo de Cliente:</strong> <span id="client-type" class="text-body fw-bold">-</span></p>
        </div>
        <div class="col-md-6 mb-3 animate__animated animate__fadeIn">
          <p class="mb-1" id="client-company" style="display:none;"></p>
          <p class="mb-1"><strong id="client-doc-label" class="text-muted">CI:</strong> <span id="client-doc" class="text-body fw-bold">-</span></p>
        </div>
        <div class="col-md-6 mb-3 animate__animated animate__fadeIn">
          <p class="mb-1"><strong class="text-muted">Lista de Precios Predefinida:</strong> <span id="client-price-list" class="text-body fw-bold">-</span></p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid animate__animated animate__fadeIn">
  <div id="errorContainer" class="alert alert-danger d-none" role="alert"></div>
  <div class="row d-flex flex-wrap bg-white" id="products-container">
  </div>
</div>

<!-- Botón para ver carrito -->
<button id="view-cart-btn" class="btn btn-lg btn-success d-flex align-items-center mb-2 mb-md-5 ms-md-2 position-fixed bottom-0 end-0 m-4 mb-5 z-3 animate__animated animate__bounceIn" data-bs-toggle="modal" data-bs-target="#cartModal">
  <i class="bx bx-cart fs-5 me-2"></i>
  <a class="">Continuar</a>
  <span id="cart-count" class="badge bg-danger position-absolute top-0 start-100 translate-middle">0</span>
</button>

<!-- Modal para ver el carrito -->
<div class="modal fade animate__animated animate__fadeIn" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
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
        <div id="cart-items" class="row gy-3 animate__animated animate__fadeIn">
          <!-- Aquí se agregarán los productos del carrito en formato de tarjeta -->
        </div>

        <!-- Totales -->
        <div class="totals-container mt-4 p-3 shadow-sm rounded bg-light d-flex flex-column align-items-end" style="max-width: 350px; margin-left: auto;">
          <div class="totals-item d-flex justify-content-between align-items-center w-100 mb-2 animate__animated animate__fadeIn">
            <h6 class="text-muted">Subtotal:</h6>
            <h6 class="subtotal text-primary fw-bold">$770</h6>
          </div>
          <div class="totals-item d-flex justify-content-between align-items-center w-100 border-top pt-2 animate__animated animate__fadeIn">
            <h5 class="text-dark">Total:</h5>
            <h4 class="total text-dark fw-bold">$770</h4>
          </div>
        </div>

        <!-- Botón de acciones -->
        <div class="d-flex justify-content-end mt-3 animate__animated animate__fadeIn">
          <button class="btn btn-outline-danger me-2" type="button" data-bs-dismiss="modal">Cerrar</button>
          <a href="{{ route('pdv.front2') }}" class="btn btn-primary disabled" id="finalizarVentaBtn" aria-disabled="true" tabindex="-1">Finalizar Venta</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Offcanvas Seleccionar Cliente -->
<div class="offcanvas offcanvas-end animate__animated animate__fadeIn" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel">
  <div class="offcanvas-header">
      <h5 id="offcanvasEndLabel" class="offcanvas-title">Seleccionar Cliente</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body d-flex flex-column animate__animated animate__fadeIn">
      <div class="d-flex flex-column align-items-start mb-3">
          <p class="text-center w-100">Selecciona un cliente o crea uno nuevo.</p>
          <button type="button" class="btn btn-primary mb-2 d-grid w-100" data-bs-toggle="offcanvas" data-bs-target="#crearClienteOffcanvas">Crear Cliente</button>
          <div id="search-client-container" class="w-100" style="display: none;">
              <input type="search" class="form-control" id="search-client" placeholder="Nombre, Razón Social, CI, RUT...">
          </div>
      </div>
      <ul id="client-list" class="list-group flex-grow-1 animate__animated animate__fadeIn">
          <!-- Aquí se cargarán los clientes -->
      </ul>
  </div>
</div>

<!-- Offcanvas Crear Cliente -->
<div class="offcanvas offcanvas-end animate__animated animate__fadeIn" tabindex="-1" id="crearClienteOffcanvas" aria-labelledby="crearClienteOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 id="crearClienteOffcanvasLabel" class="offcanvas-title">Crear Cliente</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body animate__animated animate__fadeIn">
    <form id="formCrearCliente">
    @csrf
      <div class="mb-3 animate__animated animate__fadeInLeft">
        <label for="tipoCliente" class="form-label">Tipo de Cliente</label>
        <select class="form-select" id="tipoCliente" required>
          <option value="individual">Persona</option>
          <option value="company">Empresa</option>
        </select>
      </div>
      <div class="mb-3 animate__animated animate__fadeInLeft">
        <label for="nombreCliente" class="form-label">Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="nombreCliente" placeholder="Ingrese el nombre" required>
      </div>
      <div class="mb-3 animate__animated animate__fadeInLeft">
        <label for="apellidoCliente" class="form-label">Apellido <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="apellidoCliente" placeholder="Ingrese el apellido" required>
      </div>
      <div class="mb-3 animate__animated animate__fadeInLeft" id="ciField">
        <label for="ciCliente" class="form-label">CI <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="ciCliente" placeholder="Ingrese el documento sin puntos ni guiones">
      </div>
      <div class="mb-3 animate__animated animate__fadeInLeft" id="razonSocialField" style="display: none;">
        <label for="razonSocialCliente" class="form-label">Razón Social <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="razonSocialCliente" placeholder="Ingrese la razón social">
      </div>
      <div class="mb-3 animate__animated animate__fadeInLeft" id="rutField" style="display: none;">
        <label for="rutCliente" class="form-label">RUT <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="rutCliente" placeholder="Ingrese el RUT">
      </div>
      <div class="mb-3 animate__animated animate__fadeInLeft">
        <label for="direccionCliente" class="form-label">Dirección <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="direccionCliente" placeholder="Ingrese la dirección" required>
      </div>
      <div class="mb-3 animate__animated animate__fadeInLeft">
        <label for="emailCliente" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
        <input type="email" class="form-control" id="emailCliente" placeholder="Ingrese el correo electrónico" required>
      </div>
      <div class="mb-3 mt-3 animate__animated animate__fadeInLeft">
        <label class="form-label" for="price_list_id">Lista de Precios</label>
        <select id="price_list_id" class="form-select form-select" name="price_list_id">
            <option value="" selected>Seleccionar Lista de Precios</option>
            @foreach($priceLists as $priceList)
                <option value="{{ $priceList->id }}">{{ $priceList->name }}</option>
            @endforeach
        </select>
      </div>
      <button type="button" class="btn btn-primary animate__animated animate__bounceIn" id="guardarCliente">Guardar</button>
    </form>
  </div>
</div>

<!-- Modal para seleccionar variaciones -->
<div class="modal fade animate__animated animate__fadeIn" id="flavorModal" tabindex="-1" aria-labelledby="flavorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content animate__animated animate__zoomIn">
      <div class="modal-header">
        <h5 class="modal-title" id="flavorModalLabel">Seleccionar Variaciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="flavorsContainer" class="mb-3 col-12">
          <label class="form-label">Variaciones disponibles</label>
          <select id="flavorsSelect" class="select2 form-select variationOptions" multiple="multiple" name="flavors[]">
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
<div class="alert alert-success mt-3 mb-3 animate__animated animate__fadeIn">
  <h4 class="alert-heading">¡Caja cerrada!</h4>
  <p>Para abrir una nueva caja, haga clic en el botón de abajo.</p>
  <a href="/admin/points-of-sales" class="btn btn-primary">Abrir caja</a>
</div>
@endsection

@endif
