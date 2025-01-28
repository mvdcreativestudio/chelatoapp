@extends('layouts/layoutMaster')

@section('title', 'Agregar Proveedor')

@section('page-script')
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Proveedores /</span> Crear Proveedor
</h4>

<div class="app-ecommerce">
  @if ($errors->any())
  <div class="alert alert-danger mb-4">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif
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
              <label class="form-label" for="supplier-name">Nombre *</label>
              <input type="text"
                class="form-control @error('name') is-invalid @enderror"
                id="supplier-name"
                name="name"
                value="{{ old('name') }}"
                placeholder="Nombre del proveedor">
              @error('name')
                <div class="invalid-feedback d-block">
                  {{ $message }}
                </div>
              @enderror
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-phone">Teléfono</label>
              <input type="text" class="form-control" id="supplier-phone" name="phone" placeholder="Teléfono del proveedor">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-address">Dirección</label>
              <input type="text" class="form-control" id="supplier-address" name="address" placeholder="Dirección del proveedor">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-city">Ciudad/Barrio</label>
              <input type="text" class="form-control" id="supplier-city" name="city" placeholder="Ciudad del proveedor">
            </div>

            <div class="mb-3">
              <label class="form-label mb-0" for="supplier-state">Departamento</label>
              <input type="text" class="form-control" id="supplier-state" name="state" placeholder="Departamento del proveedor">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-country">País</label>
              <input type="text" class="form-control" id="supplier-country" name="country" placeholder="País del proveedor">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-email">Email</label>
              <input type="email" class="form-control" id="supplier-email" name="email" placeholder="Email del proveedor">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-doc_type">Tipo de Documento</label>
              <select class="form-select" id="supplier-doc_type" name="doc_type">
                <option value="">Seleccione un tipo de documento</option>
                <option value="CI">CI</option>
                <option value="PASSPORT">Pasaporte</option>
                <option value="RUT">RUT</option>
                <option value="OTHER">Otro</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label mb-0" for="supplier-doc_number">Número de Documento</label>
              <input type="text" class="form-control" id="supplier-doc_number" name="doc_number" placeholder="Número de documento del proveedor">
            </div>

            <div class="mb-3">
              <label class="form-label" for="supplier-default_payment_method">Método de pago predefinido</label>
              <select class="form-select" id="supplier-default_payment_method" name="default_payment_method">
                <option value="">Seleccione un método de pago</option>
                <option value="cash">Efectivo</option>
                <option value="credit">Crédito</option>
                <option value="debit">Débito</option>
                <option value="check">Cheque</option>
              </select>
            </div>
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