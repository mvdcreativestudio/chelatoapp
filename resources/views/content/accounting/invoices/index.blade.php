@extends('layouts/layoutMaster')

@section('title', 'Facturas')

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
@endsection

@section('page-script')
@vite([
  'resources/assets/js/app-invoices-list.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Contabilidad /</span> Facturas
</h4>

{{-- <div class="card mb-4">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gy-4 gy-sm-1">
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Total de Facturas</h6>
              <h4 class="mb-2">{{ $totalReceipts }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Total</span></p>
            </div>
            <div class="avatar me-sm-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-receipt bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none me-4">
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Total Ingresos</h6>
              <h4 class="mb-2">{{ $settings->currency_symbol }} {{ number_format($totalIncome, 2) }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Total</span></p>
            </div>
            <div class="avatar me-lg-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-dollar bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none">
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-2">Empresa con más Emisiones</h6>
              <h4 class="mb-2">{{ $storeNameWithMostReceipts }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Más Emisiones</span></p>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-store bx-sm"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div> --}}

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


<!-- Receipts List Table -->
<div class="card">
  <div class="card-datatable table-responsive">
    <div class="card-header" style="padding-bottom: 0px;">
      <h5 class="card-title">Facturas</h5>
      <div class="d-flex">
        <p class="text-muted small">
          <a href="#" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches" aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
        </p>
      </div>
      <button id="btn-update-cfes" class="btn btn-primary">
        Actualizar estado de CFEs
      </button>
      @if (auth()->user()->can('access_update_all_invoices'))
        <button id="btn-update-all-cfes" class="btn btn-primary">
          Actualizar estado de todos los CFEs
        </button>
      @endif
      <div class="collapse" id="columnSwitches">
        <div class="mt-0 d-flex flex-wrap">
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="1" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Fecha</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="2" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Cliente</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="3" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Empresa</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="4" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Importe</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="8" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Acciones</span>
            </label>
          </div>
        </div>
      </div>
    </div>
    <table class="datatables-invoice table border-top" data-symbol="{{ $settings->currency_symbol }}">
      <thead>
        <tr>
          <th>N°</th>
          <th>Empresa</th>
          <th>Cliente</th>
          <th>Orden</th>
          <th>Fecha</th>
          <th>Tipo</th>
          <th>Razón</th>
          <th>Balance</th>
          <th>Moneda</th>
          <th>Total</th>
          <th>Asociado a</th>
          <th>Status</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>
<!--/ Responsive Datatable -->

<div class="modal fade" id="emitirReciboModal" tabindex="-1" aria-labelledby="emitirReciboLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="#" method="POST" id="emitirReciboForm">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="emitirReciboLabel">Emitir Recibo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea emitir un recibo sobre esta factura?</p>
          <p>No podrás emitir más notas de crédito o débito sobre esta factura.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Emitir Recibo</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="width: fit-content;">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDetalleLabel">Detalles del CFE</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-start">
        <!-- Contenido dinámico se cargará aquí -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="emitirNotaModal" tabindex="-1" aria-labelledby="emitirNotaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="#" method="POST" id="emitirNotaForm">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="emitirNotaLabel">Emitir Nota</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="noteType" class="form-label">Tipo de Nota</label>
            <select class="form-control" id="noteType" name="noteType" required>
              <option value="credit">Nota de Crédito</option>
              <option value="debit">Nota de Débito</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="noteAmount" class="form-label">Monto de la Nota</label>
            <input type="number" class="form-control" id="noteAmount" name="noteAmount" min="0" step="0.01" required>
          </div>
          <div class="mb-3">
            <label for="reason" class="form-label">Razón de la Nota</label>
            <textarea class="form-control" id="reason" name="reason" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Emitir Nota</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal de Envío de Correo -->
<div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sendEmailModalLabel">Enviar Factura por Correo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="sendEmailForm" method="POST" action="{{ route('invoices.sendEmail') }}">
        @csrf
        <input type="hidden" name="invoice_id" id="invoice_id">
        <div class="modal-body">
          <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico del cliente</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Enviar</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
