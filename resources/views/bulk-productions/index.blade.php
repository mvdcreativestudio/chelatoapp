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
  window.bulkProductions = @json($bulkProductions);
  window.csrfToken = "{{ csrf_token() }}";
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@vite(['resources/assets/js/app-bulk-productions-list.js'])
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
  <span class="text-muted fw-light">Producción /</span> Elaboraciones
</h4>

<div class="card mb-4">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gy-4 gy-sm-1">
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Total de Producciones</h6>
              <h4 class="mb-2">{{ $bulkProductions->count() }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Total</span></p>
            </div>
            <div class="avatar me-sm-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-layer bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none me-4">
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Producciones Recientes</h6>
              <h4 class="mb-2">{{ $bulkProductions->where('created_at', '>=', now()->subMonth())->count() }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Último mes</span></p>
            </div>
            <div class="avatar me-lg-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-list-ol bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none">
        </div>
        <div class="col-sm-12 col-lg-4">
          <div class="d-flex justify-content-between align-items-start pb-3 pb-sm-0 card-widget-3">
            <div>
              <h6 class="mb-2">Producción del día</h6>
              <h4 class="mb-2">{{ $bulkProductions->where('created_at', '>=', now()->startOfDay())->count() }}</h4>
              <p class="mb-0 text-muted">Hoy</p>
            </div>
            <div class="avatar me-sm-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-bar-chart-alt bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none">
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="card-title">Elaboraciones</h5>
    <div class="d-flex justify-content-end">
      <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasProduction" aria-controls="offcanvasProduction">
        Iniciar Elaboración
      </button>
    </div>
    <div class="d-flex">
      <p class="text-muted small">
        <a href="" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches" aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>

      </p>
    </div>
    <div class="collapse" id="columnSwitches">
      <div class="mt-0 d-flex flex-wrap">
        @foreach (['ID', 'Fórmula', 'Cantidad producida', 'Cantidad utilizada', 'Fecha de elaboración','Operario','Acciones'] as $index => $label)
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
  </div>
  <div class="card-datatable table-responsive mx-4 my-3">
    <table class="table datatables-bulk-productions border-top">
      <thead>
        <tr>
          <th>ID</th>
          <th>Fórmula</th>
          <th>Cantidad producida</th>
          <th>Cantidad utilizada</th>
          <th>Fecha de Elaboración</th>
          <th>Operario</th>
          <th>Acciones</th>
        </tr>
      </thead>
    </table>
  </div>
</div>


<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasProduction" aria-labelledby="offcanvasProductionLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasProductionLabel">Iniciar Elaboración</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form id="productionForm">

      <!-- Nombre -->
      <div class="mb-3">
        <label for="name" class="form-label">Número de lote</label>
        <input type="text" id="name" name="name" class="form-control" required>
      </div>


      <!-- Selector de Fórmula -->
      <div class="mb-3">
        <label for="formula" class="form-label">Seleccionar Fórmula</label>
        <select id="formula" name="formula" class="form-select" required>
          <option value="">Seleccione una fórmula</option>
          <!-- Opciones se llenarán vía AJAX -->
        </select>
      </div>

      <!-- Cantidad a producir -->
      <div class="mb-3">
        <label for="quantity" class="form-label">Cantidad a producir</label>
        <input type="number" id="quantity" name="quantity" class="form-control" required min="1">
      </div>

      <!-- Botón de enviar -->
      <button type="submit" class="btn btn-primary">Iniciar</button>
    </form>
  </div>
</div>



<!-- Modal -->
<div class="modal fade" id="productionDetailsModal" tabindex="-1" aria-labelledby="productionDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productionDetailsModalLabel">Detalles de la Producción</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Aquí se insertarán dinámicamente los detalles de los pasos y lotes -->
      </div>
    </div>
  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="modalBatches" tabindex="-1" aria-labelledby="modalBatchesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header justify-content-center">
        <h5 class="modal-title" id="modalBatchesLabel">Batches Utilizados</h5>
      </div>

      <div class="modal-body d-flex align-items-stretch">
        <!-- Sección para los detalles de los batches -->
        <div class="batch-details flex-grow-1 pe-3">
          <!-- Aquí se insertarán los batches desde el AJAX -->
        </div>

        <!-- Contenedor para la línea divisoria vertical -->
        <div class="divider-container d-flex align-items-center">
          <div class="vr h-100"></div>
        </div>

        <!-- Nueva sección para el código QR -->
        <div class="modal-qr ms-3 d-flex justify-content-center align-items-center">
          <!-- Aquí se insertará el QR -->
          <div id="qrcode"></div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="downloadQR">Descargar QR</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<style>
  #modalBatches .modal-header {
    border-bottom: none;
    padding-bottom: 0;
  }

  #modalBatches .modal-body {
    min-height: 300px;

  }

  #modalBatches .batch-details {
    max-width: 70%;
    overflow-y: auto;
  }

  #modalBatches .modal-qr {
    min-width: 30%;
  }

  #modalBatches .divider-container {
    padding: 10px 0;
  }

  #modalBatches .vr {
    margin: 0 15px;
    min-height: 250px;
    /* Ajusta este valor según sea necesario */
  }
</style>

@endsection