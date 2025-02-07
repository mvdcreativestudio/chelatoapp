@extends('layouts/layoutMaster')

@section('title', 'Configuraciones de Cuentas Corrientes')

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
<script>
  window.baseUrl = "{{ url('/') }}";
  window.detailUrl = "{{ route('current-account-settings.show', ':id') }}";
</script>
@endsection

@section('page-script')
@vite([
'resources/assets/js/current-accounts/current-account-settings/app-current-account-settings-list.js',
'resources/assets/js/current-accounts/current-account-settings/app-current-account-settings-add.js',
'resources/assets/js/current-accounts/current-account-settings/app-current-account-settings-edit.js',
'resources/assets/js/current-accounts/current-account-settings/app-current-account-settings-delete.js',
])
@endsection

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-2">
      <span class="text-muted fw-light">Contabilidad /</span> Configuraciones de Cuentas Corrientes
    </h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCurrentAccountSettingModal">
      <i class="bx bx-plus me-1"></i>
      Agregar Configuración
    </button>
  </div>

    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="card-title mb-0">Filtros</h5>
          <div>
            <button class="btn btn-outline-secondary btn-sm me-2" id="clear-filters">
              <i class="bx bx-reset me-1"></i>Limpiar
            </button>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Tipo de Transacción</label>
            <select id="transactionType" class="form-select">
              <option value="">Todos</option>
              <option value="Sale">Venta</option>
              <option value="Purchase">Compra</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Fecha Desde</label>
            <input type="date" id="startDate" class="form-control">
          </div>

          <div class="col-md-4">
            <label class="form-label">Fecha Hasta</label>
            <input type="date" id="endDate" class="form-control">
          </div>
        </div>
      </div>
    </div>

  <!-- DataTable -->
  <div class="card">
    <div class="card-datatable table-responsive">
      @if($currentAccountSettings->count() > 0)
      <table class="table datatables-current-account-settings">
        <thead>
          <tr>
            <th>N°</th>
            <th>Tipo de Transacción</th>
            <th>Tasa de Mora</th>
            <th>Términos de Pago</th>
            <th>Fecha de Creación</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          <!-- Datos llenados por DataTables -->
        </tbody>
      </table>
      @else
      <div class="text-center p-5">
        <img src="{{ asset('assets/img/illustrations/empty.svg') }}" class="mb-3" width="150">
        <h4>No hay configuraciones de cuentas corrientes</h4>
        <p class="text-muted">Comienza agregando una nueva configuración</p>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCurrentAccountSettingModal">
          <i class="bx bx-plus me-1"></i>
          Agregar Configuración
        </button>
      </div>
      @endif
    </div>
  </div>

@include('current-accounts.current-account-settings.add-current-account-settings')
@include('current-accounts.current-account-settings.edit-current-account-settings')
@endsection