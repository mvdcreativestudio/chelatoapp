@extends('layouts/layoutMaster')

@section('title', 'Categorías de Ingresos')

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
  window.detailUrl = "{{ route('income-categories.show', ':id') }}";
</script>
@endsection

@section('page-script')
@vite([
'resources/assets/js/incomes/incomes-categories/app-incomes-categories-list.js',
'resources/assets/js/incomes/incomes-categories/app-incomes-categories-add.js',
'resources/assets/js/incomes/incomes-categories/app-incomes-categories-edit.js',
// 'resources/assets/js/incomes/incomes-categories/app-incomes-categories-detail.js',
'resources/assets/js/incomes/incomes-categories/app-incomes-categories-delete.js',
])
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-2">
        <span class="text-muted fw-light">Contabilidad /</span> Categorías de Ingresos
    </h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIncomeCategoryModal">
        <i class="bx bx-plus me-1"></i>
        Agregar Categoría
    </button>
</div>

@if (Auth::user()->can('access_datacenter'))
<div class="row mb-4">
    <!-- Total Categorías Card -->
    <div class="col-xl-3 col-md-6 col-12 mb-4 mb-xl-0">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="card-info">
                        <h5 class="card-title mb-0">Total Categorías</h5>
                        <h2 class="mb-2 mt-2">{{ $totalIncomeCategories }}</h2>
                        <small class="text-muted">Categorías registradas</small>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="bx bx-category bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categorías Activas Card -->
    <div class="col-xl-3 col-md-6 col-12 mb-4 mb-xl-0">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="card-info">
                        <h5 class="card-title mb-0">Categorías Activas</h5>
                        <h2 class="mb-2 mt-2">{{ $totalIncomeCategories }}</h2>
                        <small class="text-muted">En uso actualmente</small>
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

    <!-- Categorías más Usadas Card -->
    <div class="col-xl-3 col-md-6 col-12 mb-4 mb-xl-0">
    <div class="card stats-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div class="card-info">
                    <h5 class="card-title mb-0">Ingresos Categorizados</h5>
                    <h2 class="mb-2 mt-2">{{ $totalCategorizedIncomes }}</h2>
                    <small class="text-muted">Transacciones con categoría</small>
                </div>
                <div class="card-icon">
                    <span class="badge bg-label-info rounded p-2">
                        <i class="bx bx-list-check bx-sm"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Última Actualización Card -->
    <div class="col-xl-3 col-md-6 col-12">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="card-info">
                        <h5 class="card-title mb-0">Última Actualización</h5>
                        <h2 class="mb-2 mt-2">{{ now()->format('d/m/Y') }}</h2>
                        <small class="text-muted">Fecha de actualización</small>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="bx bx-calendar bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTable Card -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Lista de Categorías</h5>
    </div>

    <div class="card-datatable table-responsive">
        @if($incomeCategories->count() > 0)
        <table class="table datatables-income-categories border-top">
            <thead class="table-light">
                <tr>
                    <th>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="checkAll">
                        </div>
                    </th>
                    <th>N°</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Fecha de Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
            </tbody>
        </table>
        @else
        <div class="text-center p-5">
            <img src="{{ asset('assets/img/illustrations/empty.svg') }}" class="mb-3" width="150">
            <h4>No hay categorías registradas</h4>
            <p class="text-muted">Comienza agregando una nueva categoría</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIncomeCategoryModal">
                <i class="bx bx-plus me-1"></i>
                Agregar Categoría
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
        padding: 1rem;
        display: flex;
        flex-direction: column;
    }
    .d-flex {
        min-height: 85px;
    }
    .card-info {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .card-info h5.card-title {
        font-size: 0.9375rem;
        margin-bottom: 0.5rem !important;
        height: 1.4rem;
        display: flex;
        align-items: center;
    }
    .card-info h2 {
        font-size: 1.5rem;
        line-height: 1.2;
        margin: 0.25rem 0 !important;
        height: 1.8rem;
        display: flex;
        align-items: center;
    }
    .card-info small {
        font-size: 0.75rem;
        height: 1rem;
        display: flex;
        align-items: center;
    }
    .badge {
        padding: 0.5rem !important;
        height: 2.25rem;
        width: 2.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .badge i {
        font-size: 1.125rem;
    }
</style>

@include('content.accounting.incomes.incomes-categories.add-income-categories')
@include('content.accounting.incomes.incomes-categories.edit-income-categories')

@endsection