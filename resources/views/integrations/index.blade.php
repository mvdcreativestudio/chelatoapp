@extends('layouts/layoutMaster')

@section('title', 'Herramientas')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/sortablejs/sortable.js'
])
@endsection

@section('page-script')
<script
  src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&loading=async&libraries=places&callback=initMap">
</script>
<script type="text/javascript">
  window.baseUrl = "{{ url('/') }}";
  window.csrfToken = "{{ csrf_token() }}";
  var stores = @json($stores);
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@vite(['resources/assets/js/integrations/app-integration-mercado-pago.js'])
@vite(['resources/assets/js/integrations/app-integration-peya.js'])
@vite(['resources/assets/js/integrations/app-integration-pymo.js'])
@vite(['resources/assets/js/integrations/app-integration-email.js'])
@vite(['resources/assets/js/integrations/app-integration-ecommerce.js'])
@vite(['resources/assets/js/integrations/app-integration-handy.js'])
@vite(['resources/assets/js/integrations/app-integration-oca.js'])
@vite(['resources/assets/js/integrations/app-integration-fiserv.js'])
@vite(['resources/assets/js/integrations/app-integration-scanntech.js'])


@endsection

@section('content')

<h4 class="py-3 mb-4">
  <span class="text-muted fw-light"></span> Herramientas
</h4>

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
  {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger mt-3 mb-3">
  {{ session('error') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
<div class="alert alert-danger">
  {{ $error }}
</div>
@endforeach
@endif

<div class="nav-tabs-container position-relative">
  <ul class="nav nav-tabs" id="storeTabs" role="tablist">
    @foreach($stores as $store)
    <li class="nav-item" role="presentation">
      <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="store-tab-{{ $store->id }}" data-bs-toggle="tab"
        data-bs-target="#store-content-{{ $store->id }}" type="button" role="tab"
        aria-controls="store-content-{{ $store->id }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
        <i class='bx bx-store-alt me-1'></i>
        {{ $store->name }}
        <small class="ms-2 text-muted">#{{ $store->id }}</small>
      </button>
    </li>
    @endforeach
  </ul>

  <div class="tab-content mt-3 overflow-hidden" id="storeTabsContent">
    @foreach($stores as $store)
    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="store-content-{{ $store->id }}"
      data-store-id="{{ $store->id }}" role="tabpanel">
      <div class="integration-grid">
        @include('stores.partials.handy', ['store' => $store, 'devices' => $store->posDevices])
        @include('stores.partials.oca', ['store' => $store, 'devices' => $store->posDevices])
        @include('stores.partials.fiserv', ['store' => $store, 'devices' => $store->posDevices])
        @include('stores.partials.scanntech', ['store' => $store, 'devices' => $store->posDevices])
        @include('stores.partials.integracion-ecommerce')
        @include('stores.partials.configuracion-correo')
        @include('stores.partials.pedidos-ya')
        @include('stores.partials.mercado-pago-pagos-presenciales', [
        'store' => $store,
        'mercadoPagoPresencial' => $store->mercadoPagoAccount->firstWhere('type',
        \App\Enums\MercadoPago\MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL),
        'mercadoPagoAccountStore' => $store->mercadoPagoAccountStore
        ])
        @include('stores.partials.mercado-pago-pagos-online', [
        'store' => $store,
        'mercadoPagoOnline' => $store->mercadoPagoAccount->firstWhere('type',
        \App\Enums\MercadoPago\MercadoPagoApplicationTypeEnum::PAID_ONLINE)
        ])
        @include('stores.partials.pymo', ['companyInfo' => $store->companyInfo])
      </div>
    </div>
    @endforeach
  </div>
</div>

<style>
  .nav-tabs {
    border-bottom: none;
    gap: 0.5rem;
    padding: 1rem 1rem 0;
    background: #f8f9fa;
    border-radius: 0.8rem 0.8rem 0 0;
  }

  .nav-tabs .nav-link {
    border-radius: 1rem 1rem 0 0;
    padding: 0.75rem 1.5rem;
    background: #e9ecef;
    border: 1px solid #dee2e6;
    border-bottom: none;
    margin-bottom: 0;
    transition: all 0.2s ease-in-out;
    position: relative;
    top: 1px;
  }

  .nav-tabs .nav-link:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
  }

  .nav-tabs .nav-link.active {
    background: white;
    border-color: #dee2e6;
    border-bottom: none;
    box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.05);
  }

  .tab-content {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0 0 1rem 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  }

  .config-wrapper {
    position: relative;
    display: flex;
    align-items: flex-start;
  }

  .main-card {
    width: 100%;
  }


  .main-card.active {
    transform: translateX(20px);
  }

  .config-panel {
    max-width: 0;
    overflow: hidden;
    opacity: 0;
    transition: all 0.3s ease;
  }

  .config-panel.active {
    max-width: 500px;
    opacity: 1;
  }

  .config-panel .card {
    width: 450px;
  }

  .card .integration-fields {
    max-height: 100%;
    overflow-y: auto;
  }

  .card .form-label {
    margin-bottom: 0.25rem;
  }

  .integration-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  }

  @media (max-width: 768px) {
    .integration-grid {
      grid-template-columns: 1fr;
    }

    .card {
      margin-bottom: 1rem;
    }
  }

  .integration-grid>* {
    width: 100%;
    min-width: 0;
  }

  .integration-card {
    width: 100%;
  }

  .integration-card .card {
    height: 220px;
    margin: 0;
  }

  .integration-card .card-header {
    height: 100px;
    position: relative;
    padding: 1rem;
  }

  .integration-card .integration-icon {
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .integration-card .integration-icon img {
    width: 70px;
    height: auto;
  }

  .integration-card .status-indicator {
    position: absolute;
    top: 0;
    right: 0;
    transform: translate(50%, -50%);
    padding: 0.25rem;
    background-color: #198754;
    border-radius: 50%;
  }

  .integration-card .card-body {
    padding: 1.5rem;
  }

  .integration-card .card-body {
    padding: 1.0rem;
  }

  .integration-card .card-title {
    margin-top: 0.6rem;
  }

  .integration-card small {
    margin-top: 0.25rem;
    display: block;
  }

  .swal-custom-popup {
    z-index: 2999 !important;
  }

  .swal2-container {
    z-index: 3000 !important;
  }

  /* Estilos para el mapa */
  #map {
    height: 300px;
    width: 100%;
    border-radius: 5px;
  }
