document.addEventListener('DOMContentLoaded', function () {
  const switches = [
    { id: 'peyaEnviosSwitch', fieldsId: 'peyaEnviosFields', requiredFields: ['peyaEnviosKey'] },
    {
      id: 'mercadoPagoSwitch',
      fieldsId: 'mercadoPagoFields',
      requiredFields: ['mercadoPagoPublicKey', 'mercadoPagoAccessToken', 'mercadoPagoSecretKey']
    },
    { id: 'ecommerceSwitch', fieldsId: null },
    { id: 'invoicesEnabledSwitch', fieldsId: 'pymoFields', requiredFields: ['pymoUser', 'pymoPassword', 'pymoBranchOffice'] },
    { id: 'scanntechSwitch', fieldsId: 'scanntechFields', requiredFields: ['scanntechCompany', 'scanntechBranch'] },
    { id: 'emailConfigSwitch', fieldsId: 'emailConfigFields', requiredFields: ['mailHost', 'mailPort', 'mailUsername', 'mailPassword', 'mailEncryption', 'mailFromAddress', 'mailFromName'] },
    { id: 'fiservSwitch', fieldsId: 'fiservFields', requiredFields: ['systemId'] },

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
              fields.querySelectorAll('input').forEach(input => input.value = '');
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
});

document.getElementById('scanntechSwitch').addEventListener('change', function () {
  if (this.checked) {
      document.getElementById('fiservSwitch').checked = false;
  }
});

document.getElementById('fiservSwitch').addEventListener('change', function () {
  if (this.checked) {
      document.getElementById('scanntechSwitch').checked = false;
  }
});
