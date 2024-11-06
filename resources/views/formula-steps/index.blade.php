@extends('layouts/layoutMaster')

@section('title', 'Pasos de la fórmula')

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
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js'
])
@endsection

@section('page-script')
<script type="text/javascript">
  window.csrfToken = "{{ csrf_token() }}";
  var purchaseOrderItems = @json($formulaRawMaterials);
</script>
@vite(['resources/assets/js/app-formula-raw-materials-list.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Fórmula /</span> Pasos
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
          <div class="col-md-6">
            <div class="card">
              <div class="d-flex justify-content-between align-items-start p-3">
                <div>
                  <h6 class="mb-2">Pasos</h6>
                  <h4 class="mb-2 total-steps">0</h4>
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

          <div class="col-md-6">
            <div class="card">
              <div class="d-flex justify-content-between align-items-start p-3">
                <div>
                  <h6 class="mb-2">Materias primas diferentes</h6>
                  <h4 class="mb-2 total-raw-material">0</h4>
                  <p class="mb-0 text-muted">Total</p>
                </div>
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-secondary">
                    <i class="bx bx-cube bx-sm"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-header">
            <div class="container">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Pasos de la fórmula</h5>
                <div class="d-flex gap-2">
                  <button class="btn btn-outline-secondary" id="downloadTemplateBtn">Descargar plantilla</button>
                  <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#csvModal">Cargar CSV</button>
                  <button class="addStepsModal btn btn-primary">Agregar Pasos</button>
                </div>
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
              @foreach (['ID', 'Fórmula', 'Materia Prima', 'Cantidad', 'Aclaración', 'Paso', 'Acciones'] as $index => $label)
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
              <table class="table datatables-formula-steps border-top">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Fórmula</th>
                    <th>Materia Prima</th>
                    <th>Cantidad</th>
                    <th>Aclaración</th>
                    <th>Paso</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <!-- Aquí se debería cargar el contenido de la tabla mediante DataTables -->
              </table>
            </div>
          </div>
        </div>



        <!-- Modal -->
        <div class="modal fade" id="csvModal" tabindex="-1" aria-labelledby="csvModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="csvModalLabel">Cargar archivo CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="csvUploadForm" enctype="multipart/form-data">
                  <div class="d-flex justify-content-center align-items-center flex-column p-5 border border-2 border-dashed rounded" id="dropzone">
                    <i class="bx bx-cloud-upload bx-lg mb-3"></i>
                    <p class="mb-0">Arrastra y suelta el archivo aquí o</p>
                    <button type="button" class="btn btn-outline-primary mt-2" id="uploadButton">Subir archivo</button>
                    <input class="file-input" type="file" id="csvFile" name="csvFile" accept=".csv" style="display:none;">
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="csvUploadForm" class="btn btn-primary">Enviar</button>
              </div>
            </div>
          </div>
        </div>

        <!-- REPEATER -->
        <div class="modal fade" id="stepsModal" tabindex="-1" aria-labelledby="stepsModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="batchModalLabel">Ingresar pasos de la fórmula</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="steps-form">
                  <div id="repeater">
                    <div data-repeater-list="steps">
                      <div data-repeater-item class="card mb-3">
                        <div class="card-body row">
                          <div class="col-3">
                            <label for="raw-material" class="form-label">Materia prima</label>
                            <select name="raw-material" id="raw-material" class="form-select raw-material" required>
                              <option value="">No utiliza materia prima</option>
                            </select>
                          </div>
                          <div class="col-2">
                            <label for="quantity" class="form-label">Cantidad</label>
                            <div class="input-group">
                              <input type="number" name="quantity" class="form-control lot-quantity" required disabled>
                              <span class="input-group-text" id="unit-of-measure">--</span>
                            </div>
                          </div>
                          <div class="col-3">
                            <label for="clarification" class="form-label">Aclaración</label>
                            <input type="text" name="clarification" class="form-control" required>
                          </div>
                          <div class="col-2">
                            <label for="step" class="form-label">Paso</label>
                            <input type="number" name="step" class="form-control step" required>
                          </div>
                          <div class="col-1 d-flex justify-content-end align-items-end">
                            <button type="button" data-repeater-delete class="btn btn-danger btn-icon">
                              <i class='bx bx-trash' style="color: white;"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <button type="button" data-repeater-create class="btn btn-success">Agregar paso</button>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="save-lots">Guardar pasos</button>
              </div>
            </div>
          </div>
        </div>
        <style>
          /* Eliminar flechas en Chrome, Safari, Edge, Opera */
          input[type=number]::-webkit-inner-spin-button,
          input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
          }

          /* Eliminar flechas en Firefox */
          input[type=number] {
            -moz-appearance: textfield;
          }
        </style>


        @endsection