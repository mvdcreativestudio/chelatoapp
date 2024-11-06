@extends('layouts/layoutMaster')

@section('title', 'Listado de Órdenes de Compra')

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
    var purchaseOrders = @json($purchaseOrders);
</script>
@vite(['resources/assets/js/app-purchase-orders-list.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Órdenes de Compra /</span> Listado
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
          <div class="row gy-4 gy-sm-1">
            <div class="col-sm-6 col-lg-3">
              <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
                <div>
                  <h6 class="mb-2">Total de Órdenes</h6>
                  <h4 class="mb-2">{{ $purchaseOrders->count() }}</h4>
                  <p class="mb-0"><span class="text-muted me-2">Total</span></p>
                </div>
                <div class="avatar me-sm-4">
                  <span class="avatar-initial rounded bg-label-secondary">
                    <i class="bx bx-purchase-tag bx-sm"></i>
                  </span>
                </div>
              </div>
              <hr class="d-none d-sm-block d-lg-none me-4">
            </div>
            <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
          <div>
            <h6 class="mb-2">Órdenes Pendientes</h6>
            <h4 class="mb-2">{{ $purchaseOrders->where('status', 1)->count() }}</h4>
            <p class="mb-0"><span class="text-muted me-2">Total</span></p>
          </div>
          <div class="avatar me-sm-4">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-timer bx-sm"></i>
            </span>
          </div>
        </div>
        <hr class="d-none d-sm-block d-lg-none me-4">
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start card-widget-3 border-end pb-3 pb-sm-0">
          <div>
            <h6 class="mb-2">Órdenes Canceladas</h4>
            <h4 class="mb-2">{{ $purchaseOrders->where('status', 0)->count() }}</h4>
            <p class="mb-0"><span class="text-muted me-2">Total</span></p>
          </div>
          <div class="avatar me-sm-4">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="bx bx-x bx-sm"></i>
            </span>
          </div>
        </div>
        <hr class="d-none d-sm-block d-lg-none me-4">
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start card-widget-4 pb-3 pb-sm-0">
          <div>
            <h6 class="mb-2">Órdenes Completadas</h6>
            <h4 class="mb-2">{{ $purchaseOrders->where('status', 2)->count() }}</h4>
            <p class="mb-0"><span class="text-muted me-2">Total</span></p>
          </div>
          <div class="avatar me-sm-4">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-check bx-sm"></i>
            </span>
          </div>
        </div>
        <hr class="d-none d-sm-block d-lg-none me-4">
      </div>
    </div>
    </div>
    </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Órdenes de Compra</h5>
      <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#addOrderOffCanvas">Crear</button>
    </div>
    <div class="d-flex">
      <p class="text-muted small">
        <a href="#" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches" aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
      </p>
    </div>
  </div> <!-- Cierre del card-header -->
  <div class="collapse" id="columnSwitches">
    <div class="mt-0 d-flex flex-wrap">
      <!-- Aquí se muestran los switches para las columnas -->
      @foreach (['ID', 'Proveedor', 'Fecha de Orden', 'Fecha de Vencimiento', 'Estado', 'Materias primas', 'Acciones'] as $index => $label)
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
  </div> <!-- Cierre del collapse -->
  <div class="card-datatable table-responsive">
    <div class="dataTables_wrapper" style="padding-left: 1rem; padding-right: 1rem;">
      <table class="table datatables-purchase-orders border-top">
          <thead>
            <tr>
              <th>ID</th>
              <th>Proveedor</th>
              <th>Fecha de Orden</th>
              <th>Fecha de Vencimiento</th>
              <th>Estado</th>
              <th>Materias primas</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <!-- Aquí se debería cargar el contenido de la tabla mediante DataTables -->
        </table>
    </div>
  </div>
</div> <!-- Cierre del card -->
<!-- Off-Canvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="addOrderOffCanvas" aria-labelledby="addOrderOffCanvasLabel">
  <div class="offcanvas-header">
    <h5 id="addOrderOffCanvasLabel">Agregar Nueva Orden</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form id="addOrderForm">
      <div class="mb-3">
        <label for="supplier_id" class="form-label">Proveedor</label>
        <select class="form-select" id="supplier_id" name="supplier_id" required>
          <!-- Opciones se llenarán con JavaScript -->
        </select>
      </div>
      <div class="mb-3">
        <label for="status" class="form-label">Estado</label>
        <select class="form-select" id="status" name="status" required>
          <option value="1">Pendiente</option>
          <option value="0">Cancelada</option>
          <option value="2">Completada</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="due_date" class="form-label">Fecha de Vencimiento</label>
        <input type="date" class="form-control" id="due_date" name="due_date" required>
      </div>
      <button type="submit" class="btn btn-success">Crear</button>
    </form>
  </div>
</div>

@endsection
