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
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Contabilidad /</span> Configuraciones de Cuentas Corrientes
</h4>

@if (Auth::user()->can('access_datacenter'))
<div class="card mb-4">
  <div class="card-body card-widget-separator">
    <div class="row gy-4 gy-sm-1">
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
          <div>
            <h6 class="mb-2">Total Configuraciones</h6>
            <h4 class="mb-2">{{ $totalCurrentAccountSettings }}</h4>
          </div>
          <div class="avatar me-lg-4">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-cog bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- Current Account Settings List Table -->
<div class="card">
  <div class="card pb-3">
    <h5 class="card-header pb-0">
      Configuraciones de Cuentas Corrientes
      <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addCurrentAccountSettingModal">
        Agregar Configuración
      </button>
      <div class="d-flex">
        <p class="text-muted small">
          <a href="" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches"
            aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
        </p>
      </div>
      <div class="collapse" id="columnSwitches">
        <div class="mt-0 d-flex flex-wrap">
          <!-- Selectores de columnas -->
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="2" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Tipo de Transacción</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="3" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Tasa de Mora</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="4" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Términos de Pago</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="5" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Fecha de Creación</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="6" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Acciones</span>
            </label>
          </div>
        </div>
        <div class="dropdown d-inline float-end mx-2">
          <button class="btn btn-primary dropdown-toggle d-none" type="button" id="dropdownMenuButton"
            data-bs-toggle="dropdown" aria-expanded="false">
            Acciones
          </button>
          <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <li><a class="dropdown-item" href="#" id="deleteSelected">Eliminar seleccionados</a></li>
          </ul>
        </div>
      </div>
    </h5>
  </div>

  <div class="card-datatable table-responsive pt-0">
    @if($currentAccountSettings->count() > 0)
    <table class="table datatables-current-account-settings">
      <thead>
        <tr>
          <th>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="checkAll">
            </div>
          </th>
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
    <div class="text-center py-5">
      <h4>No hay configuraciones de cuentas corrientes</h4>
      <p class="text-muted">Agrega una nueva configuración para comenzar</p>
    </div>
    @endif
  </div>
</div>
@include('current-accounts.current-account-settings.add-current-account-settings')
@include('current-accounts.current-account-settings.edit-current-account-settings')

@endsection
