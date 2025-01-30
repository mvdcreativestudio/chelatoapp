document.addEventListener('DOMContentLoaded', function () {

  // Validación en tiempo real
  function validateInput(input, requiredFields = []) {
    const errorMessage = document.createElement('small');
    errorMessage.className = 'text-danger error-message';

    if (input.nextElementSibling && input.nextElementSibling.classList.contains('error-message')) {
      input.nextElementSibling.remove();
    }

    if (input.value.trim() === '' && requiredFields.includes(input.id)) {
      errorMessage.textContent = 'Este campo es obligatorio.';
      input.classList.add('is-invalid');
      input.parentNode.appendChild(errorMessage);
      return false;
    } else {
      input.classList.remove('is-invalid');
    }
    return true;
  }

  // Validación antes de enviar el formulario
  const submitButton = document.querySelector('button[type="submit"]');

  submitButton.addEventListener('click', function (event) {
    let formIsValid = true;

    if (!formIsValid) {
      event.preventDefault(); // Evita el envío del formulario si hay campos vacíos
      Swal.fire({
        title: 'Campos incompletos',
        text: 'Por favor, complete todos los campos obligatorios antes de actualizar la empresa.',
        icon: 'warning',
        confirmButtonText: 'Aceptar'
      });
    }
  });


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

  // Llamar a la función cuando el checkbox de Mercado Pago Presencial se activa
  mercadoPagoSwitchPresencial.addEventListener('change', function () {
    const fieldsContainer = document.getElementById('mercadoPagoFieldsPresencial');
    if (this.checked) {
      fieldsContainer.style.display = 'block';
      parseAddress(); // Rellenar automáticamente los campos
    } else {
      fieldsContainer.style.display = 'none';
    }
  });

  // Rellenar automáticamente si ya está activado al cargar la página
  if (mercadoPagoSwitchPresencial.checked) {
    parseAddress();
  }
});

document.getElementById('scanntechSwitch').addEventListener('change', function () {
  if (this.checked) {
      document.getElementById('fiservSwitch').checked = false;
  }
});

// document.getElementById('fiservSwitch').addEventListener('change', function () {
//   if (this.checked) {
//       document.getElementById('scanntechSwitch').checked = false;
//       document.getElementById('handySwitch').checked = false;
//   }
// });

// document.getElementById('handySwitch').addEventListener('change', function () {
//   if (this.checked) {
//       document.getElementById('scanntechSwitch').checked = false;
//       document.getElementById('fiservSwitch').checked = false;
//   }
// });
