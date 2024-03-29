@extends('layouts/layoutMaster')

@section('title', 'Listado de Materias Primas')

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
    window.baseUrlAsset = "{{ asset('storage/assets/img/raw_materials/') }}";
    window.rawMaterialAdd = "{{ route('raw-materials.create') }}";
    window.rawMaterialEditTemplate = "{{ route('raw-materials.edit', ':id') }}";
    window.rawMaterialDeleteTemplate = "{{ route('raw-materials.destroy', ':id') }}";
    window.csrfToken = "{{ csrf_token() }}";
    var rawMaterials = @json($rawMaterials);
</script>
@vite(['resources/assets/js/app-raw-materials-list.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Gestión /</span> Listado de Materias Primas
</h4>

<div class="card mb-4">
  <div class="card-body">
    <div class="row gy-4 gy-sm-1">
      <div class="col-sm-6 col-lg-3 border-end">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h6 class="mb-2">Materias Primas</h6>
            <h4 class="mb-2">{{ $rawMaterials->count() }}</h4>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-layer bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3 border-end">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h6 class="mb-2">Unidades de Medida</h6>
            <h4 class="mb-2">{{ $quantityByUnitOfMeasure->count() }}</h4>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-layer bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="card-title">Materias Primas</h5>
  </div>
  <div id="dataTableInit"
     data-base-url="{{ asset('storage/assets/img/raw_materials/') }}"
     data-raw-material-add="{{ route('raw-materials.create') }}"
     data-raw-material-edit="{{ route('raw-materials.edit', ':id') }}"
     data-raw-material-delete="{{ route('raw-materials.destroy', ':id') }}"
     style="display:none;"></div>
  <div class="card-datatable table-responsive">
    <table class="table datatables-raw-materials border-top">
      <thead>
        <tr>
          <th>#</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Unidad de Medida</th>
          <th>Acciones</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection
