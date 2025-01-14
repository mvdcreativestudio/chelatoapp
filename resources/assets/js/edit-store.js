document.addEventListener('DOMContentLoaded', function () {
  const switches = [
    { id: 'peyaEnviosSwitch', fieldsId: 'peyaEnviosFields', requiredFields: ['peyaEnviosKey'] },
    {
      id: 'mercadoPagoSwitchOnline',
      fieldsId: 'mercadoPagoFieldsOnline',
      requiredFields: ['mercadoPagoPublicKeyOnline', 'mercadoPagoAccessTokenOnline', 'mercadoPagoSecretKeyOnline']
    },
    {
      id: 'mercadoPagoSwitchPresencial',
      fieldsId: 'mercadoPagoFieldsPresencial',
      requiredFields: [
        'mercadoPagoPublicKeyPresencial',
        'mercadoPagoAccessTokenPresencial',
        'mercadoPagoSecretKeyPresencial',
        'user_id',
        'street_number',
        'street_name',
        'city_name',
        'state_name',
        'latitude',
        'longitude'
      ]
    },
    { id: 'ecommerceSwitch', fieldsId: null },
    {
      id: 'invoicesEnabledSwitch',
      fieldsId: 'pymoFields',
      requiredFields: ['pymoUser', 'pymoPassword', 'pymoBranchOffice']
    },
    { id: 'scanntechSwitch', fieldsId: 'scanntechFields', requiredFields: ['scanntechCompany', 'scanntechBranch'] },

    {
      id: 'emailConfigSwitch',
      fieldsId: 'emailConfigFields',
      requiredFields: [
        'mailHost',
        'mailPort',
        'mailUsername',
        'mailPassword',
        'mailEncryption',
        'mailFromAddress',
        'mailFromName'
      ]
    }

  ];

  // Añadir animación de transición
  document.querySelectorAll('.integration-fields').forEach(field => {
    field.style.transition = 'all 0.5s ease-in-out';
  });

  switches.forEach(switchObj => {
    const toggleSwitch = document.getElementById(switchObj.id);
    const fields = switchObj.fieldsId ? document.getElementById(switchObj.fieldsId) : null;

    if (toggleSwitch && toggleSwitch.checked && fields) {
      fields.style.display = 'block';
    }

    if (toggleSwitch) {
      toggleSwitch.addEventListener('change', function () {
        if (!this.checked && fields) {
          Swal.fire({
            title: '¿Estás seguro?',
            text: 'Se perderán los datos de esta integración y deberá ser realizada nuevamente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
          }).then(result => {
            if (result.isConfirmed) {
              fields.style.opacity = 0;
              setTimeout(() => {
                fields.style.display = 'none';
                fields.style.opacity = 1;
              }, 500);
              // Limpia los campos al desactivar la integración
              fields.querySelectorAll('input').forEach(input => (input.value = ''));
              fields.querySelectorAll('.error-message').forEach(error => error.remove());
            } else {
              toggleSwitch.checked = true;
            }
          });
        } else if (fields) {
          fields.style.display = 'block';
          fields.style.opacity = 0;
          setTimeout(() => {
            fields.style.opacity = 1;
          }, 10);
        }
      });
    }
  });

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

    switches.forEach(switchObj => {
      const toggleSwitch = document.getElementById(switchObj.id);
      const fields = switchObj.fieldsId ? document.getElementById(switchObj.fieldsId) : null;

      if (toggleSwitch && toggleSwitch.checked && fields) {
        const inputs = fields.querySelectorAll('input');

        inputs.forEach(input => {
          const isValid = validateInput(input, switchObj.requiredFields || []);
          if (!isValid) {
            formIsValid = false;
          }
        });
      }
    });

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

  const addressField = document.getElementById('store-address');
  const streetNumberField = document.getElementById('street_number');
  const streetNameField = document.getElementById('street_name');
  const cityNameField = document.getElementById('city_name');
  const stateNameField = document.getElementById('state_name');
  const latitudeField = document.getElementById('latitude');
  const longitudeField = document.getElementById('longitude');

  const mercadoPagoSwitchPresencial = document.getElementById('mercadoPagoSwitchPresencial');

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