</style>
<script>
  // Objeto para almacenar las referencias de cada mapa (map, marker)
const mpMaps = {};

// ======================
// 1) Funciones de mapa
// ======================
function initMapForStore(storeId) {
  const mapDiv         = document.getElementById(`map-${storeId}`);
  const autocompleteEl = document.getElementById(`autocomplete-${storeId}`);

  // Si no existe el contenedor del mapa, salimos
  if (!mapDiv) return;

  // Posición por defecto (Buenos Aires)

  // Crear mapa
  const map = new google.maps.Map(mapDiv, {
    zoom: 15,
    mapTypeControl: false,
    fullscreenControl: false,
    streetViewControl: false
  });

  // Crear marcador
  const marker = new google.maps.Marker({
    map: map,
    title: "Ubicación seleccionada",
    draggable: true
  });

  // Guardamos las referencias en mpMaps
  mpMaps[storeId] = { map, marker };

  // Autocomplete
  if (autocompleteEl) {
    const autocomplete = new google.maps.places.Autocomplete(autocompleteEl, {
      types: ["geocode"]
      // componentRestrictions: { country: "AR" }
    });

    autocomplete.addListener("place_changed", () => {
      const place = autocomplete.getPlace();
      if (!place.geometry) {
        alert("No se encontraron coordenadas para esta dirección.");
        return;
      }
      map.setCenter(place.geometry.location);
      marker.setPosition(place.geometry.location);
      fillLocationFields(place, storeId);
    });
  }

  // Evento click en el mapa
  map.addListener("click", (event) => {
    marker.setPosition(event.latLng);
    getAddressFromCoords(event.latLng.lat(), event.latLng.lng(), storeId);
  });

  // Botón "Mi Ubicación" (opcional)
  addLocationButton(map, storeId, marker);
}

// Botón para centrar en la ubicación actual
function addLocationButton(map, storeId, marker) {
  const locationButton = document.createElement("button");
  locationButton.textContent = "📍 Mi Ubicación";
  // Estilos básicos
  locationButton.style.background = "#fff";
  locationButton.style.border = "none";
  locationButton.style.padding = "8px 12px";
  locationButton.style.fontSize = "14px";
  locationButton.style.cursor = "pointer";
  locationButton.style.borderRadius = "5px";
  locationButton.style.boxShadow = "0 2px 6px rgba(0,0,0,0.3)";
  locationButton.style.position = "absolute";
  locationButton.style.top = "10px";
  locationButton.style.right = "10px";
  locationButton.style.zIndex = "5";

  locationButton.addEventListener("click", () => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          const userLocation = {
            lat: pos.coords.latitude,
            lng: pos.coords.longitude,
          };
          map.setCenter(userLocation);
          map.setZoom(16);
          marker.setPosition(userLocation);
          getAddressFromCoords(userLocation.lat, userLocation.lng, storeId);
        },
        (err) => {
          console.error("Error geolocalización:", err);
          // alert("No se pudo obtener tu ubicación.");
          Swal.fire({
            icon: 'error',
            title: 'Error de Ubicación',
            text: 'No se pudo obtener tu ubicación.',
            confirmButtonText: 'Cerrar'
          });
        }
      );
    } else {
      // alert("Tu navegador no soporta la geolocalización.");
      Swal.fire({
        icon: 'error',
        title: 'Error de Ubicación',
        text: 'Tu navegador no soporta la geolocalización.',
        confirmButtonText: 'Cerrar'
      });
    }
  });

  map.controls[google.maps.ControlPosition.TOP_RIGHT].push(locationButton);
}

