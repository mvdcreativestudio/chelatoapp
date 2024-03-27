@extends('layouts/layoutMaster')

@section('title', 'Listado de Tiendas')

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
    window.storeAdd = "{{ route('stores.create') }}";
    window.storeEditTemplate = "{{ route('stores.edit', ':id') }}";
    window.storeDeleteTemplate = "{{ route('stores.destroy', ':id') }}";
    window.storeManageUsersTemplate = "{{ route('stores.manageUsers', ':id') }}";
    window.csrfToken = "{{ csrf_token() }}";
    var stores = @json($stores);
    console.log(stores)
</script>
@vite(['resources/assets/js/app-stores-list.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Administración /</span> Tiendas
</h4>

<div class="card mb-4">
  <div class="card-body">
    <div class="row gy-4 gy-sm-1">
      <!-- Card Total de Tiendas -->
      <div class="col-sm-6 col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-2">Total de Tiendas</h6>
                        <h4>{{ $stores->count() }}</h4>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-store bx-sm"></i></span>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <!-- Card Tiendas Activas -->
      <div class="col-sm-6 col-lg-4">
          <div class="card mb-3">
              <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center">
                      <div>
                          <h6 class="mb-2">Tiendas Activas</h6>
                          <h4>{{ $stores->filter(fn($store) => $store->status == true)->count() }}</h4>
                      </div>
                      <div class="avatar">
                          <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-circle bx-sm"></i></span>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Card Tiendas Inactivas -->
      <div class="col-sm-6 col-lg-4">
          <div class="card mb-3">
              <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center">
                      <div>
                          <h6 class="mb-2">Tiendas Inactivas</h6>
                          <h4>{{ $stores->filter(fn($store) => $store->status == false)->count() }}</h4>
                      </div>
                      <div class="avatar">
                          <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-block bx-sm"></i></span>
                      </div>
                  </div>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
  {{ session('success') }}
</div>
@endif


<div class="card">
  <div class="card-header">
    <h5 class="card-title">Tiendas</h5>
  </div>
  <div id="dataTableInit" style="display:none;"></div>
  <div class="card-datatable table-responsive">
    <table class="table datatables-stores border-top">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Teléfono</th>
          <th>Email</th>
          <th>Dirección</th>
          <th>RUT</th>
          <th>Estado</th>
          <th>Miembros</th>
          <th>Acciones</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection
