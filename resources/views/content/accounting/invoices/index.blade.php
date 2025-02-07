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
<script>
  window.isStoreConfigEmailEnabled = "{{ $isStoreConfigEmailEnabled }}";
</script>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-2">
        <span class="text-muted fw-light">Contabilidad /</span> Facturas
    </h4>
    <div class="d-flex gap-2">
        <button id="btn-update-cfes" class="btn btn-outline-primary">
            <i class="bx bx-refresh me-1"></i>
            Actualizar CFEs
        </button>
        @if (auth()->user()->can('access_update_all_invoices'))
        <button id="btn-update-all-cfes" class="btn btn-primary">
            <i class="bx bx-refresh-alt me-1 btn-sm"></i>
            Actualizar Todos
        </button>
        @endif
    </div>
</div>

@if (session('success'))
<div class="alert alert-success alert-dismissible mb-4" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger alert-dismissible mb-4" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- DataTable Card -->
<div class="card">
    <div class="card-header border-bottom">
        <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
            <div class="col-md-4 col-12">
                <h5 class="card-title mb-0">Listado de Facturas</h5>
            </div>
    </div>

    <div class="card-datatable table-responsive">
        @if($invoices->count() > 0)
        <table class="datatables-invoice table border-top" data-symbol="{{ $settings->currency_symbol }}">
            <thead class="table-light">
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
        </table>
        @else
        <div class="text-center p-5">
            <img src="{{ asset('assets/img/illustrations/empty.svg') }}" class="mb-3" width="150">
            <h4>No hay facturas registradas</h4>
            <p class="text-muted">Aún no se han registrado facturas en el sistema</p>
        </div>
        @endif
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


<style>
    .card-header {
        padding: 1.5rem 1.5rem 0;
    }
    
    .dt-buttons .btn {
        padding: 0.5rem 1rem;
    }
    
    .alert {
        margin-bottom: 1rem;
    }
    
    .table thead th {
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    
    @media (max-width: 767.98px) {
        .d-flex.gap-2 {
            flex-direction: column;
            width: 100%;
        }
        
        .d-flex.gap-2 .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
        
        .dt-buttons {
            width: 100%;
        }
        
        .dt-buttons .btn {
            width: 100%;
        }
    }
    .card-header {
        padding: 0.5rem 1.5rem 0;
    }
    
</style>

@endsection
