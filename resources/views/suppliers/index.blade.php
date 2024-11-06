@extends('layouts/layoutMaster')

@section('title', 'Listado de Proveedores')

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
    window.supplierAdd = "{{ route('suppliers.create') }}";
    window.supplierEditTemplate = "{{ route('suppliers.edit', ':id') }}";
    window.supplierDeleteTemplate = "{{ route('suppliers.destroy', ':id') }}";
    window.csrfToken = "{{ csrf_token() }}";
    var suppliers = @json($suppliers ?? []);
    window.hasViewAllSuppliersPermission = @json(auth()->user()->can('view_all_suppliers'));
</script>
@vite(['resources/assets/js/app-suppliers-list.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Gestión /</span> Listado de Proveedores
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
        <div class="col-sm-6 col-lg-6">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Proveedores</h6>
              <h4 class="mb-2">{{ isset($suppliers) ? $suppliers->count() : 0 }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Total</span></p>
            </div>
            <div class="avatar me-sm-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-user bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none me-4">
        </div>
        <div class="col-sm-6 col-lg-6">
          <div class="d-flex justify-content-between align-items-start card-widget-2 pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Pedidos Recientes</h6>
              <h4 class="mb-2">{{ isset($recentOrders) ? $recentOrders->count() : 0 }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Últimos 30 días</span></p>
            </div>
            <div class="avatar me-lg-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-cart bx-sm"></i>
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
    <h5 class="card-title">Proveedores</h5>
    <div class="d-flex">
        <p class="text-muted small">
          <a href="" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches" aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
        </p>
      </div>
      <div class="collapse" id="columnSwitches">
    <div class="mt-0 d-flex flex-wrap">
      @foreach (['Nombre', 'Teléfono', 'Email', 'Ciudad', 'Estado', 'País', 'Tipo de Doc.','Número de Doc.', 'Acciones'] as $index => $label)
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
    @can('create_suppliers')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
      <i class="bx bx-plus me-1"></i> Agregar Proveedor
    </a>
    @endcan
  </div>
  <div class="card-datatable table-responsive mx-4 my-3">
    <table class="table datatables-suppliers border-top">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Teléfono</th>
          <th>Email</th>
          <th>Ciudad</th>
          <th>Estado</th>
          <th>Pais</th>
          <th>Tipo de Doc</th>
          <th>Número de Doc</th>

          @can('view_all_suppliers')
            <th>Empresa</th>
          @endcan

          <th>Acciones</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
</div>
@endsection
