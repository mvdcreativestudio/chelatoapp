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
  ];

  // Añadir animación de transición
  document.querySelectorAll('.integration-fields').forEach(field => {
    field.style.transition = 'all 0.5s ease-in-out';
  });

  switches.forEach(switchObj => {
    const toggleSwitch = document.getElementById(switchObj.id);
    const fields = switchObj.fieldsId ? document.getElementById(switchObj.fieldsId) : null;

    if (toggleSwitch.checked && fields) {
      fields.style.display = 'block';
    }

    toggleSwitch.addEventListener('change', function () {
      console.log('Checkbox invoices_enabled changed:', this.checked); // Para verificar si se detecta el cambio

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
  const submitButton = document.querySelector('button[type="submit"]'); // Selector específico del botón de envío

  submitButton.addEventListener('click', function (event) {
    let formIsValid = true;

    switches.forEach(switchObj => {
      const toggleSwitch = document.getElementById(switchObj.id);
      const fields = switchObj.fieldsId ? document.getElementById(switchObj.fieldsId) : null;

      if (toggleSwitch.checked && fields) {
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
