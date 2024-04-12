@extends('layouts/layoutMaster')

@section('title', 'Agregar Tienda')

@section('content')
<h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Tiendas /</span><span> Crear Tienda</span>
</h4>

<div class="app-ecommerce">
    <form action="{{ route('stores.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información de la Tienda</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="store-name">Nombre</label>
                        <input type="text" class="form-control" id="store-name" name="name" required placeholder="Nombre de la tienda">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="store-address">Dirección</label>
                        <input type="text" class="form-control" id="store-address" name="address" required placeholder="Dirección de la tienda">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="store-email">Email</label>
                        <input type="email" class="form-control" id="store-email" name="email" required placeholder="Email de la tienda">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="store-rut">RUT</label>
                        <input type="text" class="form-control" id="store-rut" name="rut" required placeholder="RUT de la tienda">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="store-status">Estado</label>
                        <select class="form-select" id="store-status" name="status" required>
                            <option value="" class="disabled">Seleccione un estado</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    @if ($errors->any())
                      @foreach ($errors->all() as $error)
                        <div class="alert alert-danger">
                          {{ $error }}
                        </div>
                      @endforeach
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Guardar Tienda</button>
            </div>
            </div>
        </div>
    </form>
</div>
@endsection
