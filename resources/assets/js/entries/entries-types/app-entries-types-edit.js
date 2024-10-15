$(document).ready(function () {
  // Abrir modal para editar tipo de asiento
  $('.datatables-entry-types tbody').on('click', '.edit-record', function () {
    var recordId = $(this).data('id');
    prepareEditModal(recordId);
  });

  // Manejar el evento submit del formulario para evitar el comportamiento predeterminado
  $('#editEntryTypeForm').on('submit', function (e) {
    e.preventDefault();
    var recordId = $('#updateEntryTypeBtn').data('id');
    submitEditEntryType(recordId);
  });

  // Enviar formulario de edición al hacer clic en el botón de guardar cambios
  $('#editEntryTypeModal').on('click', '#updateEntryTypeBtn', function (e) {
    e.preventDefault();
    $('#editEntryTypeForm').submit();
  });

  function prepareEditModal(recordId) {
    // Función para preparar el modal de edición de tipo de asiento
    $.ajax({
      url: `${baseUrl}admin/entry-types/${recordId}/edit`,
      type: 'GET',
      success: function (data) {
        // Rellenar los campos del formulario con los datos obtenidos
        $('#edit_name').val(data.name);
        $('#edit_description').val(data.description);

        // Mostrar el modal
        $('#editEntryTypeModal').modal('show');
        $('#updateEntryTypeBtn').data('id', recordId); // Asigna el ID del registro al botón de actualización
      },
      error: function () {
        Swal.fire('Error', 'No se pudo cargar el formulario de edición. Por favor, intenta de nuevo.', 'error');
      }
    });
  }

  function submitEditEntryType(recordId) {
    var formData = {
      'name': $('#edit_name').val(),
      'description': $('#edit_description').val(),
      '_token': $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
      url: `${baseUrl}admin/entry-types/${recordId}`,
      type: 'PUT',
      data: formData,
      success: function () {
        $('#editEntryTypeModal').modal('hide');
        Swal.fire('¡Actualizado!', 'El tipo de asiento ha sido actualizado con éxito.', 'success').then(result => {
          location.reload();
        });
      },
      error: function (xhr) {
        $('#editEntryTypeModal').modal('hide');

        var errorMessage =
          xhr.responseJSON && xhr.responseJSON.errors
            ? Object.values(xhr.responseJSON.errors).flat().join('\n')
            : 'Error desconocido al guardar.';

        var messageFormatted = '';
        if (xhr.responseJSON.message) {
          messageFormatted = xhr.responseJSON.message;
        } else {
          errorMessage.split('\n').forEach(function (message) {
            messageFormatted += '<div class="text-danger">' + message + '</div>';
          });
        }
        Swal.fire({
          icon: 'error',
          title: 'Error al guardar',
          html: messageFormatted
        }).then(result => {
          $('#editEntryTypeModal').modal('show');
        });
      }
    });
  }
});
