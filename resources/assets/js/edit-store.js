document.addEventListener('DOMContentLoaded', function () {

  const addressField = document.getElementById('store-address');
  const streetNumberField = document.getElementById('street_number');
  const streetNameField = document.getElementById('street_name');
  const cityNameField = document.getElementById('city_name');
  const stateNameField = document.getElementById('state_name');
  const latitudeField = document.getElementById('latitude');
  const longitudeField = document.getElementById('longitude');

  function parseAddress() {
    // Si el campo de dirección ya tiene un valor
    if (addressField.value) {
      const address = addressField.value;

      // Usar Google Maps Autocomplete para analizar y rellenar los campos
      const geocoder = new google.maps.Geocoder();

      geocoder.geocode({ address }, function (results, status) {
        if (status === 'OK' && results.length > 0) {
          const place = results[0];

          const addressComponents = {
            street_number: '',
            route: '',
            locality: '',
            administrative_area_level_1: '',
            country: '',
            postal_code: ''
          };

          // Extraer componentes de la dirección
          place.address_components.forEach(component => {
            const types = component.types;
            if (types.includes('street_number')) {
              addressComponents.street_number = component.long_name;
            } else if (types.includes('route')) {
              addressComponents.route = component.long_name;
            } else if (types.includes('locality')) {
              addressComponents.locality = component.long_name;
            } else if (types.includes('administrative_area_level_1')) {
              addressComponents.administrative_area_level_1 = component.long_name;
            } else if (types.includes('country')) {
              addressComponents.country = component.long_name;
            } else if (types.includes('postal_code')) {
              addressComponents.postal_code = component.long_name;
            }
          });

          // Rellenar los campos del formulario si están vacíos
          if (!streetNumberField.value) {
            streetNumberField.value = addressComponents.street_number;
          }
          if (!streetNameField.value) {
            streetNameField.value = addressComponents.route;
          }
          if (!cityNameField.value) {
            cityNameField.value = addressComponents.locality;
          }
          if (!stateNameField.value) {
            stateNameField.value = addressComponents.administrative_area_level_1;
          }

          // Rellenar latitud y longitud si están vacíos
          if (!latitudeField.value) {
            latitudeField.value = place.geometry.location.lat();
          }
          if (!longitudeField.value) {
            longitudeField.value = place.geometry.location.lng();
          }
        } else {
          console.error('Error al procesar la dirección:', status);
        }
      });
    }
  }

});
