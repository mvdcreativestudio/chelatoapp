document.addEventListener('DOMContentLoaded', () => {
  // Variable para almacenar el estado inicial del switch
  let switchState = {};

  // Manejo de switches de configuración de correo
  const emailConfigSwitches = document.querySelectorAll('[id^="emailConfigSwitch-"]');

  emailConfigSwitches.forEach(switchEl => {
      const storeId = switchEl.id.split('-')[1];

      // Inicializamos el estado inicial del switch
      switchState[storeId] = switchEl.checked;

      switchEl.addEventListener('change', function () {
          const modalElement = document.getElementById(`emailConfigModal-${storeId}`);
          const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);

          if (this.checked) {
              // Mostrar el modal al activar el switch
              modal.show();

              // Revertir el switch si se cierra el modal sin guardar
              modalElement.addEventListener('hidden.bs.modal', () => {
                  if (!switchState[storeId]) {
                      switchEl.checked = false;
                  }
              }, { once: true });
          } else {
              // Confirmar desactivación al desmarcar el switch
              Swal.fire({
                  title: '¿Estás seguro?',
                  text: 'Se eliminará la configuración de correo actual',
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Sí, desactivar',
                  cancelButtonText: 'Cancelar'
              }).then((result) => {
                  if (result.isConfirmed) {
                      deactivateEmailConfig(storeId, switchEl);
                  } else {
                      switchEl.checked = true; // Revertir el estado si se cancela
                  }
              });
          }
      });
  });

  // Manejo de botones de guardar configuración en el modal
  const saveEmailConfigBtns = document.querySelectorAll('[id^="saveEmailConfig"]');

  saveEmailConfigBtns.forEach(btn => {
      btn.addEventListener('click', function () {
          const storeId = this.id.split('-')[1];
          const modalElement = document.getElementById(`emailConfigModal-${storeId}`);
          const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
          const form = document.getElementById(`emailConfigForm-${storeId}`);

          const formData = new FormData(form);

          $.ajax({
              url: `${window.baseUrl}admin/integrations/${storeId}/email-config`,
              type: 'POST',
              data: formData,
              processData: false,
              contentType: false,
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function (response) {
                  if (response.success) {
                      // Actualizar el estado del switch
                      switchState[storeId] = true;
                      modal.hide();

                      Swal.fire({
                          icon: 'success',
                          title: 'Éxito',
                          text: response.message,
                          showConfirmButton: false,
                          timer: 1500
                      }).then(() => {
                          location.reload();
                      });
                  } else {
                      handleError(response.message);
                  }
              },
              error: function (xhr) {
                  handleError(xhr.responseJSON?.message || 'Error al guardar la configuración');
              }
          });
      });
  });

  // Función para desactivar la configuración de correo
  function deactivateEmailConfig(storeId, switchEl) {
      const formData = new FormData();
      formData.append('stores_email_config', '0');

      $.ajax({
          url: `${window.baseUrl}admin/integrations/${storeId}/email-config`,
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (response) {
              if (response.success) {
                  // Actualizar el estado del switch
                  switchState[storeId] = false;

                  Swal.fire({
                      icon: 'success',
                      title: 'Éxito',
                      text: response.message,
                      showConfirmButton: false,
                      timer: 1500
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  handleError(response.message);
                  switchEl.checked = true; // Revertir el estado del switch si hay error
              }
          },
          error: function (xhr) {
              handleError(xhr.responseJSON?.message || 'Error al desactivar la configuración');
              switchEl.checked = true; // Revertir el estado del switch si hay error
          }
      });
  }

  // Función para manejar errores
  function handleError(message) {
      Swal.fire({
          icon: 'error',
          title: 'Error',
          text: message,
          confirmButtonText: 'Aceptar'
      });
  }
});
