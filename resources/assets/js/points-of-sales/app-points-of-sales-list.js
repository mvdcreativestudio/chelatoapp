$(document).ready(function () {
  $('#cash-registers-table').DataTable({
    order: [[0, 'desc']],
    language: {
      processing: 'Procesando...',
      search: 'Buscar:',
      lengthMenu: 'Mostrar _MENU_ registros',
      info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
      infoEmpty: 'Mostrando 0 a 0 de 0 registros',
      infoFiltered: '(filtrado de _MAX_ registros totales)',
      loadingRecords: 'Cargando...',
      zeroRecords: 'No se encontraron registros coincidentes',
      emptyTable: 'No hay datos disponibles en la tabla',
      paginate: {
        first: 'Primero',
        previous: 'Anterior',
        next: 'Siguiente',
        last: 'Último'
      }
    }
  });

  // var authenticatedUserId = @json($userId);
  // Mostrar el modal de crear al hacer clic en el botón de crear caja
  $('#crear-caja-btn').click(function () {
    $('#crearCajaModal').modal('show');
  });

  // Obtener los IDs de las tiendas para la caja registradora
  $.ajax({
    url: 'point-of-sale/stores',
    type: 'GET',
    success: function (response) {
      var storeIds = response; // Array con los IDs de las tiendas

      if (storeIds.length === 0) {
        // Si el array está vacío, ocultar el botón de crear caja
        $('#crear-caja-btn').hide();
      } else {
        // Crear un select con las opciones
        var select = $('<select>', {
          class: 'form-control',
          id: 'store_id',
          name: 'store_id',
          required: true
        });

        // Añadir las opciones de las tiendas
        $.each(storeIds, function (index, store) {
          select.append(
            $('<option>', {
              value: store.id,
              text: store.name, // Usar el nombre de la tienda para mostrar en el select
              selected: index === 0 // Seleccionar la primera tienda por defecto
            })
          );
        });

        $('#crearCajaModal .modal-body .mb-3').html(select);

        $('#crear-caja-btn').click(function () {
          $('#crearCajaModal').modal('show');
        });
      }
    },
    error: function (xhr, status, error) {
      const textToObject = JSON.parse(xhr.responseText);
      showModalError($('#crearCajaModal'), textToObject.message);
    }
  });

  // Mostrar mensaje de error en el modal correspondiente
  function showModalError(modal, message) {
    var errorMessage = $('<div>', {
      class: 'alert alert-danger mt-2',
      text: message
    });
    // Eliminar mensajes de error anteriores
    modal.find('.alert').remove();
    // Añadir nuevo mensaje de error
    modal.find('.modal-body').prepend(errorMessage);
  }

  // Enviar los datos de la nueva caja registradora al servidor
  $('#submit-crear-caja').click(function () {
    var storeId = $('#store_id').val();
    if (!storeId) {
      showModalError($('#crearCajaModal'), 'Por favor, seleccione una tienda.');
      return;
    }

    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
      url: 'points-of-sales',
      type: 'POST',
      data: {
        store_id: storeId,
        user_id: authenticatedUserId,
        _token: csrfToken
      },
      success: function (response) {
        $('#crearCajaModal').modal('hide');
        location.reload(); // Recargar la página para reflejar los cambios
      },
      error: function (xhr, status, error) {
        const textToObject = JSON.parse(xhr.responseText);
        showModalError($('#crearCajaModal'), textToObject.message);
      }
    });
  });

  // Mostrar el modal para abrir la caja con el monto inicial
  $('.btn-open').click(function () {
    var cashRegisterId = $(this).data('id');
    $('#cash_register_id').val(cashRegisterId);
    $('#abrirCajaModal').modal('show');
  });

  // Enviar los datos para abrir la caja registradora
  $('#submit-abrir-caja').click(function () {
    var cashRegisterId = $('#cash_register_id').val();
    var initialAmount = $('#initial_amount').val();
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    if (!initialAmount) {
      showModalError($('#abrirCajaModal'), 'Por favor, ingrese un monto inicial.');
      return;
    }

    $.ajax({
      url: 'pdv/open',
      type: 'POST',
      data: {
        cash_register_id: cashRegisterId,
        cash_float: initialAmount,
        _token: csrfToken
      },
      success: function (response) {
        $('#abrirCajaModal').modal('hide');
        window.location.href = '/admin/pdv/front'; // Redirigir a la página de PDV
      },
      error: function (xhr, status, error) {
        const textToObject = JSON.parse(xhr.responseText);
        showModalError($('#abrirCajaModal'), textToObject.message);
      }
    });
  });

  // Manejar la eliminación de la caja registradora
  $('.btn-delete').click(function () {
    var id = $(this).data('id');
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    if (confirm('¿Estás seguro de que deseas eliminar esta caja registradora?')) {
      $.ajax({
        url: 'points-of-sales/' + id,
        type: 'DELETE',
        data: {
          _token: csrfToken
        },
        success: function (response) {
          location.reload(); // Recargar la página para reflejar los cambios
        },
        error: function (xhr, status, error) {
          showModalError($('#editarCajaModal'), 'Error al eliminar la caja registradora: ' + xhr.responseText);
        }
      });
    }
  });

  // Mostrar el modal de cierre al hacer clic en el botón "Cerrar"
  $('.btn-closed').click(function () {
    var cashRegisterId = $(this).data('id'); // Obtener el ID de la caja registradora
    $('#cash_register_id_close').val(cashRegisterId); // Asignar el ID al campo oculto en el modal
    $('#cerrarCajaModal').modal('show'); // Mostrar el modal de cierre de caja
  });

  // Enviar la solicitud para cerrar la caja registradora al hacer clic en el botón "Cerrar"
  $('#submit-cerrar-caja').click(function () {
    var cashRegisterId = $('#cash_register_id_close').val(); // Obtener el ID de la caja a cerrar
    var csrfToken = $('meta[name="csrf-token"]').attr('content'); // Obtener el token CSRF

    $.ajax({
      url: 'pdv/close/' + cashRegisterId, // URL de la solicitud para cerrar la caja
      type: 'POST',
      data: {
        _token: csrfToken // Token CSRF para seguridad
      },
      success: function (response) {
        $('#cerrarCajaModal').modal('hide'); // Ocultar el modal al cerrar la caja
        location.reload(); // Recargar la página para reflejar los cambios
      },
      error: function (xhr, status, error) {
        showModalError($('#cerrarCajaModal'), 'Error al cerrar la caja registradora: ' + xhr.responseText);
      }
    });
  });

  // Mercado pago

  // Mostrar el modal para editar POS
  $('.btn-edit-pos').click(function () {
    const posId = $(this).data('id'); // ID del POS
    const storeId = $(this).data('store'); // ID de la tienda
    console.log(storeId);
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Obtener los datos del POS
    $.ajax({
      url: `point-of-sale/mercado-pago/edit-pos/${posId}`,
      type: 'GET',
      headers: {
        'X-CSRF-TOKEN': csrfToken
      },
      success: function (data) {
        console.log(data);
        // Cargar los datos en los campos del modal
        $('#edit_pos_id').val(posId);
        $('#edit_store_id').val(storeId);
        $('#edit_pos_name').val(data.mercadopago_pos?.name);

        // Mostrar el modal
        $('#editarPosModal').modal('show');
      },
      error: function (xhr) {
        alert('Error al cargar los datos del POS.');
      }
    });
  });

  // Enviar los datos actualizados al backend
  $('#submit-editar-pos').click(function () {
    const posId = $('#edit_pos_id').val(); // ID del POS (o del store si es necesario)
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    const updatedData = {
      name: $('#edit_pos_name').val(),
      store_id: $('#edit_store_id').val(),
    };

    // Mostrar Swal de loading
    Swal.fire({
      title: 'Guardando...',
      text: 'Por favor, espera mientras se actualiza el POS.',
      icon: 'info',
      allowOutsideClick: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    // Cierra el modal antes de enviar la solicitud
    $('#editarPosModal').modal('hide');

    $.ajax({
      url: `point-of-sale/mercado-pago/update-pos/${posId}`,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken
      },
      data: updatedData,
      success: function () {
        Swal.close(); // Cierra el Swal de loading
        Swal.fire({
          icon: 'success',
          title: 'POS actualizado correctamente',
          showConfirmButton: false,
          timer: 1500
        }).then(() => {
          location.reload(); // Recargar la página
        });
      },
      error: function (xhr) {
        Swal.close(); // Cierra el Swal de loading
        Swal.fire({
          icon: 'error',
          title: 'Error al actualizar',
          text: 'Hubo un problema al actualizar el POS. Intenta nuevamente.'
        }).then(() => {
          // Reabrir el modal con los datos existentes
          $('#editarPosModal').modal('show');
        });
      }
    });
  });

  // Mostrar el modal para eliminar POS
  $('.btn-delete-pos').click(function () {
    const posId = $(this).data('id'); // ID del POS
    console.log(posId);
    // Mostrar el modal de confirmación
    Swal.fire({
      title: '¿Estás seguro?',
      text: 'Esta acción no se puede deshacer. ¿Deseas eliminar este POS?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        // Eliminar el POS
        deletePos(posId);
      }
    });
  });

  // Eliminar POS
  function deletePos(posId) {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Mostrar Swal de loading
    Swal.fire({
      title: 'Eliminando...',
      text: 'Por favor, espera mientras se elimina el POS.',
      icon: 'info',
      allowOutsideClick: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    $.ajax({
      url: `point-of-sale/mercado-pago/delete-pos/${posId}`,
      type: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken
      },
      success: function () {
        Swal.close(); // Cierra el Swal de loading
        Swal.fire({
          icon: 'success',
          title: 'POS eliminado correctamente',
          showConfirmButton: false,
          timer: 1500
        }).then(() => {
          location.reload(); // Recargar la página
        });
      },
      error: function (xhr) {
        Swal.close(); // Cierra el Swal de loading
        Swal.fire({
          icon: 'error',
          title: 'Error al eliminar',
          text: 'Hubo un problema al eliminar el POS. Intenta nuevamente.'
        });
      }
    });
  }
});
