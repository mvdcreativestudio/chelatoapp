@extends('layouts/layoutMaster')

@section('title', 'Cuentas Corrientes')

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
</script>
@endsection

@section('page-script')
@vite([
'resources/assets/js/current-accounts/app-current-account-list.js',
'resources/assets/js/current-accounts/app-current-account-delete.js',
])
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    
  <h4 class=" mb-2">
    <span class="text-muted fw-light">Contabilidad /</span> Cuentas Corrientes
  </h4>
  <a href="{{ route('current-accounts.create') }}" class="btn btn-primary">
    <i class="bx bx-plus me-1"></i>
    Nueva Cuenta
  </a>
</div>

@if (Auth::user()->can('access_current-accounts'))
<div class="row mb-4">
  <!-- Total Debe Card -->
  <div class="col-lg-3 col-md-6 col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div class="card-info">
            <h5 class="card-title mb-0">Total Debe</h5>
            <h2 class="mb-2 mt-2">{{ $settings->currency_symbol }} {{ number_format($totalDebit, 2) }}</h2>
            <small class="text-muted">Balance total pendiente</small>
          </div>
          <div class="card-icon">
            <span class="badge bg-label-danger rounded p-2">
              <i class="bx bx-trending-down bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Total Haber Card -->
  <div class="col-lg-3 col-md-6 col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div class="card-info">
            <h5 class="card-title mb-0">Total Haber</h5>
            <h2 class="mb-2 mt-2">{{ $settings->currency_symbol }} {{ number_format($totalAmount, 2) }}</h2>
            <small class="text-muted">Total pagos recibidos</small>
          </div>
          <div class="card-icon">
            <span class="badge bg-label-success rounded p-2">
              <i class="bx bx-trending-up bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Status Cards -->
  <div class="col-lg-6 col-12">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="card-title mb-3">Estado de Cuentas</h5>
        <div class="row g-3">
          <div class="col-md-4">
            <div class="d-flex align-items-center">
              <div class="badge bg-success p-2 me-2">
                <i class="bx bx-check bx-sm"></i>
              </div>
              <div>
                <h6 class="mb-0">{{ $paidAccounts }}</h6>
                <small>Pagadas</small>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="d-flex align-items-center">
              <div class="badge bg-warning p-2 me-2">
                <i class="bx bx-time bx-sm"></i>
              </div>
              <div>
                <h6 class="mb-0">{{ $partialAccounts }}</h6>
                <small>Parciales</small>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="d-flex align-items-center">
              <div class="badge bg-danger p-2 me-2">
                <i class="bx bx-x bx-sm"></i>
              </div>
              <div>
                <h6 class="mb-0">{{ $unpaidAccounts }}</h6>
                <small>Pendientes</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filters Card -->
<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="card-title mb-0">Filtros</h5>
      <div>
        <button class="btn btn-outline-secondary btn-sm me-2" id="clear-filters">
          <i class="bx bx-reset me-1"></i>Limpiar
        </button>
        <div class="btn-group">
          <button class="btn btn-primary btn-sm" id="export-excel">
            <i class="bx bxs-file-export me-1"></i>Excel
          </button>
          <button class="btn btn-primary btn-sm" id="export-pdf">
            <i class="bx bxs-file-pdf me-1"></i>PDF
          </button>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Tipo de Entidad</label>
        <select id="entityType" class="form-select">
          <option value="">Todos</option>
          <option value="client">Cliente</option>
          <option value="supplier">Proveedor</option>
        </select>
      </div>
      
      <div class="col-md-3 client_filter" style="display: none;">
        <label class="form-label">Cliente</label>
        <select id="clientSelect" class="form-select">
          <option value="">Todos</option>
        </select>
      </div>

      <div class="col-md-3 supplier_filter" style="display: none;">
        <label class="form-label">Proveedor</label>
        <select id="supplierSelect" class="form-select">
          <option value="">Todos</option>
        </select>
      </div>

      <div class="col-md-3 status_filter">
        <label class="form-label">Estado</label>
      </div>

      <div class="col-md-3">
        <label class="form-label">Fecha Desde</label>
        <input type="date" id="startDate" class="form-control">
      </div>

      <div class="col-md-3">
        <label class="form-label">Fecha Hasta</label>
        <input type="date" id="endDate" class="form-control">
      </div>
    </div>
  </div>
</div>

<!-- DataTable -->
<div class="card">
  <div class="card-datatable table-responsive">
    @if($currentAccounts->count() > 0)
    <table class="table datatables-current-accounts border-top" data-symbol="{{ $settings->currency_symbol }}">
      <thead class="table-light">
        <tr>
          <th>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="checkAll">
            </div>
          </th>
          <th>N°</th>
          <th>Fecha</th>
          <th>Tipo</th>
          <th>Nombre</th>
          <th>Ventas</th>
          <th>Pagos</th>
          <th>Saldo</th>
          <th>Moneda</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
    </table>
    @else
    <div class="text-center p-5">
      <img src="{{ asset('assets/img/illustrations/empty.svg') }}" class="mb-3" width="150">
      <h4>No hay cuentas corrientes</h4>
      <p class="text-muted">Comienza agregando una nueva cuenta corriente</p>
      <a href="{{ route('current-accounts.create') }}" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i>
        Agregar Cuenta
      </a>
    </div>
    @endif
  </div>
</div>
@endif
@endsection