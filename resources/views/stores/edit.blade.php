@extends('layouts/layoutMaster')

@section('title', 'Editar Tienda')

@section('content')
<h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Tiendas /</span> Editar Tienda
</h4>

<div class="app-ecommerce">
    <!-- Formulario para editar tienda -->
    <form action="{{ route('stores.update', $store->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información de la Tienda</h5>
                </div>
                <div class="card-body">
                    <!-- Nombre -->
                    <div class="mb-3">
                        <label class="form-label" for="store-name">Nombre</label>
                        <input type="text" class="form-control" id="store-name" name="name" required placeholder="Nombre de la tienda" value="{{ $store->name }}">
                    </div>

                    <!-- Dirección -->
                    <div class="mb-3">
                        <label class="form-label" for="store-address">Dirección</label>
                        <input type="text" class="form-control" id="store-address" name="address" required placeholder="Dirección de la tienda" value="{{ $store->address }}">
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label" for="store-email">Email</label>
                        <input type="email" class="form-control" id="store-email" name="email" required placeholder="Email de la tienda" value="{{ $store->email }}">
                    </div>

                    <!-- RUT -->
                    <div class="mb-3">
                        <label class="form-label" for="store-rut">RUT</label>
                        <input type="text" class="form-control" id="store-rut" name="rut" required placeholder="RUT de la tienda" value="{{ $store->rut }}">
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <label class="form-label" for="store-status">Estado</label>
                        <select class="form-select" id="store-status" name="status" required>
                            <option value="1" {{ $store->status == 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ $store->status == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
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
            <!-- Botones -->
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Actualizar Tienda</button>
            </div>
        </div>
    </div>
    </form>
</div>
@endsection
