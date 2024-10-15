@extends('layouts/layoutMaster')

@section('title', 'Pedidos')

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
@endsection

@section('page-script')
@vite([
  'resources/assets/js/app-orders-list.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light"></span>Ventas
</h4>

@if (Auth::user()->can('access_datacenter'))
<div class="card mb-4">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gy-4 gy-sm-1">
        <div class="col-sm-6 col-lg-6">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Total de Ventas</h6>
              <h4 class="mb-2">{{ $totalOrders }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Total</span></p>
            </div>
            <div class="avatar me-sm-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-receipt bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none me-4">
        </div>
        <div class="col-sm-6 col-lg-6">
          <div class="d-flex justify-content-between align-items-start card-widget-2 pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Ingresos Totales</h6>
              <h4 class="mb-2">{{ $settings->currency_symbol }} {{ number_format($totalIncome, 2) }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Total</span></p>
            </div>
            <div class="avatar me-lg-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-dollar bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none">
        </div>
        {{-- <div class="col-sm-6 col-lg-3">
          <div class="d-flex justify-content-between align-items-start border-end pb-3 pb-sm-0 card-widget-3">
            <div>
              <h6 class="mb-2">Pedidos Pendientes</h6>
              <h4 class="mb-2">{{ $pendingOrders }}</h4>
              <p class="mb-0 text-muted">Total Pendientes</p>
            </div>
            <div class="avatar me-sm-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-hourglass bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none">
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-2">Pedidos Completados</h6>
              <h4 class="mb-2">{{ $completedOrders }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Total Completados</span></p>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-check bx-sm"></i>
              </span>
            </div>
          </div>
        </div> --}}
      </div>
    </div>
  </div>
</div>
@endif

<!-- Order List Table -->
<div class="card">
  <div class="card-datatable table-responsive">
  <div class="card-header">
  <h5 class="card-title">Ventas</h5>
    <div class="d-flex">
        <p class="text-muted small">
          <a href="" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches" aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
        </p>
      </div>
      <div class="collapse" id="columnSwitches">
      <div class="mt-0 d-flex flex-wrap">
        <!-- Selectores de columnas -->
        <div class="mx-3">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="1" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">Fecha</span>
          </label>
        </div>
        <div class="mx-3">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="2" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">Cliente</span>
          </label>
        </div>
        <div class="mx-3">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="3" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">Empresa</span>
          </label>
        </div>
        <div class="mx-3">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="4" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">Importe</span>
          </label>
        </div>
        <div class="mx-3">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="5" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">Pago</span>
          </label>
        </div>
        <div class="mx-3">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="6" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">Facturado</span>
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
    </div>

    {{-- filters --}}
    <div class="d-flex justify-content-start align-items-center row py-3 gap-3 mb-0 pb-0 gap-md-0">
      <div class="col-md-2 client_filter">
        <label for="client">Cliente</label>
      </div>
      <div class="col-md-2 company_filter">
        <label for="company">Empresa</label>
      </div>
      <div class="col-md-2 payment_filter">
        <label for="payment">Pago</label>
      </div>
      <div class="col-md-2 billed_filter">
        <label for="billed">Facturado</label>
      </div>
      <div class="col-md-2">
        <label for="startDate">Fecha Desde</label>
        <input type="date" class="form-control date-range-filter" id="startDate" placeholder="Fecha de inicio">
      </div>
      <div class="col-md-2">
        <label for="endDate">Fecha Hasta</label>
        <input type="date" class="form-control date-range-filter" id="endDate" placeholder="Fecha de fin">
      </div>
      <div class="col-md-2 d-flex flex-column mt-2">
        <button class="btn btn-outline-danger btn-sm clear-filters w-100 mb-2" id="clear-filters">
          <i class="fas fa-eraser"></i> Limpiar Filtros
        </button>
        <button class="btn btn-outline-success btn-sm export-excel w-100" id="export-excel">
          <i class="fas fa-file-excel"></i> Exportar a Excel
        </button>
      </div>
    </div>
  </div>
    <table class="datatables-order table border-top" data-symbol="{{ $settings->currency_symbol }}">
      <thead>
        <tr>
          <th>N°</th>
          <th>Fecha</th>
          <th>Cliente</th>
          <th>Empresa</th>
          <th>Importe</th>
          <th>Pago</th>
          <th>Facturado</th>
          <th>Acciones</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

@endsection
