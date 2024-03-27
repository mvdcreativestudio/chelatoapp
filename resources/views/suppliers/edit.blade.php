@extends('layouts/layoutMaster')

@section('title', 'Editar Proveedor')

@section('page-script')
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Proveedores /</span> Editar Proveedor
</h4>

<div class="app-ecommerce">
  <!-- Formulario para editar proveedor -->
  <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row">
      <div class="col-12">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Información del Proveedor</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label" for="supplier-name">Nombre</label>
              <input type="text" class="form-control" id="supplier-name" name="name" required placeholder="Nombre del proveedor" value="{{ $supplier->name }}">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-phone">Teléfono</label>
              <input type="text" class="form-control" id="supplier-phone" name="phone" required placeholder="Teléfono del proveedor" value="{{ $supplier->phone }}">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-address">Dirección</label>
              <input type="text" class="form-control" id="supplier-address" name="address" required placeholder="Dirección del proveedor" value="{{ $supplier->address }}">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-city">Ciudad</label>
              <input type="text" class="form-control" id="supplier-city" name="city" required placeholder="Ciudad del proveedor" value="{{ $supplier->city }}">
            </div>

            <div class="mb-3">
              <label class="form-label mb-0" for="supplier-state">Estado</label>
              <input type="text" class="form-control" id="supplier-state" name="state" required placeholder="Estado del proveedor" value="{{ $supplier->state }}">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-country">País</label>
              <input type="text" class="form-control" id="supplier-country" name="country" required placeholder="País del proveedor" value="{{ $supplier->country }}">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-email">Email</label>
              <input type="email" class="form-control" id="supplier-email" name="email" required placeholder="Email del proveedor" value="{{ $supplier->email }}">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-doc_type">Tipo de Documento</label>
              <select class="form-select" id="supplier-doc_type" name="doc_type" required>
                <option value="">Seleccione un tipo de documento</option>
                <option value="DNI" {{ $supplier->doc_type == 'DNI' ? 'selected' : '' }}>DNI</option>
                <option value="PASSPORT" {{ $supplier->doc_type == 'PASSPORT' ? 'selected' : '' }}>Pasaporte</option>
                <option value="OTHER" {{ $supplier->doc_type == 'OTHER' ? 'selected' : '' }}>Otro</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label mb-0" for="supplier-doc_number">Número de Documento</label>
              <input type="text" class="form-control" id="supplier-doc_number" name="doc_number" required placeholder="Número de documento del proveedor" value="{{ $supplier->doc_number }}">
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
          <button type="submit" class="btn btn-primary">Actualizar Proveedor</button>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
