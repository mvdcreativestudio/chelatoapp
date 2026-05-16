@extends('layouts.layoutMaster')

@section('title', 'PDV - MVD')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/toastr/toastr.scss',
'resources/assets/vendor/libs/animate-css/animate.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
<style>
  /* ============ PDV Front (catálogo) ============ */
  .pdv-front-wrap { padding: 0 .25rem; }

  .pdv-header {
    background: #fff;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    position: sticky;
    top: 0;
    z-index: 100;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
  }
  .pdv-header__title {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin: 0;
    font-weight: 700;
    color: #2c2c43;
    white-space: nowrap;
  }
  .pdv-header__title i { color: var(--bs-primary, #7367f0); }
  .pdv-header__search {
    flex: 1;
    min-width: 220px;
    position: relative;
  }
  .pdv-header__search .form-control {
    height: 42px;
    padding-left: 2.5rem;
    border-radius: 10px;
    border-color: #e6e6f0;
    background: #fafbfd;
    font-size: .9rem;
  }
  .pdv-header__search .form-control:focus { background: #fff; }
  .pdv-header__search i {
    position: absolute;
    left: .9rem;
    top: 50%;
    transform: translateY(-50%);
    color: #888;
    font-size: 1.2rem;
  }
  .pdv-header__actions {
    display: flex;
    gap: .5rem;
    align-items: center;
    flex-wrap: wrap;
  }
  .pdv-header__actions .btn {
    border-radius: 10px;
    font-size: .82rem;
    font-weight: 600;
    padding: .5rem .9rem;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
  }

  /* Product cards */
  #products-container .card-product-pos {
    transition: transform .15s ease, box-shadow .15s ease;
  }
  #products-container .card-product-pos:hover {
    transform: translateY(-2px);
  }
  #products-container .product-card-hover {
    border: 1px solid #ececf2;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    transition: all .15s ease;
  }
  #products-container .product-card-hover:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,.08);
    border-color: #d8d4ff;
  }
  #products-container .card-img-top {
    background: #f5f5f9;
  }
  #products-container .card-title {
    font-size: .92rem;
    font-weight: 600;
    margin-bottom: .35rem;
    line-height: 1.25;
  }
  #products-container .add-to-cart {
    border-radius: 8px;
    font-weight: 600;
    font-size: .85rem;
  }

  /* List view */
  #products-container .list-group-item {
    border-radius: 10px !important;
    border: 1px solid #ececf2;
    margin-bottom: .5rem;
    transition: box-shadow .15s ease;
  }
  #products-container .list-group-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,.05); }

  /* FAB cart */
  .pdv-fab {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 1040;
    border-radius: 32px;
    padding: .85rem 1.4rem;
    font-weight: 700;
    box-shadow: 0 6px 20px rgba(40,167,69,.35);
    display: flex;
    align-items: center;
    gap: .5rem;
    font-size: .95rem;
  }
  .pdv-fab .badge {
    background: #fff;
    color: var(--bs-success, #28a745);
    font-weight: 700;
    padding: .25rem .5rem;
  }
  .pdv-fab.disabled, .pdv-fab[aria-disabled="true"] {
    pointer-events: none;
    opacity: .5;
  }

  /* Quantity input pill */
  .pdv-qty-pill {
    display: inline-flex;
    align-items: center;
    border: 1px solid #e0e0e8;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
  }
  .pdv-qty-pill .btn {
    border: 0;
    background: transparent;
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #555;
  }
  .pdv-qty-pill .btn:hover { background: #f4f4fa; }
  .pdv-qty-pill .form-control {
    border: 0;
    border-left: 1px solid #e0e0e8;
    border-right: 1px solid #e0e0e8;
    height: 32px;
    width: 44px;
    text-align: center;
    padding: 0;
    border-radius: 0;
    font-weight: 600;
    font-size: .85rem;
  }

  /* Cart modal items */
  .pdv-cart-line {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: .75rem 0;
    border-bottom: 1px solid #f0f0f5;
  }
  .pdv-cart-line:last-child { border-bottom: 0; }
  .pdv-cart-line__img {
    width: 56px;
    height: 56px;
    object-fit: cover;
    border-radius: 10px;
    background: #f5f5f9;
    flex-shrink: 0;
  }
  .pdv-cart-line__info { flex: 1; min-width: 0; }
  .pdv-cart-line__name {
    font-weight: 600;
    font-size: .9rem;
    margin-bottom: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .pdv-cart-line__price { color: #888; font-size: .8rem; }
  .pdv-cart-line__total {
    font-weight: 700;
    min-width: 80px;
    text-align: right;
    white-space: nowrap;
  }
  .pdv-cart-line__remove {
    background: transparent;
    border: 0;
    color: #d44;
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
  }
  .pdv-cart-line__remove:hover { background: #fdeaea; }

  .pdv-totals-card {
    background: #f8f8fc;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-top: 1rem;
  }
  .pdv-totals-card .row-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: .35rem;
  }
  .pdv-totals-card .row-line.is-total {
    border-top: 1px solid #e6e6f0;
    padding-top: .65rem;
    margin-top: .5rem;
    margin-bottom: 0;
  }

  .pdv-totals-card .row-line.is-total h4 { margin: 0; font-weight: 700; }
</style>
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/toastr/toastr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
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
<div class="pdv-front-wrap animate__animated animate__fadeIn">
  <div id="errorContainer" class="alert alert-danger d-none" role="alert"></div>

  {{-- Header sticky --}}
  <div class="pdv-header">
    <h4 class="pdv-header__title">
      <i class="bx bx-store-alt"></i> Punto de Venta
    </h4>

    <div class="pdv-header__search">
      <i class="bx bx-search"></i>
      <input class="form-control" type="search" placeholder="Buscar por nombre o código..." id="html5-search-input" autofocus />
    </div>

    <div class="pdv-header__actions">
      <button id="toggle-view-btn" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Cambiar vista">
        <i class="bx bx-list-ul"></i>
      </button>
      <button id="registrar-egreso-btn" class="btn btn-outline-info">
        <i class="bx bx-money-withdraw"></i><span class="d-none d-md-inline">Egreso</span>
      </button>
      <button type="button" id="btn-show-close-modal" class="btn btn-outline-danger">
        <i class="bx bx-lock-alt"></i><span class="d-none d-md-inline">Cerrar Caja</span>
      </button>
    </div>
  </div>

  {{-- Grid de productos --}}
  <div class="row d-flex flex-wrap" id="products-container">
    {{-- Cargados dinámicamente desde pdv.js --}}
  </div>
</div>

{{-- FAB carrito --}}
<a id="view-cart-btn"
   href="javascript:void(0)"
   class="btn btn-success pdv-fab animate__animated animate__fadeInUp"
   data-bs-toggle="modal" data-bs-target="#cartModal">
  <i class="bx bx-cart fs-5"></i>
  <span>Continuar</span>
  <span id="cart-count" class="badge">0</span>
</a>

<!-- ===================== Modal carrito ===================== -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-light border-0 py-3">
        <h5 class="modal-title d-flex align-items-center gap-2" id="cartModalLabel">
          <i class="bx bx-cart"></i> Resumen de la venta
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {{-- Items del carrito (dinámicos) --}}
        <div id="cart-items" class="row gy-3">
          {{-- pdv.js renderiza aquí --}}
        </div>

        {{-- Totales --}}
        <div class="pdv-totals-card ms-auto" style="max-width: 380px;">
          <div class="row-line">
            <span class="text-muted">Subtotal</span>
            <span class="subtotal fw-semibold text-primary">{{ $currencySymbol }}0</span>
          </div>
          <div class="row-line is-total">
            <h4 class="text-dark">Total</h4>
            <h4 class="total text-dark">{{ $currencySymbol }}0</h4>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">
          <i class="bx bx-x me-1"></i> Cerrar
        </button>
        <a href="{{ route('pdv.front2') }}" class="btn btn-primary disabled" id="finalizarVentaBtn" aria-disabled="true" tabindex="-1">
          <i class="bx bx-check me-1"></i> Finalizar Venta
        </a>
      </div>
    </div>
  </div>
</div>


<!-- ===================== Offcanvas Crear Cliente (compat con pdv.js) ===================== -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="crearClienteOffcanvas"
  aria-labelledby="crearClienteOffcanvasLabel">
  <div class="offcanvas-header border-bottom">
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
        <input type="text" class="form-control" id="nombreCliente" required>
      </div>
      <div class="mb-3">
        <label for="apellidoCliente" class="form-label">Apellido</label>
        <input type="text" class="form-control" id="apellidoCliente">
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
        <input type="email" class="form-control" id="emailCliente">
      </div>
      <button type="button" class="btn btn-primary w-100" id="guardarCliente">
        <i class="bx bx-save me-1"></i> Guardar
      </button>
    </form>
  </div>
</div>

<!-- ===================== Modal Variaciones (sabores) ===================== -->
<div class="modal fade" id="flavorModal" tabindex="-1" aria-labelledby="flavorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-light border-0">
        <h5 class="modal-title d-flex align-items-center gap-2" id="flavorModalLabel">
          <i class="bx bx-palette"></i> Seleccionar Variaciones
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="flavorsContainer" class="mb-3 col-12">
          <label class="form-label fw-semibold">Variaciones disponibles</label>
          <select id="flavorsSelect" class="select2 form-select variationOptions" multiple="multiple" name="flavors[]">
          </select>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" id="saveFlavors" class="btn btn-primary">
          <i class="bx bx-check me-1"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ===================== Modal Cerrar Caja ===================== -->
<div class="modal fade" id="closeCashRegisterModal" tabindex="-1" aria-labelledby="closeCashRegisterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white border-0">
        <h5 class="modal-title d-flex align-items-center gap-2" id="closeCashRegisterModalLabel">
          <i class="bx bx-lock"></i> Cerrar Caja Registradora
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="cash-register-details" class="p-3">
          <div class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Cargando...</span>
            </div>
          </div>
        </div>

        {{-- Confirmación efectivo --}}
        <div id="cash-confirmation-section" class="mb-4 px-3" style="display: none;">
          <div class="alert alert-info d-flex align-items-start gap-2">
            <i class="bx bx-info-circle fs-5"></i>
            <div>
              <strong>Verificación de Efectivo:</strong> Cuente el efectivo físico en caja e ingrese el monto exacto.
            </div>
          </div>

          <div class="row justify-content-center">
            <div class="col-md-8">
              <label for="actual_cash" class="form-label fw-bold">Efectivo contado en caja:</label>
              <div class="input-group input-group-lg">
                <span class="input-group-text">{{ $settings->currency_symbol ?? '$' }}</span>
                <input type="number" id="actual_cash" class="form-control" step="0.01" min="0" placeholder="0.00">
              </div>
            </div>
          </div>

          <div id="verification-result" class="mt-4" style="display: none;">
            <hr>
            <div class="row">
              <div class="col-md-6 mb-2">
                <div class="card border-primary">
                  <div class="card-body text-center py-3">
                    <small class="text-muted text-uppercase">Esperado</small>
                    <h4 class="mb-0 text-primary mt-1" id="expected-cash-display">{{ $settings->currency_symbol ?? '$' }}0</h4>
                  </div>
                </div>
              </div>
              <div class="col-md-6 mb-2">
                <div class="card border-info">
                  <div class="card-body text-center py-3">
                    <small class="text-muted text-uppercase">Contado</small>
                    <h4 class="mb-0 text-info mt-1" id="actual-cash-display">{{ $settings->currency_symbol ?? '$' }}0</h4>
                  </div>
                </div>
              </div>
            </div>

            <div id="cash-difference-alert" class="alert mt-3 d-flex align-items-start gap-2" style="display: none;">
              <i class="bx bx-error-circle fs-5"></i>
              <span id="cash-difference-message"></span>
            </div>
          </div>

          <input type="hidden" id="expected_cash">
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="verify-cash-btn" class="btn btn-primary" style="display: none;">
          <i class="bx bx-check me-1"></i> Verificar Efectivo
        </button>
        <button type="button" id="submit-cerrar-caja" class="btn btn-outline-danger" style="display: none;">
          <i class="bx bx-lock-alt me-1"></i> Cerrar Caja
        </button>
        <button type="button" id="force-close-btn" class="btn btn-warning" style="display: none;">
          <i class="bx bx-error me-1"></i> Cerrar con Diferencia
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ===================== Modal Egreso ===================== -->
<div class="modal fade" id="registrarEgresoModal" tabindex="-1" aria-labelledby="registrarEgresoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-light border-0">
        <h5 class="modal-title d-flex align-items-center gap-2" id="registrarEgresoLabel">
          <i class="bx bx-money-withdraw"></i> Registrar Egreso de Caja
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="registrarEgresoForm">
          <div class="mb-3">
            <label for="egreso_concepto" class="form-label">Concepto <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="egreso_concepto" required placeholder="Concepto del egreso">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="egreso_monto" class="form-label">Monto <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="egreso_monto" required step="0.01" min="0.01" placeholder="0.00">
            </div>
            <div class="col-md-6 mb-3">
              <label for="egreso_currency" class="form-label">Moneda <span class="text-danger">*</span></label>
              <select class="form-select" id="egreso_currency" required>
                <option value="Peso" selected>Peso</option>
                <option value="Dolar">Dólar</option>
              </select>
            </div>
          </div>
          <div class="mb-3" id="egreso_currency_rate_field" style="display: none;">
            <label for="egreso_currency_rate" class="form-label">Cotización <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="egreso_currency_rate" step="0.01" value="0">
          </div>
          <div class="mb-3">
            <label for="egreso_categoria" class="form-label">Categoría</label>
            <select class="form-select" id="egreso_categoria">
              <option value="" selected>Sin categoría</option>
              @foreach(\App\Models\ExpenseCategory::all() as $categoria)
              <option value="{{ $categoria->id }}">{{ $categoria->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="egreso_observaciones" class="form-label">Observaciones</label>
            <textarea id="egreso_observaciones" class="form-control" rows="2" placeholder="Observaciones adicionales"></textarea>
          </div>
          <input type="hidden" id="egreso_cash_register_log_id">
        </form>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="submit-registrar-egreso" class="btn btn-primary">
          <i class="bx bx-save me-1"></i> Guardar Egreso
        </button>
      </div>
    </div>
  </div>
</div>

@endsection
@else

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height: 60vh;">
  <div class="text-center">
    <div class="mb-3">
      <i class="bx bx-lock-alt" style="font-size: 4rem; color: #ccc;"></i>
    </div>
    <h4 class="mb-2">Caja cerrada</h4>
    <p class="text-muted mb-3">Para realizar ventas, primero abrí una caja registradora.</p>
    <a href="/admin/points-of-sales" class="btn btn-primary">
      <i class="bx bx-lock-open-alt me-1"></i> Abrir caja
    </a>
  </div>
</div>
@endsection

@endif
