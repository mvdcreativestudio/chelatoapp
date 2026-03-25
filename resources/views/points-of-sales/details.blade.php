@extends('layouts.layoutMaster')

@section('title', 'Detalles de la Caja Registradora')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('page-script')
@vite(['resources/assets/js/app-cash-register-details-list.js'])
<script>
  window.currency_symbol = '{{ $settings->currency_symbol ?? "$" }}';
</script>
@endsection

@section('content')
<div class="container-fluid">
    @hasrole('Administrador')
    <!-- Header -->
    <div class="card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
              <div class="avatar avatar-lg me-3">
                  <span class="avatar-initial rounded-circle bg-label-info">
                      <i class="bx bx-user"></i>
                  </span>
              </div>
              <div>
                  <h4 class="mb-1">
                      Caja Registradora: {{ $cashRegister->store->name ?? 'N/A' }} - {{ $cashRegister->user->name ?? 'N/A' }}
                  </h4>
                  <p class="mb-0 text-muted">
                      Operada por: <strong>{{ $cashRegister->user->name ?? 'N/A' }}</strong>
                  </p>
              </div>
          </div>

          <div class="d-flex gap-2">
            <!-- Botón de Filtros -->
            <button id="openFilters" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1">
                <i class="bx bx-filter-alt"></i> Filtros
            </button>

            <button id="clearFiltersBtn" class="btn btn-outline-danger btn-sm shadow-sm d-flex align-items-center gap-1 d-none">
              <i class="bx bx-x"></i> Limpiar Filtros
            </button>

            <!-- Botones para cambiar vista -->
            <div class="btn-group">
                <button id="btnViewGrid" class="btn btn-sm btn-outline-primary active">
                    <i class="bx bx-grid-alt"></i> Tarjetas
                </button>
                <button id="btnViewList" class="btn btn-sm btn-outline-primary">
                    <i class="bx bx-list-ul"></i> Lista
                </button>
            </div>
          </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-sm-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Registros</p>
                            <h3 class="mb-0">{{ $details->count() }}</h3>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-time fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">{{ $openCount == 1 ? 'Caja abierta' : 'Cajas abiertas' }}</p>
                            <h3 class="mb-0" id="openCount">{{ $openCount }}</h3>
                        </div>
                        <div class="avatar bg-label-success rounded p-2">
                            <i class="fas fa-cash-register fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">{{ $closedCount <= 1 ? 'Caja cerrada' : 'Cajas cerradas' }}</p>
                            <h3 class="mb-0" id="closedCount">{{ $closedCount }}</h3>
                        </div>
                        <div class="avatar bg-label-danger rounded p-2">
                            <i class="fas fa-lock fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor para ambas vistas -->
    <div id="order-list-container" class="view-grid">
      <!-- Vista en Cards -->
      <div id="viewGrid">
          <div class="row g-4">
              @forelse($details as $detail)
                  <div class="col-md-6 col-lg-4 col-xl-4 clients-card-container">
                      <div class="clients-card position-relative">
                          <div class="card-header bottom p-3">
                              <div class="d-flex justify-content-between align-items-center">
                                  <div class="d-flex align-items-center gap-2">
                                      <div class="avatar avatar-sm">
                                          <span class="avatar-initial rounded-circle {{ is_null($detail->close_time) ? 'bg-label-success' : 'bg-label-danger' }}">
                                              <i class="bx {{ is_null($detail->close_time) ? 'bx-lock-open' : 'bx-lock' }}"></i>
                                          </span>
                                      </div>
                                      <h6 class="mb-0">{{ $detail->name ? ucwords($detail->name) : 'Registro #' . $detail->id }}</h6>
                                  </div>
                                  <div class="d-flex align-items-center">
                                      <span class="badge {{ is_null($detail->close_time) ? 'bg-success' : 'bg-danger' }}">
                                          {{ is_null($detail->close_time) ? 'ABIERTA' : 'CERRADA' }}
                                      </span>
                                      <div class="clients-card-toggle ms-2" style="cursor:pointer;">
                                          <i class="bx bx-chevron-down fs-3"></i>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <!-- Contenido desplegable -->
                          <div class="clients-card-body" style="display: none;">
                              <div class="card-body p-3">
                                  <div class="mb-3">
                                      <div class="d-flex align-items-center gap-2 mb-2">
                                          <i class="bx bx-calendar text-muted"></i>
                                          <span class="text-muted">Apertura: {{ \Carbon\Carbon::parse($detail->open_time)->translatedFormat('d \d\e F Y') }}</span>
                                      </div>
                                      <div class="d-flex align-items-center gap-2 mb-2">
                                          <i class="bx bx-time text-muted"></i>
                                          <span class="text-muted">{{ \Carbon\Carbon::parse($detail->open_time)->format('h:i a') }}</span>
                                      </div>
                                      <div class="d-flex align-items-center gap-2 mb-2">
                                          <i class="bx bx-calendar text-muted"></i>
                                          <span class="text-muted">Cierre: {{ $detail->close_time ? \Carbon\Carbon::parse($detail->close_time)->translatedFormat('d \d\e F Y') : 'Pendiente' }}</span>
                                      </div>
                                      <div class="d-flex align-items-center gap-2 mb-2">
                                          <i class="bx bx-time text-muted"></i>
                                          <span class="text-muted">{{ $detail->close_time ? \Carbon\Carbon::parse($detail->close_time)->format('h:i a') : '-' }}</span>
                                      </div>
                                      <hr class="my-2">
                                      <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-calculator text-muted"></i>
                                        <span class="text-muted">Fondo de caja: {{ $settings->currency_symbol }}{{ number_format($detail->cash_float ?? 0, 0, ',', '.') }}</span>
                                      </div>
                                      <div class="d-flex align-items-center gap-2 mb-2 mt-3">
                                        <span class="text-muted fw-semibold">VENTAS POR METODO:</span>
                                      </div>
                                      <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-money text-success"></i>
                                        <span class="text-muted">Efectivo: {{ $settings->currency_symbol }}{{ number_format($detail->getCurrentCashSales(), 0, ',', '.') }}</span>
                                      </div>
                                      <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-credit-card-alt text-info"></i>
                                        <span class="text-muted">POS (Cred/Deb): {{ $settings->currency_symbol }}{{ number_format($detail->getCurrentPosSales(), 0, ',', '.') }}</span>
                                      </div>
                                      <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bxl-paypal text-primary"></i>
                                        <span class="text-muted">Mercadopago: {{ $settings->currency_symbol }}{{ number_format($detail->getCurrentMercadopagoSales(), 0, ',', '.') }}</span>
                                      </div>
                                      <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-transfer text-warning"></i>
                                        <span class="text-muted">Transferencias: {{ $settings->currency_symbol }}{{ number_format($detail->getCurrentBankTransferSales(), 0, ',', '.') }}</span>
                                      </div>
                                      <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-notepad text-secondary"></i>
                                        <span class="text-muted">Cuenta corriente: {{ $settings->currency_symbol }}{{ number_format($detail->getCurrentInternalCreditSales(), 0, ',', '.') }}</span>
                                      </div>
                                      <hr class="my-2">
                                      <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-wallet text-danger"></i>
                                        <span class="text-muted">Gastos: {{ $settings->currency_symbol }}{{ number_format($detail->total_expenses ?? 0, 0, ',', '.') }}</span>
                                      </div>
                                      @if($detail->close_time && $detail->actual_cash !== null)
                                      <hr class="my-2">
                                      <div class="d-flex align-items-center gap-2 mb-2 mt-3">
                                        <span class="text-muted fw-semibold">VERIFICACION DE EFECTIVO:</span>
                                      </div>
                                      <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-calculator text-primary"></i>
                                        <span class="text-muted">Efectivo esperado: {{ $settings->currency_symbol }}{{ number_format($detail->getFinalCashBalance(), 0, ',', '.') }}</span>
                                      </div>
                                      <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-money text-info"></i>
                                        <span class="text-muted">Efectivo contado: {{ $settings->currency_symbol }}{{ number_format($detail->actual_cash, 0, ',', '.') }}</span>
                                      </div>
                                      @if($detail->cash_difference != 0)
                                      <div class="d-flex align-items-center gap-2 mb-2">
                                        @if($detail->cash_difference > 0)
                                          <i class="bx bx-up-arrow-alt text-warning"></i>
                                          <span class="text-warning fw-semibold">Sobrante: {{ $settings->currency_symbol }}{{ number_format(abs($detail->cash_difference), 0, ',', '.') }}</span>
                                        @else
                                          <i class="bx bx-down-arrow-alt text-danger"></i>
                                          <span class="text-danger fw-semibold">Faltante: {{ $settings->currency_symbol }}{{ number_format(abs($detail->cash_difference), 0, ',', '.') }}</span>
                                        @endif
                                      </div>
                                      @else
                                      <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-check-circle text-success"></i>
                                        <span class="text-success fw-semibold">Cuadra perfecto</span>
                                      </div>
                                      @endif
                                      @endif
                                  </div>
                                  <div class="card-footer bg-light border-top pt-3">
                                      <div class="d-flex justify-content-between align-items-center">
                                          <button class="btn btn-sm btn-primary btn-view-sales" data-id="{{ $detail->id }}">
                                              Ver Ventas <i class='bx bx-right-arrow-alt'></i>
                                          </button>
                                          <div class="text-end">
                                              <small class="text-muted d-block">Efectivo final en caja:</small>
                                              <h5 class="mb-0 fw-semibold">
                                                  @if($detail->close_time && $detail->actual_cash !== null)
                                                      {{ $settings->currency_symbol }}{{ number_format($detail->actual_cash, 0, ',', '.') }}
                                                  @else
                                                      {{ $settings->currency_symbol }}{{ $detail->getFormattedFinalCashBalance() }}
                                                  @endif
                                              </h5>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              @empty
                  <div class="col-12 text-center py-5">
                      <h5>No hay registros</h5>
                      <p class="text-muted">No se encontraron registros de caja para mostrar</p>
                  </div>
              @endforelse
          </div>
      </div>

      <!-- Vista en Lista -->
      <div id="viewList" class="d-none">
          <table class="table table-striped">
              <thead>
                  <tr>
                      <th>ID</th>
                      <th>Nombre</th>
                      <th>Estado</th>
                      <th>Apertura</th>
                      <th>Cierre</th>
                      <th>Ventas</th>
                      <th>Acciones</th>
                  </tr>
              </thead>
              <tbody>
                  @forelse($details as $detail)
                      <tr>
                          <td>{{ $detail->id }}</td>
                          <td>{{ $detail->name ? ucwords($detail->name) : 'Registro #' . $detail->id }}</td>
                          <td>
                              <span class="badge {{ is_null($detail->close_time) ? 'bg-success' : 'bg-danger' }}">
                                  {{ is_null($detail->close_time) ? 'ABIERTA' : 'CERRADA' }}
                              </span>
                          </td>
                          <td>{{ \Carbon\Carbon::parse($detail->open_time)->translatedFormat('d \d\e F Y h:i a') }}</td>
                          <td>{{ $detail->close_time ? \Carbon\Carbon::parse($detail->close_time)->translatedFormat('d \d\e F Y h:i a') : 'Pendiente' }}</td>
                          <td>
                              <div class="text-end">
                                  <div class="mb-1"><small class="text-muted">Efectivo:</small> <strong>{{ $settings->currency_symbol }}{{ number_format($detail->getCurrentCashSales(), 0, ',', '.') }}</strong></div>
                                  <div class="mb-1"><small class="text-muted">POS:</small> <strong>{{ $settings->currency_symbol }}{{ number_format($detail->getCurrentPosSales(), 0, ',', '.') }}</strong></div>
                                  <div class="mb-1"><small class="text-muted">MP:</small> <strong>{{ $settings->currency_symbol }}{{ number_format($detail->getCurrentMercadopagoSales(), 0, ',', '.') }}</strong></div>
                                  <div class="mb-1"><small class="text-muted">Transf:</small> <strong>{{ $settings->currency_symbol }}{{ number_format($detail->getCurrentBankTransferSales(), 0, ',', '.') }}</strong></div>
                                  <div class="mb-1"><small class="text-muted">Cta.Cte:</small> <strong>{{ $settings->currency_symbol }}{{ number_format($detail->getCurrentInternalCreditSales(), 0, ',', '.') }}</strong></div>
                                  <div class="border-top pt-1 mt-1"><small class="text-muted">Total:</small> <strong class="text-primary">{{ $settings->currency_symbol }}{{ number_format($detail->getTotalSales(), 0, ',', '.') }}</strong></div>
                              </div>
                          </td>
                          <td>
                              <button class="btn btn-primary btn-sm btn-view-sales" data-id="{{ $detail->id }}">
                                  Ver Ventas <i class='bx bx-right-arrow-alt'></i>
                              </button>
                          </td>
                      </tr>
                  @empty
                      <tr>
                          <td colspan="7" class="text-center">No hay registros disponibles</td>
                      </tr>
                  @endforelse
              </tbody>
          </table>
      </div>
    </div>

    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bx bx-lock-alt text-danger mb-3" style="font-size: 3rem;"></i>
            <h4>Acceso Restringido</h4>
            <p class="text-muted">No tienes permisos para acceder a esta seccion.</p>
        </div>
    </div>
    @endhasrole
