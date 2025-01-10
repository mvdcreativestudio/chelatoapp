@extends('layouts/layoutMaster')

@section('title', 'Materias primas de la orden')

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
  window.csrfToken = "{{ csrf_token() }}";
  var purchaseOrderItems = @json($purchaseOrdersItems);
  window.baseUrl = "{{ url('') }}/";
</script>
@vite(['resources/assets/js/app-purchase-order-items-list.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Items de Órdenes de Compra /</span> Listado
</h4>

<a href="{{ url('admin/purchase-orders/') }}" class="text-dark text-decoration-none mt-2 mb-4 d-flex align-items-center">
  <i class="bx bx-arrow-back me-2"></i> Volver a órdenes de compra
</a>

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
        <!-- Card Total Suma Dólares -->
        <!-- Cards Section -->
        <div class="row">
          <!-- Card Total Suma Dólares -->
          <div class="col-md-4">
            <div class="card">
              <div class="d-flex justify-content-between align-items-start p-3">
                <div>
                  <h6 class="mb-2">Total en Dólares</h6>
                  <h4 class="mb-2 total-usd">0</h4>
                  <p class="mb-0 text-muted">Total en USD</p>
                </div>
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-secondary">
                    <i class="bx bx-dollar-circle bx-sm"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Card Total Suma Pesos -->
          <div class="col-md-4">
            <div class="card">
              <div class="d-flex justify-content-between align-items-start p-3">
                <div>
                  <h6 class="mb-2">Total en Pesos</h6>
                  <h4 class="mb-2 total-uyu">0</h4>
                  <p class="mb-0 text-muted">Total en UYU</p>
                </div>
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-secondary">
                    <i class="bx bx-money bx-sm"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Card Cantidad de materias primas diferentes -->
          <div class="col-md-4">
            <div class="card">
              <div class="d-flex justify-content-between align-items-start p-3">
                <div>
                  <h6 class="mb-2">Cantidad de materias primas diferentes</h6>
                  <h4 class="mb-2 different-raw-materials">0</h4>
                  <p class="mb-0 text-muted">Total</p>
                </div>
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-secondary">
                    <i class="bx bx-package bx-sm"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">Materias primas de la orden</h5>
              <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-outline-primary" onclick="window.location.href='purchase-entries'">Confirmar recepción</button>
                <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#addItemOffCanvas">Gestionar Orden</button>
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
              @foreach (['ID', 'Materia Prima', 'Cantidad', 'Moneda', 'Precio Unitario', 'Total', 'Acciones'] as $index => $label)
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
              <table class="table datatables-purchase-order-items border-top">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Materia Prima</th>
                    <th>Cantidad</th>
                    <th>Moneda</th>
                    <th>Precio Unitario</th>
                    <th>Total</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <!-- Aquí se debería cargar el contenido de la tabla mediante DataTables -->
              </table>
            </div>
          </div>
        </div>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="addItemOffCanvas" aria-labelledby="addItemOffCanvasLabel">
          <div class="offcanvas-header">
            <h5 id="addItemOffCanvasLabel">Agregar Nuevo Item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <form id="addItemForm">
              <div id="purchaseOrderId" data-id="{{ $id }}"></div>

              <div class="mb-3">
                <label for="raw_material_id" class="form-label">Materia Prima</label>
                <select class="form-select" id="raw_material_id" name="raw_material_id" required>
                  <!-- Opciones se llenarán con JavaScript -->
                </select>
              </div>
              <div class="mb-3">
                <label for="quantity" class="form-label">Cantidad</label>
                <div class="input-group">
                  <input type="number" class="form-control" id="quantity" name="quantity" required>
                  <input type="text" class="form-control" id="unit_of_measure" style="max-width: 80px" readonly>
                </div>
              </div>
              <div class="mb-3">
                <label for="unit_price" class="form-label">Precio Unitario</label>
                <div class="input-group">
                  <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" required>
                  <select class="form-select" id="currency" name="currency" required>
                    <option value="USD">USD</option>
                    <option value="UYU">UYU</option>
                  </select>
                </div>
              </div>
              <button type="submit" class="btn btn-success">Guardar</button>
            </form>
          </div>
        </div>

        @endsection