@extends('layouts/layoutMaster')

@section('title', 'Vehículos')

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
  var vehicles = @json($vehicles);
</script>
@vite('resources/assets/js/app-vehicle-list.js')
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
      <i class="bx bx-user-circle me-2"></i> Vehículos
    </h4>
  </div>

  <div class="d-flex align-items-center justify-content-center flex-grow-1 gap-3 mb-3 mb-md-0 mx-md-4">
    <div class="input-group w-100 w-md-75 shadow-sm">
      <span class="input-group-text bg-white">
        <i class="bx bx-search"></i>
      </span>
      <input type="text" id="searchVehicle" class="form-control" placeholder="Buscar vehículo..." aria-label="Buscar Vehiculo">
    </div>
  </div>

  <div class="text-end d-flex gap-2">
  <button id="btnAddVehicle" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1 w-100" data-bs-toggle="offcanvas" data-bs-target="#addVehicleOffCanvas">
    <i class="bx bx-plus"></i> Agregar Vehículo
  </button>
</div>
</div>

<!-- vehicle List Container -->
<div class="row vehicle-list-container">
  <!-- Las tarjetas de vehículos se generarán aquí dinámicamente -->

</div>

<!-- Off-Canvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="addVehicleOffCanvas" aria-labelledby="addVehicleOffCanvasLabel">
  <div class="offcanvas-header">
    <h5 id="addVehicleOffCanvasLabel">Agregar Nuevo Vehículo</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form id="addVehicleForm">
      <!-- Número -->
      <div class="mb-3">
        <label for="number" class="form-label">Número</label>
        <input type="text" class="form-control" id="number" name="number" required>
      </div>
      <!-- Marca -->
      <div class="mb-3">
        <label for="brand" class="form-label">Marca</label>
        <input type="text" class="form-control" id="brand" name="brand" required>
      </div>
      <!-- Matrícula -->
      <div class="mb-3">
        <label for="plate" class="form-label">Matrícula</label>
        <input type="text" class="form-control" id="plate" name="plate" required>
      </div>
      <!-- Capacidad -->
      <div class="mb-3">
        <label for="capacity" class="form-label">Capacidad</label>
        <input type="number" class="form-control" id="capacity" name="capacity" required>
      </div>
      <!-- Fecha de APPLUS -->
      <div class="mb-3">
        <label for="applus_date" class="form-label">Fecha de APPLUS</label>
        <input type="date" class="form-control" id="applus_date" name="applus_date" required>
      </div>
      <!-- Botón de envío -->
      <button type="submit" class="btn btn-success">Guardar</button>
    </form>
  </div>
</div>
@endsection