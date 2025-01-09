@extends('layouts/layoutMaster')

@section('title', 'Lotes')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss'
])
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js'
])
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
@endsection

@section('page-script')
<script type="text/javascript">
  window.csrfToken = "{{ csrf_token() }}";
  var batches = @json($batches);
  var totalBatches = @json($totalBatches);
</script>
@vite(['resources/assets/js/app-batches-list.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Lotes /</span> Listado
</h4>

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
  {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger mt-3 mb-3">
  {{ session('error') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
<div class="alert alert-danger">
  {{ $error }}
</div>
@endforeach
@endif

<div class="card mb-4">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gx-4 gy-3">
        <!-- Cards Section -->
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="d-flex justify-content-between align-items-start p-3">
                <div>
                  <h6 class="mb-2">Lotes</h6>
                  <h4 class="mb-2 total-steps">{{ $totalBatches }}</h4>
                  <p class="mb-0 text-muted">Total</p>
                </div>
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-secondary">
                    <i class="bx bx-list-check bx-sm"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-3">Lotes</h5>
            </div>
            <div class="d-flex">
              <p class="text-muted small">
                <a href="" class="toggle-filter" data-bs-toggle="collapse">Filtrado por fecha de expiración</a>
              </p>
            </div>
            <div class="d-flex align-items-center mb-4">
              <input type="date" id="start-time" name="start-time" class="form-control me-2" style="max-width: 150px;">
              <input type="date" id="end-time" name="end-time" class="form-control me-2" style="max-width: 150px;">
              <button id="filter-button" class="btn btn-primary">Buscar</button>
              <button id="reset-filter" class="btn btn-secondary ms-2">Resetear</button>
            </div>
            <div class="d-flex">
              <div class="mb-3">
                <label for="item-name-filter" class="form-label">Filtrar por Materia prima / Producto:</label>
                <input type="text" class="form-control" id="item-name-filter" placeholder="Ingrese nombre...">
              </div>
            </div>
            <div class="d-flex">
              <p class="text-muted small">
                <a href="#" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches" aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
              </p>
            </div>
          </div>
          <div class="collapse" id="columnSwitches">
            <div class="mt-0 d-flex flex-wrap">
              @foreach (['Nro Lote', 'Materia Prima / Producto', 'Cantidad', 'Fecha producción', 'Fecha expiración'] as $index => $label)
              <div class="mx-3">
                <label class="switch switch-square">
                  <input type="checkbox" class="toggle-column switch-input" data-column="{{ $index }}" checked>
                  <span class="switch-toggle-slider">
                    <span class="switch-on"><i class="bx bx-check"></i></span>
                    <span class="switch-off"><i class="bx bx-x"></i></span>
                  </span>
                  <span class="switch-label">{{ $label }}</span>
                </label>
              </div>
              @endforeach
            </div>
          </div>
          <div class="card-datatable table-responsive">
            <div class="dataTables_wrapper" style="padding-left: 1rem; padding-right: 1rem;">
              <table class="table datatables-batches border-top">
                <thead>
                  <tr>
                    <th>N° lote</th>
                    <th>Materia prima / Producto</th>
                    <th>Cantidad</th>
                    <th>Fecha producción</th>
                    <th>Fecha expiración</th>
                  </tr>
                </thead>
                <!-- Aquí se debería cargar el contenido de la tabla mediante DataTables -->
              </table>
            </div>
          </div>
        </div>
        @endsection