</div>

<!-- Modal de Filtros -->
<div id="filterModal" class="filter-modal">
  <div class="filter-modal-content">
      <button id="closeFilterModal" class="close-filter-modal">
          <i class="bx bx-x"></i>
      </button>

      <h5 class="mb-4">Filtros</h5>

      <div class="mb-3">
          <label for="statusFilter">Estado de Caja</label>
          <select id="statusFilter" class="form-select">
              <option value="">Todos</option>
              <option value="open">Abierta</option>
              <option value="closed">Cerrada</option>
          </select>
      </div>

      <div class="mb-3">
          <label for="startDate">Desde:</label>
          <input type="date" class="form-control date-range-filter" id="startDate">
      </div>
      <div class="mb-3">
          <label for="endDate">Hasta:</label>
          <input type="date" class="form-control date-range-filter" id="endDate">
      </div>

      <div class="mb-3">
          <button id="applyFilters" class="btn btn-primary w-100">
              Aplicar Filtros
          </button>
      </div>
  </div>
</div>

<style>
.filter-modal {
    position: fixed;
    top: 0;
    right: -300px;
    width: 300px;
    height: 100%;
    background: #fff;
    box-shadow: -2px 0 10px rgba(0, 0, 0, 0.2);
    z-index: 2000;
    transition: right 0.3s ease-in-out;
    overflow-y: auto;
}
.filter-modal.open { right: 0; }
.filter-modal-content { padding: 20px; }
.close-filter-modal {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
}
</style>
@endsection
