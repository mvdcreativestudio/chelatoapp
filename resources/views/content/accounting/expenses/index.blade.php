@extends('layouts/layoutMaster')

@section('title', 'Gastos')

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
  window.detailUrl = "{{ route('expenses.show', ':id') }}";
</script>
@endsection

@section('page-script')
@vite([
'resources/assets/js/expenses/app-expenses-list.js',
'resources/assets/js/expenses/app-expenses-add.js',
'resources/assets/js/expenses/app-expenses-edit.js',
// 'resources/assets/js/expenses/app-expenses-detail.js',
'resources/assets/js/expenses/app-expenses-delete.js',
])
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-2">
        <span class="text-muted fw-light">Contabilidad /</span> Gastos
    </h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
        <i class="bx bx-plus me-1"></i>
        Agregar Gasto
    </button>
</div>

@if (Auth::user()->can('access_datacenter'))
<div class="row mb-4">
    <!-- Total Gastos Card -->
    <div class="col-xl-3 col-md-6 col-12 mb-4 mb-xl-0">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="card-info">
                        <h5 class="card-title mb-0">Total Gastos</h5>
                        <h2 class="mb-2 mt-2">{{ $settings->currency_symbol }} {{ number_format($totalAmount, 2) }}</h2>
                        <small class="text-muted">Monto total en gastos</small>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="bx bx-dollar bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gastos Pagados Card -->
    <div class="col-xl-3 col-md-6 col-12 mb-4 mb-xl-0">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="card-info">
                        <h5 class="card-title mb-0">Gastos Pagados</h5>
                        <h2 class="mb-2 mt-2">{{ $paidExpenses }}</h2>
                        <small class="text-muted">Transacciones completadas</small>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-success rounded p-2">
                            <i class="bx bx-check-circle bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gastos Parcialmente Pagados Card -->
    <div class="col-xl-3 col-md-6 col-12 mb-4 mb-xl-0">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="card-info">
                        <h5 class="card-title mb-0">Pagos Parciales</h5>
                        <h2 class="mb-2 mt-2">{{ $partialExpenses }}</h2>
                        <small class="text-muted">Transacciones pendientes</small>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-info rounded p-2">
                            <i class="bx bx-hourglass bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gastos No Pagados Card -->
    <div class="col-xl-3 col-md-6 col-12">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="card-info">
                        <h5 class="card-title mb-0">Sin Pagar</h5>
                        <h2 class="mb-2 mt-2">{{ $unpaidExpenses }}</h2>
                        <small class="text-muted">Pendientes de pago</small>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="bx bx-time bx-sm"></i>
                        </span>
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
            <div class="col-md-2 supplier_filter">
                <label class="form-label">Proveedor</label>
            </div>
            <div class="col-md-2 store_filter">
                <label class="form-label">Local</label>
            </div>
            <div class="col-md-2 category_filter">
                <label class="form-label">Categoría</label>
            </div>
            <div class="col-md-2 status_filter">
                <label class="form-label">Estado Pago</label>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha Desde</label>
                <input type="date" class="form-control" id="startDate">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" class="form-control" id="endDate">
            </div>
        </div>
    </div>
</div>

<!-- DataTable -->
<div class="card">
    <div class="card-datatable table-responsive">
        @if($expenses->count() > 0)
        <table class="table datatables-expenses border-top" data-symbol="{{ $settings->currency_symbol }}">
            <thead class="table-light">
                <tr>
                    <th>N°</th>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>Concepto</th>
                    <th>Tienda</th>
                    <th>Importe</th>
                    <th>Abonado</th>
                    <th>Categoria</th>
                    <th>Moneda</th>
                    <th>Estado de Pago</th>
                    <th>Estado Temporal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
        @else
        <div class="text-center p-5">
            <img src="{{ asset('assets/img/illustrations/empty.svg') }}" class="mb-3" width="150">
            <h4>No hay gastos registrados</h4>
            <p class="text-muted">Comienza agregando un nuevo gasto</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="bx bx-plus me-1"></i>
                Agregar Gasto
            </button>
        </div>
        @endif
    </div>
</div>
@endif

<style>
    .stats-card {
        height: 100%;
        min-height: 120px;
    }
    .stats-card .card-body {
        padding: 1.25rem;
    }
    .card-info h5 {
        font-size: 0.9375rem;
        margin-bottom: 0.5rem !important;
    }
    .card-info h2 {
        font-size: 1.5rem;
        line-height: 1.2;
        margin: 0.25rem 0 !important;
    }
    .card-info small {
        font-size: 0.75rem;
    }
    .badge {
        padding: 0.5rem !important;
        height: 2.25rem;
        width: 2.25rem;
    }
    .badge i {
        font-size: 1.125rem;
    }

    .table .badge.pill {
        padding: 0.5rem 1rem !important;
        height: auto;
        width: auto;
        white-space: nowrap;
        font-size: 0.8125rem;
    }

    /* Estados específicos */
    .badge.bg-success {
        background-color: #71dd37 !important;
    }
    .badge.bg-danger {
        background-color: #ff3e1d !important;
    }
    .badge.bg-warning {
        background-color: #ffab00 !important;
        color: #fff;
    }
</style>

@include('content.accounting.expenses.add-expense')
@include('content.accounting.expenses.edit-expense')
@include('content.accounting.expenses.details-expense')

@endsection
