$(function () {
  $('.create-dispatch-note').on('click', function () {
    const productId = $(this).data('product-id');
    const productName = $(this).data('product-name');
    const productQuantity = $(this).data('product-quantity');

    $('#product_id').val(productId);
    $('#product_name').val(productName);
    $('#quantity').attr('max', productQuantity);

    $('#offcanvasCreateDispatchNote').offcanvas('show');
  });

  $('#createDispatchNoteForm').on('submit', function (e) {
    e.preventDefault();

    const formData = $(this).serialize();
    var createDispatchNote = `${baseUrl}admin/dispatch-notes`;

    $.ajax({
      url: createDispatchNote,
      type: 'POST',
      data: formData,
      headers: {
        'X-CSRF-TOKEN': window.csrfToken
      },
      success: function (response) {
        $('#offcanvasCreateDispatchNote').offcanvas('hide');
        Swal.fire({
          title: '¡Éxito!',
          text: 'El remito ha sido creado correctamente.',
          icon: 'success',
          confirmButtonText: 'Aceptar'
        }).then((result) => {
          if (result.isConfirmed) {
            location.reload();
          }
        });
      },
      error: function (error) {
        $('#offcanvasCreateDispatchNote').offcanvas('hide');
        Swal.fire({
          title: 'Error',
          text: error.responseJSON.error || 'Hubo un problema al crear el remito.',
          icon: 'error',
          confirmButtonText: 'Aceptar'
        });
      }
    });
  });


  $('.create-delivery').on('click', function () {
    const dispatchNoteId = $(this).data('dispatch-note-id');
    $('#dispatch_note_id').val(dispatchNoteId);

    $.ajax({
      url: `${baseUrl}admin/note-deliveries/form`,
      type: 'GET',
      success: function (response) {
        $('#vehicle_id').empty();
        $('#driver_id').empty();
        $('#store_id').empty();
        console.log(response);
        response.vehicles.forEach(vehicle => {
          $('#vehicle_id').append(new Option(`${vehicle.number} - ${vehicle.plate}`, vehicle.id));
        });

        response.drivers.forEach(driver => {
          $('#driver_id').append(new Option(`${driver.name} - ${driver.document}`, driver.id));
        });

        response.stores.forEach(store => {
          $('#store_id').append(new Option(store.name, store.id));
        });

        $('#offcanvasCreateDelivery').offcanvas('show');
      },
      error: function (error) {
        Swal.fire({
          title: 'Error',
          text: error.responseJSON.error || 'Hubo un problema al obtener la información del formulario.',
          icon: 'error',
          confirmButtonText: 'Aceptar'
        });
      }
    });
  });

  $('#createDeliveryForm').on('submit', function (e) {
    e.preventDefault();

    const formData = $(this).serialize();
    const createDelivery = `${baseUrl}admin/note-deliveries`;

    $.ajax({
      url: createDelivery,
      type: 'POST',
      data: formData,
      headers: {
        'X-CSRF-TOKEN': window.csrfToken
      },
      success: function (response) {
        $('#offcanvasCreateDelivery').offcanvas('hide');
        Swal.fire({
          title: '¡Éxito!',
          text: 'El envío ha sido creado correctamente.',
          icon: 'success',
          confirmButtonText: 'Aceptar'
        }).then((result) => {
          if (result.isConfirmed) {
            location.reload();
          }
        });
      },
      error: function (error) {
        $('#offcanvasCreateDelivery').offcanvas('hide');
        Swal.fire({
          title: 'Error',
          text: error.responseJSON.error || 'Hubo un problema al crear el envío.',
          icon: 'error',
          confirmButtonText: 'Aceptar'
        });
      }
    });
  });

  $('.show-delivery').on('click', function () {
    const dispatchNoteId = $(this).data('dispatch-note-id');

    $.ajax({
      url: `${baseUrl}admin/note-deliveries/${dispatchNoteId}`,
      type: 'GET',
      success: function (response) {
        const formatDateTime = (dateString) => {
          if (!dateString) {
            return 'No disponible';
          }
          const date = new Date(dateString);
          const day = String(date.getDate()).padStart(2, '0');
          const month = String(date.getMonth() + 1).padStart(2, '0');
          const year = String(date.getFullYear()).slice(-2);
          const hours = String(date.getHours()).padStart(2, '0');
          const minutes = String(date.getMinutes()).padStart(2, '0');
          return `${day}/${month}/${year} ${hours}:${minutes}`;
        };

        const cubaStatus = response.dispatch_note.quantity >= 5 ?
          '<span class="badge bg-success">Cuba: Completa</span>' :
          '<span class="badge bg-warning">Cuba: Incompleta</span>';

        $('#deliveryDetailsModal .modal-body').html(`
                <div class="mb-3">${cubaStatus}</div>
                <p><strong>Vehículo:</strong> ${response.vehicle.number} - ${response.vehicle.plate}</p>
                <p><strong>Conductor:</strong> ${response.driver.name} - ${response.driver.document}</p>
                <p><strong>Producido en:</strong> ${response.store.name}</p>
                <p><strong>Salida:</strong> ${formatDateTime(response.departuring)}</p>
                <p><strong>Llegada:</strong> ${formatDateTime(response.arriving)}</p>
                <p><strong>Inicio de descarga:</strong> ${formatDateTime(response.unload_starting)}</p>
                <p><strong>Fin de descarga:</strong> ${formatDateTime(response.unload_finishing)}</p>
                <p><strong>Salida del sitio:</strong> ${formatDateTime(response.departure_from_site)}</p>
                <p><strong>Regreso a la planta:</strong> ${formatDateTime(response.return_to_plant)}</p>
            `);
        $('#deliveryDetailsModal').modal('show');
      },
      error: function (error) {
        Swal.fire({
          title: 'Error',
          text: error.responseJSON.error || 'Hubo un problema al obtener los detalles del envío.',
          icon: 'error',
          confirmButtonText: 'Aceptar'
        });
      }
    });
  });

  $(document).on('click', '.delete-dispatch-note', function (e) {
    e.preventDefault();
    const dispatchNoteId = $(this).data('dispatch-note-id');
    const card = $(this).closest('.col-md-4');

    Swal.fire({
      title: '¿Está seguro?',
      text: "Esta acción eliminará el remito y no se puede deshacer",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: `${window.baseUrl}admin/dispatch-notes/${dispatchNoteId}`,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': window.csrfToken
          },
          success: function (response) {
            card.fadeOut(300, function () {
              $(this).remove();
            });
            Swal.fire(
              '¡Eliminado!',
              'El remito ha sido eliminado.',
              'success'
            );
          },
          error: function () {
            Swal.fire(
              'Error',
              'No se pudo eliminar el remito',
              'error'
            );
          }
        });
      }
    });
  });

  $(document).on('click', '.edit-dispatch-note', function (e) {
    e.preventDefault();
    const dispatchNoteId = $(this).data('dispatch-note-id');
    const editDeliveryUrl = `${baseUrl}admin/note-deliveries/${dispatchNoteId}`;
  
    $.ajax({
      url: editDeliveryUrl,
      type: 'GET',
      success: function (response) {
        $('#editDeliveryForm #note_delivery_id').val(response.id);
        $('#editDeliveryForm #edit_dispatch_note_id').val(response.dispatch_note_id);
        $('#editDeliveryForm #edit_vehicle').val(`${response.vehicle.number} - ${response.vehicle.plate}`);
        $('#editDeliveryForm #edit_vehicle_id').val(response.vehicle_id);
        $('#editDeliveryForm #edit_driver').val(`${response.driver.name} - ${response.driver.document}`);
        $('#editDeliveryForm #edit_driver_id').val(response.driver_id);
        $('#editDeliveryForm #edit_store').val(response.store.name);
        $('#editDeliveryForm #edit_store_id').val(response.store_id);
  
        const formatDateTime = (dateString) => {
          if (!dateString) return '';
          const date = new Date(dateString);
          const year = date.getFullYear();
          const month = String(date.getMonth() + 1).padStart(2, '0');
          const day = String(date.getDate()).padStart(2, '0');
          const hours = String(date.getHours()).padStart(2, '0');
          const minutes = String(date.getMinutes()).padStart(2, '0');
          return `${year}-${month}-${day}T${hours}:${minutes}`;
        };
  
        $('#editDeliveryForm #edit_departuring').val(formatDateTime(response.departuring));
        $('#editDeliveryForm #edit_arriving').val(formatDateTime(response.arriving));
        $('#editDeliveryForm #edit_unload_starting').val(formatDateTime(response.unload_starting));
        $('#editDeliveryForm #edit_unload_finishing').val(formatDateTime(response.unload_finishing));
        $('#editDeliveryForm #edit_departure_from_site').val(formatDateTime(response.departure_from_site));
        $('#editDeliveryForm #edit_return_to_plant').val(formatDateTime(response.return_to_plant));
  
        // Si el usuario no tiene el permiso, bloquea solo los campos que ya tienen valor
        if (!window.hasEditDeliveryData) {
          let fields = [
            '#edit_departuring',
            '#edit_arriving',
            '#edit_unload_starting',
            '#edit_unload_finishing',
            '#edit_departure_from_site',
            '#edit_return_to_plant'
          ];
          fields.forEach(function (field) {
            if ($(field).val()) {
              $(field).prop('readonly', true);
            } else {
              $(field).prop('readonly', false);
            }
          });
        }
  
        $('#offcanvasEditDelivery').offcanvas('show');
      },
      error: function (error) {
        Swal.fire({
          title: 'Error',
          text: error.responseJSON.error || 'Hubo un problema al obtener la información del envío.',
          icon: 'error',
          confirmButtonText: 'Aceptar'
        });
      }
    });
  });

  $('#editDeliveryForm').on('submit', function (e) {
    e.preventDefault();
    
    const formData = $(this).serialize();
    const noteDeliveryId = $('#note_delivery_id').val();
    const updateDeliveryUrl = `${baseUrl}admin/note-deliveries/${noteDeliveryId}`;

    $.ajax({
      url: updateDeliveryUrl,
      type: 'PUT',
      data: formData,
      headers: {
        'X-CSRF-TOKEN': window.csrfToken
      },
      success: function (response) {
        $('#offcanvasEditDelivery').offcanvas('hide');
        Swal.fire({
          title: '¡Éxito!',
          text: 'El envío ha sido actualizado correctamente.',
          icon: 'success',
          confirmButtonText: 'Aceptar'
        }).then((result) => {
          if (result.isConfirmed) {
            location.reload();
          }
        });
      },
      error: function (error) {
        $('#offcanvasEditDelivery').offcanvas('hide');
        Swal.fire({
          title: 'Error',
          text: error.responseJSON.error || 'Hubo un problema al actualizar el envío.',
          icon: 'error',
          confirmButtonText: 'Aceptar'
        });
      }
    });
  });
});