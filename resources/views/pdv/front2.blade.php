@extends('layouts.layoutMaster')

@section('title', 'Checkout - PDV')

@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
])
<style>
  .pdv-checkout-wrap {
    max-width: 100%;
    padding: 0 .5rem;
  }
  /* Cart item */
  .cart-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #f0f0f0;
  }
  .cart-item:last-child { border-bottom: none; }
  .cart-item-img {
    width: 56px;
    height: 56px;
    object-fit: cover;
    border-radius: 10px;
    background: #f5f5f9;
    flex-shrink: 0;
  }
  .cart-item-info { flex: 1; min-width: 0; }
  .cart-item-name {
    font-weight: 600;
    font-size: .9rem;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .cart-item-price { color: #888; font-size: .82rem; }
  .cart-item-total {
    font-weight: 700;
    font-size: .95rem;
    min-width: 80px;
    text-align: right;
    white-space: nowrap;
  }
  /* Qty control */
  .qty-control {
    display: inline-flex;
    align-items: center;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    height: 34px;
  }
  .qty-control .qty-btn {
    background: none;
    border: none;
    width: 32px;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1rem;
    color: #555;
    transition: background .15s;
  }
  .qty-control .qty-btn:hover { background: #f0f0f5; }
  .qty-control .qty-value {
    width: 36px;
    text-align: center;
    font-weight: 600;
    font-size: .9rem;
    border-left: 1px solid #e0e0e0;
    border-right: 1px solid #e0e0e0;
    line-height: 34px;
  }
  .cart-item-remove {
    background: none;
    border: none;
    color: #d44;
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
    transition: background .15s;
    flex-shrink: 0;
  }
  .cart-item-remove:hover { background: #fdeaea; }
  /* Summary card sticky */
  @media (min-width: 992px) {
    .summary-col { position: sticky; top: 80px; align-self: flex-start; }
  }
  /* Payment pills */
  .payment-pills { display: flex; flex-wrap: wrap; gap: .5rem; }
  .payment-pill {
    flex: 1 1 calc(50% - .25rem);
    min-width: 120px;
  }
  .payment-pill input { display: none; }
  .payment-pill label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    padding: .6rem .75rem;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 500;
    font-size: .85rem;
    transition: all .2s;
    width: 100%;
    text-align: center;
  }
  .payment-pill input:checked + label {
    border-color: var(--bs-primary, #7367f0);
    background: rgba(115, 103, 240, .08);
    color: var(--bs-primary, #7367f0);
  }
  /* Discount toggle */
  .discount-toggle {
    cursor: pointer;
    user-select: none;
  }
  .discount-toggle .bx { transition: transform .2s; }
  .discount-toggle.collapsed .bx { transform: rotate(-90deg); }
  /* Empty cart */
  .empty-cart {
    text-align: center;
    padding: 3rem 1rem;
    color: #aaa;
  }
  .empty-cart i { font-size: 3rem; margin-bottom: .5rem; }
  /* Client mini card */
  .client-mini {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .75rem 1rem;
    background: #f5f5f9;
    border-radius: 10px;
  }
  .client-mini-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--bs-primary, #7367f0);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: .9rem;
    flex-shrink: 0;
  }
  .client-mini-info { flex: 1; min-width: 0; }
  .client-mini-name { font-weight: 600; font-size: .9rem; }
  .client-mini-doc { font-size: .78rem; color: #888; }
  /* Mobile finalize bar */
  @media (max-width: 991.98px) {
    .mobile-finalize-bar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      z-index: 1050;
      background: #fff;
      border-top: 1px solid #e0e0e0;
      padding: .75rem 1rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 -2px 12px rgba(0,0,0,.08);
    }
    .pdv-checkout-wrap { padding-bottom: 80px; }
  }
  @media (min-width: 992px) {
    .mobile-finalize-bar { display: none !important; }
  }
</style>
@endsection

@php
  $currencySymbol = $settings->currency_symbol;
@endphp

<script>
    window.cashRegisterId = "{{ Session::get('open_cash_register_id') }}";
    window.baseUrl = "{{ url('') }}/";
    window.frontRoute = "{{ route('pdv.front') }}";
    const posResponsesConfig = @json(config('posResponses'));
    window.currencySymbol = '{{ $currencySymbol }}';
    window.userPermissions = @json(auth()->user()->getAllPermissions()->pluck('name')->toArray());
</script>

@section('content')

<div class="pdv-checkout-wrap">
  <div id="errorContainer" class="alert alert-danger d-none mb-3" role="alert"></div>

  {{-- Header --}}
  <div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('pdv.front') }}" class="btn btn-icon btn-outline-secondary rounded-circle">
      <i class="bx bx-arrow-back"></i>
    </a>
    <div>
      <h5 class="mb-0">Checkout</h5>
      <small class="text-muted">Revisá los productos y completá la venta</small>
    </div>
  </div>

  <div class="row g-4">
    {{-- ===================== LEFT COLUMN ===================== --}}
    <div class="col-lg-8">

      {{-- Client section --}}
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          {{-- No client selected --}}
          <div id="client-selection-container">
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center gap-2">
                <span class="avatar rounded-circle bg-label-secondary d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                  <i class="bx bx-user" style="font-size:1.1rem;"></i>
                </span>
                <span class="fw-semibold">Consumidor Final</span>
              </div>
              <button class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd">
                <i class="bx bx-user-plus me-1"></i> Asignar cliente
              </button>
            </div>
          </div>
          {{-- Client selected --}}
          <div id="client-info" style="display:none;">
            <div class="d-flex justify-content-between align-items-center">
              <div class="client-mini flex-grow-1">
                <div class="client-mini-avatar" id="client-avatar">--</div>
                <div class="client-mini-info">
                  <div class="client-mini-name" id="client-name">-</div>
                  <div class="client-mini-doc">
                    <span id="client-type-label">Persona</span> &middot;
                    <span id="client-doc-label">CI</span>: <span id="client-doc">-</span>
                    <span id="client-company" style="display:none;"> &middot; <span id="client-company-name"></span></span>
                  </div>
                </div>
              </div>
              <button id="deselect-client" class="btn btn-sm btn-icon btn-outline-danger ms-2" title="Quitar cliente">
                <i class="bx bx-x"></i>
              </button>
            </div>
            <input type="hidden" id="client-id" value="">
          </div>
        </div>
      </div>

      {{-- Cart items --}}
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
          <h6 class="mb-0"><i class="bx bx-cart me-1"></i> Productos <span class="badge bg-label-primary ms-1" id="cart-count">0</span></h6>
        </div>
        <div class="card-body pt-0">
          <div id="cart-items">
            {{-- Items render dynamically --}}
          </div>
          <div id="cart-empty" class="empty-cart" style="display:none;">
            <i class="bx bx-cart"></i>
            <p class="mb-0">El carrito está vacío</p>
            <a href="{{ route('pdv.front') }}" class="btn btn-sm btn-outline-primary mt-2">Agregar productos</a>
          </div>
        </div>
      </div>

      {{-- Notes --}}
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <label class="form-label fw-semibold mb-2"><i class="bx bx-note me-1"></i> Observación</label>
          <textarea class="form-control" rows="2" placeholder="Nota interna sobre esta venta (opcional)" id="orderNotes"></textarea>
        </div>
      </div>
    </div>

    {{-- ===================== RIGHT COLUMN ===================== --}}
    <div class="col-lg-4 summary-col">

      {{-- Summary --}}
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <h6 class="mb-3">Resumen</h6>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Subtotal</span>
            <span class="subtotal fw-semibold">{{ $currencySymbol }}0.00</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Descuento</span>
            <span class="discount-amount text-danger">-{{ $currencySymbol }}0.00</span>
          </div>
          <hr class="my-2">
          <div class="d-flex justify-content-between">
            <span class="fw-bold fs-5">Total</span>
            <span class="total fw-bold fs-5">{{ $currencySymbol }}0.00</span>
          </div>
        </div>
      </div>

      {{-- Discounts --}}
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <div class="discount-toggle d-flex justify-content-between align-items-center collapsed" data-bs-toggle="collapse" data-bs-target="#discountCollapse">
            <h6 class="mb-0"><i class="bx bx-purchase-tag me-1"></i> Descuentos</h6>
            <i class="bx bx-chevron-down"></i>
          </div>
          <div class="collapse" id="discountCollapse">
            <div class="mt-3">
              <label class="form-label small text-muted">Cupón</label>
              <div class="input-group input-group-sm mb-3">
                <input type="text" id="coupon-code" class="form-control" placeholder="Código de cupón">
                <button class="btn btn-outline-primary" type="button" id="apply-coupon-btn">Aplicar</button>
              </div>
              <label class="form-label small text-muted">Descuento manual</label>
              <div class="input-group input-group-sm mb-2">
                <input type="number" id="fixed-discount" class="form-control" placeholder="Valor" min="0" step=".01">
                <button class="btn btn-outline-secondary discount-type-btn active" data-type="fixed" type="button">$</button>
                <button class="btn btn-outline-secondary discount-type-btn" data-type="percentage" type="button">%</button>
                <button class="btn btn-outline-primary" type="button" id="apply-fixed-btn">OK</button>
              </div>
              <input type="hidden" id="discount-type-value" value="fixed">
              <button class="btn btn-sm btn-outline-danger w-100 mt-2" id="quitarDescuento" style="display:none;">
                <i class="bx bx-x me-1"></i>Quitar descuento
              </button>
            </div>
          </div>
        </div>
      </div>

      {{-- Payment method --}}
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <h6 class="mb-3"><i class="bx bx-wallet me-1"></i> Método de pago</h6>
          <div class="payment-pills">
            <div class="payment-pill">
              <input type="radio" name="paymentMethod" id="cash" checked>
              <label for="cash"><i class="bx bx-money"></i> Efectivo</label>
            </div>
            <div class="payment-pill">
              <input type="radio" name="paymentMethod" id="debit">
              <label for="debit"><i class="bx bx-credit-card"></i> Débito</label>
            </div>
            <div class="payment-pill">
              <input type="radio" name="paymentMethod" id="credit">
              <label for="credit"><i class="bx bx-credit-card-front"></i> Crédito</label>
            </div>
            <div class="payment-pill">
              <input type="radio" name="paymentMethod" id="internalCredit">
              <label for="internalCredit"><i class="bx bx-transfer"></i> Crédito Int.</label>
            </div>
          </div>
          {{-- Cash details --}}
          <div id="cashDetails" class="mt-3">
            <label class="form-label small text-muted">Valor recibido</label>
            <input type="number" id="valorRecibido" min="0" step=".01" class="form-control form-control-lg mb-2" placeholder="0.00">
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-muted">Vuelto:</span>
              <span id="vuelto" class="fw-bold fs-5">{{ $currencySymbol }}0</span>
            </div>
            <small id="mensajeError" class="text-danger d-none">El valor recibido es insuficiente.</small>
          </div>
        </div>
      </div>

      {{-- Shipping status --}}
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <h6 class="mb-3"><i class="bx bx-package me-1"></i> Entrega</h6>
          <select id="shippingStatus" class="form-select form-select-sm">
            <option value="delivered">Entregado</option>
            <option value="shipped">Enviado</option>
            <option value="pending">Pendiente</option>
          </select>
        </div>
      </div>

      {{-- Action buttons (desktop) --}}
      <div class="d-none d-lg-flex gap-2">
        <a href="{{ route('pdv.front') }}" id="descartarVentaBtn" class="btn btn-outline-danger">
          <i class="bx bx-x me-1"></i>Descartar
        </a>
        <button class="btn btn-success flex-grow-1" id="finalizarVentaBtn">
          <i class="bx bx-check me-1"></i> Finalizar venta
        </button>
      </div>

      {{-- Transaction status --}}
      <div id="transaction-status" style="display:none;" class="mt-3">
        <div class="spinner-border text-primary" role="status" id="transaction-spinner" style="display:none;">
          <span class="sr-only">Procesando...</span>
        </div>
        <div id="transaction-message" class="mt-3"></div>
      </div>
    </div>
  </div>
</div>

{{-- Mobile bottom bar --}}
<div class="mobile-finalize-bar">
  <div>
    <small class="text-muted">Total</small>
    <div class="total fw-bold fs-5">{{ $currencySymbol }}0.00</div>
  </div>
  <button class="btn btn-success" id="finalizarVentaMobileBtn">
    <i class="bx bx-check me-1"></i> Finalizar
  </button>
</div>

{{-- ===================== OFFCANVAS: Seleccionar Cliente ===================== --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasEndLabel" class="offcanvas-title">Seleccionar Cliente</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body d-flex flex-column">
    <div class="d-flex flex-column align-items-start mb-3">
      <p class="text-center w-100">Selecciona un cliente o crea uno nuevo.</p>
      <button type="button" class="btn btn-primary mb-2 d-grid w-100" data-bs-toggle="offcanvas" data-bs-target="#crearClienteOffcanvas">
        <i class="bx bx-plus me-1"></i> Crear Cliente
      </button>
      <div id="search-client-container" class="w-100" style="display:none;">
        <input type="search" class="form-control" id="search-client" placeholder="Nombre, Razón Social, CI, RUT...">
      </div>
    </div>
    <ul id="client-list" class="list-group flex-grow-1 overflow-auto"></ul>
  </div>
</div>

{{-- ===================== OFFCANVAS: Crear Cliente ===================== --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="crearClienteOffcanvas" aria-labelledby="crearClienteOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 id="crearClienteOffcanvasLabel" class="offcanvas-title">Crear Cliente</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form id="formCrearCliente">
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
      <div class="mb-3" id="ciField">
        <label for="ciCliente" class="form-label">CI</label>
        <input type="text" class="form-control" id="ciCliente" placeholder="Ingrese el CI">
      </div>
      <div class="mb-3" id="razonSocialField" style="display:none;">
        <label for="razonSocialCliente" class="form-label">Razón Social <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="razonSocialCliente" placeholder="Ingrese la razón social">
      </div>
      <div class="mb-3" id="rutField" style="display:none;">
        <label for="rutCliente" class="form-label">RUT <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="rutCliente" placeholder="Ingrese el RUT">
      </div>
      <div class="mb-3">
        <label for="direccionCliente" class="form-label">Dirección</label>
        <input type="text" class="form-control" id="direccionCliente" placeholder="Ingrese la dirección">
      </div>
      <div class="mb-3">
        <label for="emailCliente" class="form-label">Correo Electrónico</label>
        <input type="email" class="form-control" id="emailCliente" placeholder="Ingrese el correo electrónico">
      </div>
      <button type="button" class="btn btn-primary w-100" id="guardarCliente">
        <i class="bx bx-save me-1"></i> Guardar
      </button>
    </form>
  </div>
</div>

@endsection

@section('vendor-script')
@vite([
    'resources/assets/js/pdvCheckout.js'
])
@endsection
