@extends('layouts/layoutMaster')

@section('title', 'Agregar Tienda')

@section('page-script')
@vite([
  'resources/assets/js/custom-js/add-store.js'
])
@endsection

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

                    <!-- Acepta MercadoPago Switch -->
                    <div class="mb-3">
                      <div class="form-check form-switch">
                          <!-- Campo oculto para asegurar que un valor falso se envíe si el checkbox no está marcado -->
                          <input type="hidden" name="accepts_mercadopago" value="0">
                          <input class="form-check-input" type="checkbox" id="mercadoPagoSwitch" name="accepts_mercadopago" value="1">
                          <label class="form-check-label" for="mercadoPagoSwitch">Acepta MercadoPago</label>
                      </div>
                    </div>


                    <!-- Campos MercadoPago (ocultos por defecto) -->
                    <div id="mercadoPagoFields" style="display: none;">
                      <!-- Public Key -->
                      <div class="mb-3">
                          <label class="form-label" for="mercadoPagoPublicKey">Public Key</label>
                          <input type="text" class="form-control" id="mercadoPagoPublicKey" name="mercadoPagoPublicKey" placeholder="Public Key de MercadoPago">
                      </div>

                      <!-- Access Token -->
                      <div class="mb-3">
                          <label class="form-label" for="mercadoPagoAccessToken">Access Token</label>
                          <input type="text" class="form-control" id="mercadoPagoAccessToken" name="mercadoPagoAccessToken" placeholder="Access Token de MercadoPago">
                      </div>

                      <!-- Secret Key -->
                      <div class="mb-3">
                          <label class="form-label" for="mercadoPagoSecretKey">Clave Secreta</label>
                          <input type="text" class="form-control" id="mercadoPagoSecreyKey" name="mercadoPagoSecretKey" placeholder="Clave secreta de MercadoPago">
                      </div>
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
