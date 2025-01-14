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


<div class="d-flex flex-column flex-md-row align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">
  <div class="d-flex flex-column justify-content-center mb-3 mb-md-0">
    <h4 class="mb-0 page-title">
      <i class="bx bx-store me-2"></i> Proveedores
    </h4>
  </div>

  <div class="d-flex align-items-center justify-content-center flex-grow-1 gap-3 mb-3 mb-md-0 mx-md-4">
    <div class="input-group w-100 w-md-75 shadow-sm">
      <span class="input-group-text bg-white">
        <i class="bx bx-search"></i>
      </span>
      <input type="text" id="searchSupplier" class="form-control" placeholder="Buscar proveedor..." aria-label="Buscar Proveedor">
    </div>
  </div>

  <div class="text-end d-flex gap-2" place-id="buttonCreate">>
  </div>
</div>
  <div class="card-body">
    <div class="row supplier-list-container">
    </div>
  </div>
@endsection