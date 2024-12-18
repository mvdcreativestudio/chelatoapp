@extends('layouts/layoutMaster')

@section('title', 'Conductores')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/moment/moment.js',
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js',
  'resources/assets/vendor/libs/cleavejs/cleave.js',
  'resources/assets/vendor/libs/cleavejs/cleave-phone.js'
])
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
<script type="text/javascript">
  window.csrfToken = "{{ csrf_token() }}";
  var drivers = @json($drivers);
</script>
@vite('resources/assets/js/app-driver-list.js')
@endsection

@section('content')

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
  {{ session('success') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
  <div class="alert alert-danger">
    {{ $error }}
  </div>
@endforeach
@endif

<div class="d-flex flex-column flex-md-row align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">
  <div class="d-flex flex-column justify-content-center mb-3 mb-md-0">
    <h4 class="mb-0 page-title">
      <i class="bx bx-user-circle me-2"></i> Choferes
    </h4>
  </div>

  <div class="d-flex align-items-center justify-content-center flex-grow-1 gap-3 mb-3 mb-md-0 mx-md-4">
    <div class="input-group w-100 w-md-75 shadow-sm">
      <span class="input-group-text bg-white">
        <i class="bx bx-search"></i>
      </span>
      <input type="text" id="searchClient" class="form-control" placeholder="Buscar chofer..." aria-label="Buscar Chofer">
    </div>
  </div>

  <div class="text-end d-flex gap-2">
    <button type="button" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1 w-100" data-bs-toggle="offcanvas" data-bs-target="#offCanvasDriverAdd">
      <i class="bx bx-plus"></i> Nuevo Chofer
    </button>
  </div>
</div>

<!-- Drivers List Container -->
<div class="row driver-list-container">
  <!-- Las tarjetas de choferes se generarán aquí dinámicamente -->
  
</div>


<!-- Off-Canvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="addDriverOffCanvas" aria-labelledby="addDriverOffCanvasLabel">
  <div class="offcanvas-header">
    <h5 id="addDriverOffCanvasLabel">Agregar Nuevo Chofer</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form id="addDriverForm">
      <!-- Nombre -->
      <div class="mb-3">
        <label for="name" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="name" name="name" required>
      </div>
      <!-- Apellido -->
      <div class="mb-3">
        <label for="last_name" class="form-label">Apellido</label>
        <input type="text" class="form-control" id="last_name" name="last_name" required>
      </div>
      <!-- Documento -->
      <div class="mb-3">
        <label for="document" class="form-label">Documento</label>
        <input type="number" class="form-control" id="document" name="document" required>
      </div>
      <!-- Dirección -->
      <div class="mb-3">
        <label for="address" class="form-label">Dirección</label>
        <input type="text" class="form-control" id="address" name="address" required>
      </div>
      <!-- Teléfono -->
      <div class="mb-3">
        <label for="phone" class="form-label">Teléfono</label>
        <input type="tel" class="form-control" id="phone" name="phone" required>
      </div>
      <!-- Fecha de vencimiento -->
      <div class="mb-3">
        <label for="license_date" class="form-label">Fecha de Vencimiento de Licencia</label>
        <input type="date" class="form-control" id="license_date" name="license_date" required>
      </div>
      <!-- Fecha de vencimiento de carnet de salud -->
      <div class="mb-3">
        <label for="health_date" class="form-label">Fecha de Vencimiento de Carnet de Salud</label>
        <input type="date" class="form-control" id="health_date" name="health_date" required>
      </div>
      <!-- Estado -->
      <div class="mb-3">
        <label for="is_active" class="form-label">Estado</label>
        <select class="form-select" id="is_active" name="is_active" required>
          <option value="1">Activo</option>
          <option value="0">No Activo</option>
        </select>
      </div>
      <!-- Botón de envío -->
      <button type="submit" class="btn btn-success">Crear</button>
    </form>
  </div>
</div>



@endsection
