@extends('layouts/layoutMaster')

@section('title', 'Editar Empresa')

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
])
@endsection

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  ])
@endsection


@section('page-script')
@vite(['resources/assets/js/edit-store.js'])
<script
    src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&callback=initAutocomplete"
    async defer></script>
    <script>
        window.baseUrl = "{{ url('') }}/";
    </script>
@endsection

@section('content')

<style>
    /* Estilos para mejorar la visualización de las cards de Pymo */
    .card {
        border-radius: 10px;
        transition: all 0.3s ease-in-out;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        padding: 15px;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
    }
</style>


<h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Empresas /</span> Editar Empresa
</h4>

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
    {{ session('success') }}
</div>
@endif


@if (session('mercado_pago_errors'))
<div class="alert alert-danger mt-3 mb-3">
    {{ session('mercado_pago_errors') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
<div class="alert alert-danger">
    {{ $error }}
</div>
@endforeach
@endif

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
                            <input type="text" class="form-control" id="store-name" name="name" required
                                placeholder="Nombre de la tienda" value="{{ $store->name }}">
                        </div>

                        <!-- Dirección -->
                        <div class="mb-3">
                            <label class="form-label" for="store-address">Dirección</label>
                            <input type="text" class="form-control" id="store-address" name="address"
                                placeholder="Calle, esquina, número de puerta" onFocus="geolocate()" role="presentation"
                                autocomplete="off" value="{{ $store->address }}">
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label" for="store-email">Email</label>
                            <input type="email" class="form-control" id="store-email" name="email" required
                                placeholder="Email de la Empresa" value="{{ $store->email }}">
                        </div>

                        <!-- RUT -->
                        <div class="mb-3">
                            <label class="form-label" for="store-rut">RUT</label>
                            <input type="text" class="form-control" id="store-rut" name="rut" required
                                placeholder="RUT de la Empresa" value="{{ $store->rut }}">
                        </div>

                        <!-- Régimen Impositivo -->
                        <div class="mb-3">
                          <label class="form-label" for="store-tax-rate">Régimen Impositivo</label>
                          <select class="form-select" id="store-tax-rate" name="tax_rate_id" required>
                              @foreach($taxRates as $taxRate)
                                  <option value="{{ $taxRate->id }}" {{ $store->tax_rate_id == $taxRate->id ? 'selected' : '' }}>
                                      {{ $taxRate->name }} ({{ $taxRate->rate }}%)
                                  </option>
                              @endforeach
                          </select>
                        </div>


                        <!-- Estado -->
                        <div class="mb-3">
                            <label class="form-label" for="store-status">Estado</label>
                            <select class="form-select" id="store-status" name="status" required>
                                <option value="1" {{ $store->status == 'activo' ? 'selected' : '' }}>Activo
                                </option>
                                <option value="0" {{ $store->status == 'inactivo' ? 'selected' : '' }}>Inactivo
                                </option>
                            </select>
                        </div>

                        <!-- Cliente Asociado -->
                        <div class="mb-3">
                          <label class="form-lablel" for="store-client">Cliente Asociado</label>
                          <select class="form-select" id="store-client" name="client_id">
                              <option value="">Seleccione un cliente</option>
                              @foreach($clients as $client)
                                  <option value="{{ $client->id }}" {{ $store->client_id == $client->id ? 'selected' : '' }}>
                                      @if($client->type === 'company')
                                          {{ $client->company_name }} ({{ $client->email }})
                                      @else
                                          {{ $client->name }} {{ $client->lastname }}
                                      @endif
                                  </option>
                              @endforeach
                          </select>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end mt-5">
                            <button type="submit" class="btn btn-primary">Actualizar Tienda</button>
                        </div>
                    </div>
    </form>
</div>


<script>
    let autocomplete;

    function initAutocomplete() {
        autocomplete = new google.maps.places.Autocomplete(document.getElementById('store-address'), {
            types: ['geocode']
        });
        autocomplete.setFields(['address_component']);
    }
    function initAutocomplete() {
        autocomplete = new google.maps.places.Autocomplete(document.getElementById('store-address'), {
            types: ['geocode']
        });
        autocomplete.setFields(['address_component']);
    }

    function geolocate() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const geolocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                const circle = new google.maps.Circle({
                    center: geolocation,
                    radius: position.coords.accuracy
                });
                autocomplete.setBounds(circle.getBounds());
            });
        }
    }
</script>
@endsection
