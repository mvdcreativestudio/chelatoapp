@extends('layouts/layoutMaster')

@section('title', 'Agregar Empresa')

@section('page-script')
<script
  src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&callback=initAutocomplete"
  async defer></script>
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Empresas /</span><span> Crear Empresa</span>
</h4>

<div class="app-ecommerce">
  <form action="{{ route('stores.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
      <div class="col-12">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Información de la Empresa</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label" for="store-name">Nombre</label>
              <input type="text" class="form-control" id="store-name" name="name" required
                placeholder="Nombre de la Empresa">
            </div>

            <div class="mb-3">
              <label class="form-label" for="store-address">Dirección</label>
              <input type="text" class="form-control" id="store-address" name="address" required
                placeholder="Calle, esquina, número de puerta" onFocus="geolocate()" role="presentation"
                autocomplete="off">
            </div>

            <div class="mb-3">
              <label class="form-label" for="store-email">Email</label>
              <input type="email" class="form-control" id="store-email" name="email" required
                placeholder="Email de la Empresa">
            </div>

            <div class="mb-3">
              <label class="form-label" for="store-rut">RUT</label>
              <input type="text" class="form-control" id="store-rut" name="rut" required
                placeholder="RUT de la Empresa">
            </div>

            <div class="mb-3">
              <label class="form-label" for="store-status">Estado</label>
              <select class="form-select" id="store-status" name="status" required>
                <option value="" class="disabled">Seleccione un estado</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
              </select>
            </div>
            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">Guardar Empresa</button>
            </div>
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

  function geolocate() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function (position) {
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
