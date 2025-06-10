@extends('layouts/layoutMaster')

@section('title', 'Listado de Vendedores')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/toastr/toastr.scss',
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/toastr/toastr.js',
])
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">
  <div class="d-flex flex-column justify-content-center mb-3 mb-md-0">
    <h4 class="mb-0 page-title">
      <i class="bx bx-user-circle me-2"></i> Vendedores
    </h4>
  </div>

  <div class="d-flex align-items-center justify-content-center flex-grow-1 gap-3 mb-3 mb-md-0 mx-md-4">
    <div class="input-group w-100 w-md-75 shadow-sm">
      <span class="input-group-text bg-white">
        <i class="bx bx-search"></i>
      </span>
      <input type="text" id="searchVendor" class="form-control" placeholder="Buscar vendedor..." aria-label="Buscar Vendedor">
    </div>
  </div>

  <div class="text-end d-flex gap-2">
    <button type="button" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvasVendorAdd">
      <i class="bx bx-plus"></i> Nuevo Vendedor
    </button>
  </div>
</div>

@if(session('success'))
<div class="alert alert-success d-flex" role="alert">
  <span class="badge badge-center rounded-pill bg-success border-label-success p-3 me-2"><i class="bx bx-user fs-6"></i></span>
  <div class="d-flex flex-column ps-1">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Correcto!</h6>
    <span>{{ session('success') }}</span>
  </div>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger d-flex" role="alert">
  <span class="badge badge-center rounded-pill bg-danger border-label-danger p-3 me-2"><i class="bx bx-error-circle fs-6"></i></span>
  <div class="d-flex flex-column ps-1">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Error!</h6>
    <span>{{ session('error') }}</span>
  </div>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger d-flex" role="alert">
  <span class="badge badge-center rounded-pill bg-danger border-label-danger p-3 me-2"><i class="bx bx-error-circle fs-6"></i></span>
  <div class="d-flex flex-column ps-1">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Error!</h6>
    <ul class="mb-0">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
</div>
@endif

<div class="card">
    <div class="card-body">
        @if($vendors->isEmpty())
            <div class="alert alert-info text-center">
                <i class="ti ti-info-circle me-2"></i>
                Aún no hay vendedores registrados
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered" id="vendorsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Usuario Asociado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendors as $vendor)
                        <tr>
                            <td>{{ $vendor->id }}</td>
                            <td>{{ $vendor->name }}</td>
                            <td>{{ $vendor->lastname }}</td>
                            <td>{{ $vendor->email }}</td>
                            <td>{{ $vendor->phone }}</td>
                            <td>{{ $vendor->user ? $vendor->user->name : 'No asignado' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-primary edit-vendor" data-id="{{ $vendor->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-vendor" data-id="{{ $vendor->id }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Offcanvas para agregar nuevo vendedor -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasVendorAdd" aria-labelledby="offcanvasVendorAddLabel">
    <div class="offcanvas-header">
        <h5 id="offcanvasVendorAddLabel" class="offcanvas-title">Crear vendedor</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0">
        <form id="vendorAddForm" method="POST" action="{{ route('vendors.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="name">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required />
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="lastname">Apellido <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('lastname') is-invalid @enderror" id="lastname" name="lastname" value="{{ old('lastname') }}" required />
                @error('lastname')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" />
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="phone">Teléfono</label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" />
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="user_id">Usuario Asociado</label>
                <select class="form-select select2 @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                    <option value="">Seleccione un usuario</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="pt-3">
                <button type="submit" class="btn btn-primary me-sm-3 me-1">Crear vendedor</button>
                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancelar</button>
            </div>
        </form>
    </div>
</div>

@push('page-script')
<script>
$(document).ready(function() {
    // Inicializar select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Seleccione un usuario'
    });

    $('#vendorsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        responsive: true
    });

    // Limpiar el formulario al cerrar el offcanvas
    $('#offcanvasVendorAdd').on('hidden.bs.offcanvas', function () {
        $('#vendorAddForm')[0].reset();
        $('.select2').val('').trigger('change');
    });
});
</script>
@endpush

@endsection
