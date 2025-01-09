@extends('layouts.layoutMaster')

@section('title', 'Pago - Sumeria')

@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    // 'resources/assets/vendor/libs/bootstrap/bootstrap.min.css',
    // 'resources/assets/vendor/libs/fontawesome/fontawesome.min.css'
])
@endsection

@php
  $currencySymbol = $settings->currency_symbol;
@endphp
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    window.cashRegisterId = "{{ Session::get('open_cash_register_id') }}";
    window.baseUrl = "{{ url('') }}/";
    window.frontRoute = "{{ route('pdv.front') }}";
    // Configuración de las respuestas del POS Scanntech
    const posResponsesConfig = @json(config('posResponses'));
    window.currencySymbol = '{{ $currencySymbol }}';
    window.userPermissions = @json(auth()->user()->getAllPermissions()->pluck('name')->toArray());
    window.csrfToken = "{{ csrf_token() }}";
</script>

@section('content')

<div class="p-1 ">
  <div id="errorContainer" class="alert alert-danger d-none" role="alert"></div>

  <div class="row">
    <div class="col-12 d-flex justify-content-between align-items-center mb-4">
      <h5 class="mb-0">
        <button class="btn m-0 p-0">
          <a href="{{ route('pdv.front') }}"><i class="bx bx-chevron-left fs-2"></i></a>
        </button> Atras
      </h5>
    </div>

    <div class="col-md-8">
      <div class="row">
        <div class="col-12 mb-3" id="client-selection-container">
          <div class="card shadow-sm p-3 rounded-lg client-card-custom">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="m-0 text-secondary">Cliente</h5>
              <button class="btn btn-outline-primary btn-sm d-flex align-items-center" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd">
                <i class="bx bx-plus me-1"></i>
                <span>Seleccionar Cliente</span>
              </button>
            </div>
          </div>
        </div>
        {{-- @if(isset($posDevice))
          <p>POS Device: {{ $posDevice->identifier }} (User: {{ $posDevice->user }})</p>
        @else
          <p>No POS device is associated with the open cash register.</p>
        @endif --}}

        <div class="col-12 mb-3">
          <div id="client-info" class="card shadow-sm p-4 mb-3 rounded-lg border-0 client-info-card" style="display: block;">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="m-0">Información del Cliente</h5>
              <button id="deselect-client" class="btn btn-outline-danger btn-sm">
                <span class="d-none d-md-inline">Deseleccionar</span>
                <i class="bx bx-x d-inline d-md-none"></i>
              </button>
            </div>
            <div class="client-details">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <p class="mb-1"><strong class="text-muted">ID:</strong> <span id="client-id" class="text-body fw-bold">-</span></p>
                </div>
                <div class="col-md-6 mb-3">
                  <p class="mb-1"><strong class="text-muted">Nombre:</strong> <span id="client-name" class="text-body fw-bold">-</span></p>
                </div>
                <div class="col-md-6 mb-3">
                  <p class="mb-1"><strong class="text-muted">Tipo de Cliente:</strong> <span id="client-type" class="text-body fw-bold">-</span></p>
                </div>
                <div class="col-md-6 mb-3">
                  <p class="mb-1" id="client-company" style="display:none;"></p>
                  <p class="mb-1"><strong id="client-doc-label" class="text-muted">CI:</strong> <span id="client-doc" class="text-body fw-bold">-</span></p>
                </div>
                <div class="col-md-6 mb-3">
                  <p class="mb-1"><strong class="text-muted">Lista de Precios Predefinida:</strong> <span id="client-price-list" class="text-body fw-bold">-</span></p>
                </div>
              </div>
            </div>
          </div>
          <div class="card shadow-sm p-4 border-0">
            <h5 class="mb-3">Productos de la venta</h5>
            <!-- Listado de items seleccionados -->
            <ul class="list-group list-group-flush" id="cart-items">
              <!-- Aquí se insertarán los items del carrito dinámicamente -->
            </ul>
          </div>
        </div>
        <div class="col-12">
          <div class="card shadow-sm p-3">
            <h5>Observación</h5>
            <textarea class="form-control" placeholder="Digite la observación aquí"></textarea>
            {{-- Campo para mostrar la nota en el recibo --}}
            {{-- <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" id="mostrarRecibo">
              <label class="form-check-label" for="mostrarRecibo">Mostrar en el recibo</label>
            </div> --}}
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm p-3 mb-3">
        <h5>Resumen de la venta</h5>
        <div class="d-flex justify-content-between">
          <span>Subtotal de productos</span>
          <span class="subtotal">$0.00</span>
        </div>
        <div class="d-flex justify-content-between">
          <span>Descuentos</span>
          <span class="discount-amount">$0.00</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between">
          <strong>Total</strong>
          <strong class="total">$0.00</strong>
        </div>
      </div>

      <div class="discount-section mt-3">
        <div class="card shadow-sm border-0">
            <!-- Encabezado de la tarjeta colapsable -->
            <div class="card-header p-3" id="headingDiscount" data-bs-toggle="collapse" data-bs-target="#discountCollapse" aria-expanded="false" aria-controls="discountCollapse" role="button">
                <h5 class="mb-0 font-weight-bold">Descuentos <i class="bx bx-chevron-down float-end"></i></h5>
            </div>
    
            <!-- Contenido de la tarjeta que será colapsable -->
            <div id="discountCollapse" class="collapse">
                <div class="card-body p-3">
                    <div class="form-group mb-3">
                        <label for="coupon-code" class="text-muted small">Cupón</label>
                        <input type="text" id="coupon-code" class="form-control form-control-sm" placeholder="Ingresa código de cupón">
                    </div>
                    <div class="form-group mb-3">
                        <label for="fixed-discount" class="text-muted small">Descuento</label>
                        <div class="input-group">
                            <input type="number" id="fixed-discount" class="form-control form-control-sm" placeholder="Ingresa cantidad o porcentaje">
                            <div class="input-group-append">
                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-outline-secondary btn-sm">
                                        <input type="radio" name="discount-type" value="fixed" autocomplete="off"> Monto Fijo
                                    </label>
                                    <label class="btn btn-outline-secondary btn-sm">
                                        <input type="radio" name="discount-type" value="percentage" autocomplete="off"> Porcentaje
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm w-100" id="apply-discount-btn">Aplicar</button>
                    <button class="btn btn-danger btn-sm w-100 mt-1" id="quitarDescuento" style="display: none;">Eliminar descuento</button>
                </div>
            </div>
        </div>
      </div>
    

      <div class="price-list-section mt-3 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-header p-3" id="headingPriceList" data-bs-toggle="collapse" data-bs-target="#priceListCollapse" aria-expanded="false" aria-controls="priceListCollapse" role="button">
                <h5 class="mb-0 font-weight-bold">Lista de Precios <i class="bx bx-chevron-down float-end"></i></h5>
            </div>
            <div id="priceListCollapse" class="collapse">
                <div class="card-body p-3">
                    <div class="mb-3 mt-1">
                        <label class="form-label" for="manual_price_list_id">Lista de Precios</label>
                        <select id="manual_price_list_id" class="form-select form-select">
                            <option value="" selected>Seleccionar lista de precios (Opcional)</option>
                            <option value="0" style="color: white; background-color: #ff7e7e;">No utilizar lista de precios</option>
                            @foreach($priceLists as $priceList)
                                <option value="{{ $priceList->id }}">{{ $priceList->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
      </div>
    

      <div class="card shadow-sm p-4 bg-light">
        <h5 class="mb-3 font-weight-bold">Pago y Envío</h5>
    
        <div class="payment-options d-flex flex-wrap gap-2">
          <div class="payment-option flex-grow-1">
            <input class="btn-check" type="radio" name="paymentMethod" id="cash" autocomplete="off">
            <label class="btn btn-outline-primary d-flex align-items-center justify-content-center w-100" for="cash" style="min-width: 100px;">
              <i class="bx bx-money me-2"></i> Efectivo
            </label>
          </div>
    
          <div class="payment-option flex-grow-1">
            <input class="btn-check" type="radio" name="paymentMethod" id="debit" autocomplete="off">
            <label class="btn btn-outline-primary d-flex align-items-center justify-content-center w-100" for="debit" style="min-width: 100px;">
              <i class="bx bx-credit-card me-2"></i> Débito
            </label>
          </div>
    
          <div class="payment-option flex-grow-1">
            <input class="btn-check" type="radio" name="paymentMethod" id="credit" autocomplete="off">
            <label class="btn btn-outline-primary d-flex align-items-center justify-content-center w-100" for="credit" style="min-width: 100px;">
              <i class="bx bx-credit-card-front me-2"></i> Crédito
            </label>
          </div>
    
          <div class="payment-option flex-grow-1">
            <input class="btn-check" type="radio" name="paymentMethod" id="internalCredit" autocomplete="off">
            <label class="btn btn-outline-primary d-flex align-items-center justify-content-center w-100" for="internalCredit" style="min-width: 100px;">
              <i class="bx bx-credit-card-front me-2"></i> Crédito Interno
            </label>
          </div>

          @if($enableMercadoPagoAccountPos)
          <div class="payment-option flex-grow-1">
            <input class="btn-check" type="radio" name="paymentMethod" id="mercadoPago" autocomplete="off">
            <label class="btn btn-outline-primary d-flex align-items-center justify-content-center w-100" for="mercadoPago" data-bs-toggle="modal" data-bs-target="#modalMercadopago" style="min-width: 100px;">
              <i class="bx bxl-mercadopago me-2"></i> Mercado Pago
            </label>
          </div>
          @else
          <div class="payment-option flex-grow-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Debe crear un POS para usar esta opción">
            <input class="btn-check" type="radio" name="paymentMethod" id="mercadoPago" autocomplete="off" disabled>
            <label class="btn btn-outline-primary d-flex align-items-center justify-content-center w-100" for="mercadoPago" style="min-width: 100px;">
                <i class="bx bxl-mercadopago me-2"></i> Mercado Pago
            </label>
          </div>
          @endif
        </div>
    
        <div class="mt-3 cash-details" id="cashDetails">
          <input type="number" id="valorRecibido" min="0" step=".01" class="form-control form-control-lg mb-2" placeholder="Valor recibido">
          <p class="text-muted mb-0">Vuelto: <span id="vuelto" class="fw-bold">0</span></p>
          <small id="mensajeError" class="text-danger d-none">El valor recibido es insuficiente.</small>
        </div>
    
        <div class="mt-4">
          <h5 class="card-title mb-3 text-primary">Estado de entrega</h5>
          <select id="shippingStatus" class="form-select form-select-sm">
            <option value="delivered">Entregado</option>
            <option value="shipped">Enviado</option>
            <option value="pending">Pendiente</option>
          </select>
        </div>
    </div>
    
      <div class="demo-inline-spacing d-flex justify-content-between">
        <a href="{{ route('pdv.front') }}" id="descartarVentaBtn" class="btn btn-outline-danger"><i class="bx bx-x"></i>Descartar</a>
        <button class="btn btn-success w-100"><i class="bx bx-check"></i> Finalizar venta</button>
      </div>
      <!-- Contenedor para el estado de la transacción -->
      <div id="transaction-status" style="display: none;">
        <div class="spinner-border text-primary" role="status" id="transaction-spinner" style="display: none;">
            <span class="sr-only">Procesando...</span>
        </div>
        <div id="transaction-message" class="mt-3"></div>
      </div>
    </div>
  </div>
</div>

<!-- Offcanvas Seleccionar Cliente -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel">
  <div class="offcanvas-header">
      <h5 id="offcanvasEndLabel" class="offcanvas-title">Seleccionar Cliente</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <!-- Contenedor principal con flexbox para alinear los elementos al inicio -->
  <div class="offcanvas-body d-flex flex-column">
      <!-- Contenedor del contenido superior -->
      <div class="d-flex flex-column align-items-start mb-3">
          <p class="text-center w-100">Selecciona un cliente o crea uno nuevo.</p>
          <button type="button" class="btn btn-primary mb-2 d-grid w-100" data-bs-toggle="offcanvas" data-bs-target="#crearClienteOffcanvas">Crear Cliente</button>
          <!-- Contenedor de la barra de búsqueda -->
          <div id="search-client-container" class="w-100" style="display: none;">
              <input type="search" class="form-control" id="search-client" placeholder="Nombre, Razón Social, CI, RUT...">
          </div>
      </div>
      <!-- Lista de clientes, que será scrollable si hay muchos clientes -->
      <ul id="client-list" class="list-group flex-grow-1 ">
          <!-- Aquí se cargarán los clientes -->
      </ul>
  </div>
</div>

<!-- Offcanvas Crear Cliente -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="crearClienteOffcanvas" aria-labelledby="crearClienteOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 id="crearClienteOffcanvasLabel" class="offcanvas-title">Crear Cliente</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form id="formCrearCliente">
    @csrf
      <div class="mb-3">
        <label for="tipoCliente" class="form-label">Tipo de Cliente</label>
        <select class="form-select" id="tipoCliente" required>
          <option value="individual">Persona</option>
          <option value="company">Empresa</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="nombreCliente" class="form-label">Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="nombreCliente" placeholder="Ingrese el nombre" required>
      </div>
      <div class="mb-3">
        <label for="apellidoCliente" class="form-label">Apellido <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="apellidoCliente" placeholder="Ingrese el apellido" required>
      </div>

      <!-- Campo CI para Persona -->
      <div class="mb-3" id="ciField">
        <label for="ciCliente" class="form-label">CI <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="ciCliente" placeholder="Ingrese el documento sin puntos ni guiones">
      </div>

      <!-- Campo RUT y Razón Social para Empresa -->
      <div class="mb-3" id="razonSocialField" style="display: none;">
        <label for="razonSocialCliente" class="form-label">Razón Social <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="razonSocialCliente" placeholder="Ingrese la razón social">
      </div>

      <div class="mb-3" id="rutField" style="display: none;">
        <label for="rutCliente" class="form-label">RUT <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="rutCliente" placeholder="Ingrese el RUT">
      </div>

      <!-- Campo Dirección (requerido para ambos tipos de cliente) -->
      <div class="mb-3">
        <label for="direccionCliente" class="form-label">Dirección <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="direccionCliente" placeholder="Ingrese la dirección" required>
      </div>

      <!-- Campo Email (requerido para ambos) -->
      <div class="mb-3">
        <label for="emailCliente" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
        <input type="email" class="form-control" id="emailCliente" placeholder="Ingrese el correo electrónico" required>
      </div>

      <!-- Selección de lista de precios -->
      <div class="mb-3 mt-3">
        <label class="form-label" for="price_list_id">Lista de Precios</label>
        <select id="price_list_id" class="form-select form-select" name="price_list_id">
            <option value="" selected>Seleccionar Lista de Precios</option>
            @foreach($priceLists as $priceList)
                <option value="{{ $priceList->id }}">{{ $priceList->name }}</option>
            @endforeach
        </select>
      </div>

      <button type="button" class="btn btn-primary" id="guardarCliente">Guardar</button>
    </form>
  </div>
</div>

<!-- Modal de venta exitosa -->
<div class="modal fade" id="ventaExitosaModal" tabindex="-1" aria-labelledby="ventaExitosaModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ventaExitosaModalLabel">Venta Realizada con Éxito</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p>La venta se ha realizado exitosamente.</p>
        <p>¿Desea ver la venta?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" id="verOrdenBtn" class="btn btn-primary">Ver Venta</button>
      </div>
    </div>
  </div>
</div>
@include('pdv.mercado-pago-qr.index')
@endsection

@section('vendor-script')
@vite([
    // 'resources/assets/vendor/libs/jquery/jquery.min.js',
    // 'resources/assets/vendor/libs/popper/popper.min.js',
    // 'resources/assets/vendor/libs/bootstrap/bootstrap.min.js',
    'resources/assets/js/pdvCheckout.js',
    // 'resources/assets/js/pdv/app-mercado-pago-pdv.js',
])
@endsection
