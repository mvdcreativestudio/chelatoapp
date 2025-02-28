@extends('layouts/layoutMaster')

@section('title', 'Listado de Presupuestos')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
<script type="text/javascript">
    // Inicializar con los datos del servidor
    window.initialBudgets = @json($budgets);
</script>
@vite(['resources/assets/js/app-budgets-list.js'])
@endsection

@section('content')
<!-- Buscador -->
<div class="d-flex flex-column flex-md-row align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">
    <div class="d-flex flex-column justify-content-center mb-3 mb-md-0">
        <h4 class="mb-0 page-title">
            <i class='bx bx-calculator'></i> Presupuestos
        </h4>
    </div>

    <div class="d-flex align-items-center justify-content-center flex-grow-1 gap-3 mb-3 mb-md-0 mx-md-4">
        <div class="input-group w-100 w-md-75 shadow-sm">
            <span class="input-group-text bg-white">
                <i class="bx bx-search"></i>
            </span>
            <input type="text" id="searchClient" class="form-control" placeholder="Buscar presupuesto..." aria-label="Buscar Cliente">
        </div>
    </div>

    <!-- Botones alineados a la derecha, ahora responsive -->
    <div class="text-end d-flex gap-2 align-items-center animate__animated animate__fadeIn">

        <!-- Toggle tipo de visualización -->
        <button id="toggle-view-btn" class="btn btn-sm btn-outline-secondary d-flex align-items-center animate__animated animate__pulse" 
                data-bs-toggle="tooltip" 
                data-bs-offset="0,4" 
                data-bs-placement="left" 
                data-bs-html="true" 
                title="Lista / Cuadrícula">
            <i class="bx bx-grid-alt fs-5"></i>
        </button>

        <div class="text-end d-flex gap-2">
        <a href="{{ route('budgets.create') }}" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1 w-100">
            <i class="bx bx-plus"></i> Nuevo Presupuesto
        </a>
        </div>
    </div>
</div>
<!-- Buscador -->

<div id="budgets-view" class="row" data-ajax-url="{{ route('budgets.index') }}">
    <!-- Los presupuestos se renderizarán aquí -->
</div>
@endsection

<meta name="csrf-token" content="{{ csrf_token() }}">