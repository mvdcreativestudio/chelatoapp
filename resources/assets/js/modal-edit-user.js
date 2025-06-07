'use strict';

document.addEventListener('DOMContentLoaded', function () {
  (function () {
    const editUserForm = document.getElementById('editClientForm');
    const submitButton = editUserForm.querySelector('button[type="submit"]'); // Botón de envío

    if (editUserForm) {
      editUserForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // Validar campos antes de enviar
        const isValid = validateForm();
        if (!isValid) return;

        disableSubmitButton();
        submitEditClient();
      });
    }

    function validateForm() {
      let isValid = true;

      // Limpiar errores previos
      const errorFields = editUserForm.querySelectorAll('.text-danger');
      errorFields.forEach((field) => field.remove());

      // Validación de Email (solo si no está vacío)
      const emailInput = document.getElementById('modalEditUserEmail');
      const emailValue = emailInput.value.trim();
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (emailValue && !emailPattern.test(emailValue)) {
        showFieldError(emailInput, 'Por favor, ingresá un correo electrónico válido.');
        isValid = false;
      }

      // Validación de CI (8 caracteres numéricos)
      const ciInput = document.getElementById('modalEditUserCi');
      if (ciInput) {
        const ciValue = ciInput.value.trim();
        const ciPattern = /^\d{8}$/;

        if (!ciPattern.test(ciValue)) {
          showFieldError(ciInput, 'El documento (CI) debe tener exactamente 8 caracteres numéricos.');
          isValid = false;
        }
      }

      return isValid;
    }

    function showFieldError(field, message) {
      const errorElement = document.createElement('small');
      errorElement.classList.add('text-danger'); // Clase de Bootstrap para estilos de error
      errorElement.textContent = message;
      field.parentElement.appendChild(errorElement);
    }

    function disableSubmitButton() {
      submitButton.disabled = true;
      submitButton.textContent = 'Guardando...';
    }

    function enableSubmitButton() {
      submitButton.disabled = false;
      submitButton.textContent = 'Guardar cambios';
    }

    function submitEditClient() {
      const formData = new FormData(editUserForm);
      const clientId = editUserForm.getAttribute('data-client-id');
      if (!clientId) return;

      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
      const url = `${window.baseUrl}admin/clients/${clientId}`;
      const clientData = Object.fromEntries(formData.entries());

      fetch(url, {
          method: 'PUT',
          headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Content-Type': 'application/json',
          },
          body: JSON.stringify(clientData),
      })
          .then(async (response) => {
              if (!response.ok) {
                  const errorData = await response.json();
                  throw errorData;
              }
              return response.json();
          })
          .then((data) => {
              if (data.success) {
                  const modal = bootstrap.Modal.getInstance(document.getElementById('editUser'));
                  modal.hide();

                  Swal.fire({
                      icon: 'success',
                      title: 'Cliente Actualizado',
                      text: data.message,
                  }).then(() => {
                      window.location.reload(); // Recargar la página
                  });
              }
          })
          .catch((error) => {
              if (error.errors) {
                  displayValidationErrors(error.errors);
              } else {
                  displayGeneralError(error.message || 'Error inesperado al actualizar el cliente.');
              }
              enableSubmitButton(); // Rehabilitar el botón en caso de error
          });
    }

    function displayValidationErrors(errors) {
      for (const fieldName in errors) {
        const field = editUserForm.querySelector(`[name="${fieldName}"]`);
        if (field) {
          const errorMessage = errors[fieldName][0];
          showFieldError(field, errorMessage);
        }
      }
    }

    function displayGeneralError(message) {
      const generalErrorContainer = editUserForm.querySelector('.general-errors');
      if (generalErrorContainer) generalErrorContainer.remove();

      const errorContainer = document.createElement('div');
      errorContainer.classList.add('alert', 'alert-danger', 'general-errors');
      errorContainer.textContent = message;
      editUserForm.prepend(errorContainer);
    }
  })();
});
