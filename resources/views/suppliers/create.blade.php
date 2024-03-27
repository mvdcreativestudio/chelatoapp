@extends('layouts/layoutMaster')

@section('title', 'Agregar Proveedor')

@section('page-script')
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Proveedores /</span> Crear Proveedor
</h4>

<div class="app-ecommerce">
  <form action="{{ route('suppliers.store') }}" method="POST">
    @csrf
    <div class="row">
      <div class="col-12">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Información del Proveedor</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label" for="supplier-name">Nombre</label>
              <input type="text" class="form-control" id="supplier-name" name="name" required placeholder="Nombre del proveedor">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-phone">Teléfono</label>
              <input type="text" class="form-control" id="supplier-phone" name="phone" required placeholder="Teléfono del proveedor">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-address">Dirección</label>
              <input type="text" class="form-control" id="supplier-address" name="address" required placeholder="Dirección del proveedor">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-city">Ciudad</label>
              <input type="text" class="form-control" id="supplier-city" name="city" required placeholder="Ciudad del proveedor">
            </div>

            <div class="mb-3">
              <label class="form-label mb-0" for="supplier-state">Estado</label>
              <input type="text" class="form-control" id="supplier-state" name="state" required placeholder="Estado del proveedor">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-country">País</label>
              <input type="text" class="form-control" id="supplier-country" name="country" required placeholder="País del proveedor">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-email">Email</label>
              <input type="email" class="form-control" id="supplier-email" name="email" required placeholder="Email del proveedor">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-doc_type">Tipo de Documento</label>
              <select class="form-select" id="supplier-doc_type" name="doc_type" required>
                <option value="">Seleccione un tipo de documento</option>
                <option value="DNI">DNI</option>
                <option value="PASSPORT">Pasaporte</option>
                <option value="OTHER">Otro</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label mb-0" for="supplier-doc_number">Número de Documento</label>
              <input type="text" class="form-control" id="supplier-doc_number" name="doc_number" required placeholder="Número de documento del proveedor">
            </div>

            @if ($errors->any())
              @foreach ($errors->all() as $error)
                <div class="alert alert-danger">
                  {{ $error }}
                </div>
              @endforeach
            @endif

            @if (session('error'))
              <div class="alert alert-danger">
                {{ session('error') }}
              </div>
            @endif
          </div>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary">Guardar Proveedor</button>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
