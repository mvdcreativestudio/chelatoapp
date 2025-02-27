document.addEventListener('DOMContentLoaded', () => {
  const configureButtons = document.querySelectorAll('.configure-mp-presencial');

  configureButtons.forEach(button => {
    button.addEventListener('click', function () {
      const storeId = this.dataset.storeId;
      const modalId = `mercadoPagoPresencialModal-${storeId}`;
      const modal = document.getElementById(modalId);

      const bootstrapModal = new bootstrap.Modal(modal);
      bootstrapModal.show();
    });
  });

  document.querySelectorAll('[id^="mercadoPagoSwitchPresencial-"]').forEach(checkbox => {
    checkbox.addEventListener('change', function (e) {
      const storeId = this.id.split('-')[1];
      const fieldsContainer = document.getElementById(`mercadoPagoFieldsPresencial-${storeId}`);
      fieldsContainer.style.display = this.checked ? 'block' : 'none';

      if (!this.checked) {
        e.preventDefault();
        Swal.fire({
          title: '¿Estás seguro?',
          text: 'Vas a desactivar MercadoPago Presencial',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, desactivar',
          cancelButtonText: 'Cancelar'
        }).then(result => {
          if (result.isConfirmed) {
            $.ajax({
              url: `${window.baseUrl}admin/integrations/${storeId}/mercadopago-presencial`,
              method: 'POST',
              data: {
                accepts_mercadopago_presencial: 0
              },
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function (response) {
                if (response.success) {
                  const card = document.querySelector(`#store-content-${storeId} .integration-card`);

                  fieldsContainer.style.display = 'none';

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
                  Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al desactivar MercadoPago Presencial',
                    customClass: {
                      popup: 'swal-custom-popup'
                    }
                  });
                  checkbox.checked = true;
                  fieldsContainer.style.display = 'block';
                }
              },
              error: function (xhr) {
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: xhr.responseJSON?.message || 'Error al desactivar MercadoPago Presencial',
                  customClass: {
                    popup: 'swal-custom-popup'
                  }
                });
                checkbox.checked = true;
                fieldsContainer.style.display = 'block';
              }
            });
          } else {
            this.checked = true;
            fieldsContainer.style.display = 'block';
          }
        });
      } else {
        const modal = new bootstrap.Modal(document.querySelector(`#mercadoPagoPresencialModal-${storeId}`));
        modal.show();
      }
    });
  });

  document.querySelectorAll('.mercadoPagoPresencialForm').forEach(form => {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const storeId = this.id.split('-')[1];
      const formData = new FormData(this);
      formData.append('accepts_mercadopago_presencial', '1');

      $.ajax({
        url: `${window.baseUrl}admin/integrations/${storeId}/mercadopago-presencial`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
          if (response.success) {
            const modal = document.getElementById(`mercadoPagoPresencialModal-${storeId}`);
            const bsModal = bootstrap.Modal.getInstance(modal);
            bsModal.hide();

            const card = document.querySelector(`#store-content-${storeId} .integration-card`);
            let statusIndicator = card.querySelector('.status-indicator');

            if (formData.get('accepts_mercadopago_presencial')) {
              if (!statusIndicator) {
                statusIndicator = document.createElement('span');
                statusIndicator.className = 'status-indicator';
                statusIndicator.innerHTML = '<i class="bx bx-check text-white"></i>';
                card.querySelector('.card-header').appendChild(statusIndicator);
              }
            } else if (statusIndicator) {
              statusIndicator.remove();
            }

            Swal.fire({
              icon: 'success',
              title: 'Éxito',
              text: response.message,
              showConfirmButton: false,
              timer: 1500
            }).then(() => {
              location.reload();
            });
          }
        },
        error: function (xhr) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: xhr.responseJSON?.message || 'Error al actualizar la configuración'
          });
        }
      });
    });
  });

  function checkMercadoPagoPresencialConnection(storeId) {
    // 1) Seleccionamos los elementos clave del modal
    const modalSelector = '#mercadoPagoPresencialConnectionModal-' + storeId;
    const loaderSelector = '#mercadoPagoPresencialConnectionLoader-' + storeId;
    const dataSelector = '#mercadoPagoPresencialConnectionData-' + storeId;
    const errorSelector = '#mercadoPagoPresencialConnectionError-' + storeId;

    // 2) Mostramos el modal
    $(modalSelector).modal('show');

    // 3) Reseteamos estados: mostrar loader, ocultar datos y error
    $(loaderSelector).show();
    $(dataSelector).hide();
    $(errorSelector).hide().empty();

    // 4) Hacemos la llamada AJAX para obtener información de la conexión
    $.ajax({
      url: `${window.baseUrl}admin/integrations/${storeId}/mercadopago-presencial-connection`,
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        // Éxito: rellenamos los campos con los datos recibidos
        // (Ajusta los nombres de las propiedades de 'response' según tu API)
        $(modalSelector)
          .find('.mp-public-key')
          .text(response.data.public_key || 'N/A');
        $(modalSelector)
          .find('.mp-access-token')
          .text(response.data.access_token || 'N/A');
        $(modalSelector)
          .find('.mp-secret-key')
          .text(response.data.secret_key || 'N/A');
        $(modalSelector)
          .find('.mp-user-id')
          .text(response.data.user_id_mp || 'N/A');
        $(modalSelector)
          .find('.mp-branch-name')
          .text(response.data.name || 'N/A');

        // Ocultamos el loader y mostramos la sección de datos
        $(loaderSelector).hide();
        $(dataSelector).show();
      },
      error: function (xhr) {
        // Error: informamos el mensaje de error que devuelva el servidor
        let errorMessage = 'Ocurrió un error al obtener la información.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }

        // Mostramos el error
        $(loaderSelector).hide();
        $(errorSelector).text(errorMessage).show();
      }
    });
  }

  window.checkMercadoPagoPresencialConnection = checkMercadoPagoPresencialConnection;

  document.querySelectorAll('[id^="mercadoPagoSwitchOnline-"]').forEach(checkbox => {
    checkbox.addEventListener('change', function (e) {
      const storeId = this.id.split('-')[1];
      const fieldsContainer = document.getElementById(`mercadoPagoFieldsOnline-${storeId}`);
      fieldsContainer.style.display = this.checked ? 'block' : 'none';

      if (!this.checked) {
        e.preventDefault();
        Swal.fire({
          title: '¿Estás seguro?',
          text: 'Vas a desactivar MercadoPago Online',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, desactivar',
          cancelButtonText: 'Cancelar'
        }).then(result => {
          if (result.isConfirmed) {
            $.ajax({
              url: `${window.baseUrl}admin/integrations/${storeId}/mercadopago-online`,
              method: 'POST',
              data: {
                accepts_mercadopago_online: 0
              },
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function (response) {
                if (response.success) {
                  const card = document.querySelector(`#store-content-${storeId} .integration-card`);

                  fieldsContainer.style.display = 'none';

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
                  Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al desactivar MercadoPago Online'
                  });
                  checkbox.checked = true;
                  fieldsContainer.style.display = 'block';
                }
              },
              error: function (xhr) {
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: xhr.responseJSON?.message || 'Error al desactivar MercadoPago Online'
                });
                checkbox.checked = true;
                fieldsContainer.style.display = 'block';
              }
            });
          } else {
            this.checked = true;
            fieldsContainer.style.display = 'block';
          }
        });
      } else {
        const modal = new bootstrap.Modal(document.querySelector(`#mercadoPagoOnlineModal-${storeId}`));
        modal.show();
      }
    });
  });

  document.querySelectorAll('.mercadoPagoOnlineForm').forEach(form => {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const storeId = this.id.split('-')[1];
      const formData = new FormData(this);

      $.ajax({
        url: `${window.baseUrl}admin/integrations/${storeId}/mercadopago-online`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
          if (response.success) {
            const modal = document.getElementById(`mercadoPagoOnlineModal-${storeId}`);
            const bsModal = bootstrap.Modal.getInstance(modal);
            bsModal.hide();

            const card = document.querySelector(`#store-content-${storeId} .integration-card`);
            let statusIndicator = card.querySelector('.status-indicator');

            if (formData.get('accepts_mercadopago_online')) {
              if (!statusIndicator) {
                statusIndicator = document.createElement('span');
                statusIndicator.className = 'status-indicator';
                statusIndicator.innerHTML = '<i class="bx bx-check text-white"></i>';
                card.querySelector('.card-header').appendChild(statusIndicator);
              }
            } else if (statusIndicator) {
              statusIndicator.remove();
            }

            Swal.fire({
              icon: 'success',
              title: 'Éxito',
              text: response.message,
              showConfirmButton: false,
              timer: 1500
            }).then(() => {
              location.reload();
            });
          }
        },
        error: function (xhr) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: xhr.responseJSON?.message || 'Error al actualizar la configuración'
          });
        }
      });
    });
  });

});