// Rellena los campos lat/lng/calle/etc.
function fillLocationFields(place, storeId) {
  const latEl   = document.getElementById(`latitude-${storeId}`);
  const lngEl   = document.getElementById(`longitude-${storeId}`);
  const streetEl= document.getElementById(`street_name-${storeId}`);
  const numEl   = document.getElementById(`street_number-${storeId}`);
  const cityEl  = document.getElementById(`city_name-${storeId}`);
  const stateEl = document.getElementById(`state_name-${storeId}`);

  if (latEl) latEl.value = place.geometry.location.lat();
  if (lngEl) lngEl.value = place.geometry.location.lng();

  let streetName = "", streetNumber = "", city = "", state = "";
  place.address_components.forEach((component) => {
    if (component.types.includes("route")) {
      streetName = component.long_name;
    }
    if (component.types.includes("street_number")) {
      streetNumber = component.long_name;
    }
    if (component.types.includes("locality")) {
      city = component.long_name;
    }
    if (component.types.includes("administrative_area_level_1")) {
      state = component.long_name;
    }
  });

  if (streetEl) streetEl.value = streetName;
  if (numEl)    numEl.value    = streetNumber;
  if (cityEl)   cityEl.value   = city;
  if (stateEl)  stateEl.value  = state;
}

// Geocodifica coords -> dirección
function getAddressFromCoords(lat, lng, storeId) {
  const geocoder = new google.maps.Geocoder();
  geocoder.geocode({ location: { lat, lng } }, (results, status) => {
    if (status === "OK" && results[0]) {
      const autoEl = document.getElementById(`autocomplete-${storeId}`);
      if (autoEl) autoEl.value = results[0].formatted_address;
      fillLocationFields(results[0], storeId);
    } else {
      // alert("No se encontraron resultados en esta ubicación.");
      Swal.fire({
        icon: 'error',
        title: 'Error de Geocodificación',
        text: 'No se encontraron resultados en esta ubicación.',
        confirmButtonText: 'Cerrar'
      });
    }
  });
}

// =====================
// 2) Al abrir cada modal
// =====================
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".mercadoPagoPresencialModal").forEach(modal => {
    modal.addEventListener("shown.bs.modal", function () {
      // 1) Identificar storeId
      const storeId = this.id.split("-")[1];

      // 2) Inicializa el mapa para esta tienda
      initMapForStore(storeId);

      // 3) Obtener ref al mapa y marcador (guardados en mpMaps)
      const mapData = mpMaps[storeId];
      if (!mapData) return; // Por seguridad

      const map    = mapData.map;
      const marker = mapData.marker;

      // 4) Botón de credenciales
      const btnCredenciales = document.getElementById(`btnCredencialesMercadoPagoPresencial-${storeId}`);
      if (btnCredenciales) btnCredenciales.disabled = true; // Por defecto

      // 5) Geolocalización automática
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          (pos) => {
            const userLocation = { lat: pos.coords.latitude, lng: pos.coords.longitude };
            map.setCenter(userLocation);
            map.setZoom(16);
            marker.setPosition(userLocation);

            // Rellena campos
            getAddressFromCoords(userLocation.lat, userLocation.lng, storeId);

            // Habilita el botón
            if (btnCredenciales) {
              btnCredenciales.disabled = false;
            }
          },
          (err) => {
            console.log("Geolocation error:", err);
            Swal.fire({
              icon: 'error',
              title: 'Error de Ubicación',
              text: 'No se pudo obtener tu ubicación.',
              confirmButtonText: 'Cerrar'
            });
            // Si falla la geolocalización, deshabilitamos el botón
            if (btnCredenciales) btnCredenciales.disabled = true;
          }
        );
      } else {
        // Si no soporta geolocalización, deshabilitamos
        if (btnCredenciales) btnCredenciales.disabled = true;
      }
    });
  });
});


</script>
@endsection