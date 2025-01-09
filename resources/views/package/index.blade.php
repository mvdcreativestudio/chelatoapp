@extends('layouts/layoutMaster')

@section('title', 'Elaboraciones')

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
  window.packages = @json($packages);
  window.csrfToken = "{{ csrf_token() }}";
  window.baseUrl = "{{ url('') }}/";
</script>
@vite(['resources/assets/js/app-packages-list.js'])
@endsection

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

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Stock /</span> Preparación producto
</h4>

<a href="{{ url('admin/packagings/') }}" class="text-dark text-decoration-none mt-2 mb-4 d-flex align-items-center">
  <i class="bx bx-arrow-back me-2"></i> Volver a envasados
</a>

<!-- Card general que envuelve todo el contenido -->
<div class="card">
  <div class="card-body">

    <!-- Widgets de información -->
    <div class="card mb-4">
      <div class="card-widget-separator-wrapper">
        <div class="card-body card-widget-separator">
          <div class="row gy-4 gy-sm-1">
            <!-- Primer widget -->
            <div class="col-sm-6 col-lg-4">
              <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
                <div>
                  <h6 class="mb-2">Total de envasados</h6>
                  <h4 class="mb-2">{{ $packages->count() }}</h4>
                  <p class="mb-0"><span class="text-muted me-2">Total</span></p>
                </div>
                <div class="avatar me-sm-4">
                  <span class="avatar-initial rounded bg-label-secondary">
                    <i class="bx bx-list-ul bx-sm"></i>
                  </span>
                </div>
              </div>
              <hr class="d-none d-sm-block d-lg-none me-4">
            </div>

            <!-- Segundo widget -->
            <div class="col-sm-6 col-lg-4">
              <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
                <div>
                  <h6 class="mb-2">Stock agregado recientemente</h6>
                  <h4 class="mb-2">{{ $packages->where('created_at', '>=', now()->subMonth())->count() }}</h4>
                  <p class="mb-0"><span class="text-muted me-2">Último mes</span></p>
                </div>
                <div class="avatar me-lg-4">
                  <span class="avatar-initial rounded bg-label-secondary">
                    <i class="bx bx-calendar bx-sm"></i>
                  </span>
                </div>
              </div>
              <hr class="d-none d-sm-block d-lg-none">
            </div>

            <!-- Tercer widget -->
            <div class="col-sm-12 col-lg-4">
              <div class="d-flex justify-content-between align-items-start pb-3 pb-sm-0 card-widget-3">
                <div>
                  <h6 class="mb-2">Stock agregado hoy</h6>
                  <h4 class="mb-2">{{ $packages->where('created_at', '>=', now()->startOfDay())->count() }}</h4>
                  <p class="mb-0 text-muted">Hoy</p>
                </div>
                <div class="avatar me-sm-4">
                  <span class="avatar-initial rounded bg-label-secondary">
                    <i class="bx bx-time-five bx-sm"></i>
                  </span>
                </div>
              </div>
              <hr class="d-none d-sm-block d-lg-none">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Botón para abrir el offcanvas alineado a la derecha -->
    <div class="d-flex justify-content-end mb-4">
      <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasPackage" aria-controls="offcanvasPackage">
        Agregar envase
      </button>
      <button class="btn btn-primary ms-2" id="loadTapLabels">
        Stock tapas/etiquetas
      </button>
    </div>

    <!-- Segunda card que contiene las tarjetas generadas por el foreach -->
    <div class="container mt-2">
      <div class="row g-4">
        @foreach($packages as $package)
        <div class="col-md-6">
          <div class="card h-100 border-0 shadow-sm hover-shadow transition">
            <div class="card-body p-4 position-relative">
              <h5 class="card-title text-primary mb-3">{{ $package->name }}</h5>
              <ul class="list-unstyled mb-0">
                <li class="mb-2"><i class="fas fa-box text-secondary me-2"></i> <strong>Tamaño:</strong> {{ $package->size }} {{ $package->unit_of_measure }}</li>
                <li><i class="fas fa-cubes text-secondary me-2"></i> <strong>Stock:</strong> {{ $package->stock }}</li>
              </ul>
              <div class="button-group position-absolute top-50 end-0 translate-middle-y me-3">
                <button class="btn btn-outline-primary btn-edit-stock me-2" data-id="{{ $package->id }}" data-bs-toggle="modal" data-bs-target="#editStockModal">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-outline-danger btn-delete-package" data-id="{{ $package->id }}">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

<!-- Offcanvas para agregar -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasPackage" aria-labelledby="offcanvasPackageLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasPackageLabel">Agregar envase</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form id="addPackageForm">
      <!-- Formulario para agregar un nuevo package -->
      <div class="mb-3">
        <label for="name" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="name" name="name" required>
      </div>
      <div class="mb-3">
        <label for="is_sellable" class="form-label">Es vendible</label>
        <select class="form-control" id="is_sellable" name="is_sellable" required>
          <option value="1">Sí</option>
          <option value="0">No</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="cost" class="form-label">Costo</label>
        <input type="number" step="0.01" class="form-control" id="cost" name="cost" required>
      </div>
      <div class="mb-3">
        <label for="price" class="form-label">Precio</label>
        <input type="number" step="0.01" class="form-control" id="price" name="price">
      </div>
      <div class="mb-3">
        <label for="unit_of_measure" class="form-label">Unidad de Medida</label>
        <select class="form-control" id="unit_of_measure" name="unit_of_measure" required>
          <option value="L">Litros</option>
          <option value="ml">Mililitros</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="size" class="form-label">Tamaño</label>
        <input type="number" step="0.01" class="form-control" id="size" name="size" required>
      </div>
      <div class="mb-3">
        <label for="stock" class="form-label">Stock</label>
        <input type="number" class="form-control" id="stock" name="stock" required>
      </div>
      <button type="submit" class="btn btn-success">Guardar</button>
    </form>
  </div>
</div>

<!-- Modal for editing stock -->
<div class="modal fade" id="editStockModal" tabindex="-1" aria-labelledby="editStockModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editStockModalLabel">Agregar Stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editStockForm">
          <input type="hidden" id="componentId" name="componentId">
          <div class="mb-3">
            <label for="stockToAdd" class="form-label">Cantidad de stock a agregar</label>
            <input type="number" class="form-control" id="stockToAdd" name="stockToAdd" required min="1">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="addStockButton">Agregar</button>
      </div>
    </div>
  </div>
</div>
<style>
  .card {
    overflow: hidden;
  }

  .button-group {
    display: flex;
    align-items: center;
  }

  .btn-edit-stock,
  .btn-delete-package {
    padding: 0.5rem;
    line-height: 1;
    border-radius: 50%;
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }


  .hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
  }
</style>
@endsection