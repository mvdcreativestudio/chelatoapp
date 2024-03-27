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

<div class="card">
  <div class="card-header">
    <h5 class="card-title">Proveedores</h5>
    @can('create_suppliers')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
      <i class="bx bx-plus me-1"></i> Agregar Proveedor
    </a>
    @endcan
  </div>
  <div class="card-datatable table-responsive">
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
            <th>Tienda</th>
          @endcan
          <th>Acciones</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection
